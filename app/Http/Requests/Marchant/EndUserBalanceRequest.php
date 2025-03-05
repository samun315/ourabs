<?php

namespace App\Http\Requests\Marchant;

use Illuminate\Foundation\Http\FormRequest;

class EndUserBalanceRequest extends FormRequest
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
            'imo_id' => 'required',
            'amount' => 'required',
            'phone_number' => 'nullable',
        ];
    }

    public function fields(): array
    {
        $inputData = [];

        $inputData['imo_id'] = $this->input('imo_id');
        $inputData['amount'] = $this->input('amount');
        $inputData['phone_number'] = $this->input('phone_number') ?? null;

        $inputData['request_by'] = loggedInUserId();
        $inputData['created_by'] = loggedInUserId();
        $inputData['created_at'] = createdAtDateConvertToDB();

        return $inputData;
    }
}
