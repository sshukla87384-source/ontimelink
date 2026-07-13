<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // guests allowed for their single free link; quota enforced in controller
    }

    public function rules(): array
    {
        return [
            'destination' => ['required', 'string', 'max:2048', 'url:http,https'],
            'label' => ['nullable', 'string', 'max:120'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ];
    }

    public function messages(): array
    {
        return [
            'destination.url' => 'Enter a full URL starting with http:// or https://.',
        ];
    }
}
