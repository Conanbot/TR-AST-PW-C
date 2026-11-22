<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MenuItemRequest extends FormRequest

{

    public function authorize(): bool

    {

        return true;

    }

    public function rules(): array

    {

        $rules = [

            'category_id' => 'required|exists:categories,id',

            'name' => 'required|string|max:255',

            'description' => 'nullable|string|max:1000',

            'price' => 'required|integer|min:0',

            'is_available' => 'nullable|boolean',

            'is_best_seller' => 'nullable|boolean',

            'is_package' => 'nullable|boolean',

            'sort_order' => 'nullable|integer|min:0',

            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

        ];

        return $rules;

    }

    public function messages(): array

    {

        return [

            'name.required' => 'Nama menu wajib diisi.',

            'category_id.required' => 'Kategori menu wajib dipilih.',

            'category_id.exists' => 'Kategori yang dipilih tidak valid.',

            'price.required' => 'Harga menu wajib diisi.',

            'price.integer' => 'Harga harus berupa bilangan bulat rupiah.',

            'price.min' => 'Harga tidak boleh negatif.',

            'image.image' => 'Berkas yang diunggah harus berupa gambar.',

            'image.mimes' => 'Format gambar harus JPG, JPEG, atau PNG.',

            'image.max' => 'Ukuran gambar maksimal 2 MB (2048 KB).',

        ];

    }

}

