<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RedirectRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'status' => 'sometimes|in:ativo,inativo',
            'url' => 'required|url|max:2048',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'status.in' => 'O status deve ser "ativo" ou "inativo"',
            'url.url' => 'A URL deve ser um endereço válido',
            'url.max' => 'A URL não pode exceder 2048 caracteres',
            'url.required' => 'A URL é obrigatória'
        ];
    }
}
