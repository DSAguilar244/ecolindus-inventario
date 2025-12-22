<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;

class MonthlyReportExport
{
    protected $dateFrom;
    protected $dateTo;
    protected $invoices;
    protected $totalSales;
    protected $totalIva;
    protected $byCash;
    protected $byTransfer;
    protected $countEmitted;
    protected $countPending;

    public function __construct($dateFrom, $dateTo, $invoices, $totalSales, $totalIva, $byCash, $byTransfer, $countEmitted, $countPending)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->invoices = $invoices;
        $this->totalSales = $totalSales;
        $this->totalIva = $totalIva;
        $this->byCash = $byCash;
        $this->byTransfer = $byTransfer;
        $this->countEmitted = $countEmitted;
        $this->countPending = $countPending;
    }

    public function view(): View
    {
        return view('exports.monthly-report', [
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'invoices' => $this->invoices,
            'totalSales' => $this->totalSales,
            'totalIva' => $this->totalIva,
            'byCash' => $this->byCash,
            'byTransfer' => $this->byTransfer,
            'countEmitted' => $this->countEmitted,
            'countPending' => $this->countPending,
        ]);
    }
}
