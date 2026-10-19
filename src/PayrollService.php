<?php

declare(strict_types=1);

namespace App;

class PayrollService
{
    public function __construct(private CsvRepository $repository)
    {
    }

    public function employees(): array
    {
        return $this->repository->employees();
    }

    public function payrollReport(): array
    {
        $grossCents = 0;
        $deductionCents = 0;
        $netCents = 0;
        $payrollRows = $this->repository->payroll();

        foreach ($payrollRows as $row) {
            $rowGrossCents = (int) round($row['gross_pay'] * 100);
            $rowNetCents = (int) round($row['net_pay'] * 100);

            $grossCents += $rowGrossCents;
            $deductionCents += $rowGrossCents - $rowNetCents;
            $netCents += $rowNetCents;
        }

        return [
            'total_payroll_records' => count($payrollRows),
            'total_gross_pay' => round($grossCents / 100, 2),
            'total_deductions' => round($deductionCents / 100, 2),
            'total_net_pay' => round($netCents / 100, 2),
        ];
    }

    public function payslip(int $payrollId): ?array
    {
        $payrollRow = null;
        foreach ($this->repository->payroll() as $row) {
            if ($row['id'] === $payrollId) {
                $payrollRow = $row;
                break;
            }
        }

        if ($payrollRow === null) {
            return null;
        }

        $employee = null;
        foreach ($this->repository->employees() as $row) {
            if ($row['id'] === $payrollRow['employee_id']) {
                $employee = $row;
                break;
            }
        }

        if ($employee === null) {
            return null;
        }

        $grossCents = (int) round($payrollRow['gross_pay'] * 100);
        $netCents = (int) round($payrollRow['net_pay'] * 100);

        return [
            'payroll_id' => $payrollRow['id'],
            'employee_id' => $employee['id'],
            'employee_name' => $employee['full_name'],
            'email' => $employee['email'],
            'department_id' => $employee['department_id'],
            'position_id' => $employee['position_id'],
            'pay_period_start' => $payrollRow['pay_period_start'],
            'pay_period_end' => $payrollRow['pay_period_end'],
            'gross_pay' => round($grossCents / 100, 2),
            'deductions' => round(($grossCents - $netCents) / 100, 2),
            'net_pay' => round($netCents / 100, 2),
        ];
    }
}