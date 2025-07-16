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
            'page' => 'sometimes|integer',
            'per_page' => 'sometimes|integer',
            'status' => 'sometimes|in:ativo,inativo',
            'url' => 'sometimes|url|max:2048',
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
            'page.integer' => 'A página deve ser um número inteiro',
            'per_page.integer' => 'O número de itens por página deve ser um número inteiro',
            'status.in' => 'O status deve ser "ativo" ou "inativo"',
            'url.url' => 'A URL deve ser um endereço válido',
            'url.max' => 'A URL não pode exceder 2048 caracteres',
        ];
    }
}
