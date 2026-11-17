<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_open' => 'nullable|boolean',
            'wa_number' => 'required|string|max:30|regex:/^[0-9+]+$/',
            'address' => 'required|string|max:500',
            'opening_hours' => 'required|string|max:255',
            'packaging_fee' => 'required|integer|min:0',
            'maps_url' => 'nullable|url|max:1000',
            'review_url' => 'nullable|url|max:1000',
            'store_lat' => 'nullable|numeric|between:-90,90',
            'store_lng' => 'nullable|numeric|between:-180,180',
            'delivery_enabled' => 'nullable|boolean',
            'delivery_radius_m' => 'required|integer|min:100|max:10000',
        ];
    }

    public function messages(): array
    {
        return [
            'wa_number.required' => 'Nomor WhatsApp kedai wajib diisi.',
            'wa_number.regex' => 'Nomor WhatsApp hanya boleh berisi angka dan tanda plus (+).',
            'address.required' => 'Alamat lengkap kedai wajib diisi.',
            'opening_hours.required' => 'Jam operasional wajib diisi.',
            'packaging_fee.required' => 'Biaya kemasan takeaway wajib diisi.',
            'packaging_fee.integer' => 'Biaya kemasan harus berupa angka.',
            'delivery_radius_m.required' => 'Batas radius pesan antar wajib diisi.',
        ];
    }
}
