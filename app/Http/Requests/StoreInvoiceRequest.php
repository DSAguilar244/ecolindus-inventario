<?php

namespace App\Http\Requests;

use App\Rules\EcuadorIdentification;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    public function rules()
    {
        return [
            'customer_id' => 'nullable|integer|exists:customers,id',
            'customer.identification' => ['required_without:customer_id', new EcuadorIdentification],
            'customer.first_name' => 'nullable|string',
            'customer.last_name' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|in:0,15',
        ];
    }

    /**
    * Prepare the data for validation.
    * Remove any empty items that do not have a product selected so validation
    * only runs against real items added by the user. Also normalize numbers
    * and merge duplicate items (same product_id) summing quantities to avoid
    * creating duplicate invoice_items server-side.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $items = $this->input('items', []);
        if (! is_array($items)) {
            $items = [];
        }

        $filtered = array_values(array_filter($items, function ($it) {
            if (! is_array($it)) {
                return false;
            }
            // consider item valid if product_id is present and not empty
            if (isset($it['product_id']) && $it['product_id'] !== '' && $it['product_id'] !== null) {
                return true;
            }

            return false;
        }));

        // normalize quantity and unit_price if present (use reference to mutate items inline)
        foreach ($filtered as &$it) {
            if (isset($it['quantity'])) {
                $it['quantity'] = is_numeric($it['quantity']) ? (float) $it['quantity'] : $it['quantity'];
            }
            if (isset($it['unit_price'])) {
                $it['unit_price'] = is_numeric($it['unit_price']) ? (float) $it['unit_price'] : $it['unit_price'];
            }
            if (! isset($it['tax_rate']) || $it['tax_rate'] === '') {
                // Keep as null to allow server-side fallback to product default tax
                $it['tax_rate'] = null;
            }
        }
        // avoid reference side effects in subsequent foreach by unsetting
        unset($it);

        // Bulk fetch products for default prices to avoid N+1 queries
        $productIds = array_values(array_unique(array_filter(array_map(function ($it) {
            return $it['product_id'] ?? null;
        }, $filtered))));

        $productMap = [];
        if (!empty($productIds)) {
            $productMap = Product::whereIn('id', $productIds)->get()->keyBy('id');
        }

        // If a product_id is present and unit_price was missing, use the prefetched product price
        foreach ($filtered as &$it) {
            if (isset($it['product_id']) && (!isset($it['unit_price']) || $it['unit_price'] === '' || is_null($it['unit_price']))) {
                $p = $productMap[$it['product_id']] ?? null;
                if ($p) {
                    $it['unit_price'] = (float) $p->price;
                }
            }

            // default missing quantity to 1
            if (!isset($it['quantity']) || $it['quantity'] === '' || $it['quantity'] === null) {
                $it['quantity'] = 1;
            }

            // ensure quantity is float
            $it['quantity'] = is_numeric($it['quantity']) ? (float) $it['quantity'] : $it['quantity'];
        }
        // clean reference
        unset($it);

        // Merge duplicate items by product_id to avoid duplicate invoice items
        $merged = [];
        foreach ($filtered as $it) {
            $pid = $it['product_id'];
            if (! isset($merged[$pid])) {
                $merged[$pid] = $it;
            } else {
                // sum quantities
                $merged[$pid]['quantity'] = (float) $merged[$pid]['quantity'] + (float) $it['quantity'];
                // if merged doesn't have a price but this one does, set it
                if ((empty($merged[$pid]['unit_price']) || $merged[$pid]['unit_price'] === 0) && (!empty($it['unit_price']) || $it['unit_price'] === 0)) {
                    $merged[$pid]['unit_price'] = $it['unit_price'];
                }
                // prefer tax_rate if specified
                if (isset($it['tax_rate'])) {
                    // Always overwrite merged tax_rate with the latest specified value
                    $merged[$pid]['tax_rate'] = $it['tax_rate'];
                }
            }
        }

        $filtered = array_values($merged);

        // debug logging temporarily during tests was removed; keep logic quiet in production

        $this->merge(['items' => $filtered]);
    }
}
