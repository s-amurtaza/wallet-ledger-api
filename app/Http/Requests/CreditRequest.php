<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'string',
                'min:1',
                'max:64',
                'regex:/\A[A-Za-z0-9_-]+\z/',
            ],

            'amount' => [
                'required',
                'integer',
                'min:1',
                'max:1000000000',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            if (!is_int($this->json('amount'))) {
                $validator->errors()->add(
                    'amount',
                    'Amount must be a JSON integer.'
                );
            }

        });
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'error' => 'invalid_request',
                'message' => 'Invalid request data',
            ], 400)
        );
    }
}
