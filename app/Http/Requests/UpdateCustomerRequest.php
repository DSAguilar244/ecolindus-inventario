<?php

namespace App\Http\Requests;

use App\Rules\EcuadorIdentification;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    public function rules()
    {
        $customerId = $this->route('customer')?->id ?? null;
        $identificationRules = ['required', 'unique:customers,identification,'.($customerId ?? 'NULL')];
        // Only re-validate Ecuador identification if the identification has changed
        $originalId = (string) ($this->route('customer')?->identification ?? '');
        $inputId = (string) ($this->input('identification') ?? '');
        if ($this->input('identification') !== null && $inputId !== $originalId) {
            $identificationRules[] = new EcuadorIdentification;
        }

        return [
            'id_type' => 'nullable|string',
            'identification' => $identificationRules,
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ];
    }
}
