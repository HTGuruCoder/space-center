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
            //dd(123);
            $imagePath = $image instanceof UploadedFile
                ? $image->getRealPath()
                : Storage::disk('private')->path($image);

            if (!file_exists($imagePath)) {
                return [
                    'success' => false,
                    'error' => 'file_not_found',
                    'message' => __('face.file_not_found'),
                ];
            }

            // Validate image
            $validation = $this->validateImage($imagePath);
            if (!$validation['success']) {
                return $validation;
            }

            //dd($this->apiKey);

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

            dd(123);

            $data = $response->json();
            dd($data);

            if (isset($data['error_message'])) {
                Log::error('Face++ Detect API Error', ['error' => $data]);
                return [
                    'success' => false,
                    'error' => 'api_error',
                    'message' => __('face.api_error'),
                ];
            }

            if (empty($data['faces'])) {
                return [
                    'success' => false,
                    'error' => 'no_face_detected',
                    'message' => __('face.no_face_detected'),
                ];
            }

            if (count($data['faces']) > 1) {
                return [
                    'success' => false,
                    'error' => 'multiple_faces',
                    'message' => __('face.multiple_faces'),
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
                'message' => __('face.exception'),
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
                    'message' => __('face.compare_error'),
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
                'message' => __('face.compare_exception'),
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
                    'message' => __('face.faceset_create_error'),
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
                'message' => __('face.faceset_create_error'),
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
                    'message' => __('face.faceset_add_error'),
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
                'message' => __('face.faceset_add_error'),
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
                    'message' => __('face.faceset_get_error'),
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
                'message' => __('face.faceset_get_error'),
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
                'message' => __('face.file_too_large'),
            ];
        }

        // Get image info
        $imageInfo = @getimagesize($imagePath);
        if ($imageInfo === false) {
            return [
                'success' => false,
                'error' => 'invalid_image',
                'message' => __('face.invalid_image'),
            ];
        }

        [$width, $height, $type] = $imageInfo;

        // Check format (JPEG or PNG)
        if (!in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG])) {
            return [
                'success' => false,
                'error' => 'invalid_format',
                'message' => __('face.invalid_format'),
            ];
        }

        // Check dimensions (48x48 to 4096x4096)
        if ($width < 48 || $height < 48 || $width > 4096 || $height > 4096) {
            return [
                'success' => false,
                'error' => 'invalid_dimensions',
                'message' => __('face.invalid_dimensions'),
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
                    'message' => __('face.poor_quality'),
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
                    'message' => __('face.too_blurry'),
                ];
            }
        }

        // Check eyestatus attributes
        // According to Face++ API documentation:
        // - Values are floating-point numbers between [0,100]
        // - The sum of all values for each eye = 100 (probability distribution)
        // - dark_glasses: confidence that the eye wears dark glasses
        // - no_glass_eye_open/normal_glass_eye_open: confidence that eye is open
        if (isset($attributes['eyestatus'])) {
            $leftEye = $attributes['eyestatus']['left_eye_status'] ?? [];
            $rightEye = $attributes['eyestatus']['right_eye_status'] ?? [];

            // 1. Check for dark glasses
            // Since sum = 100, dark_glasses > 60 means it's the dominant state
            // We require BOTH eyes to show dark glasses to avoid false positives
            $leftDarkGlasses = $leftEye['dark_glasses'] ?? 0;
            $rightDarkGlasses = $rightEye['dark_glasses'] ?? 0;

            if ($leftDarkGlasses > 60 && $rightDarkGlasses > 60) {
                return [
                    'success' => false,
                    'error' => 'sunglasses_detected',
                    'message' => __('face.sunglasses_detected'),
                ];
            }

            // 2. Check if eyes are open
            // Sum all "eye open" states for each eye
            $leftEyeOpen = ($leftEye['no_glass_eye_open'] ?? 0) +
                          ($leftEye['normal_glass_eye_open'] ?? 0);

            $rightEyeOpen = ($rightEye['no_glass_eye_open'] ?? 0) +
                           ($rightEye['normal_glass_eye_open'] ?? 0);

            // Calculate total "closed" probability for each eye
            $leftEyeClosed = ($leftEye['no_glass_eye_close'] ?? 0) +
                            ($leftEye['normal_glass_eye_close'] ?? 0);

            $rightEyeClosed = ($rightEye['no_glass_eye_close'] ?? 0) +
                             ($rightEye['normal_glass_eye_close'] ?? 0);

            // Only fail if BOTH eyes show closed state is dominant (>60)
            if ($leftEyeClosed > 60 && $rightEyeClosed > 60) {
                return [
                    'success' => false,
                    'error' => 'eyes_closed',
                    'message' => __('face.eyes_closed'),
                ];
            }
        }

        return ['success' => true];
    }
}
