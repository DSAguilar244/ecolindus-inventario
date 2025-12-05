<?php

namespace App\Http\Requests;

use App\Rules\EcuadorIdentification;
use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    public function rules()
    {
        return [
            'id_type' => 'nullable|string',
            'identification' => ['required', 'unique:customers,identification', new EcuadorIdentification],
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ];
    }
}
