# Cuison Payroll API

A serverless payroll API for AWS Lambda, backed by CSV files and built with PHP 8.4 and Bref 3. Payroll data and employee records are read from `data/employees.csv` and `data/payroll.csv`.

## Tech Stack

- PHP 8.4 and Bref 3 for AWS Lambda
- Bramus Router for HTTP routing
- Dompdf for PDF payslips
- AWS SDK for PHP for S3 uploads
- CSV files for data storage; no database

## Endpoints

List all employees:

```sh
curl http://localhost:8000/api/employees
```

Get payroll totals:

```sh
curl http://localhost:8000/api/reports/payroll
```

Get a payslip by payroll ID:

```sh
curl http://localhost:8000/api/payslips/1
```

Generate a PDF payslip and upload it to S3:

```sh
curl -X POST http://localhost:8000/api/payslips/1/generate
```

## Setup

Install the Composer dependencies:

```sh
composer install
```

## Local Run

```sh
php -S localhost:8000 index.php
```

## Deploy

Configure AWS credentials for the deployment environment and run:

```sh
serverless deploy
```

Payroll deductions are calculated as `gross_pay - net_pay` for each payroll record; they are not stored separately in the CSV.