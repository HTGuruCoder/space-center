<x-drawer
    wire:model="showDrawer"
    :title="$isEditMode ? __('Edit Role') : __('Create Role')"
    right
    class="w-full sm:w-[500px] lg:w-1/2 max-w-full"
    separator
    with-close-button
>
    <x-form wire:submit="save">
        <div class="space-y-6">
            {{-- Role Name --}}
            <x-input
                label="{{ __('Role Name') }}"
                wire:model="name"
                placeholder="{{ __('Enter role name') }}"
                icon="mdi.shield-account"
                required
            />

            {{-- Permissions Selection --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">{{ __('Permissions') }}</h3>
                    <div class="flex gap-2">
                        <x-button
                            wire:click="selectAllPermissions"
                            class="btn-xs btn-primary"
                            icon="mdi.checkbox-multiple-marked"
                        >
                            {{ __('Select All') }}
                        </x-button>
                        <x-button
                            wire:click="deselectAllPermissions"
                            class="btn-xs btn-ghost"
                            icon="mdi.checkbox-multiple-blank-outline"
                        >
                            {{ __('Deselect All') }}
                        </x-button>
                    </div>
                </div>

                @foreach($permissionsGrouped as $category => $permissions)
                    <div class="space-y-2 border border-base-300 rounded-lg p-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $category }}</h4>
                            <x-button
                                wire:click="toggleCategoryPermissions('{{ $category }}')"
                                class="btn-xs btn-ghost"
                                icon="mdi.checkbox-multiple-marked-outline"
                            >
                                {{ __('Toggle All') }}
                            </x-button>
                        </div>
                        <div class="space-y-1 pl-4">
                            @foreach($permissions as $permission)
                                <x-checkbox
                                    label="{{ $permission['label'] }}"
                                    wire:model="selectedPermissions"
                                    value="{{ $permission['value'] }}"
                                    hint="{{ $permission['description'] }}"
                                />
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Action Buttons --}}
        <x-slot:actions>
            <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center w-full gap-3">
                {{-- Cancel Button --}}
                <x-button
                    label="{{ __('Cancel') }}"
                    @click="$wire.closeDrawer()"
                    class="order-last sm:order-first"
                />

                <div class="flex flex-col sm:flex-row gap-2">
                    {{-- Add and Add Another Button (only in create mode) --}}
                    @if(!$isEditMode)
                        <x-button
                            wire:click="saveAndAddAnother"
                            spinner="saveAndAddAnother"
                            class="btn-secondary"
                        >
                            {{ __('Add & Add Another') }}
                        </x-button>
                    @endif

                    {{-- Primary Action Button --}}
                    <x-button
                        :label="$isEditMode ? __('Update') : __('Add')"
                        type="submit"
                        spinner="save"
                        class="btn-primary"
                    />
                </div>
            </div>
        </x-slot:actions>
    </x-form>
</x-drawer>
