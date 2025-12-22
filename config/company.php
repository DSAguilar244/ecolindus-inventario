<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Company Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for company details that appear in documents and invoices.
    |
    */

    'ruc' => env('COMPANY_RUC', '20000000000'),
    'name' => env('COMPANY_NAME', 'ECOLINDUS S.A.'),
    'address' => env('COMPANY_ADDRESS', ''),
    'phone' => env('COMPANY_PHONE', ''),
    'email' => env('COMPANY_EMAIL', ''),
    // For invoice printing: establecimiento (branch) and punto de emisión
    'establishment_number' => env('COMPANY_ESTABLISHMENT', '001'),
    'emission_number' => env('COMPANY_EMISSION', '001'),

];
