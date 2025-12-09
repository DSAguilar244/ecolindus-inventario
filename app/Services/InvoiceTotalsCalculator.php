<?php

namespace App\Services;

use App\Models\Product;

class InvoiceTotalsCalculator
{
    /**
     * Calculate subtotal, tax total and grand total for given invoice items.
     * Items: array of ['product_id'=>..., 'quantity'=>..., 'unit_price'=>..., 'tax_rate'=>...]
     * Returns: ['subtotal' => float, 'tax' => float, 'total' => float]
     */
    public function calculate(array $items): array
    {
        $productIds = array_values(array_unique(array_filter(array_map(function ($it) {
            return $it['product_id'] ?? null;
        }, $items))));

        $productMap = [];
        if (!empty($productIds)) {
            $productMap = Product::whereIn('id', $productIds)->get()->keyBy('id');
        }

        $subtotal = 0.0;
        $taxTotal = 0.0;

        foreach ($items as $it) {
            $quantity = isset($it['quantity']) ? (float) $it['quantity'] : 0.0;
            $unitPrice = isset($it['unit_price']) ? (float) $it['unit_price'] : 0.0;
            $line = $quantity * $unitPrice;

            $taxRate = null;
            if (array_key_exists('tax_rate', $it) && $it['tax_rate'] !== null && $it['tax_rate'] !== '') {
                $taxRate = (int) $it['tax_rate'];
            } else {
                $taxRate = (int) ($productMap[$it['product_id']]->tax_rate ?? 0);
            }

            $tax = ($taxRate / 100) * $line;

            $subtotal += $line;
            $taxTotal += $tax;
        }

        $total = $subtotal + $taxTotal;

        return [
            'subtotal' => round($subtotal, 4),
            'tax' => round($taxTotal, 4),
            'total' => round($total, 4),
        ];
    }
}
