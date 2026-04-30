<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];

        if ($this->user()->role === 'farmer') {
            $rules['garden.name'] = ['nullable', 'string', 'max:255'];
            $rules['garden.address'] = ['nullable', 'string', 'max:500'];
            $rules['garden.description'] = ['nullable', 'string'];
            $rules['garden.instagram_link'] = ['nullable', 'string', 'max:255'];
            $rules['garden.facebook_link'] = ['nullable', 'string', 'max:255'];
            $rules['garden.youtube_link'] = ['nullable', 'string', 'max:255'];
            $rules['garden.whatsapp_number'] = ['nullable', 'string', 'max:255'];
            $rules['garden_photo'] = ['nullable', 'image', 'max:2048'];
            $rules['garden_cover'] = ['nullable', 'image', 'max:5120'];
            $rules['delete_garden_photo'] = ['nullable', 'boolean'];
            $rules['delete_garden_cover'] = ['nullable', 'boolean'];
            $rules['garden.bmkg_adm4_code'] = ['nullable', 'string', 'max:50'];
        }

        return $rules;
    }
}
