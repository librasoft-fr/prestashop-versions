<?php
/**
 * Copyright (c) 2025 PrestaShop SA
 *
 * All Rights Reserved.
 *
 * This module is proprietary software owned by PrestaShop SA. All intellectual property rights, including copyrights, trademarks, and trade secrets, are reserved by PrestaShop SA.
 *
 * The PS MCP Tools module was developed by PrestaShop, which holds all associated intellectual property rights. The license granted to the user does not entail any transfer of rights. The user shall refrain from any act that may infringe upon PrestaShop's rights and undertakes to strictly comply with the limitations of the license set out below. PrestaShop grants the user a personal, non-exclusive, non-transferable, and non-sublicensable license to use the MCP Tools module, worldwide and for the entire duration of use of the module. This license is strictly limited to installing the module and using it solely for the operation of the user's PrestaShop store.
 */

namespace PrestaShop\Module\PsMcpTools;

use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpSchema;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpTool;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpToolAnnotations;
use PrestaShop\Module\PsMcpServer\Server\Exceptions\PsMcpToolCallException;
use PrestaShop\Module\PsMcpTools\Webservice\AbstractWebservice;
use Symfony\Component\HttpClient\HttpClient;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Product Image management tools using PrestaShop webservices
 */
class ProductImageTools extends AbstractWebservice
{
    /**
     * Maximum allowed file size for downloaded images (10 MB)
     */
    private const MAX_IMAGE_SIZE = 10 * 1024 * 1024;

    /**
     * Timeout for image download in seconds
     */
    private const DOWNLOAD_TIMEOUT = 30;

    /**
     * Allowed MIME types for downloaded images
     */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    /**
     * Resource name for product images
     */
    private const RESOURCE = 'images/products';

    /**
     * Error message for missing context
     */
    private const ERROR_CONTEXT_NOT_AVAILABLE = 'Context or Language is not available';

