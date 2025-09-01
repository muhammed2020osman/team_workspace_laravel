<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Driver;
use App\Models\PayrollRun;
use App\Models\DriverPayroll;
use App\Models\Allowance;
use App\Models\Deduction;
use App\Models\ManualAdjustment;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء سائقين تجريبيين
        $drivers = [
            [
                'name' => 'أحمد محمد',
                'phone' => '0501234567',
                'email' => 'ahmed@example.com',
                'national_id' => '1234567890',
                'license_number' => 'DL001',
                'license_expiry' => '2026-12-31',
                'salary' => 3000.00,
                'status' => 'active'
            ],
            [
                'name' => 'محمد علي',
                'phone' => '0502345678',
                'email' => 'mohamed@example.com',
                'national_id' => '0987654321',
                'license_number' => 'DL002',
                'license_expiry' => '2026-11-30',
                'salary' => 3500.00,
                'status' => 'active'
            ],
            [
                'name' => 'علي أحمد',
                'phone' => '0503456789',
                'email' => 'ali@example.com',
                'national_id' => '1122334455',
                'license_number' => 'DL003',
                'license_expiry' => '2026-10-31',
                'salary' => 2800.00,
                'status' => 'active'
            ]
        ];

        foreach ($drivers as $driverData) {
            Driver::create($driverData);
        }

        // إنشاء مسير رواتب تجريبي
        $payrollRun = PayrollRun::create([
            'period' => '2025-01',
            'period_name' => 'رواتب شهر يناير 2025',
            'working_days' => 26,
            'status' => 'draft',
            'summary' => [
                'total_drivers' => 3,
                'total_net_salaries' => 9300.00,
                'total_working_days' => 78,
                'total_absence_days' => 0
            ]
        ]);

        // إنشاء رواتب السائقين
        $driverPayrolls = [
            [
                'driver_id' => 1,
                'driver_name' => 'أحمد محمد',
                'base_salary' => 3000.00,
                'target_bonus' => 500.00,
                'working_days_actual' => 26,
                'absence_days' => 0,
                'overtime_hours' => 0,
                'total_earnings' => 3600.00,
                'total_deductions' => 0.00,
                'net_salary' => 3600.00,
                'payment_status' => 'pending'
            ],
            [
                'driver_id' => 2,
                'driver_name' => 'محمد علي',
                'base_salary' => 3500.00,
                'target_bonus' => 300.00,
                'working_days_actual' => 26,
                'absence_days' => 0,
                'overtime_hours' => 0,
                'total_earnings' => 3900.00,
                'total_deductions' => 0.00,
                'net_salary' => 3900.00,
                'payment_status' => 'pending'
            ],
            [
                'driver_id' => 3,
                'driver_name' => 'علي أحمد',
                'base_salary' => 2800.00,
                'target_bonus' => 200.00,
                'working_days_actual' => 26,
                'absence_days' => 0,
                'overtime_hours' => 0,
                'total_earnings' => 3000.00,
                'total_deductions' => 0.00,
                'net_salary' => 3000.00,
                'payment_status' => 'pending'
            ]
        ];

        foreach ($driverPayrolls as $payrollData) {
            $driverPayroll = $payrollRun->driverPayrolls()->create($payrollData);

            // إضافة بدل إنترنت شهري لكل سائق
            Allowance::create([
                'driver_payroll_id' => $driverPayroll->id,
                'name' => 'بدل إنترنت شهري',
                'amount' => 100.00,
                'type' => 'monthly'
            ]);

            // إضافة بدل طعام يومي للسائق الأول
            if ($driverPayroll->driver_id == 1) {
                Allowance::create([
                    'driver_payroll_id' => $driverPayroll->id,
                    'name' => 'بدل طعام يومي',
                    'amount' => 25.00,
                    'type' => 'daily'
                ]);
            }

            // إضافة خصم تأخير للسائق الثاني
            if ($driverPayroll->driver_id == 2) {
                Deduction::create([
                    'driver_payroll_id' => $driverPayroll->id,
                    'name' => 'خصم تأخير',
                    'amount' => 50.00,
                    'type' => 'fixed',
                    'reason' => 'تأخير في الوصول للعمل'
                ]);
            }

            // إضافة تعديل يدوي للسائق الثالث
            if ($driverPayroll->driver_id == 3) {
                ManualAdjustment::create([
                    'driver_payroll_id' => $driverPayroll->id,
                    'name' => 'مكافأة إضافية',
                    'amount' => 100.00,
                    'type' => 'addition',
                    'reason' => 'أداء ممتاز'
                ]);
            }
        }

        $this->command->info('✅ تم إنشاء بيانات الرواتب التجريبية بنجاح!');
        $this->command->info('📊 تم إنشاء:');
        $this->command->info('   - ' . Driver::count() . ' سائق');
        $this->command->info('   - ' . PayrollRun::count() . ' مسير رواتب');
        $this->command->info('   - ' . DriverPayroll::count() . ' راتب سائق');
        $this->command->info('   - ' . Allowance::count() . ' بدل');
        $this->command->info('   - ' . Deduction::count() . ' خصم');
        $this->command->info('   - ' . ManualAdjustment::count() . ' تعديل يدوي');
    }
}
