<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitiateChatRequest extends FormRequest
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
            // Option 1: Direct recipient (existing)
            'recipient_id' => 'required_without_all:merchant_id,courier_id,transaction_id,order_id|exists:users,id',
            'recipient_type' => 'required_without_all:merchant_id,courier_id,transaction_id,order_id|in:USER,MERCHANT,COURIER',
            
            // Option 2: By merchant_id (new - for customer app)
            'merchant_id' => 'required_without_all:recipient_id,courier_id,transaction_id|exists:merchants,id',
            
            // Option 3: By courier_id (new - for customer app)
            'courier_id' => 'required_without_all:recipient_id,merchant_id,transaction_id|exists:couriers,id',
            
            // Option 4: By transaction_id (new - for courier chat from transaction)
            'transaction_id' => 'required_without_all:recipient_id,merchant_id,courier_id|exists:transactions,id',
            
            'order_id' => 'nullable|exists:orders,id',
            'message' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'recipient_id.required' => 'ID penerima wajib diisi',
            'recipient_id.exists' => 'Penerima tidak ditemukan',
            'recipient_type.required' => 'Tipe penerima wajib diisi',
            'recipient_type.in' => 'Tipe penerima tidak valid',
            'order_id.exists' => 'Order tidak ditemukan',
            'transaction_id.exists' => 'Transaksi tidak ditemukan',
            'message.required' => 'Pesan wajib diisi',
            'message.max' => 'Pesan maksimal 1000 karakter',
        ];
    }
}
