<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    public function rules()
    {
        $categoryId = $this->route('category')?->id ?? null;

        return [
            'name' => ['required', 'string', 'max:255', "unique:categories,name,{$categoryId}"],
            'description' => 'nullable|string|max:2000',
        ];
    }
}
