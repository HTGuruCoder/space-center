<?php

namespace App\Livewire\Auth;

use App\Enums\CompensationUnitEnum;
use App\Enums\ContractTypeEnum;
use App\Enums\CountryEnum;
use App\Enums\CurrencyEnum;
use App\Enums\RoleEnum;
use App\Livewire\Forms\Auth\EmployeeRegistrationForm;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Store;
use App\Models\User;
use App\Services\FaceRecognitionService;
use App\Utils\Timezone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class EmployeeRegister extends Component
{
    use WithFileUploads, Toast;

    public EmployeeRegistrationForm $form;

    public int $currentStep = 1;

    public bool $showPinPad = false;

    public function nextStep()
    {
        try {
            match ($this->currentStep) {
                1 => $this->form->validateStep1(),
                2 => $this->form->validateStep2(),
                3 => $this->form->validateStep3(),
                4 => $this->form->validateStep4(),
                default => null,
            };

            $this->currentStep++;
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Show toast for Step 1 (face capture) and Step 2 (PIN) since they don't have inline validation
            // Steps 3, 4, 5 have inline validation at the input field level
            if ($this->currentStep === 1 || $this->currentStep === 2) {
                $errors = $e->validator->errors()->all();
                foreach ($errors as $error) {
                    $this->error($error);
                }
            }
            throw $e;
        }
    }

    public function previousStep()
    {
        $this->currentStep--;
    }

    public function register(FaceRecognitionService $faceService)
    {
        $this->form->validateStep5();

        DB::transaction(function () use ($faceService) {
            // Detect face and get face_token
            $detectResult = $faceService->detectFace($this->form->photo);

            if (!$detectResult['success']) {
                $this->error($detectResult['message']);
                throw new \Exception($detectResult['message']);
            }

            $faceToken = $detectResult['face_token'];

            // Add face_token to FaceSet (makes it permanent)
            $facesetToken = Cache::get('facepp_faceset_token');
            if ($facesetToken) {
                $addResult = $faceService->addToFaceSet($facesetToken, $faceToken);
                if (!$addResult['success']) {
                    $this->warning(__('Face token could not be added to FaceSet. It will expire in 72 hours.'));
                }
            }

            // Store photo in private storage
            $photoPath = $this->form->photo->store('profile-pictures', 'local');

            // Create user
            $user = User::create([
                'first_name' => $this->form->first_name,
                'last_name' => $this->form->last_name,
                'email' => $this->form->email,
                'password' => Hash::make($this->form->pin),
                'phone_number' => $this->form->phone_number,
                'timezone' => $this->form->timezone,
                'birth_date' => $this->form->birth_date,
                'country_code' => $this->form->country_code,
                'currency_code' => $this->form->currency_code,
                'picture_url' => $photoPath,
                'face_token' => $faceToken,
                'bank_name' => $this->form->bank_name,
                'bank_account_number' => $this->form->bank_account_number,
            ]);

            // Assign employee role
            $user->assignRole(RoleEnum::EMPLOYEE->value);

            // Create employee
            Employee::create([
                'user_id' => $user->id,
                'store_id' => $this->form->store_id,
                'position_id' => $this->form->position_id,
                'manager_id' => $this->form->manager_id,
                'type' => $this->form->type,
                'compensation_unit' => $this->form->compensation_unit,
                'compensation_amount' => $this->form->compensation_amount,
                'started_at' => $this->form->started_at,
                'ended_at' => $this->form->ended_at,
                'probation_period' => $this->form->probation_period,
                'bank_name' => $this->form->bank_name,
                'bank_account_number' => $this->form->bank_account_number,
                'contract_file_url' => $this->form->contract_file ? $this->form->contract_file->store('contracts', 'public') : null,
                'created_by' => $user->id,
            ]);

            // Log in the user (without remember token)
            Auth::login($user, false);
        });

        $this->success(
            title: __('Registration successful!'),
            description: __('Welcome to the team!'),
            redirectTo: '/'
        );
    }

    public function render()
    {
        return view('livewire.auth.employee-register', [
            'timezones' => Timezone::options(),
            'countries' => CountryEnum::options(),
            'currencies' => CurrencyEnum::options(),
            'stores' => Store::orderBy('name')->get(['id', 'name'])->toArray(),
            'positions' => Position::orderBy('name')->get(['id', 'name'])->toArray(),
            'managers' => Employee::with('user:id,first_name,last_name')->get(['id', 'user_id'])->map(fn($e) => ['id' => $e->id, 'name' => $e->user->full_name])->toArray(),
            'contractTypes' => ContractTypeEnum::options(),
            'compensationUnits' => CompensationUnitEnum::options(),
        ])->title(__('Employee Registration'));
    }
}
