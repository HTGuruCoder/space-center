<?php

namespace App\Livewire\Auth;

use App\Enums\RoleEnum;
use App\Livewire\Forms\Auth\EmployeeLoginForm;
use App\Models\User;
use App\Services\FaceRecognitionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class EmployeeLogin extends Component
{
    use WithFileUploads, Toast;

    public EmployeeLoginForm $form;

    public bool $showFaceCapture = false;
    public bool $showPinPad = false;
    public ?User $user = null;

    public function nextToFaceCapture()
    {
        //dd("sddjhsj");
        $this->form->validateEmail();

        // Check if user exists and has employee role
        $this->user = User::where('email', $this->form->email)->first();

        if (!$this->user) {
            $this->error(__('User not found.'));
            return;
        }

        if (!$this->user->hasRole(RoleEnum::EMPLOYEE->value)) {
            $this->error(__('This login is for employees only. Please use the admin login.'));
            return;
        }

        if (!$this->user->hasFaceAuthEnabled()) {
            $this->error(__('Face authentication is not enabled for this account.'));
            return;
        }

        $this->showFaceCapture = true;
        $this->form->currentStep = 2;
    }

    public function verifyFace(FaceRecognitionService $faceService)
    {
        try {
            $this->form->validatePhoto();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            foreach ($errors as $error) {
                $this->error($error);
            }
            throw $e;
        }

        if (!$this->user) {
            $this->error(__('Session expired. Please start over.'));
            $this->reset();
            return;
        }

        // Authenticate face using Face++ API
        $result = $faceService->authenticateFace(
            $this->form->photo,
            $this->user->face_token
        );

        if (!$result['success']) {
            $this->error($result['message']);
            return;
        }

        if (!$result['is_match']) {
            $this->error(__('Face recognition failed. The face does not match our records.'));
            return;
        }

        // Face verified, show PIN pad
        $this->showFaceCapture = false;
        $this->showPinPad = true;
        $this->form->currentStep = 3;

        $this->success(__('Face verified! Please enter your PIN.'));
    }

    public function login()
    {
        $this->form->validatePin();

        if (!$this->user) {
            $this->error(__('Session expired. Please start over.'));
            $this->reset();
            return;
        }

        // Verify PIN
        if (!Hash::check($this->form->pin, $this->user->password)) {
            $this->error(__('Invalid PIN. Please try again.'));
            $this->form->pin = '';
            return;
        }

        // Login user without remember token
        Auth::login($this->user, false);

        session()->regenerate();

        $this->success(
            title: __('Welcome back!'),
            description: __('Login successful.'),
            redirectTo: '/'
        );
    }

    public function backToEmail()
    {
        $this->reset(['showFaceCapture', 'showPinPad', 'user']);
        $this->form->currentStep = 1;
        $this->form->photo = null;
        $this->form->pin = '';
    }

    public function render()
    {
        return view('livewire.auth.employee-login')
            ->title(__('Employee Login'));
    }
}