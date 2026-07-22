<?php

namespace App\Http\Requests\Client;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SubmitOfflinePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'bank_name' => trim((string) $this->input('bank_name')),
            'account_holder' => trim((string) $this->input('account_holder')),
            'payment_reference' => strtoupper(trim((string) $this->input('payment_reference'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'plan_id' => [
                'required',
                'integer',
                Rule::exists('plans', 'id')->where(
                    fn (Builder $query): Builder => $query->where('is_active', true)->where('price', '>', 0),
                ),
            ],
            'bank_name' => ['required', 'string', 'max:120'],
            'account_holder' => ['required', 'string', 'max:120'],
            'payment_reference' => ['required', 'string', 'max:120'],
            'receipt' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',
            ],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $receipt = $this->file('receipt');

            if (! $receipt || ! str_starts_with((string) $receipt->getMimeType(), 'image/')) {
                return;
            }

            $dimensions = $receipt->dimensions();

            if ($dimensions === false) {
                $validator->errors()->add('receipt', 'The receipt image could not be decoded safely.');

                return;
            }

            [$width, $height] = $dimensions;

            if ($width < 200 || $height < 200 || $width > 4096 || $height > 4096) {
                $validator->errors()->add(
                    'receipt',
                    'Receipt images must be between 200×200 and 4096×4096 pixels.',
                );
            }
        }];
    }
}
