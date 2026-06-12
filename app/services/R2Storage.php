<?php

namespace App\Services;

use Aws\S3\S3Client;

/**
 * Thin wrapper around Cloudflare R2 (S3-compatible object storage).
 *
 * Security model: the bucket is PRIVATE. Files are uploaded server-side after
 * the controller has validated the bytes, and read back server-side and streamed
 * to the admin. The bucket is never exposed publicly and no URL to it is ever
 * handed to a browser.
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
            // Recent aws-sdk-php enables request/response checksums by default,
            // which S3-compatible providers like R2 don't fully support and which
            // can cause AccessDenied / signature errors. Restore the old behaviour.
            'request_checksum_calculation' => 'when_required',
            'response_checksum_validation' => 'when_required',
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
     * Fetch an object's bytes and content type. Uses the same live, signed
     * request path as put() (so if uploads work, downloads work too).
     * Throws Aws\Exception\AwsException on failure (caller must handle).
     *
     * @return array{body: string, contentType: string}
     */
    public function get(string $key): array
    {
        $result = $this->client->getObject([
            'Bucket' => $this->bucket,
            'Key'    => $key,
        ]);

        return [
            'body'        => (string) $result['Body'],
            'contentType' => (string) ($result['ContentType'] ?? 'application/octet-stream'),
        ];
    }
}
