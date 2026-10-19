<?php

declare(strict_types=1);

namespace App;

use Aws\S3\S3Client;

class S3Uploader
{
    private S3Client $client;
    private string $bucketName;
    private string $regionName;

    public function __construct()
    {
        $this->bucketName = getenv('PAYSLIP_BUCKET') ?: 'cuison-sam10-payslips';
        $this->regionName = getenv('AWS_REGION') ?: 'ap-southeast-1';
        $this->client = new S3Client([
            'version' => 'latest',
            'region' => $this->regionName,
        ]);
    }

    public function upload(string $key, string $body): void
    {
        $this->client->putObject([
            'Bucket' => $this->bucketName,
            'Key' => $key,
            'Body' => $body,
            'ContentType' => 'application/pdf',
        ]);
    }

    public function bucket(): string
    {
        return $this->bucketName;
    }

    public function region(): string
    {
        return $this->regionName;
    }
}