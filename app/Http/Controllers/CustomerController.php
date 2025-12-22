<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function index()
    {
        $query = Customer::query();
        if (request()->filled('q')) {
            $q = request('q');
            $driver = DB::getDriverName();
            $op = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function ($qr) use ($q, $op) {
                $qr->where('identification', $op, "%{$q}%")
                    ->orWhere('first_name', $op, "%{$q}%")
                    ->orWhere('last_name', $op, "%{$q}%");
            });
        }
        $customers = $query->orderBy('first_name')->paginate(20)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function exportPdf(Request $request)
    {
        $query = Customer::query();
        if ($request->filled('q')) {
            $q = $request->get('q');
            $driver = DB::getDriverName();
            $op = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function ($qr) use ($q, $op) {
                $qr->where('identification', $op, "%{$q}%")
                    ->orWhere('first_name', $op, "%{$q}%")
                    ->orWhere('last_name', $op, "%{$q}%");
            });
        }
        $customers = $query->orderBy('first_name')->get();
        if (app()->environment('testing')) {
            Log::debug('Customers CSV export result', ['names' => $customers->pluck('first_name')->toArray(), 'q' => $request->get('q')]);
        }
        // Use an export-friendly view (no heavy layout) to produce a clean PDF
        $pdf = Pdf::loadView('customers.export_pdf', compact('customers'));
        // set some reasonable options for output
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions(['dpi' => 150]);

        // use stream so the PDF opens in browser (target=_blank), or download if needed
        return $pdf->stream('ECOLINDUS_Clientes_'.now()->format('Ymd').'.pdf');
    }

    public function exportCsv(Request $request)
    {
        $query = Customer::query();
        if ($request->filled('q')) {
            $q = $request->get('q');
            $driver = DB::getDriverName();
            $op = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function ($qr) use ($q, $op) {
                $qr->where('identification', $op, "%{$q}%")
                    ->orWhere('first_name', $op, "%{$q}%")
                    ->orWhere('last_name', $op, "%{$q}%");
            });
        }
        $customers = $query->orderBy('first_name')->get();
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="ECOLINDUS_Clientes_'.now()->format('Ymd').'.csv"',
        ];

        $columns = ['identification', 'first_name', 'last_name', 'phone', 'email', 'address'];

        $rows = [];
        $rows[] = $columns;
        foreach ($customers as $c) {
            $rows[] = [$c->identification, $c->first_name, $c->last_name, $c->phone, $c->email, $c->address];
        }

        if (app()->environment('testing')) {
            // Build in-memory CSV string for easier assertions during testing
            $fp = fopen('php://temp', 'r+');
            foreach ($rows as $row) {
                fputcsv($fp, $row);
            }
            rewind($fp);
            $content = stream_get_contents($fp);
            fclose($fp);

            return response($content, 200, $headers);
        }

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            foreach ($rows as $r) {
                fputcsv($file, $r);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        $data = $request->validated();
        // prevent duplicates: if identification already exists, return appropriate response
        $exists = Customer::where('identification', $data['identification'])->first();
        if ($exists) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Cliente con esta identificación ya existe', 'customer' => $exists], 409);
            }

            return redirect()->route('customers.index')->with('error', 'Cliente con esta identificación ya existe');
        }

        $customer = Customer::create($data);
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['id' => $customer->id, 'text' => $customer->first_name.' '.$customer->last_name.' - '.$customer->identification, 'customer' => $customer], 201);
        }

        return redirect()->route('customers.index')->with('success', 'Cliente creado.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Cliente actualizado', 'customer' => $customer], 200);
        }

        return redirect()->route('customers.index')->with('success', 'Cliente actualizado.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Cliente eliminado.');
    }

    /**
     * AJAX search endpoint for Select2
     */
    public function search(Request $request)
    {
        $q = $request->get('q');
        $query = Customer::query();
        if ($q) {
            $driver = DB::getDriverName();
            $op = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function ($qry) use ($q, $op) {
                $qry->where('identification', $op, "%{$q}%")
                    ->orWhere('first_name', $op, "%{$q}%")
                    ->orWhere('last_name', $op, "%{$q}%");
            });
        }

        $customers = $query->orderBy('first_name')->limit(20)->get();

        $results = $customers->map(function ($c) {
            return [
                'id' => $c->id,
                'text' => $c->first_name.' '.$c->last_name.' - '.$c->identification,
                'identification' => $c->identification,
                'first_name' => $c->first_name,
                'last_name' => $c->last_name,
                'phone' => $c->phone,
                'email' => $c->email,
                'address' => $c->address,
            ];
        });

        return response()->json(['results' => $results]);
    }
}
