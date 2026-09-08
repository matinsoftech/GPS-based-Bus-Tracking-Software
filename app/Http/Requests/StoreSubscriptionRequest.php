<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('subscription.create');
    }

    public function rules(): array
    {
        return [
            'school_id' => ['required', 'exists:schools,id'],
            'plan_id' => ['required', 'exists:plans,id'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'status' => ['nullable', 'in:trialing,active'],
        ];
    }

    public function messages(): array
    {
        return [
            'school_id.required' => 'Please select a school.',
            'plan_id.required' => 'Please select a plan.',
            'billing_cycle.required' => 'Please select a billing cycle.',
            'billing_cycle.in' => 'Billing cycle must be monthly or yearly.',
        ];
    }
}
