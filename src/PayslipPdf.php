<?php

declare(strict_types=1);

namespace App;

use Dompdf\Dompdf;
use Dompdf\Options;

class PayslipPdf
{
    public function render(array $payslip): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('tempDir', sys_get_temp_dir());
        $options->set('fontCache', sys_get_temp_dir());
        $options->setDefaultFont('DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($this->html($payslip), 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private function html(array $payslip): string
    {
        $escape = static fn (string|int|float|null $value): string => htmlspecialchars(
            (string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
        $generatedDate = date('Y-m-d H:i:s T');

        return '<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { color: #202a31; font-family: "DejaVu Sans", sans-serif; font-size: 11px; }
        header { border-bottom: 2px solid #176b63; margin-bottom: 22px; padding-bottom: 12px; }
        h1 { color: #176b63; font-size: 24px; margin: 0 0 4px; }
        h2 { border-bottom: 1px solid #d8e0df; color: #176b63; font-size: 14px; margin: 20px 0 8px; padding-bottom: 5px; }
        p { margin: 4px 0; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border-bottom: 1px solid #e1e7e6; padding: 8px; text-align: left; }
        th { background: #f1f6f5; }
        td.amount { text-align: right; }
        tr.total td { border-top: 2px solid #176b63; font-weight: bold; }
        footer { border-top: 1px solid #d8e0df; color: #65716f; font-size: 9px; margin-top: 32px; padding-top: 10px; }
    </style>
</head>
<body>
    <header>
        <h1>PAYSLIP</h1>
        <p>Cuison Payroll System</p>
        <p>Payroll ID: ' . $escape($payslip['payroll_id']) . '</p>
        <p>Generated: ' . $escape($generatedDate) . '</p>
    </header>
    <h2>Employee Details</h2>
    <table>
        <tr><th>Employee ID</th><td>' . $escape($payslip['employee_id']) . '</td></tr>
        <tr><th>Name</th><td>' . $escape($payslip['employee_name']) . '</td></tr>
        <tr><th>Email</th><td>' . $escape($payslip['email']) . '</td></tr>
        <tr><th>Department ID</th><td>' . $escape($payslip['department_id']) . '</td></tr>
        <tr><th>Position ID</th><td>' . $escape($payslip['position_id']) . '</td></tr>
    </table>
    <h2>Pay Period</h2>
    <p>' . $escape($payslip['pay_period_start']) . ' &ndash; ' . $escape($payslip['pay_period_end']) . '</p>
    <h2>Earnings &amp; Deductions</h2>
    <table>
        <tr><th>Description</th><th style="text-align:right">Amount</th></tr>
        <tr><td>Gross Pay</td><td class="amount">$' . number_format((float) $payslip['gross_pay'], 2) . '</td></tr>
        <tr><td>Deductions</td><td class="amount">$' . number_format((float) $payslip['deductions'], 2) . '</td></tr>
        <tr class="total"><td>Net Pay</td><td class="amount">$' . number_format((float) $payslip['net_pay'], 2) . '</td></tr>
    </table>
    <footer>System-generated payslip &mdash; SAM10 Lab, Yoj T. Cuison</footer>
</body>
</html>';
    }
}