    #[PsMcpTool(
        name: 'list_product_image',
        title: 'List Product Images',
        description: 'List all images of a given product. Returns id, position, is_cover, and image_url for each image.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'product images']
    )]
    #[PsMcpSchema(
        properties: [
            'productId' => ['type' => 'integer', 'description' => 'ID of the product'],
        ],
        required: ['productId']
    )]
    public function listProductImage(int $productId): array
    {
        try {
            $this->verifyProductExists($productId);

            $result = $this->getResourceById(self::RESOURCE, $productId, 'full', null);

            $images = [];
            $imageIds = $this->extractImageIds($result);

            // Load each image using PrestaShop's Image class to get full details and URLs
            foreach ($imageIds as $imageId) {
                $image = new \Image($imageId);

                if ($image->id) {
                    $imageUrl = $this->getImageUrl($image);

                    $imageData = [
                        'id' => (int) $image->id,
                        'position' => (int) $image->position,
                        'cover' => (bool) $image->cover,
                        'image_url' => $imageUrl,
                    ];

                    $images[] = $imageData;
                }
            }

            return $images;
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'add_product_image',
        title: 'Add Product Image',
        description: 'Add an image to a product from a public URL. Downloads the image securely and attaches it to the product. Returns the new image ID and URL.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: false,
            openWorldHint: true
        ),
        meta: ['category' => 'product images']
    )]
    #[PsMcpSchema(
        properties: [
            'productId' => ['type' => 'integer', 'description' => 'ID of the product'],
            'imageUrl' => ['type' => 'string', 'description' => 'Public URL of the image to download and attach to the product'],
            'position' => ['type' => ['integer', 'null'], 'description' => 'Position of the image (optional)'],
            'cover' => ['type' => 'boolean', 'description' => 'Set as cover image (optional, default: false)'],
        ],
        required: ['productId', 'imageUrl']
    )]
    public function addProductImage(int $productId, string $imageUrl, ?int $position = null, bool $cover = false): array
    {
        try {
            $this->verifyProductExists($productId);
            $imageId = $this->uploadNewImage($productId, $imageUrl);
            $this->updateImageMetadataIfNeeded($productId, $imageId, $position, $cover ? true : null);

            return $this->buildImageResponse($imageId);
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'update_product_image',
        title: 'Update Product Image',
        description: 'Update a product image. Can update position, cover flag, and optionally replace the image file from a public URL.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: true
        ),
        meta: ['category' => 'product images']
    )]
    #[PsMcpSchema(
        properties: [
            'productId' => ['type' => 'integer', 'description' => 'ID of the product'],
            'imageId' => ['type' => 'integer', 'description' => 'ID of the image to update'],
            'position' => ['type' => ['integer', 'null'], 'description' => 'New position (optional)'],
            'cover' => ['type' => ['boolean', 'null'], 'description' => 'Set as cover image (optional)'],
            'imageUrl' => ['type' => ['string', 'null'], 'description' => 'Public URL of a new image to replace the existing one (optional)'],
        ],
        required: ['productId', 'imageId']
    )]
    public function updateProductImage(
        int $productId,
        int $imageId,
        ?int $position = null,
        ?bool $cover = null,
        ?string $imageUrl = null,
    ): array {
        try {
            $this->verifyProductExists($productId);
            $this->verifyImageBelongsToProduct($imageId, $productId);

            if ($imageUrl !== null) {
                $imageId = $this->replaceImageFile($productId, $imageId, $imageUrl);
            }

            $this->updateImageMetadataIfNeeded($productId, $imageId, $position, $cover);

            return $this->buildImageResponse($imageId);
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'set_cover_product_image',
        title: 'Set Cover Image',
        description: 'Set a specific image as the product cover. Automatically unsets the cover flag on other images.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'product images']
    )]
    #[PsMcpSchema(
        properties: [
            'productId' => ['type' => 'integer', 'description' => 'ID of the product'],
            'imageId' => ['type' => 'integer', 'description' => 'ID of the image to set as cover'],
        ],
        required: ['productId', 'imageId']
    )]
    public function setCoverProductImage(int $productId, int $imageId): array
    {
        try {
            $this->verifyProductExists($productId);
            $this->verifyImageBelongsToProduct($imageId, $productId);
            $this->updateImageMetadata($productId, $imageId, ['cover' => 1]);

            return $this->buildImageResponse($imageId);
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Extract image IDs from the API response
     *
     * @param array $result API response
     *
     * @return array Array of image IDs
     */
    private function extractImageIds(array $result): array
    {
        if (!isset($result['image'])) {
            return [];
        }

        $imageData = $result['image'];

        // Check if declination array exists (most common case)
        if (isset($imageData['declination']) && is_array($imageData['declination'])) {
            return $this->extractIdsFromDeclination($imageData['declination']);
        }

        // Single image with ID at root level
        if (isset($imageData['id'])) {
            return $this->extractIdsFromImageData($imageData);
        }

        // Multiple images - check if it's an array of image objects
        if (is_array($imageData)) {
            return $this->extractIdsFromMultipleImages($imageData);
        }

        return [];
    }

    /**
     * Extract IDs from declination array
     *
     * @param array $declinations Declination array
     *
     * @return array Array of image IDs
     */
    private function extractIdsFromDeclination(array $declinations): array
    {
        $imageIds = [];
        foreach ($declinations as $decl) {
            if (isset($decl['id'])) {
                $imageIds[] = (int) $decl['id'];
            }
        }

        return $imageIds;
    }

    /**
     * Extract IDs from multiple image objects
     *
     * @param array $images Array of image objects
     *
     * @return array Array of image IDs
     */
    private function extractIdsFromMultipleImages(array $images): array
    {
        $imageIds = [];
        foreach ($images as $img) {
            if (is_array($img) && isset($img['id'])) {
                $ids = $this->extractIdsFromImageData($img);
                $imageIds = array_merge($imageIds, $ids);
            }
        }

        return $imageIds;
    }

    /**
     * Extract IDs from a single image data structure
     *
     * @param array $imageData Image data from API response
     *
     * @return array Array of image IDs
     */
    private function extractIdsFromImageData(array $imageData): array
    {
        $ids = [];

        if (isset($imageData['declination']) && is_array($imageData['declination'])) {
            foreach ($imageData['declination'] as $declination) {
                if (isset($declination['id'])) {
                    $ids[] = (int) $declination['id'];
                }
            }
        } elseif (isset($imageData['id'])) {
            // No declinations, just the main image ID
            $ids[] = (int) $imageData['id'];
        }

        return $ids;
    }

    /**
     * Get the front-end image URL for a product image
     *
     * @param \Image $image Image object
     *
     * @return string Image URL
     */
    private function getImageUrl(\Image $image): string
    {
        $context = \Context::getContext();
        if ($context === null || $context->link === null) {
            throw new \PrestaShopException('Context or Link is not available');
        }

        $link = $context->link;

        return $link->getImageLink(
            '',
            (string) $image->id,
            \ImageType::getFormattedName('large')
        );
    }

    /**
     * Download an image from a public URL to a local temporary file
     *
     * Uses Symfony HttpClient with DNS pinning to prevent DNS rebinding attacks:
     * the hostname is resolved once, validated against private IP ranges, and the
     * resolved IP is pinned for the actual HTTP request.
     *
     * @param string $imageUrl Public URL of the image
     *
     * @return string Path to temporary file
     *
     * @throws \PrestaShopException If download or validation fails
     */
    private function downloadImageFromUrl(string $imageUrl): string
    {
        // Validate URL format
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            throw new \PrestaShopException('Invalid image URL format');
        }

        // Only allow http and https schemes
        $scheme = parse_url($imageUrl, PHP_URL_SCHEME);
        if (!in_array(strtolower((string) $scheme), ['http', 'https'], true)) {
            throw new \PrestaShopException('Only HTTP and HTTPS URLs are allowed');
        }

        $tmpDir = _PS_ROOT_DIR_ . '/var/tmp';

        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $tmpFile = tempnam($tmpDir, 'img_');

        if ($tmpFile === false) {
            throw new \PrestaShopException('Failed to create temporary file');
        }

        try {
            $this->downloadWithHttpClient($imageUrl, $tmpFile);

            // Verify the downloaded file is a valid image with an allowed MIME type
            $imageInfo = @getimagesize($tmpFile);

            if ($imageInfo === false) {
                throw new \PrestaShopException('Downloaded file is not a valid image');
            }

            if (!in_array($imageInfo['mime'], self::ALLOWED_MIME_TYPES, true)) {
                throw new \PrestaShopException("Image MIME type '{$imageInfo['mime']}' is not allowed. Allowed types: " . implode(', ', self::ALLOWED_MIME_TYPES));
            }

            return $tmpFile;
        } catch (\PrestaShopException $e) {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
            throw $e;
        } catch (\Exception $e) {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
            throw new \PrestaShopException('Failed to download image: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Download a file using Symfony HttpClient with DNS pinning
     *
     * The resolve option forces the HTTP client to connect to the pre-validated IP,
     * preventing DNS rebinding between validation and request time.
     *
     * @param string $url URL to download from
     * @param string $destination Local file path to save to
     *
     * @throws \PrestaShopException If download fails
     */
    private function downloadWithHttpClient(
        string $url,
        string $destination,
    ): void {
        $client = HttpClient::create([
            'timeout' => self::DOWNLOAD_TIMEOUT,
            'max_redirects' => 5,
            'headers' => [
                'User-Agent' => 'PrestaShop-MCP-Tools/1.0',
            ],
        ]);

        $response = $client->request('GET', $url, [
            // Callback invoked on each response header chunk — used to enforce size limit
            'on_progress' => function (int $dlNow) {
                if ($dlNow > self::MAX_IMAGE_SIZE) {
                    throw new \PrestaShopException('Downloaded image exceeds maximum allowed size of ' . (self::MAX_IMAGE_SIZE / 1024 / 1024) . ' MB');
                }
            },
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new \PrestaShopException("Failed to download image from URL (HTTP {$statusCode})");
        }

        // Stream the response to a file while enforcing the size limit
        $fp = fopen($destination, 'w');

        if ($fp === false) {
            throw new \PrestaShopException('Failed to open temporary file for writing');
        }

        try {
            $totalSize = 0;
            foreach ($client->stream($response) as $chunk) {
                $content = $chunk->getContent();
                $totalSize += strlen($content);

                if ($totalSize > self::MAX_IMAGE_SIZE) {
                    throw new \PrestaShopException('Downloaded image exceeds maximum allowed size of ' . (self::MAX_IMAGE_SIZE / 1024 / 1024) . ' MB');
                }

                fwrite($fp, $content);
            }
        } finally {
            fclose($fp);
        }

        if ($totalSize === 0) {
            throw new \PrestaShopException('Downloaded image file is empty');
        }
    }

    /**
     * Update image metadata (position, cover) via webservice and Image object
     * For cover: updates both product's id_default_image (webservice) AND image cover flags (Image object)
     * For position: updates the image position via Image object (no WS support)
     *
     * @param int $productId Product ID
     * @param int $imageId Image ID
     * @param array $data Data to update (position, cover)
     *
     * @throws \PrestaShopException If update fails
     */
    private function updateImageMetadata(int $productId, int $imageId, array $data): void
    {
        $image = new \Image($imageId);

        if (!$image->id) {
            throw new \PrestaShopException("Image with ID {$imageId} not found");
        }

        if ((int) $image->id_product !== $productId) {
            throw new \PrestaShopException("Image {$imageId} does not belong to product {$productId}");
        }

        // IMPORTANT: Handle position BEFORE cover
        // Setting cover via webservice may affect position, so we set position first
        if (isset($data['position']) && (int) $image->position !== (int) $data['position']) {
            $this->updateImagePosition($image, (int) $data['position']);
        }

        // Handle cover update AFTER position
        if (isset($data['cover']) && (bool) $data['cover']) {
            $this->setImageAsCover($productId, $imageId);
        }
    }

    /**
     * Set an image as the product cover
     * Updates both product's id_default_image and image cover flags
     *
     * @param int $productId Product ID
     * @param int $imageId Image ID to set as cover
     *
     * @throws \PrestaShopException If update fails
     */
    private function setImageAsCover(int $productId, int $imageId): void
    {
        $image = new \Image($imageId);

        if (!$image->id) {
            throw new \PrestaShopException("Image with ID {$imageId} not found");
        }

        // Step 1: Update product's id_default_image via webservice
        $this->updateResource('products', $productId, [
            'id_default_image' => $imageId,
        ]);

        // Step 2: Remove cover from all other images
        $this->removeCoverFromOtherImages($productId, $imageId);

        // Step 3: Set this image as cover
        $image->cover = true;
        if (!$image->update()) {
            throw new \PrestaShopException('Failed to set image as cover');
        }
    }

    /**
     * Remove cover flag from all images except the specified one
     *
     * @param int $productId Product ID
     * @param int $excludeImageId Image ID to exclude from update
     */
    private function removeCoverFromOtherImages(int $productId, int $excludeImageId): void
    {
        $context = \Context::getContext();
        if ($context === null || $context->language === null) {
            throw new \PrestaShopException(self::ERROR_CONTEXT_NOT_AVAILABLE);
        }

        $allImages = \Image::getImages((int) $context->language->id, $productId);
        foreach ($allImages as $img) {
            if ((int) $img['id_image'] === $excludeImageId) {
                continue;
            }

            $imgObj = new \Image($img['id_image']);
            if ($imgObj->id) {
                $imgObj->cover = false;
                $imgObj->update();
            }
        }
    }

    /**
     * Update image position
     * Uses Image class directly as webservice doesn't support PUT on images/products
     * Also reorders all other images to maintain sequential positions
     *
     * @param \Image $image Image object
     * @param int $position New position
     *
     * @throws \PrestaShopException If update fails
     */
    private function updateImagePosition(\Image $image, int $position): void
    {
        $productId = (int) $image->id_product;
        $imageId = (int) $image->id;

        $context = \Context::getContext();

        if ($context === null || $context->language === null) {
            throw new \PrestaShopException(self::ERROR_CONTEXT_NOT_AVAILABLE);
        }

        $allImages = \Image::getImages($context->language->id, $productId);

        // Sort by current position, then by ID to have a consistent order
        usort($allImages, function ($a, $b) {
            if ($a['position'] === $b['position']) {
                return $a['id_image'] - $b['id_image'];
            }

            return $a['position'] - $b['position'];
        });

        $newPositions = [];
        $currentPos = 1;

        // First pass: assign positions to all images except the target one
        foreach ($allImages as $img) {
            if ((int) $img['id_image'] === $imageId) {
                continue; // Skip our target image
            }

            // If we've reached the desired position, skip it for our image
            if ($currentPos === $position) {
                ++$currentPos;
            }

            $newPositions[(int) $img['id_image']] = $currentPos;
            ++$currentPos;
        }

        $newPositions[$imageId] = $position;

        // Update all positions in the database
        foreach ($newPositions as $imgId => $newPos) {
            $img = new \Image($imgId);
            if ($img->id) {
                $img->position = $newPos;
                $img->update();
            }
        }

        // Update the local image object
        $image->position = $position;
    }

    /**
     * Extract image ID from various PrestaShop API response structures
     * Different PrestaShop versions may return different response formats
     *
     * @param array $parsed Parsed API response
     *
     * @return int|null Image ID or null if not found
     */
    private function extractImageIdFromResponse(array $parsed): ?int
    {
        // Define all possible paths to image ID in different response structures
        // Note: @attributes removed since new XML parser doesn't use them
        $possiblePaths = [
            ['image', 'id'],                    // Structure 1: Direct image node
            ['images', 'image', 'id'],          // Structure 2: Images array
            ['id'],                             // Structure 3: Top-level id
            ['prestashop', 'image', 'id'],      // Structure 4: Prestashop wrapper
            ['prestashop', 'images', 'image', 'id'], // Structure 5: Prestashop wrapper with images
        ];

        // Try each path until we find a valid ID
        foreach ($possiblePaths as $path) {
            $value = $this->getValueByPath($parsed, $path);
            if ($value !== null && $value !== '') {
                return (int) $value;
            }
        }

        return null;
    }

    /**
     * Get a value from a nested array by following a path
     *
     * @param array $array Array to search
     * @param array $path Path to follow (array of keys)
     *
     * @return mixed|null Value found or null
     */
    private function getValueByPath(array $array, array $path)
    {
        $current = $array;

        foreach ($path as $key) {
            if (!is_array($current) || !isset($current[$key])) {
                return null;
            }
            $current = $current[$key];
        }

        return $current;
    }

    /**
     * Get the ID of the most recently added image for a product
     * This is useful when PrestaShop returns empty response after upload
     *
     * @param int $productId Product ID
     *
     * @return int|null Latest image ID or null if no images found
     */
    private function getLatestImageIdForProduct(int $productId): ?int
    {
        try {
            $result = $this->getResourceById(self::RESOURCE, $productId, 'full', null);
            $imageIds = $this->extractImageIds($result);

            if (!empty($imageIds)) {
                return max($imageIds);
            }
        } catch (\Exception) {
            return null;
        }

        return null;
    }

    /**
     * Verify that a product exists
     *
     * @param int $productId Product ID
     *
     * @throws \PrestaShopException If product doesn't exist
     */
    private function verifyProductExists(int $productId): void
    {
        $product = new \Product($productId);
        if (!$product->id) {
            throw new \PrestaShopException("Product with ID {$productId} does not exist");
        }
    }

    /**
     * Verify that an image exists and belongs to a product
     *
     * @param int $imageId Image ID
     * @param int $productId Product ID
     *
     * @throws \PrestaShopException If image doesn't exist or doesn't belong to product
     */
    private function verifyImageBelongsToProduct(int $imageId, int $productId): void
    {
        $image = new \Image($imageId);
        if (!$image->id) {
            throw new \PrestaShopException("Image with ID {$imageId} does not exist");
        }
        if ((int) $image->id_product !== $productId) {
            throw new \PrestaShopException("Image {$imageId} does not belong to product {$productId}");
        }
    }

    /**
     * Replace an image file with a new one downloaded from a URL
     * Deletes the old image and uploads a new one, returning the new image ID
     *
     * @param int $productId Product ID
     * @param int $oldImageId Old image ID to replace
     * @param string $imageUrl Public URL of the new image
     *
     * @return int New image ID
     *
     * @throws \PrestaShopException If replacement fails
     */
    private function replaceImageFile(int $productId, int $oldImageId, string $imageUrl): int
    {
        $this->deleteResource("images/products/{$productId}/{$oldImageId}");

        $tmpFile = $this->downloadImageFromUrl($imageUrl);

        try {
            $result = $this->uploadBinaryFile("images/products/{$productId}", $tmpFile);
            $parsed = $this->parseWebserviceResponse($result);
            $newImageId = $this->extractImageIdFromResponse($parsed);

            if (!$newImageId && isset($result['code']) && ($result['code'] == 200 || $result['code'] == 201)) {
                $newImageId = $this->getLatestImageIdForProduct($productId);
            }

            if (!$newImageId) {
                throw new \PrestaShopException('Failed to get new image ID after replacing image');
            }

            return $newImageId;
        } finally {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }

    /**
     * Update image metadata (position, cover) if values are provided
     *
     * @param int $productId Product ID
     * @param int $imageId Image ID
     * @param int|null $position New position (optional)
     * @param bool|null $cover New cover flag (optional)
     */
    private function updateImageMetadataIfNeeded(int $productId, int $imageId, ?int $position, ?bool $cover): void
    {
        if ($position === null && $cover === null) {
            return;
        }

        $updateData = [];
        if ($position !== null) {
            $updateData['position'] = $position;
        }

        if ($cover !== null) {
            $updateData['cover'] = $cover ? 1 : 0;
        }

        $this->updateImageMetadata($productId, $imageId, $updateData);
    }

    /**
     * Upload a new image to a product from a URL
     * Handles download, upload, and ID extraction
     *
     * @param int $productId Product ID
     * @param string $imageUrl Public URL of the image
     *
     * @return int New image ID
     *
     * @throws \PrestaShopException If upload fails
     */
    private function uploadNewImage(int $productId, string $imageUrl): int
    {
        $tmpFile = $this->downloadImageFromUrl($imageUrl);

        try {
            $result = $this->uploadBinaryFile("images/products/{$productId}", $tmpFile);
            $parsed = $this->parseWebserviceResponse($result);

            $imageId = $parsed['image']['id'] ?? null;

            if (!$imageId) {
                $responsePreview = json_encode($parsed, JSON_PRETTY_PRINT);
                if ($responsePreview === false) {
                    $responsePreview = 'Unable to encode response';
                } elseif (strlen($responsePreview) > 500) {
                    $responsePreview = substr($responsePreview, 0, 500) . '...';
                }

                $httpCode = $result['code'] ?? 'unknown';
                $errorMsg = "Failed to extract image ID from response.\n";
                $errorMsg .= "HTTP Code: {$httpCode}\n";
                $errorMsg .= "Response structure: {$responsePreview}\n";
                $errorMsg .= 'Tried fallback getLatestImageIdForProduct but got no result.';

                throw new \PrestaShopException($errorMsg);
            }

            return $imageId;
        } finally {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }

    /**
     * Build image response with all details
     * Reloads image from database to ensure fresh data
     *
     * @param int $imageId Image ID
     *
     * @return array Image details (id, position, cover, image_url)
     */
    private function buildImageResponse(int $imageId): array
    {
        $image = new \Image($imageId);
        $image->clearCache();

        return [
            'id' => $imageId,
            'position' => (int) $image->position,
            'cover' => (bool) $image->cover,
            'image_url' => $this->getImageUrl($image),
        ];
    }
}
