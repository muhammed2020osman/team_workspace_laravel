<?php

declare(strict_types=1);

namespace LaraCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExampleRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $id = $this->route('example')?->id ?? null;

        $rules = [
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'description' => ['nullable', 'string', 'max:1000'],
            'email' => ['required', 'email', 'unique:examples'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
        ];

        // قواعد التحديث - تجاهل السجل الحالي في التحقق من التفرد
        if ($id) {
            $updateRules = [
                'email' => ['required', 'email', Rule::unique('examples')->ignore($id)],
            ];
            $rules = array_merge($rules, $updateRules);
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => __('Name'),
            'description' => __('Description'),
            'email' => __('Email'),
            'phone' => __('Phone'),
            'is_active' => __('Status'),
            'category_id' => __('Category'),
            'tags' => __('Tags'),
            'image' => __('Image'),
            'price' => __('Price'),
            'start_date' => __('Start Date'),
            'end_date' => __('End Date'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // تحويل القيم النصية إلى boolean
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        // تنظيف رقم الهاتف
        if ($this->has('phone')) {
            $phone = preg_replace('/[^0-9+]/', '', $this->phone);
            $this->merge(['phone' => $phone]);
        }

        // تحويل المصفوفات من JSON إذا لزم الأمر
        if ($this->has('tags') && is_string($this->tags)) {
            $this->merge([
                'tags' => json_decode($this->tags, true) ?? [],
            ]);
        }
    }
}
