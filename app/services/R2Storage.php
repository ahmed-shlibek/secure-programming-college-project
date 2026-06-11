<?php

namespace App\Services;

use Aws\S3\S3Client;

/**
 * Thin wrapper around Cloudflare R2 (S3-compatible object storage).
 *
 * Security model: the bucket is PRIVATE. Files are uploaded server-side only,
 * after the controller has validated the bytes. Objects are stored with
 * Content-Disposition: attachment so they are always downloaded, never rendered
 * inline. Reading a file back requires a short-lived presigned URL minted here —
 * there is no public object URL.
 */
class R2Storage
{
    private S3Client $client;
    private string $bucket;

    public function __construct()
    {
        $accountId    = (string) env('R2_ACCOUNT_ID');
        $this->bucket = (string) env('R2_BUCKET');

        $this->client = new S3Client([
            'region'                  => 'auto',
            'version'                 => 'latest',
            'endpoint'                => "https://{$accountId}.r2.cloudflarestorage.com",
            'use_path_style_endpoint' => true,
            'credentials'             => [
                'key'    => (string) env('R2_ACCESS_KEY_ID'),
                'secret' => (string) env('R2_SECRET_ACCESS_KEY'),
            ],
        ]);
    }

    /**
     * True only when every R2 credential is present. Lets callers skip uploads
     * gracefully in local dev where R2 isn't configured.
     */
    public static function isConfigured(): bool
    {
        foreach (['R2_ACCOUNT_ID', 'R2_ACCESS_KEY_ID', 'R2_SECRET_ACCESS_KEY', 'R2_BUCKET'] as $key) {
            if (empty(env($key))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Upload a validated local file to R2 under the given key.
     * Throws Aws\Exception\AwsException on failure (caller must handle).
     */
    public function put(string $sourcePath, string $key, string $mime): void
    {
        $this->client->putObject([
            'Bucket'             => $this->bucket,
            'Key'                => $key,
            'SourceFile'         => $sourcePath,
            'ContentType'        => $mime,
            'ContentDisposition' => 'attachment',
        ]);
    }

    /**
     * Mint a short-lived presigned GET URL for an object (for a future admin
     * viewer). The bucket itself stays private.
     */
    public function presignedGetUrl(string $key, int $minutes = 15): string
    {
        $command = $this->client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key'    => $key,
        ]);

        $request = $this->client->createPresignedRequest($command, "+{$minutes} minutes");

        return (string) $request->getUri();
    }
}