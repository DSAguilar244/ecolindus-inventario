<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:2000',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if (($this->ajax() || $this->wantsJson()) && $validator->errors()->has('name')) {
            $name = $this->input('name');
            $exists = Category::where('name', $name)->first();
            if ($exists) {
                $response = response()->json(['message' => 'Categoría ya existe', 'category' => $exists], 409);
                throw new HttpResponseException($response);
            }
        }

        parent::failedValidation($validator);
    }
}
