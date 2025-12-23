<?php

namespace App\Livewire\Employee;

use App\Services\BreakService;
use App\Services\FaceRecognitionService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class FaceVerificationModal extends Component
{
    use Toast;
    use WithFileUploads;

    public bool $showModal = false;
    public string $action = ''; // 'end_break', 'clock_out', etc.

    // Geolocation
    public ?float $latitude = null;
    public ?float $longitude = null;

    // Photo captured from webcam (base64)
    public ?string $capturedPhoto = null;

    // Verification state
    public bool $isVerifying = false;
    public bool $verificationFailed = false;
    public ?string $errorMessage = null;

    /**
     * Show the face verification modal.
     */
    #[On('show-face-verification')]
    public function show(string $action, ?float $latitude = null, ?float $longitude = null): void
    {
        Log::info('FaceVerificationModal: Opening modal', [
            'action' => $action,
            'latitude' => $latitude,
            'longitude' => $longitude
        ]);

        $this->reset(['capturedPhoto', 'isVerifying', 'verificationFailed', 'errorMessage']);

        $this->action = $action;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->showModal = true;
    }

    /**
     * Receive captured photo from JavaScript.
     */
    public function setCapturedPhoto(string $base64Image): void
    {
        Log::info('FaceVerificationModal: Photo received', [
            'length' => strlen($base64Image),
            'starts_with' => substr($base64Image, 0, 50)
        ]);

        $this->capturedPhoto = $base64Image;
        $this->verificationFailed = false;
        $this->errorMessage = null;
    }

    /**
     * Verify the captured face and perform the action.
     */
    public function verify(FaceRecognitionService $faceService, BreakService $breakService): void
    {
        Log::info('FaceVerificationModal: Starting verification');

        if (!$this->capturedPhoto) {
            $this->error(__('Please capture your photo first.'));
            return;
        }

        $this->isVerifying = true;
        $this->verificationFailed = false;
        $this->errorMessage = null;

        try {
            $user = auth()->user();
            $employee = $user->employee;

            if (!$employee) {
                Log::error('FaceVerificationModal: No employee record');
                $this->error(__('Employee record not found.'));
                $this->isVerifying = false;
                return;
            }

            // IMPORTANT: face_token is stored on the USER model, not the Employee model!
            // This is how it's used in EmployeeLogin.php during authentication
            $faceToken = $user->face_token;

            // Check if user has a stored face token
            if (!$faceToken) {
                Log::warning('FaceVerificationModal: No face token for user', [
                    'user_id' => $user->id,
                    'employee_id' => $employee->id
                ]);
                $this->error(__('No face registered. Please contact your administrator.'));
                $this->isVerifying = false;
                return;
            }

            Log::info('FaceVerificationModal: User has face token', [
                'user_id' => $user->id,
                'employee_id' => $employee->id,
                'face_token' => substr($faceToken, 0, 10) . '...'
            ]);

            // Convert base64 to temporary file
            $tempPath = $this->saveBase64Image($this->capturedPhoto);

            if (!$tempPath) {
                Log::error('FaceVerificationModal: Failed to save temp image');
                $this->error(__('Failed to process the captured image.'));
                $this->isVerifying = false;
                return;
            }

            Log::info('FaceVerificationModal: Temp image saved', ['path' => $tempPath]);

            // Authenticate face using Face++ API
            $result = $faceService->authenticateFace($tempPath, $faceToken);

            Log::info('FaceVerificationModal: Face++ result', $result);

            // Clean up temp file
            if (file_exists($tempPath)) {
                unlink($tempPath);
                Log::info('FaceVerificationModal: Temp file cleaned up');
            }

            if (!$result['success']) {
                $this->verificationFailed = true;
                $this->errorMessage = $result['message'] ?? __('Face verification failed.');
                $this->isVerifying = false;
                Log::warning('FaceVerificationModal: Verification failed', [
                    'error' => $result['error'] ?? 'unknown',
                    'message' => $this->errorMessage
                ]);
                return;
            }

            if (!$result['is_match']) {
                $this->verificationFailed = true;
                $this->errorMessage = __('Face does not match. Please try again.') .
                    ' (Confidence: ' . round($result['confidence'] ?? 0, 1) . '%)';
                $this->isVerifying = false;
                Log::warning('FaceVerificationModal: Face does not match', [
                    'confidence' => $result['confidence'] ?? 0
                ]);
                return;
            }

            Log::info('FaceVerificationModal: Face verified successfully', [
                'confidence' => $result['confidence'] ?? 0
            ]);

            // Face verified! Now perform the action
            $this->performAction($breakService);

        } catch (\Exception $e) {
            Log::error('FaceVerificationModal: Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->verificationFailed = true;
            $this->errorMessage = $e->getMessage();
            $this->isVerifying = false;
        }
    }

    /**
     * Perform the action after successful face verification.
     */
    protected function performAction(BreakService $breakService): void
    {
        $employee = auth()->user()->employee;

        Log::info('FaceVerificationModal: Performing action', [
            'action' => $this->action,
            'employee_id' => $employee->id
        ]);

        switch ($this->action) {
            case 'end_break':
                $break = $breakService->endBreak($employee, $this->latitude, $this->longitude);

                $this->success(__('Break ended. Duration: :duration', [
                    'duration' => $break->getFormattedDuration()
                ]));

                $this->dispatch('break-ended');
                $this->dispatch('work-period-updated');

                Log::info('FaceVerificationModal: Break ended successfully', [
                    'break_id' => $break->id,
                    'duration' => $break->duration_minutes
                ]);
                break;

            default:
                Log::warning('FaceVerificationModal: Unknown action', [
                    'action' => $this->action
                ]);
                $this->error(__('Unknown action.'));
                break;
        }

        $this->showModal = false;
        $this->isVerifying = false;
        $this->reset(['capturedPhoto', 'action']);
    }

    /**
     * Save base64 image to temporary file.
     */
    protected function saveBase64Image(string $base64Image): ?string
    {
        try {
            // Remove data URL prefix if present
            $base64Data = $base64Image;
            if (str_contains($base64Image, ',')) {
                $base64Data = explode(',', $base64Image)[1];
            }

            $imageData = base64_decode($base64Data);

            if ($imageData === false) {
                Log::error('FaceVerificationModal: Failed to decode base64');
                return null;
            }

            // Create temp directory if it doesn't exist
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Create temp file
            $tempPath = $tempDir . '/' . uniqid('face_') . '.jpg';

            $written = file_put_contents($tempPath, $imageData);

            if ($written === false) {
                Log::error('FaceVerificationModal: Failed to write temp file');
                return null;
            }

            Log::info('FaceVerificationModal: Temp file created', [
                'path' => $tempPath,
                'size' => $written
            ]);

            return $tempPath;

        } catch (\Exception $e) {
            Log::error('FaceVerificationModal: Exception saving image', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Close modal and reset state.
     */
    public function close(): void
    {
        Log::info('FaceVerificationModal: Closing modal');
        $this->showModal = false;
        $this->reset(['capturedPhoto', 'isVerifying', 'verificationFailed', 'errorMessage', 'action']);
    }

    /**
     * Retry capture after failure.
     */
    public function retry(): void
    {
        Log::info('FaceVerificationModal: Retrying capture');
        $this->capturedPhoto = null;
        $this->verificationFailed = false;
        $this->errorMessage = null;
    }

    public function render()
    {
        return view('livewire.employee.face-verification-modal');
    }
}