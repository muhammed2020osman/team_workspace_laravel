<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayrollRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period' => 'required|string|max:10',
            'period_name' => 'required|string|max:255',
            'working_days' => 'required|integer|min:20|max:31',
            'status' => 'required|in:draft,approved,paid,cancelled',
            'driver_payrolls' => 'required|array|min:1',
            'driver_payrolls.*.driver_id' => 'required|exists:drivers,id',
            'driver_payrolls.*.driver_name' => 'required|string|max:255',
            'driver_payrolls.*.base_salary' => 'required|numeric|min:0',
            'driver_payrolls.*.target_bonus' => 'nullable|numeric|min:0',
            'driver_payrolls.*.working_days_actual' => 'required|integer|min:0|max:31',
            'driver_payrolls.*.absence_days' => 'nullable|integer|min:0',
            'driver_payrolls.*.overtime_hours' => 'nullable|integer|min:0',
            'driver_payrolls.*.total_earnings' => 'required|numeric|min:0',
            'driver_payrolls.*.total_deductions' => 'nullable|numeric|min:0',
            'driver_payrolls.*.net_salary' => 'required|numeric|min:0',
            'driver_payrolls.*.payment_status' => 'required|in:pending,paid,cancelled',
            'driver_payrolls.*.allowances' => 'nullable|array',
            'driver_payrolls.*.allowances.*.name' => 'required|string|max:255',
            'driver_payrolls.*.allowances.*.amount' => 'required|numeric|min:0',
            'driver_payrolls.*.allowances.*.type' => 'required|in:monthly,daily,one_time',
            'driver_payrolls.*.deductions' => 'nullable|array',
            'driver_payrolls.*.deductions.*.name' => 'required|string|max:255',
            'driver_payrolls.*.deductions.*.amount' => 'required|numeric|min:0',
            'driver_payrolls.*.deductions.*.type' => 'required|in:fixed,percentage,daily,expenses,advances',
            'driver_payrolls.*.deductions.*.reason' => 'nullable|string',
            'driver_payrolls.*.manual_adjustments' => 'nullable|array',
            'driver_payrolls.*.manual_adjustments.*.name' => 'required|string|max:255',
            'driver_payrolls.*.manual_adjustments.*.amount' => 'required|numeric|min:0',
            'driver_payrolls.*.manual_adjustments.*.type' => 'required|in:addition,subtraction',
            'driver_payrolls.*.manual_adjustments.*.reason' => 'nullable|string',
            'summary' => 'nullable|array',
            'summary.total_drivers' => 'nullable|integer|min:0',
            'summary.total_net_salaries' => 'nullable|numeric|min:0',
            'summary.total_working_days' => 'nullable|integer|min:0',
            'summary.total_absence_days' => 'nullable|integer|min:0',
            'summary.total_expenses' => 'nullable|numeric|min:0',
            'summary.total_advances' => 'nullable|numeric|min:0',
            'summary.total_deductions' => 'nullable|numeric|min:0'
        ];
    }

    public function messages(): array
    {
        return [
            'period.required' => 'فترة الراتب مطلوبة',
            'period_name.required' => 'اسم فترة الراتب مطلوب',
            'working_days.required' => 'عدد أيام العمل مطلوب',
            'working_days.min' => 'عدد أيام العمل يجب أن يكون 20 يوم على الأقل',
            'working_days.max' => 'عدد أيام العمل يجب أن يكون 31 يوم كحد أقصى',
            'driver_payrolls.required' => 'بيانات رواتب السائقين مطلوبة',
            'driver_payrolls.min' => 'يجب أن يكون هناك سائق واحد على الأقل',
            'driver_payrolls.*.driver_id.required' => 'معرف السائق مطلوب',
            'driver_payrolls.*.driver_id.exists' => 'السائق غير موجود في النظام',
            'driver_payrolls.*.base_salary.required' => 'الراتب الأساسي مطلوب',
            'driver_payrolls.*.base_salary.min' => 'الراتب الأساسي يجب أن يكون صفر أو أكثر',
            'driver_payrolls.*.net_salary.required' => 'صافي الراتب مطلوب',
            'driver_payrolls.*.net_salary.min' => 'صافي الراتب يجب أن يكون صفر أو أكثر'
        ];
    }
}
