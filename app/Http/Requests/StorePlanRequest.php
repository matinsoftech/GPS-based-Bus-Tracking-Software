<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('plan.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:plans,name'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'yearly_price' => ['required', 'numeric', 'min:0'],
            'max_buses' => ['nullable', 'integer', 'min:0'],
            'max_students' => ['nullable', 'integer', 'min:0'],
            'max_parents' => ['nullable', 'integer', 'min:0'],
            'max_drivers' => ['nullable', 'integer', 'min:0'],
            'max_routes' => ['nullable', 'integer', 'min:0'],
            'max_devices' => ['nullable', 'integer', 'min:0'],
            'feature_live_tracking' => ['nullable', 'boolean'],
            'feature_parent_app' => ['nullable', 'boolean'],
            'feature_notifications' => ['nullable', 'boolean'],
            'feature_attendance' => ['nullable', 'boolean'],
            'feature_reports' => ['nullable', 'boolean'],
            'feature_analytics' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Plan name is required.',
            'name.unique' => 'A plan with this name already exists.',
            'monthly_price.required' => 'Monthly price is required.',
            'monthly_price.numeric' => 'Monthly price must be a number.',
            'yearly_price.required' => 'Yearly price is required.',
            'yearly_price.numeric' => 'Yearly price must be a number.',
        ];
    }
}
