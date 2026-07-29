<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\GifEncoder;
use Intervention\Image\Interfaces\ImageInterface;
use RuntimeException;
use Throwable;
use Exception;
use InvalidArgumentException;

/**
 * Class UploadService
 *
 * Provides a comprehensive solution for managing file and image uploads
 *
 */
class UploadService
{
    protected ImageManager $imageManager;
    protected string $basePath;
    protected string $uploadsFolder = 'uploads';
    protected string $tempFolder = 'temp';
    protected object $config;
    protected ?StorageService $storageService = null;

    /**
     * UploadService constructor
     *
     * @param object $config The global application configuration object.
     */
    public function __construct(object $config)
    {
        $this->basePath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR;

        if (file_exists(APPPATH . 'ThirdParty/intervention-image/vendor/autoload.php')) {
            require_once APPPATH . 'ThirdParty/intervention-image/vendor/autoload.php';
        }

        try {
            $this->imageManager = new ImageManager(new GdDriver());
        } catch (Exception $e) {
            log_message('critical', '[UploadService] Initialization failed. Intervention/Image driver failed: ' . $e->getMessage());
            throw new RuntimeException('Image processing library is not available. Please check server configuration.', 0, $e);
        }

        $this->config = $config;

        // Initialize StorageService if not using 'local' storage
        if (isset($this->config->active_storage) && $this->config->active_storage !== 'local') {
            $this->storageService = service('storage');
        }
    }

    /**
     * Uploads a file (image or any other type) to a temporary directory without processing
     *
     * @param UploadedFile $file The file object from the HTTP request.
     * @param bool $isImage Whether to restrict validation to image types only.
     * @param array $allowedExtensions Optional custom extensions to override defaults.
     *
     * @return array Returns an associative array containing path and extension data.
     */
    public function uploadTempFile(UploadedFile $file, bool $isImage = false, array $allowedExtensions = []): array
    {
        // Ensure the file object is valid before proceeding
        if (!$file->isValid()) {
            throw new RuntimeException($file->getErrorString() . ' (' . $file->getError() . ')');
        }

        // Define allowed extensions depending on the file type if not provided
        if (empty($allowedExtensions)) {
            $allowedExtensions = $isImage
                ? ['jpg', 'jpeg', 'png', 'gif', 'webp']
                : ['json', 'csv', 'txt', 'xml', 'zip', 'pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'];
        }

        $extension = strtolower((string)$file->getClientExtension());

        // Validate the extension against the allowed list
        if (!in_array($extension, $allowedExtensions, true)) {
            throw new RuntimeException("Invalid file type. Allowed: " . implode(', ', $allowedExtensions));
        }

        $originalName = (string)$file->getName();

        // Prepare the temp directory path
        $tempDirectory = $this->basePath . $this->tempFolder . DIRECTORY_SEPARATOR;

        // Ensure the directory exists (create if not)
        if (!is_dir($tempDirectory)) {
            if (!mkdir($tempDirectory, 0755, true) && !is_dir($tempDirectory)) {
                throw new RuntimeException("Unable to create temp directory: {$tempDirectory}");
            }
        }

        // Generate a unique secure filename to prevent collisions
        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;

        // Move the file to the temporary directory
        if (!$file->move($tempDirectory, $fileName)) {
            throw new RuntimeException("Failed to move uploaded file.");
        }

        // Return structured metadata for later use
        return [
            'path'         => $tempDirectory . $fileName,
            'relativePath' => $this->uploadsFolder . DIRECTORY_SEPARATOR . $this->tempFolder . DIRECTORY_SEPARATOR . $fileName,
            'originalName' => $originalName,
            'extension'    => $extension,
        ];
    }

    /**
     * Handles the retrieval, validation, and temporary uploading of an image file
     *
     * @param string $inputName
     * @return array|null Returns temp file metadata or null on failure.
     */
    protected function uploadTempImage(string $inputName): ?array
    {
        $file = service('request')->getFile($inputName);

        // Check if file exists and is structurally valid
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return null;
        }

