<?php

namespace App\Http\Requests;

use App\Enums\SubscriptionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Add / edit a subscription: Nume, Cost Lunar, Status (Activ sau Pauză).
 */
class SubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $subscription = $this->route('subscription');

        return $subscription === null || $this->user()->can('update', $subscription);
    }

    protected function prepareForValidation(): void
    {
        // Accept both "15.99" and "15,99".
        if (is_string($this->input('monthly_cost'))) {
            $this->merge(['monthly_cost' => str_replace(',', '.', trim($this->input('monthly_cost')))]);
        }

        $this->merge(['name' => trim((string) $this->input('name'))]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'monthly_cost' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999.99'],
            'status' => ['required', Rule::enum(SubscriptionStatus::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nume',
            'monthly_cost' => 'cost lunar',
            'status' => 'status',
        ];
    }
}
