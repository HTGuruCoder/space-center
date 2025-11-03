<?php

namespace App\Livewire\Ui\Forms;

use App\Enums\CountryEnum;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class PhoneNumberInput extends Component
{
    #[Modelable]
    public ?string $phoneNumber = null;

    public string $countryCode = 'US';
    public string $nationalNumber = '';

    public ?string $label = null;
    public ?string $hint = null;
    public ?string $placeholder = null;
    public bool $required = false;
    public bool $disabled = false;

    protected $phoneUtil;

    public function mount(): void
    {
        $this->phoneUtil = PhoneNumberUtil::getInstance();

        // Parse existing phone number if provided
        if ($this->phoneNumber) {
            $this->parsePhoneNumber($this->phoneNumber);
        }
    }

    public function updatedCountryCode(): void
    {
        $this->updatePhoneNumber();
    }

    public function updatedNationalNumber(): void
    {
        $this->updatePhoneNumber();
    }

    protected function parsePhoneNumber(string $phoneNumber): void
    {
        try {
            $parsedNumber = $this->phoneUtil->parse($phoneNumber);
            $this->countryCode = $this->phoneUtil->getRegionCodeForNumber($parsedNumber);
            $this->nationalNumber = (string) $parsedNumber->getNationalNumber();
        } catch (NumberParseException $e) {
            // If parsing fails, keep the raw number in national number
            $this->nationalNumber = $phoneNumber;
        }
    }

    protected function updatePhoneNumber(): void
    {
        if (empty($this->nationalNumber)) {
            $this->phoneNumber = null;
            return;
        }

        try {
            $parsedNumber = $this->phoneUtil->parse($this->nationalNumber, $this->countryCode);

            if ($this->phoneUtil->isValidNumber($parsedNumber)) {
                // Format in E164 format for storage (+15551234567)
                $this->phoneNumber = $this->phoneUtil->format($parsedNumber, PhoneNumberFormat::E164);
            } else {
                $this->phoneNumber = null;
            }
        } catch (NumberParseException $e) {
            $this->phoneNumber = null;
        }
    }

    public function getFormattedPhoneNumberProperty(): ?string
    {
        if (!$this->phoneNumber) {
            return null;
        }

        try {
            $parsedNumber = $this->phoneUtil->parse($this->phoneNumber);
            return $this->phoneUtil->format($parsedNumber, PhoneNumberFormat::INTERNATIONAL);
        } catch (NumberParseException $e) {
            return $this->phoneNumber;
        }
    }

    public function getCountryOptionsProperty(): array
    {
        return CountryEnum::options();
    }

    public function getCountryDialCodeProperty(): string
    {
        try {
            $exampleNumber = $this->phoneUtil->getExampleNumber($this->countryCode);
            return '+' . $exampleNumber->getCountryCode();
        } catch (\Exception $e) {
            return '+1';
        }
    }

    public function getIsValidProperty(): bool
    {
        if (empty($this->nationalNumber)) {
            return !$this->required;
        }

        try {
            $parsedNumber = $this->phoneUtil->parse($this->nationalNumber, $this->countryCode);
            return $this->phoneUtil->isValidNumber($parsedNumber);
        } catch (NumberParseException $e) {
            return false;
        }
    }

    public function render()
    {
        return view('livewire.ui.forms.phone-number-input');
    }
}
