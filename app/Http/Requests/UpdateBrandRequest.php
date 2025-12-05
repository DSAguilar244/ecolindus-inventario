<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    public function rules()
    {
        $brandId = $this->route('brand')?->id ?? null;

        return [
            'name' => ['required', 'string', 'max:255', "unique:brands,name,{$brandId}"],
            'description' => 'nullable|string|max:2000',
        ];
    }
}
