<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class PromoRequest extends FormRequest

{

    public function authorize(): bool

    {

        return true;

    }

    protected function prepareForValidation(): void

    {

        $this->merge([

            'discount_type' => $this->input('discount_type') ?: 'fixed',

            'discount_value' => $this->input('discount_value') !== null && $this->input('discount_value') !== '' ? (int)$this->input('discount_value') : 0,

        ]);

    }

    public function rules(): array

    {

        return [

            'title' => 'required|string|max:255',

            'subtitle' => 'nullable|string|max:1000',

            'code' => [

                'nullable',

                'string',

                'max:50',

                'regex:/^[A-Za-z0-9_-]+$/',

                Rule::unique('promos', 'code')->ignore($this->route('promo')),

            ],

            'description' => 'nullable|string|max:1000',

            'discount_type' => 'required|in:fixed,percentage,free_delivery',

            'discount_value' => 'required|integer|min:0',

            'minimum_subtotal' => 'nullable|integer|min:0',

            'maximum_discount' => 'nullable|required_if:discount_type,percentage|integer|min:0',

            'usage_limit' => 'nullable|integer|min:0',

            'per_customer_limit' => 'nullable|integer|min:0',

            'stackable' => 'nullable|boolean',

            'is_active' => 'nullable|boolean',

            'start_date' => 'nullable|date',

            'end_date' => 'nullable|date|after_or_equal:start_date',

            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

        ];

    }

    public function withValidator($validator): void

    {

        $validator->after(function ($validator) {

            if ($this->input('discount_type') === 'percentage' && (int) $this->input('discount_value') > 100) {

                $validator->errors()->add('discount_value', 'Persentase diskon tidak boleh lebih dari 100%.');

            }

        });

    }

    public function messages(): array

    {

        return [

            'title.required' => 'Judul promo wajib diisi.',

            'end_date.after_or_equal' => 'Tanggal akhir promo harus setelah atau sama dengan tanggal mulai.',

            'image.image' => 'Berkas harus berupa gambar.',

            'image.mimes' => 'Format gambar harus JPG, JPEG, atau PNG.',

            'image.max' => 'Ukuran gambar maksimal 2 MB.',

        ];

    }

}

