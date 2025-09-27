<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TransferRequest extends FormRequest
{
    public function rules(): array
    {
        $precision = Transaction::PRECISION;
        $regex = "/^\\d+(\\.\\d{1,{$precision}})?$/";

        return [
            'receiver_id' => [
                'required',
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail): void {
                    if ((int) $value === Auth::id()) {
                        $fail('The receiver cannot be the same as the sender.');
                    }
                },
            ],
            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'regex:' . $regex,
            ],
        ];
    }

    #[\Override]
    public function messages(): array
    {
        return [
            'amount.regex' => 'The amount must be a valid monetary value with up to ' . Transaction::PRECISION . ' decimal places.',
        ];
    }
}
