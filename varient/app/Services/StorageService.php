<?php

declare(strict_types=1);

namespace App\Services;

require_once APPPATH . 'ThirdParty/aws-sdk/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\Credentials\Credentials;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

/**
 * Class StorageService
 *
 * Provides a unified interface for interacting with multiple S3-compatible
 * object storage services like AWS S3, Cloudflare R2, and Backblaze B2
 */
class StorageService
{
    /** * @var S3Client|null The AWS S3 client instance
     */
    private ?S3Client $s3Client = null;

    /** * @var S3Client|null The Cloudflare R2 client instance
     */
    private ?S3Client $cloudflareClient = null;

    /** * @var S3Client|null The Backblaze B2 client instance
     */
    private ?S3Client $backblazeClient = null;

    /** * @var array<string, string> Holds bucket information for each configured service
     */
    protected array $buckets = [];

    /** * @var string The key of the currently active/default storage service
     */
    protected string $activeStorage;

    /**
     * Constructor: Initializes clients for all configured storage services
     *
     * @param object $settings A flat object containing all necessary credentials and configuration.
     * @param string $activeStorage The identifier for the currently active storage mechanism (e.g., 'aws_s3').
     */
    public function __construct(object $settings, string $activeStorage)
    {
        // Set the active storage driver, defaulting to 'local' if empty
        $this->activeStorage = $activeStorage !== '' ? $activeStorage : 'local';

        // Initialize AWS S3 client if configured safely
        if (!empty($settings->aws_key) && !empty($settings->aws_secret)) {
            $this->s3Client = $this->buildS3Client($settings);
            $this->buckets['aws_s3'] = (string)($settings->aws_bucket ?? '');
        }

        // Initialize Cloudflare R2 client if configured safely
        if (!empty($settings->r2_key) && !empty($settings->r2_secret) && !empty($settings->r2_endpoint_url)) {
            $this->cloudflareClient = $this->buildCloudflareClient($settings);
            $this->buckets['cloudflare_r2'] = (string)($settings->r2_bucket ?? '');
        }

        // Initialize Backblaze B2 client if configured safely
        if (!empty($settings->b2_key) && !empty($settings->b2_secret) && !empty($settings->b2_endpoint_url)) {
            $this->backblazeClient = $this->buildBackblazeClient($settings);
            $this->buckets['backblaze_b2'] = (string)($settings->b2_bucket ?? '');
        }
    }

    /**
     * Uploads a file to a specified storage service
     *
     * @param string $key The destination path/key for the object.
     * @param string $tempPath The local path to the source file.
     * @param string|null $storageType The target storage service (e.g., 'aws_s3'). Defaults to the active storage.
     * @return bool True on success, false on failure.
     */
    public function putObject(string $key, string $tempPath, ?string $storageType = null): bool
    {
        $storageType = $storageType ?? $this->activeStorage;

        if (!file_exists($tempPath)) {
            return false;
        }

        $client = $this->getClient($storageType);
        $bucket = $this->buckets[$storageType] ?? null;

        if (!$client || !$bucket) {
            log_message('error', "[StorageService] Upload Error: Client or bucket not configured for '{$storageType}'.");
            return false;
        }

        $fileResource = null;

        try {
            $fileResource = fopen($tempPath, 'r');
            if ($fileResource === false) {
                log_message('error', "[StorageService] Upload Error: Could not open file for reading at {$tempPath}");
                return false;
            }

            $params = [
                'Bucket' => $bucket,
                'Key'    => $key,
                'Body'   => $fileResource,
            ];

            // Specific ACL for AWS S3 if public objects are required
            if ($storageType === 'aws_s3') {
                $params['ACL'] = 'public-read';
            }

            $client->putObject($params);

            return true;
        } catch (S3Exception $e) {
            log_message('error', "[StorageService] Upload Error ({$storageType}): " . $e->getMessage());
            return false;
        } finally {
            // Ensure the file resource is always closed, even if an exception occurs
            if (is_resource($fileResource)) {
                fclose($fileResource);
            }
        }
    }

