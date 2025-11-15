<x-modal wire:model="showModal" title="{{ __('Change Password for :name', ['name' => $userName]) }}" separator>
    <x-form wire:submit="updatePassword">
        <div class="space-y-6">
            {{-- New Password --}}
            <x-password
                label="{{ __('New Password') }}"
                wire:model="new_password"
                icon="mdi.lock-reset"
                placeholder="{{ __('Minimum 8 characters') }}"
                right
                inline
                autocomplete="new-password"
            />

            {{-- Confirm New Password --}}
            <x-password
                label="{{ __('Confirm New Password') }}"
                wire:model="new_password_confirmation"
                icon="mdi.lock-check"
                placeholder="{{ __('Re-enter new password') }}"
                right
                inline
                autocomplete="new-password"
            />
        </div>

        {{-- Actions --}}
        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" @click="$wire.closeModal()" />
            <x-button label="{{ __('Update Password') }}" type="submit" spinner="updatePassword" class="btn-primary" />
        </x-slot:actions>
    </x-form>
</x-modal>