        // Size Validation (Based on App Config)
        $limitMB = (int)getMaxUploadLimit('image');

        // Convert MB to Bytes for cleaner comparison (1 MB = 1048576 Bytes)
        $bytesInMB = 1048576;

        if ($limitMB > 0 && (int)$file->getSize() > ($limitMB * $bytesInMB)) {
            return null;
        }

        // Prepare Allowed Extensions
        $allowedExtensions = getAppDefault('allowedExtensions')['image'] ?? [];

        try {
            // Delegate to the generic upload function
            return $this->uploadTempFile($file, true, (array)$allowedExtensions);

        } catch (Throwable $e) {
            log_message('error', '[UploadService] Temp Image Upload failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Uploads a raw file without any processing
     *
     * @param UploadedFile $file The file from the request.
     * @param array $options Configuration options for the upload.
     * @return array Details of the uploaded file.
     */
    public function uploadRawFile(UploadedFile $file, array $options): array
    {
        // Merge array with options
        $options = array_merge([
            'prefix'            => '',
            'allowedExtensions' => null,
            'forceLocal'        => false,
            'folderName'        => null,
        ], $options);

        if (empty($options['folderName'])) {
            throw new InvalidArgumentException('The "folderName" option is required for uploadRawFile.');
        }

        $folderName = (string)$options['folderName'];
        $prefix = (string)$options['prefix'];
        $allowedExtensions = $options['allowedExtensions'] ? (array)$options['allowedExtensions'] : null;
        $forceLocal = (bool)$options['forceLocal'];

        $this->validateFile($file, $allowedExtensions);

        $originalClientName = (string)$file->getName();

        // Path setup
        $dateFolder = $this->createUploadDirectory($folderName);
        $uploadDirectory = 'uploads/' . $folderName . '/' . $dateFolder . '/';
        $localDirectoryPath = FCPATH . $uploadDirectory;

        // Filename generation
        $extension = strtolower((string)$file->getClientExtension());
        $randomName = bin2hex(random_bytes(16));
        $fullFileName = $prefix . $randomName . '.' . $extension;
        $savePath = $localDirectoryPath . $fullFileName;

        // Move the uploaded file
        if (!$file->move($localDirectoryPath, $fullFileName)) {
            throw new RuntimeException($file->getErrorString() . ' (' . $file->getError() . ')');
        }

        // Storage
        $storageType = (string)($this->config->active_storage ?? 'local');
        $finalStorageType = $storageType;

        if ($storageType !== 'local' && $forceLocal === false) {
            $remotePath = $uploadDirectory . $fullFileName;
            if (!$this->moveToStorage($savePath, $remotePath)) {
                log_message('error', '[UploadService] uploadRawFile: Failed to move to cloud. File remains local.');
                $finalStorageType = 'local';
            }
        } else {
            $finalStorageType = 'local';
        }

        // Return the file details
        return [
            'name'         => $dateFolder . '/' . $fullFileName,
            'originalName' => $originalClientName,
            'path'         => $uploadDirectory . $fullFileName,
            'fullPath'     => $savePath,
            'extension'    => $extension,
            'storage'      => $finalStorageType,
        ];
    }

    /**
     * Processes an existing image file (e.g., a temp file) based on options
     *
     * @param string $sourcePath The full server path to the source image.
     * @param array $options Processing options.
     * @return array Details of the final saved image.
     */
    public function processImage(string $sourcePath, array $options): array
    {
        if (!file_exists($sourcePath)) {
            throw new RuntimeException("Source file not found for processing: {$sourcePath}");
        }

        // Merge user options with sensible defaults for the image processing pipeline
        $options = array_merge([
            'resizeMethod'     => 'resize', // 'resize' (scaleDown) or 'cover' (crop)
            'folderName'       => 'images',
            'prefix'           => 'img_',
            'width'            => null,
            'height'           => null,
            'quality'          => 85,
            'forceLocal'       => false,
            'forceOriginalExt' => false,
        ], $options);

        // Determine format & Encoder
        $originalExt = strtolower((string)pathinfo($sourcePath, PATHINFO_EXTENSION)) ?: 'jpg';

        $finalExt = $originalExt;

        if (!$options['forceOriginalExt']) {
            $targetExt = strtolower(trim($this->config->image_file_format ?? 'jpg'));
            if (in_array($targetExt, ['jpg', 'webp', 'png'])) {
                $finalExt = $targetExt;
            }
        }

        $quality = (int)$options['quality'];

        // Select the correct Intervention Image encoder based on the final format.
        $encoder = match ($finalExt) {
            'webp' => new WebpEncoder(quality: $quality),
            'png' => new PngEncoder(),
            'gif' => new GifEncoder(),
            default => new JpegEncoder(quality: $quality),
        };

        // If using 'cover' (crop), add dimensions to the prefix for clarity (e.g., "thumb_150x150_").
        $prefix = (string)$options['prefix'];
        if ($options['resizeMethod'] === 'cover' && !empty($options['width']) && !empty($options['height'])) {
            $prefix .= $options['width'] . 'x' . $options['height'] . '_';
        }

        // Get or create the date-based subfolder (e.g., "2025/10/").
        $dateFolder = $this->createUploadDirectory((string)$options['folderName']);
        $uploadDirectory = 'uploads/' . $options['folderName'] . '/' . $dateFolder . '/';
        $forceLocal = (bool)$options['forceLocal'];

        // Generate a secure, unique file name to prevent collisions and enumeration.
        $newName = $prefix . bin2hex(random_bytes(16));
        $fullFileName = $newName . '.' . $finalExt;
        $savePath = FCPATH . $uploadDirectory . $fullFileName;

        // Read the image and auto-orient it (fixes rotation issues from mobile phone uploads).
        $image = $this->imageManager->read($sourcePath)->orient();

        $width = isset($options['width']) ? (int)$options['width'] : null;
        $height = isset($options['height']) ? (int)$options['height'] : null;

        // Apply the chosen resize method based on options.
        switch ($options['resizeMethod']) {
            case 'cover':
                // Crop to exact dimensions, filling the space.
                if ($width !== null && $height !== null) {
                    $image->cover($width, $height);
                }
                break;
            case 'resize':
                // Scale down (constraining aspect ratio) *up to* the max width/height.
                if ($width !== null) {
                    $image->scaleDown($width, $height);
                }
                break;
        }

        // Encode the processed image into the target format and save it to the local disk
        $image->encode($encoder)->save($savePath);

        // Cloud storage handling
        $storageType = (string)($this->config->active_storage ?? 'local');

        if ($storageType !== 'local' && $forceLocal === false) {
            $remotePath = 'uploads/' . $options['folderName'] . '/' . $dateFolder . '/' . $fullFileName;
            $this->moveToStorage($savePath, $remotePath);
        }

        // Return file data
        return [
            'name'         => $dateFolder . '/' . $fullFileName, // Path within the 'folderName'
            'originalName' => pathinfo($sourcePath, PATHINFO_BASENAME),
            'path'         => $uploadDirectory . $fullFileName, // Full relative path from web root
            'fullPath'     => $savePath, // Absolute local path
            'extension'    => $finalExt,
            'storage'      => $storageType
        ];
    }

    /**
     * Moves a local file to the configured active storage
     */
    private function moveToStorage(string $localPath, string $remotePath, bool $deleteSource = true): bool
    {
        if (!$this->storageService || !file_exists($localPath)) {
            log_message('warning', '[UploadService] moveToStorage: Storage service unavailable or file missing.');
            return false;
        }

        try {
            if ($this->storageService->putObject($remotePath, $localPath)) {
                if ($deleteSource) {
                    @unlink($localPath);
                }
                return true;
            }
        } catch (Throwable $e) {
            log_message('error', '[UploadService] moveToStorage failed: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Validates a single uploaded file against basic and custom rules.
     */
    private function validateFile(UploadedFile $file, ?array $allowedExtensions): void
    {
        if (!$file->isValid()) {
            throw new RuntimeException('Invalid file provided.');
        }

        if (!empty($allowedExtensions)) {
            $ext = strtolower((string)$file->getClientExtension());
            if (!in_array($ext, $allowedExtensions, true)) {
                throw new RuntimeException('Invalid file type. Allowed: ' . implode(', ', $allowedExtensions));
            }
        }
    }

    /**
     * Ensures a date-based upload directory exists
     */
    private function createUploadDirectory(string $folderName): string
    {
        $dateFolder = date('Ym');
        $path = $this->basePath . $folderName . DIRECTORY_SEPARATOR . $dateFolder;

        if (!is_dir($path)) {
            if (!mkdir($path, 0755, true) && !is_dir($path)) {
                throw new RuntimeException("Unable to create directory: {$path}");
            }

            $indexPath = $path . DIRECTORY_SEPARATOR . 'index.html';

            // Define the source path of your master index.html
            $sourceIndexPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'index.html';

            if (!file_exists($indexPath) && file_exists($sourceIndexPath)) {
                @copy($sourceIndexPath, $indexPath);
            }
        }

        return $dateFolder;
    }

    /**
     * Deletes a temporary file.
     */
    public function deleteTempFile(?string $tempPath): void
    {
        if ($tempPath && file_exists($tempPath) && is_file($tempPath)) {
            @unlink($tempPath);
        }
    }

    /**
     * Upload app icon
     */
    public function uploadAppIcon(?string $inputName = 'file'): ?array
    {
        $file = service('request')->getFile($inputName);
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return null;
        }

        $tempFile = null;
        $appIcon = [];

        try {
            $tempFile = $this->uploadTempFile($file, true);
            $sizes = [512, 192, 144, 32];

            foreach ($sizes as $size) {
                $fileData = $this->processImage($tempFile['path'], [
                    'resizeMethod'     => 'cover',
                    'folderName'       => 'logo',
                    'prefix'           => 'app_icon_',
                    'width'            => $size,
                    'height'           => $size,
                    'quality'          => 100,
                    'forceLocal'       => true,
                    'forceOriginalExt' => true
                ]);

                if (!empty($fileData['path'])) {
                    $appIcon["icon_{$size}"] = $fileData['path'];
                }
            }

            return $appIcon;

        } catch (Exception $e) {
            return null;
        } finally {
            if (!empty($tempFile['path'])) {
                $this->deleteTempFile($tempFile['path']);
            }
        }
    }

    /**
     * Upload post image
     */
    public function uploadPostImage(?string $inputName = 'file', ?array $tempFile = []): ?array
    {
        $file = null;
        if (!empty($inputName)) {
            $file = service('request')->getFile($inputName);
        }

        $sizes = [
            'image_big'     => ['method' => 'cover', 'prefix' => 'image_', 'w' => 900, 'h' => 600],
            'image_default' => ['method' => 'resize', 'prefix' => 'image_1200x_', 'w' => 1200],
            'image_slider'  => ['method' => 'cover', 'prefix' => 'image_', 'w' => 646, 'h' => 520],
            'image_mid'     => ['method' => 'cover', 'prefix' => 'image_', 'w' => 450, 'h' => 280],
            'image_small'   => ['method' => 'cover', 'prefix' => 'image_', 'w' => 140, 'h' => 98],
        ];

        $sourceFile = null;

        try {
            if ($file && $file->isValid()) {

                $extension = strtolower((string)$file->getClientExtension());

                // Validate extension and size
                $allowedExtensions = getAppDefault('allowedExtensions')['image'] ?? [];
                if (!in_array($extension, (array)$allowedExtensions, true)) {
                    return null;
                }

                $limitMB = (int)getMaxUploadLimit('image');
                if ($limitMB > 0 && (int)$file->getSize() > ($limitMB * 1048576)) {
                    return null;
                }

                // Upload GIF directly
                if ($extension === 'gif') {
                    return $this->handlePostGifUpload($file, $sizes);
                }

                $uploadedTemp = $this->uploadTempFile($file, true);
                if (!empty($uploadedTemp['path'])) {
                    $sourceFile = $uploadedTemp;
                }
            }

            if (!$sourceFile && !empty($tempFile['path'])) {
                $sourceFile = $tempFile;
            }

            if (empty($sourceFile['path']) || !file_exists($sourceFile['path'])) {
                return null;
            }

            $paths = [];
            $activeStorage = (string)($this->config->active_storage ?? 'local');
            $extension = '';

            foreach ($sizes as $key => $cfg) {
                $fileData = $this->processImage($sourceFile['path'], [
                    'resizeMethod' => $cfg['method'],
                    'folderName'   => 'images',
                    'prefix'       => $cfg['prefix'],
                    'width'        => $cfg['w'],
                    'height'       => $cfg['h'] ?? null,
                ]);

                if (!empty($fileData['path'])) {
                    $paths[$key] = $fileData['path'];
                    if (!empty($fileData['storage'])) {
                        $activeStorage = $fileData['storage'];
                    }
                    if (!empty($fileData['extension'])) {
                        $extension = $fileData['extension'];
                    }
                }
            }

            if (!$paths) {
                return null;
            }

            $paths['storage'] = $activeStorage;
            $paths['extension'] = $extension;
            $paths['originalName'] = $sourceFile['originalName'] ?? '';

            return $paths;

        } catch (Throwable $e) {
            log_message('error', '[UploadService] Post Image Upload Error: ' . $e->getMessage());
            return null;

        } finally {
            if (!empty($sourceFile['path']) && file_exists($sourceFile['path'])) {
                $this->deleteTempFile($sourceFile['path']);
            }
        }
    }

    /**
     * Handles direct upload of GIF files to preserve animation
     */
    private function handlePostGifUpload(UploadedFile $file, array $sizes): ?array
    {
        $raw = $this->uploadRawFile($file, [
            'folderName'        => 'images',
            'prefix'            => 'image_',
            'allowedExtensions' => ['gif']
        ]);

        if (empty($raw['path'])) {
            return null;
        }

        $paths = [];
        foreach (array_keys($sizes) as $key) {
            $paths[$key] = $raw['path'];
        }

        $paths['storage'] = $raw['storage'];
        $paths['originalName'] = $raw['originalName'];

        return $paths;
    }

    /**
     * Upload post item image
     */
    public function uploadPostItemImage(string $inputName = 'file', string $source = 'quiz'): ?array
    {
        $file = service('request')->getFile($inputName);
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return null;
        }

        $limitMB = (int)getMaxUploadLimit('image');
        if ($limitMB > 0 && (int)$file->getSize() > ($limitMB * 1048576)) {
            return null;
        }

        $allowedExtensions = (array)(getAppDefault('allowedExtensions')['image'] ?? []);
        $extension = strtolower((string)$file->getClientExtension());

        if (!in_array($extension, $allowedExtensions, true)) {
            return null;
        }

        $sizes = [
            'image_default' => ['method' => 'cover', 'prefix' => 'image_', 'w' => 870, 'h' => 580],
            'image_small'   => ['method' => 'cover', 'prefix' => 'image_', 'w' => 420, 'h' => 420]
        ];

        if ($source === 'event_avatar_image') {
            $sizes = [
                'image_small' => ['method' => 'cover', 'prefix' => 'avatar_', 'w' => 240, 'h' => 240]
            ];
        }

        $targetFolder = match ($source) {
            'recipe_image' => 'recipe',
            'event_avatar_image' => 'event',
            default => 'quiz',
        };

        // Upload GIF directly
        if ($extension === 'gif') {
            try {
                $firstConfig = reset($sizes);
                $prefix = $firstConfig['prefix'] ?? 'image_';

                $raw = $this->uploadRawFile($file, [
                    'folderName'        => $targetFolder,
                    'prefix'            => $prefix,
                    'allowedExtensions' => ['gif']
                ]);

                if (empty($raw['path'])) {
                    return null;
                }

                $paths = [];
                foreach (array_keys($sizes) as $key) {
                    $paths[$key] = $raw['path'];
                }

                $paths['storage'] = $raw['storage'];
                $paths['originalName'] = $raw['originalName'];

                return $paths;

            } catch (Throwable $e) {
                log_message('error', '[UploadService] Post Item GIF Upload Error: ' . $e->getMessage());
                return null;
            }
        }

        $tempFile = null;

        try {
            $tempFile = $this->uploadTempFile($file, true);
            $originalName = $tempFile['originalName'] ?? '';
            $paths = [];
            $storageType = $this->config->active_storage ?? 'local';

            foreach ($sizes as $key => $cfg) {
                $fileData = $this->processImage($tempFile['path'], [
                    'resizeMethod' => $cfg['method'],
                    'folderName'   => $targetFolder,
                    'prefix'       => $cfg['prefix'],
                    'width'        => $cfg['w'],
                    'height'       => $cfg['h'] ?? null,
                ]);

                if (!empty($fileData['path'])) {
                    $paths[$key] = $fileData['path'];
                    $storageType = $fileData['storage'] ?? $storageType;
                }
            }

            if (!empty($paths)) {
                $paths['storage'] = $storageType;
                $paths['originalName'] = $originalName;
                return $paths;
            }

            return null;

        } catch (Throwable $e) {
            log_message('error', '[UploadService] Post Item Image Upload Error: ' . $e->getMessage());
            return null;
        } finally {
            if (!empty($tempFile['path'])) {
                $this->deleteTempFile($tempFile['path']);
            }
        }
    }

    /**
     * Upload media
     */
    public function uploadMedia(string $inputName = 'file', string $mediaType = 'file'): ?array
    {
        $file = service('request')->getFile($inputName);
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            log_message('error', '[UploadService] File is invalid or missing.');
            return null;
        }

        $limitMB = (int)getMaxUploadLimit($mediaType);
        if ($limitMB > 0 && (int)$file->getSize() > ($limitMB * 1048576)) {
            log_message('error', '[UploadService] File size exceeds limit: ' . $limitMB . 'MB');
            return null;
        }

        $fileExts = [];
        $fileExtsStr = (string)getAllowedExtensionsBySource('file');
        if ($fileExtsStr !== '') {
            $fileExts = array_map('trim', explode(',', strtolower($fileExtsStr)));
        }

        $allowedExtensions = match ($mediaType) {
            'audio' => (array)(getAppDefault('allowedExtensions')['audio'] ?? []),
            'video' => (array)(getAppDefault('allowedExtensions')['video'] ?? []),
            default => $fileExts,
        };

        $allowedExtensions = array_map('strtolower', $allowedExtensions);
        $extension = strtolower((string)$file->getClientExtension());

        if (!in_array($extension, $allowedExtensions, true)) {
            log_message('error', '[UploadService] Extension not allowed: ' . $extension . ' | Allowed: ' . implode(',', $allowedExtensions));
            return null;
        }

        try {
            return $this->uploadRawFile($file, [
                'folderName'        => 'media',
                'prefix'            => $mediaType . '_',
                'allowedExtensions' => $allowedExtensions
            ]);
        } catch (Exception $e) {
            log_message('error', '[UploadService] uploadRawFile Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Upload logo
     */
    public function uploadLogo(?string $inputName = 'file', ?array $allowedExtensions = null): ?string
    {
        $file = service('request')->getFile($inputName);
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return null;
        }

        $allowedExtensions = $allowedExtensions ?: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

        try {
            $fileData = $this->uploadRawFile($file, [
                'folderName'        => 'logo',
                'prefix'            => 'logo_',
                'allowedExtensions' => $allowedExtensions,
                'forceLocal'        => true
            ]);

            return !empty($fileData['path']) ? $fileData['path'] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Delete old app icon
     */
    public function deleteOldAppIcon(?array $iconPaths): void
    {
        if (!is_array($iconPaths) || empty($iconPaths)) {
            return;
        }

        foreach ($iconPaths as $path) {
            if (!empty($path)) {
                $fullPath = FCPATH . ltrim((string)$path, '/');
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }
        }
    }

    /**
     * Upload and parse language file
     */
    public function uploadLanguageFile(?string $inputName = 'file'): ?array
    {
        $file = service('request')->getFile($inputName);
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return null;
        }

        try {
            $tempFile = $this->uploadTempFile($file, false, ['json']);
            if (!file_exists($tempFile['path'])) {
                return null;
            }

            $json = file_get_contents($tempFile['path']);
            @unlink($tempFile['path']);

            if (empty($json)) {
                return null;
            }

            $jsonArray = safeDecode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($jsonArray['language'])) {
                return null;
            }

            return $jsonArray;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Upload maintenance mode image
     */
    public function uploadMaintenanceModeImage(string $inputName = 'file', ?array $allowedExtensions = null): ?array
    {
        $file = service('request')->getFile($inputName);
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return null;
        }

        $allowedExtensions = $allowedExtensions ?: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $extension = strtolower((string)$file->getClientExtension());

        if (!in_array($extension, $allowedExtensions, true)) {
            return null;
        }

        $tempFile = null;

        try {
            $tempFile = $this->uploadTempFile($file, true);

            $fileData = $this->processImage($tempFile['path'], [
                'resizeMethod' => 'resize',
                'folderName'   => 'blocks',
                'prefix'       => 'img_',
                'width'        => 1920
            ]);

            return [
                'path'    => $fileData['path'] ?? '',
                'storage' => $fileData['storage'] ?? ($this->config->active_storage ?? 'local'),
            ];
        } catch (Exception $e) {
            return null;
        } finally {
            if (!empty($tempFile['path'])) {
                $this->deleteTempFile($tempFile['path']);
            }
        }
    }

    /**
     * Upload newsletter popup image
     */
    public function uploadNewsletterImage(string $inputName = 'file', ?array $allowedExtensions = null): ?array
    {
        $file = service('request')->getFile($inputName);
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return null;
        }

        $allowedExtensions = $allowedExtensions ?: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $extension = strtolower((string)$file->getClientExtension());

        if (!in_array($extension, $allowedExtensions, true)) {
            return null;
        }

        $tempFile = null;

        try {
            $tempFile = $this->uploadTempFile($file, true);

            $fileData = $this->processImage($tempFile['path'], [
                'folderName'   => 'blocks',
                'prefix'       => 'img_',
                'width'        => 500,
                'height'       => 500,
                'resizeMethod' => 'cover'
            ]);

            return [
                'path'    => $fileData['path'] ?? '',
                'storage' => $fileData['storage'] ?? ($this->config->active_storage ?? 'local'),
            ];
        } catch (Exception $e) {
            return null;
        } finally {
            if (!empty($tempFile['path'])) {
                $this->deleteTempFile($tempFile['path']);
            }
        }
    }

    /**
     * Upload gallery image
     */
    public function uploadGalleryImage(string $inputName = 'file'): ?array
    {
        $file = service('request')->getFile($inputName);
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return null;
        }

        $limitMB = (int)getMaxUploadLimit('image');
        if ($limitMB > 0 && (int)$file->getSize() > ($limitMB * 1048576)) {
            return null;
        }

        $allowedExtensions = (array)(getAppDefault('allowedExtensions')['image'] ?? []);
        $extension = strtolower((string)$file->getClientExtension());

        if (!in_array($extension, $allowedExtensions, true)) {
            return null;
        }

        $sizes = [
            'path_big'   => ['prefix' => 'image_1920x_', 'w' => 1920],
            'path_small' => ['prefix' => 'image_500x_', 'w' => 500]
        ];

        $tempFile = null;

        try {
            $tempFile = $this->uploadTempFile($file, true);
            $originalName = $tempFile['originalName'] ?? '';
            $paths = [];
            $storageType = $this->config->active_storage ?? 'local';

            foreach ($sizes as $key => $cfg) {
                $fileData = $this->processImage($tempFile['path'], [
                    'resizeMethod' => 'resize',
                    'folderName'   => 'gallery',
                    'prefix'       => $cfg['prefix'],
                    'width'        => $cfg['w']
                ]);

                if (!empty($fileData['path'])) {
                    $paths[$key] = $fileData['path'];
                    $storageType = $fileData['storage'] ?? $storageType;
                }
            }

            if (!empty($paths)) {
                $paths['storage'] = $storageType;
                $paths['originalName'] = $originalName;
                return $paths;
            }

            return null;

        } catch (Throwable $e) {
            return null;
        } finally {
            if (!empty($tempFile['path'])) {
                $this->deleteTempFile($tempFile['path']);
            }
        }
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(?string $inputName = 'file', ?string $tempFileName = null): ?array
    {
        $tempPath = '';
        if (!empty($tempFileName)) {
            $tempPath = $this->basePath . $this->tempFolder . DIRECTORY_SEPARATOR . $tempFileName;
        } else {
            $img = $this->uploadTempImage((string)$inputName);
            $tempPath = $img['path'] ?? '';
        }

        if (empty($tempPath)) {
            return null;
        }

        try {
            $fileData = $this->processImage($tempPath, [
                'resizeMethod' => 'cover',
                'folderName'   => 'profile',
                'prefix'       => 'avatar_',
                'width'        => 240,
                'height'       => 240
            ]);

            return [
                'path'    => $fileData['path'] ?? '',
                'storage' => $fileData['storage'] ?? ($this->config->active_storage ?? 'local'),
            ];
        } catch (Throwable $e) {
            return null;
        } finally {
            if (!empty($tempPath)) {
                $this->deleteTempFile($tempPath);
            }
        }
    }

    /**
     * Upload profile cover image
     */
    public function uploadProfileCover(string $inputName = 'file'): ?array
    {
        $tempImg = $this->uploadTempImage($inputName);
        if (empty($tempImg['path'])) {
            return null;
        }

        try {
            $fileData = $this->processImage($tempImg['path'], [
                'resizeMethod' => 'cover',
                'folderName'   => 'profile',
                'prefix'       => 'cover_',
                'width'        => 1920,
                'height'       => 600
            ]);

            return [
                'path'    => $fileData['path'] ?? '',
                'storage' => $fileData['storage'] ?? ($this->config->active_storage ?? 'local'),
            ];
        } catch (Throwable $e) {
            return null;
        } finally {
            if (!empty($tempImg['path'])) {
                $this->deleteTempFile($tempImg['path']);
            }
        }
    }

    /**
     * Upload font file
     */
    public function uploadFontFile(string $inputName = 'file'): ?string
    {
        $file = service('request')->getFile($inputName);
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return null;
        }

        try {
            $fileData = $this->uploadRawFile($file, [
                'folderName'        => 'fonts',
                'prefix'            => 'font_',
                'allowedExtensions' => ['woff2'],
                'forceLocal'        => true
            ]);

            return !empty($fileData['path']) ? $fileData['path'] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Upload event speakers avatar
     */
    public function uploadSpeakerAvatars(array $postData): array
    {
        $uploadedAvatars = [];

        if (!empty($postData['speakers'])) {
            foreach ($postData['speakers'] as $id => $speakerInput) {
                $inputName = "speakers.$id.avatar";
                $tempImg = $this->uploadTempImage($inputName);

                if (empty($tempImg['path'])) {
                    continue;
                }

                try {
                    $fileData = $this->processImage($tempImg['path'], [
                        'resizeMethod' => 'cover',
                        'folderName'   => 'event',
                        'prefix'       => 'avatar_',
                        'width'        => 240,
                        'height'       => 240
                    ]);

                    if (!empty($fileData['path'])) {
                        $uploadedAvatars[$id] = [
                            'speakerId' => $id,
                            'path'      => $fileData['path'],
                            'storage'   => $fileData['storage'] ?? ($this->config->active_storage ?? 'local'),
                        ];
                    }
                } catch (Throwable $e) {
                } finally {
                    if (!empty($tempImg['path'])) {
                        $this->deleteTempFile($tempImg['path']);
                    }
                }
            }
        }

        return $uploadedAvatars;
    }
}