    /**
     * Deletes an object from a specified storage service
     *
     * @param string|null $key The key of the object to delete.
     * @param string|null $storageType The storage service where the object is located.
     * @return bool Returns true on success, false on failure.
     */
    public function deleteObject(?string $key, ?string $storageType): bool
    {
        if (empty($key) || empty($storageType)) {
            return false;
        }

        $client = $this->getClient($storageType);
        $bucket = $this->buckets[$storageType] ?? null;

        if (!$client || !$bucket) {
            log_message('warning', "[StorageService] Deletion Error: Client or bucket not specified for '{$storageType}'.");
            return false;
        }

        try {
            $client->deleteObject([
                'Bucket' => $bucket,
                'Key'    => $key,
            ]);
            return true;
        } catch (S3Exception $e) {
            log_message('error', "[StorageService] Deletion Error ({$storageType}): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves a file from the storage service and prepares it for forced download
     *
     * @param string $key The storage key (path) of the file.
     * @param string $fileName The name the user will see when downloading.
     * @param string $storageType The storage driver (e.g., 'aws_s3', 'cloudflare_r2').
     * @return ResponseInterface|null Returns the CodeIgniter response on success, null on failure.
     */
    public function downloadFile(string $key, string $fileName, string $storageType): ?ResponseInterface
    {
        $client = $this->getClient($storageType);
        $bucket = $this->buckets[$storageType] ?? null;

        if (!$client || !$bucket || empty($key)) {
            return null;
        }

        try {
            // Get object stream from the configured S3-compatible service
            $result = $client->getObject([
                'Bucket' => $bucket,
                'Key'    => $key,
            ]);

            // Safely cast the Body stream to a string
            $fileContent = (string)($result['Body'] ?? '');

            return service('response')
                ->setContentType('application/octet-stream')
                ->download($fileName, $fileContent);

        } catch (S3Exception $e) {
            log_message('error', "[StorageService] Download Error ({$storageType}): " . $e->getMessage());
            return null;
        } catch (Exception $e) {
            log_message('error', "[StorageService] General Download Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieves the initialized S3 client for a specific storage type
     *
     * @param string $storageType The key for the storage service.
     * @return S3Client|null The client instance, or null if not configured.
     */
    private function getClient(string $storageType): ?S3Client
    {
        return match ($storageType) {
            'aws_s3' => $this->s3Client,
            'cloudflare_r2' => $this->cloudflareClient,
            'backblaze_b2' => $this->backblazeClient,
            default => null,
        };
    }

    /**
     * Builds and configures the native AWS S3 Client
     *
     * @param object $settings Configuration settings.
     * @return S3Client
     */
    private function buildS3Client(object $settings): S3Client
    {
        $credentials = new Credentials((string)$settings->aws_key, (string)$settings->aws_secret);

        return new S3Client([
            'version'     => 'latest',
            'region'      => (string)($settings->aws_region ?? 'us-east-1'),
            'credentials' => $credentials,
        ]);
    }

    /**
     * Builds and configures the Cloudflare R2 Client
     *
     * @param object $settings Configuration settings.
     * @return S3Client
     */
    private function buildCloudflareClient(object $settings): S3Client
    {
        $credentials = new Credentials((string)$settings->r2_key, (string)$settings->r2_secret);

        return new S3Client([
            'version'           => 'latest',
            'region'            => 'auto', // R2 requires 'auto' region
            'endpoint'          => (string)$settings->r2_endpoint_url,
            'credentials'       => $credentials,
            'signature_version' => 'v4',
        ]);
    }

    /**
     * Builds and configures the Backblaze B2 Client
     *
     * @param object $settings Configuration settings.
     * @return S3Client
     */
    private function buildBackblazeClient(object $settings): S3Client
    {
        $credentials = new Credentials((string)$settings->b2_key, (string)$settings->b2_secret);

        // Auto-detect B2 region from the endpoint URL (e.g., s3.us-west-002.backblazeb2.com)
        $endpoint = (string)$settings->b2_endpoint_url;
        $host = preg_replace('#^https?://#', '', $endpoint);

        $hostParts = explode('.', (string)$host);
        $region = $hostParts[1] ?? 'us-west-002';

        return new S3Client([
            'version'           => 'latest',
            'region'            => $region,
            'endpoint'          => $endpoint,
            'credentials'       => $credentials,
            'signature_version' => 'v4',
        ]);
    }
}