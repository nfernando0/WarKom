<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PaymentWebhookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invoice_number' => ['required_without:gateway_reference_id', 'string', 'max:50'],
            'gateway_reference_id' => ['nullable', 'string', 'max:100'],
            'payment_status' => ['required', 'in:unpaid,pending,settlement,expired,failed,refunded'],
            'payment_channel' => ['nullable', 'string', 'max:50'],
            'paid_amount' => ['nullable', 'numeric'],
            'paid_at' => ['nullable', 'date'],
        ];
    }
}
