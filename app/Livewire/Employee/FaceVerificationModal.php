<?php

namespace App\Livewire\Employee;

use App\Services\BreakService;
use App\Services\FaceRecognitionService;
use Illuminate\Support\Facades\Storage;
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
        $this->capturedPhoto = $base64Image;
        $this->verificationFailed = false;
        $this->errorMessage = null;
    }

    /**
     * Verify the captured face and perform the action.
     */
    public function verify(FaceRecognitionService $faceService, BreakService $breakService): void
    {
        if (!$this->capturedPhoto) {
            $this->error(__('Please capture your photo first.'));
            return;
        }

        $this->isVerifying = true;
        $this->verificationFailed = false;
        $this->errorMessage = null;

        try {
            $employee = auth()->user()->employee;

            // Check if employee has a stored face token
            if (!$employee->face_token) {
                $this->error(__('No face registered. Please contact your administrator.'));
                $this->isVerifying = false;
                return;
            }

            // Convert base64 to temporary file
            $tempPath = $this->saveBase64Image($this->capturedPhoto);

            if (!$tempPath) {
                $this->error(__('Failed to process the captured image.'));
                $this->isVerifying = false;
                return;
            }

            // Authenticate face using Face++ API
            $result = $faceService->authenticateFace($tempPath, $employee->face_token);

            // Clean up temp file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            if (!$result['success']) {
                $this->verificationFailed = true;
                $this->errorMessage = $result['message'] ?? __('Face verification failed.');
                $this->isVerifying = false;
                return;
            }

            if (!$result['is_match']) {
                $this->verificationFailed = true;
                $this->errorMessage = __('Face does not match. Please try again.');
                $this->isVerifying = false;
                return;
            }

            // Face verified! Now perform the action
            $this->performAction($breakService);

        } catch (\Exception $e) {
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

        switch ($this->action) {
            case 'end_break':
                $break = $breakService->endBreak($employee, $this->latitude, $this->longitude);
                
                $this->success(__('Break ended. Duration: :duration', [
                    'duration' => $break->getFormattedDuration()
                ]));
                
                $this->dispatch('break-ended');
                $this->dispatch('work-period-updated');
                break;

            // Add more actions here if needed (clock_out, etc.)
            default:
                $this->error(__('Unknown action.'));
                break;
        }

        $this->showModal = false;
        $this->reset(['capturedPhoto', 'isVerifying', 'action']);
    }

    /**
     * Save base64 image to temporary file.
     */
    protected function saveBase64Image(string $base64Image): ?string
    {
        try {
            // Remove data URL prefix if present
            $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
            $imageData = base64_decode($base64Image);

            if ($imageData === false) {
                return null;
            }

            // Create temp file
            $tempPath = storage_path('app/temp/' . uniqid('face_') . '.jpg');
            
            // Ensure temp directory exists
            $tempDir = dirname($tempPath);
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            file_put_contents($tempPath, $imageData);

            return $tempPath;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Close modal and reset state.
     */
    public function close(): void
    {
        $this->showModal = false;
        $this->reset(['capturedPhoto', 'isVerifying', 'verificationFailed', 'errorMessage', 'action']);
    }

    /**
     * Retry capture after failure.
     */
    public function retry(): void
    {
        $this->capturedPhoto = null;
        $this->verificationFailed = false;
        $this->errorMessage = null;
    }

    public function render()
    {
        return view('livewire.employee.face-verification-modal');
    }
}