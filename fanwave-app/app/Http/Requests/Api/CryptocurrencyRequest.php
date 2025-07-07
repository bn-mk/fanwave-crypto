<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CryptocurrencyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'limit' => 'sometimes|integer|min:1|max:100'
        ];

        if ($this->is('api/crypto/search')) {
            $rules['query'] = 'required|string|min:1|max:50';
        }

        return $rules;
    }
}

