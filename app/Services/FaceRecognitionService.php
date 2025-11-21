<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FaceRecognitionService
{
    private string $apiKey;
    private string $apiSecret;
    private string $apiUrl;
    private string $returnAttributes;
    private int $faceQualityThreshold;
    private int $blurThreshold;

    public function __construct()
    {
        $this->apiKey = config('facepp.api_key');
        $this->apiSecret = config('facepp.api_secret');
        $this->apiUrl = config('facepp.api_url');
        $this->returnAttributes = config('facepp.return_attributes');
        $this->faceQualityThreshold = config('facepp.face_quality_threshold');
        $this->blurThreshold = config('facepp.blur_threshold');
    }

    /**
     * Detect face in an image and return face_token with attributes.
     *
     * @param UploadedFile|string $image File upload or file path
     * @return array{success: bool, face_token?: string, attributes?: array, error?: string, message?: string}
     */
    public function detectFace(UploadedFile|string $image): array
    {
        try {
            $imagePath = $image instanceof UploadedFile
                ? $image->getRealPath()
                : Storage::disk('private')->path($image);

            if (!file_exists($imagePath)) {
                return [
                    'success' => false,
                    'error' => 'file_not_found',
                    'message' => __('Image file not found.'),
                ];
            }

            // Validate image
            $validation = $this->validateImage($imagePath);
            if (!$validation['success']) {
                return $validation;
            }

            $response = Http::asMultipart()
                ->post("{$this->apiUrl}/detect", [
                    [
                        'name' => 'api_key',
                        'contents' => $this->apiKey,
                    ],
                    [
                        'name' => 'api_secret',
                        'contents' => $this->apiSecret,
                    ],
                    [
                        'name' => 'image_file',
                        'contents' => fopen($imagePath, 'r'),
                        'filename' => 'face.jpg',
                    ],
                    [
                        'name' => 'return_attributes',
                        'contents' => $this->returnAttributes,
                    ],
                ]);

            $data = $response->json();

            if (isset($data['error_message'])) {
                Log::error('Face++ Detect API Error', ['error' => $data]);
                return [
                    'success' => false,
                    'error' => 'api_error',
                    'message' => __('An error occurred with the face recognition service.'),
                ];
            }

            if (empty($data['faces'])) {
                return [
                    'success' => false,
                    'error' => 'no_face_detected',
                    'message' => __('No face detected in the image.'),
                ];
            }

            if (count($data['faces']) > 1) {
                return [
                    'success' => false,
                    'error' => 'multiple_faces',
                    'message' => __('Multiple faces detected. Please ensure only one face is visible.'),
                ];
            }

            $face = $data['faces'][0];

            // Check face quality
            $qualityCheck = $this->checkFaceQuality($face['attributes'] ?? []);
            if (!$qualityCheck['success']) {
                return $qualityCheck;
            }

            return [
                'success' => true,
                'face_token' => $face['face_token'],
                'attributes' => $face['attributes'] ?? [],
            ];

        } catch (\Exception $e) {
            Log::error('Face++ Detect Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'exception',
                'message' => __('An error occurred while detecting face.'),
            ];
        }
    }

    /**
     * Compare two face tokens and return confidence score.
     *
     * @param string $faceToken1 First face token
     * @param string $faceToken2 Second face token
     * @return array{success: bool, confidence?: float, thresholds?: array, is_match?: bool, error?: string, message?: string}
     */
    public function compareFaces(string $faceToken1, string $faceToken2): array
    {
        try {
            $response = Http::asForm()->post("{$this->apiUrl}/compare", [
                'api_key' => $this->apiKey,
                'api_secret' => $this->apiSecret,
                'face_token1' => $faceToken1,
                'face_token2' => $faceToken2,
            ]);

            $data = $response->json();

            if (isset($data['error_message'])) {
                Log::error('Face++ Compare API Error', ['error' => $data]);
                return [
                    'success' => false,
                    'error' => 'api_error',
                    'message' => __('An error occurred with the face recognition service.'),
                ];
            }

            $confidence = $data['confidence'] ?? 0;
            $thresholds = $data['thresholds'] ?? [];

            // 1e-5 threshold = 76.5 confidence (high confidence match)
            $isMatch = $confidence >= 76.5;

            return [
                'success' => true,
                'confidence' => $confidence,
                'thresholds' => $thresholds,
                'is_match' => $isMatch,
            ];

        } catch (\Exception $e) {
            Log::error('Face++ Compare Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'exception',
                'message' => __('An error occurred while comparing faces.'),
            ];
        }
    }

    /**
     * Create a new FaceSet.
     *
     * @param string $displayName Display name for the FaceSet
     * @param string|null $outerId Optional outer ID for the FaceSet
     * @param array $tags Optional tags for the FaceSet
     * @return array{success: bool, faceset_token?: string, outer_id?: string, error?: string, message?: string}
     */
    public function createFaceSet(string $displayName, ?string $outerId = null, array $tags = []): array
    {
        try {
            $params = [
                'api_key' => $this->apiKey,
                'api_secret' => $this->apiSecret,
                'display_name' => $displayName,
            ];

            if ($outerId) {
                $params['outer_id'] = $outerId;
            }

            if (!empty($tags)) {
                $params['tags'] = implode(',', $tags);
            }

            $response = Http::asForm()->post("{$this->apiUrl}/faceset/create", $params);

            $data = $response->json();

            if (isset($data['error_message'])) {
                Log::error('Face++ Create FaceSet API Error', ['error' => $data]);
                return [
                    'success' => false,
                    'error' => 'api_error',
                    'message' => __('An error occurred while creating FaceSet.'),
                ];
            }

            return [
                'success' => true,
                'faceset_token' => $data['faceset_token'],
                'outer_id' => $data['outer_id'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Face++ Create FaceSet Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'exception',
                'message' => __('An error occurred while creating FaceSet.'),
            ];
        }
    }

    /**
     * Add a face token to a FaceSet (makes it permanent).
     *
     * @param string $facesetToken FaceSet token or outer_id
     * @param string $faceToken Face token to add
     * @return array{success: bool, face_added?: int, face_count?: int, error?: string, message?: string}
     */
    public function addToFaceSet(string $facesetToken, string $faceToken): array
    {
        try {
            $response = Http::asForm()->post("{$this->apiUrl}/faceset/addface", [
                'api_key' => $this->apiKey,
                'api_secret' => $this->apiSecret,
                'faceset_token' => $facesetToken,
                'face_tokens' => $faceToken,
            ]);

            $data = $response->json();

            if (isset($data['error_message'])) {
                Log::error('Face++ Add to FaceSet API Error', ['error' => $data]);
                return [
                    'success' => false,
                    'error' => 'api_error',
                    'message' => __('An error occurred while adding face to FaceSet.'),
                ];
            }

            return [
                'success' => true,
                'face_added' => $data['face_added'] ?? 0,
                'face_count' => $data['face_count'] ?? 0,
            ];

        } catch (\Exception $e) {
            Log::error('Face++ Add to FaceSet Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'exception',
                'message' => __('An error occurred while adding face to FaceSet.'),
            ];
        }
    }

    /**
     * Get FaceSet details.
     *
     * @param string $facesetToken FaceSet token or outer_id
     * @return array{success: bool, faceset?: array, error?: string, message?: string}
     */
    public function getFaceSetDetail(string $facesetToken): array
    {
        try {
            $response = Http::asForm()->post("{$this->apiUrl}/faceset/getdetail", [
                'api_key' => $this->apiKey,
                'api_secret' => $this->apiSecret,
                'faceset_token' => $facesetToken,
            ]);

            $data = $response->json();

            if (isset($data['error_message'])) {
                Log::error('Face++ Get FaceSet Detail API Error', ['error' => $data]);
                return [
                    'success' => false,
                    'error' => 'api_error',
                    'message' => __('An error occurred while getting FaceSet details.'),
                ];
            }

            return [
                'success' => true,
                'faceset' => $data,
            ];

        } catch (\Exception $e) {
            Log::error('Face++ Get FaceSet Detail Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'exception',
                'message' => __('An error occurred while getting FaceSet details.'),
            ];
        }
    }

    /**
     * Complete authentication flow: detect face in live image and compare with stored token.
     *
     * @param UploadedFile|string $liveImage Live captured image
     * @param string $storedFaceToken Stored face token from database
     * @return array{success: bool, confidence?: float, is_match?: bool, error?: string, message?: string}
     */
    public function authenticateFace(UploadedFile|string $liveImage, string $storedFaceToken): array
    {
        // Step 1: Detect face in live image
        $detectResult = $this->detectFace($liveImage);

        if (!$detectResult['success']) {
            return $detectResult;
        }

        $liveFaceToken = $detectResult['face_token'];

        // Step 2: Compare tokens
        $compareResult = $this->compareFaces($liveFaceToken, $storedFaceToken);

        if (!$compareResult['success']) {
            return $compareResult;
        }

        return [
            'success' => true,
            'confidence' => $compareResult['confidence'],
            'is_match' => $compareResult['is_match'],
        ];
    }

    /**
     * Validate image format, size, and dimensions.
     *
     * @param string $imagePath Path to image file
     * @return array{success: bool, error?: string, message?: string}
     */
    private function validateImage(string $imagePath): array
    {
        // Check file size (max 2MB)
        $fileSize = filesize($imagePath);
        if ($fileSize > 2 * 1024 * 1024) {
            return [
                'success' => false,
                'error' => 'file_too_large',
                'message' => __('Image file size must be less than 2MB.'),
            ];
        }

        // Get image info
        $imageInfo = @getimagesize($imagePath);
        if ($imageInfo === false) {
            return [
                'success' => false,
                'error' => 'invalid_image',
                'message' => __('Invalid image file.'),
            ];
        }

        [$width, $height, $type] = $imageInfo;

        // Check format (JPEG or PNG)
        if (!in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG])) {
            return [
                'success' => false,
                'error' => 'invalid_format',
                'message' => __('Image must be in JPG or PNG format.'),
            ];
        }

        // Check dimensions (48x48 to 4096x4096)
        if ($width < 48 || $height < 48 || $width > 4096 || $height > 4096) {
            return [
                'success' => false,
                'error' => 'invalid_dimensions',
                'message' => __('Image dimensions must be between 48x48 and 4096x4096 pixels.'),
            ];
        }

        return ['success' => true];
    }

    /**
     * Check face quality attributes.
     *
     * @param array $attributes Face attributes from detect API
     * @return array{success: bool, error?: string, message?: string}
     */
    private function checkFaceQuality(array $attributes): array
    {
        // Check face quality
        if (isset($attributes['facequality']['value'])) {
            $quality = $attributes['facequality']['value'];
            if ($quality < $this->faceQualityThreshold) {
                return [
                    'success' => false,
                    'error' => 'poor_quality',
                    'message' => __('Face quality is too low. Please ensure good lighting and face the camera directly.'),
                ];
            }
        }

        // Check blur
        if (isset($attributes['blur']['blurness']['value'])) {
            $blur = $attributes['blur']['blurness']['value'];
            if ($blur > $this->blurThreshold) {
                return [
                    'success' => false,
                    'error' => 'too_blurry',
                    'message' => __('Image is too blurry. Please hold the camera steady.'),
                ];
            }
        }

        // Check for sunglasses
        if (isset($attributes['eyestatus'])) {
            $leftEye = $attributes['eyestatus']['left_eye_status'] ?? [];
            $rightEye = $attributes['eyestatus']['right_eye_status'] ?? [];

            if (
                ($leftEye['dark_glasses'] ?? 0) > 50 ||
                ($rightEye['dark_glasses'] ?? 0) > 50
            ) {
                return [
                    'success' => false,
                    'error' => 'sunglasses_detected',
                    'message' => __('Please remove sunglasses.'),
                ];
            }

            // Check if eyes are open
            if (
                ($leftEye['normal_glass_eye_open'] ?? 0) < 50 &&
                ($leftEye['no_glass_eye_open'] ?? 0) < 50
            ) {
                return [
                    'success' => false,
                    'error' => 'eyes_closed',
                    'message' => __('Please keep your eyes open.'),
                ];
            }
        }

        return ['success' => true];
    }
}
