<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class BulkStoreInvoiceRequest extends FormRequest
{

public function authorize():bool{
    $user = $this->user();
    return $user != null && $user->tokenCan('delete');
}
    /**
     * Determine if the user is authorized to make this request.
     */public function rules(): array
{
    return [
        '*.customerId' => ['required', 'integer'],
        '*.amount' => ['required', 'numeric'],
        '*.status' => ['required', Rule::in(['B', 'P', 'V', 'b', 'p', 'v'])],
        '*.billedDate' => ['required', 'date_format:Y-m-d H:i:s'],
        '*.paidDate' => ['date_format:Y-m-d H:i:s', 'nullable'],
    ];
}

protected function prepareForValidation()
{
    $data = [];
    foreach ($this->toArray() as $obj) {
        $obj['customer_id'] = $obj['customerId'] ?? null;
        $obj['billed_dated'] = $obj['billedDate'] ?? null;
        $obj['paid_dated'] = $obj['paidDate'] ?? null;
        $data[] = $obj;
    }

    $this->merge($data);
}
}
