<?php

declare(strict_types=1);

use App\CsvRepository;
use App\PayslipPdf;
use App\PayrollService;
use App\Response;
use App\S3Uploader;
use Bramus\Router\Router;

require __DIR__ . '/vendor/autoload.php';

$payrollService = new PayrollService(new CsvRepository());
$router = new Router();

$router->get('/', function (): void {
    Response::json([
        'success' => true,
        'message' => 'Cuison Payroll API',
        'endpoints' => [
            'GET /api/employees',
            'GET /api/reports/payroll',
            'GET /api/payslips/{id}',
            'POST /api/payslips/{id}/generate',
        ],
    ]);
});

$router->get('/api/employees', function () use ($payrollService): void {
    $employees = $payrollService->employees();

    Response::json([
        'success' => true,
        'count' => count($employees),
        'data' => $employees,
    ]);
});

$router->get('/api/reports/payroll', function () use ($payrollService): void {
    Response::json([
        'success' => true,
        'data' => $payrollService->payrollReport(),
    ]);
});

$router->get('/api/payslips/(\d+)', function (string $payrollId) use ($payrollService): void {
    $payslip = $payrollService->payslip((int) $payrollId);

    if ($payslip === null) {
        Response::json([
            'success' => false,
            'message' => 'Payslip not found.',
        ], 404);
    }

    Response::json([
        'success' => true,
        'data' => $payslip,
    ]);
});

$router->post('/api/payslips/(\d+)/generate', function (string $payrollId) use ($payrollService): void {
    $id = (int) $payrollId;
    $payslip = $payrollService->payslip($id);

    if ($payslip === null) {
        Response::json([
            'success' => false,
            'message' => 'Payslip not found.',
        ], 404);
    }

    try {
        $key = 'payslips/' . $id . '.pdf';
        $pdf = (new PayslipPdf())->render($payslip);
        $uploader = new S3Uploader();
        $uploader->upload($key, $pdf);

        Response::json([
            'success' => true,
            'message' => 'Payslip generated and uploaded.',
            'data' => [
                'payroll_id' => $id,
                's3_bucket' => $uploader->bucket(),
                's3_key' => $key,
                'payslip_file' => sprintf(
                    'https://%s.s3.%s.amazonaws.com/%s',
                    $uploader->bucket(),
                    $uploader->region(),
                    $key
                ),
            ],
        ]);
    } catch (Throwable $exception) {
        Response::json([
            'success' => false,
            'message' => $exception->getMessage(),
        ], 500);
    }
});

$router->set404(function (): void {
    Response::json([
        'success' => false,
        'message' => 'Route not found.',
    ], 404);
});

$router->run();