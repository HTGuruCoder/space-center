<div>
    <x-modal 
        wire:model="showModal" 
        title="{{ __('Face Verification Required') }}"
        box-class="max-w-lg"
        persistent
    >
        <div class="space-y-4">
            {{-- Instructions --}}
            <x-alert icon="mdi.face-recognition" class="alert-info">
                <p>{{ __('Please verify your identity to end your break.') }}</p>
                <p class="text-sm mt-1">{{ __('Position your face in front of the camera and take a photo.') }}</p>
            </x-alert>

            {{-- Camera Preview / Captured Photo --}}
            <div 
                x-data="faceCapture()"
                x-init="initCamera()"
                class="relative"
            >
                {{-- Video Preview (when not captured) --}}
                <div x-show="!captured" class="relative">
                    <video 
                        x-ref="video" 
                        autoplay 
                        playsinline
                        class="w-full rounded-lg bg-base-300 aspect-[4/3] object-cover"
                    ></video>
                    
                    {{-- Face Guide Overlay --}}
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="w-48 h-48 border-4 border-dashed border-warning/50 rounded-full"></div>
                    </div>

                    {{-- Capture Button --}}
                    <div class="absolute bottom-4 left-0 right-0 flex justify-center">
                        <button 
                            type="button"
                            @click="capturePhoto()"
                            class="btn btn-circle btn-lg btn-primary shadow-lg"
                            :disabled="!cameraReady"
                        >
                            <x-icon name="mdi.camera" class="w-8 h-8" />
                        </button>
                    </div>
                </div>

                {{-- Captured Photo Preview --}}
                <div x-show="captured" x-cloak class="relative">
                    <img 
                        x-ref="preview" 
                        class="w-full rounded-lg aspect-[4/3] object-cover"
                        alt="Captured photo"
                    />
                    
                    {{-- Retake Button --}}
                    <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-2">
                        <button 
                            type="button"
                            @click="retakePhoto()"
                            class="btn btn-ghost btn-sm"
                            wire:loading.attr="disabled"
                        >
                            <x-icon name="mdi.refresh" class="w-5 h-5" />
                            {{ __('Retake') }}
                        </button>
                    </div>
                </div>

                {{-- Hidden Canvas for capture --}}
                <canvas x-ref="canvas" class="hidden"></canvas>

                {{-- Camera Error --}}
                <template x-if="cameraError">
                    <div class="absolute inset-0 flex items-center justify-center bg-base-300 rounded-lg">
                        <div class="text-center p-4">
                            <x-icon name="mdi.camera-off" class="w-16 h-16 mx-auto text-error mb-2" />
                            <p class="text-error" x-text="cameraError"></p>
                            <button 
                                type="button" 
                                @click="initCamera()" 
                                class="btn btn-sm btn-outline mt-2"
                            >
                                {{ __('Retry Camera') }}
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Verification Status --}}
            @if($isVerifying)
                <div class="flex items-center justify-center gap-3 p-4 bg-info/10 rounded-lg">
                    <span class="loading loading-spinner loading-md text-info"></span>
                    <span class="text-info font-medium">{{ __('Verifying your identity...') }}</span>
                </div>
            @endif

            {{-- Error Message --}}
            @if($verificationFailed && $errorMessage)
                <x-alert icon="mdi.alert-circle" class="alert-error">
                    <p>{{ $errorMessage }}</p>
                    <p class="text-sm mt-1">{{ __('Please try again with better lighting and positioning.') }}</p>
                </x-alert>
            @endif
        </div>

        <x-slot:actions>
            <x-button 
                label="{{ __('Cancel') }}" 
                @click="$wire.close()" 
                :disabled="$isVerifying"
            />
            <x-button
                class="btn-primary"
                icon="mdi.check-circle"
                wire:click="verify"
                wire:loading.attr="disabled"
                :disabled="!$capturedPhoto || $isVerifying"
            >
                <span wire:loading.remove wire:target="verify">{{ __('Verify & End Break') }}</span>
                <span wire:loading wire:target="verify">{{ __('Verifying...') }}</span>
            </x-button>
        </x-slot:actions>
    </x-modal>

    @script
    <script>
        Alpine.data('faceCapture', () => ({
            stream: null,
            captured: false,
            cameraReady: false,
            cameraError: null,

            async initCamera() {
                this.cameraError = null;
                this.cameraReady = false;

                try {
                    // Stop existing stream if any
                    if (this.stream) {
                        this.stream.getTracks().forEach(track => track.stop());
                    }

                    // Request camera access
                    this.stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user',
                            width: { ideal: 640 },
                            height: { ideal: 480 }
                        },
                        audio: false
                    });

                    // Set video source
                    const video = this.$refs.video;
                    video.srcObject = this.stream;
                    
                    video.onloadedmetadata = () => {
                        this.cameraReady = true;
                    };

                } catch (error) {
                    console.error('Camera error:', error);
                    
                    if (error.name === 'NotAllowedError') {
                        this.cameraError = '{{ __("Camera access denied. Please allow camera access.") }}';
                    } else if (error.name === 'NotFoundError') {
                        this.cameraError = '{{ __("No camera found on this device.") }}';
                    } else {
                        this.cameraError = '{{ __("Failed to access camera.") }}';
                    }
                }
            },

            capturePhoto() {
                const video = this.$refs.video;
                const canvas = this.$refs.canvas;
                const preview = this.$refs.preview;

                // Set canvas dimensions to match video
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;

                // Draw video frame to canvas
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0);

                // Convert to base64
                const base64Image = canvas.toDataURL('image/jpeg', 0.9);

                // Show preview
                preview.src = base64Image;
                this.captured = true;

                // Send to Livewire
                $wire.setCapturedPhoto(base64Image);
            },

            retakePhoto() {
                this.captured = false;
                $wire.retry();
            },

            // Cleanup when component is destroyed
            destroy() {
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                }
            }
        }));

        // Also cleanup when modal closes
        $wire.on('close', () => {
            const video = document.querySelector('video');
            if (video && video.srcObject) {
                video.srcObject.getTracks().forEach(track => track.stop());
            }
        });
    </script>
    @endscript
</div>