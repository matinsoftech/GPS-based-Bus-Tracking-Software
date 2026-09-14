<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('subscription.update');
    }

    public function rules(): array
    {
        return [
            'school_id' => ['required', 'exists:schools,id'],
            'plan_id' => ['required', 'exists:plans,id'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'status' => [
                'nullable',
                'in:trialing,active,past_due,cancelled,expired',
                function ($attribute, $value, $fail) {
                    $subscription = $this->route('subscription');

                    if ($subscription?->status === 'past_due' && $value === 'active') {
                        $fail('A past due subscription cannot be changed back to active.');
                    }
                },
            ],
        ];
    }
}
