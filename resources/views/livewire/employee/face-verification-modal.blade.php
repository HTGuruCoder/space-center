<div>
    <x-modal wire:model="showModal" title="{{ __('Face Verification Required') }}" box-class="max-w-lg" persistent>
        <div class="space-y-4">
            {{-- Instructions --}}
            <x-alert icon="mdi.face-recognition" class="alert-info">
                <p>{{ __('Please verify your identity to end your break.') }}</p>
                <p class="text-sm mt-1">{{ __('Position your face in front of the camera and take a photo.') }}</p>
            </x-alert>

            {{-- Camera Preview / Captured Photo --}}
            <div x-data="faceCapture(@js($capturedPhoto))" x-init="init()" class="relative">
                {{-- Video Preview (when not captured) --}}
                <div x-show="!captured" x-transition class="relative">
                    <video x-ref="video" autoplay playsinline muted
                        class="w-full rounded-lg bg-base-300 aspect-[4/3] object-cover mirror-video"></video>

                    {{-- Face Guide Overlay --}}
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="w-48 h-48 border-4 border-dashed border-warning/50 rounded-full"></div>
                    </div>

                    {{-- Camera Loading --}}
                    <div x-show="!cameraReady && !cameraError"
                        class="absolute inset-0 flex items-center justify-center bg-base-300/80 rounded-lg">
                        <div class="text-center">
                            <span class="loading loading-spinner loading-lg text-primary"></span>
                            <p class="mt-2 text-sm">{{ __('Starting camera...') }}</p>
                        </div>
                    </div>

                    {{-- Capture Button --}}
                    <div x-show="cameraReady" class="absolute bottom-4 left-0 right-0 flex justify-center">
                        <button type="button" @click="capturePhoto()"
                            class="btn btn-circle btn-lg btn-primary shadow-lg">
                            <x-icon name="mdi.camera" class="w-8 h-8" />
                        </button>
                    </div>
                </div>

                {{-- Captured Photo Preview --}}
                <div x-show="captured" x-transition x-cloak class="relative">
                    <img :src="previewSrc" class="w-full rounded-lg aspect-[4/3] object-cover" alt="Captured photo" />

                    {{-- Success indicator --}}
                    <div
                        class="absolute top-2 right-2 bg-success text-success-content px-2 py-1 rounded-full text-xs flex items-center gap-1">
                        <x-icon name="mdi.check" class="w-4 h-4" />
                        {{ __('Photo captured') }}
                    </div>

                    {{-- Retake Button --}}
                    <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-2">
                        <button type="button" @click="retakePhoto()" class="btn btn-sm bg-base-100/80 hover:bg-base-100"
                            wire:loading.attr="disabled">
                            <x-icon name="mdi.refresh" class="w-5 h-5" />
                            {{ __('Retake') }}
                        </button>
                    </div>
                </div>

                {{-- Hidden Canvas for capture --}}
                <canvas x-ref="canvas" class="hidden"></canvas>

                {{-- Camera Error --}}
                <div x-show="cameraError" x-cloak
                    class="flex items-center justify-center bg-base-300 rounded-lg aspect-[4/3]">
                    <div class="text-center p-4">
                        <x-icon name="mdi.camera-off" class="w-16 h-16 mx-auto text-error mb-2" />
                        <p class="text-error" x-text="cameraError"></p>
                        <button type="button" @click="initCamera()" class="btn btn-sm btn-outline mt-2">
                            {{ __('Retry Camera') }}
                        </button>
                    </div>
                </div>
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
            <x-button label="{{ __('Cancel') }}" @click="$wire.close()" :disabled="$isVerifying" />
            <x-button class="btn-primary" icon="mdi.check-circle" wire:click="verify" wire:loading.attr="disabled"
                :disabled="!$capturedPhoto || $isVerifying">
                <span wire:loading.remove wire:target="verify">{{ __('Verify & End Break') }}</span>
                <span wire:loading wire:target="verify">{{ __('Verifying...') }}</span>
            </x-button>
        </x-slot:actions>
    </x-modal>

    {{-- CSS for mirrored video --}}
    <style>
        .mirror-video {
            transform: scaleX(-1);
        }
    </style>

    @script
    <script>
        Alpine.data('faceCapture', (initialPhoto) => ({
            stream: null,
            captured: !!initialPhoto,
            previewSrc: initialPhoto || '',
            cameraReady: false,
            cameraError: null,

            init() {
                // Watch for modal open
                this.$watch('$wire.showModal', (value) => {
                    if (value) {
                        this.captured = false;
                        this.previewSrc = '';
                        this.cameraError = null;
                        this.$nextTick(() => this.initCamera());
                    } else {
                        this.stopCamera();
                    }
                });

                // If modal is already open, init camera
                if (this.$wire.showModal) {
                    this.$nextTick(() => this.initCamera());
                }
            },

            async initCamera() {
                this.cameraError = null;
                this.cameraReady = false;

                try {
                    // Stop existing stream if any
                    this.stopCamera();

                    console.log('Requesting camera access...');

                    // Request camera access
                    this.stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user',
                            width: { ideal: 640 },
                            height: { ideal: 480 }
                        },
                        audio: false
                    });

                    console.log('Camera access granted');

                    // Set video source
                    const video = this.$refs.video;
                    if (video) {
                        video.srcObject = this.stream;

                        video.onloadedmetadata = () => {
                            video.play();
                            this.cameraReady = true;
                            console.log('Camera ready');
                        };
                    }

                } catch (error) {
                    console.error('Camera error:', error);

                    if (error.name === 'NotAllowedError') {
                        this.cameraError = '{{ __("Camera access denied. Please allow camera access.") }}';
                    } else if (error.name === 'NotFoundError') {
                        this.cameraError = '{{ __("No camera found on this device.") }}';
                    } else if (error.name === 'NotReadableError') {
                        this.cameraError = '{{ __("Camera is in use by another application.") }}';
                    } else {
                        this.cameraError = '{{ __("Failed to access camera.") }} (' + error.message + ')';
                    }
                }
            },

            capturePhoto() {
                console.log('Capturing photo...');

                const video = this.$refs.video;
                const canvas = this.$refs.canvas;

                if (!video || !canvas) {
                    console.error('Video or canvas not found');
                    return;
                }

                // Set canvas dimensions to match video
                canvas.width = video.videoWidth || 640;
                canvas.height = video.videoHeight || 480;

                console.log('Canvas dimensions:', canvas.width, 'x', canvas.height);

                // Draw video frame to canvas (flip horizontally to match mirror view)
                const ctx = canvas.getContext('2d');
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                // Convert to base64
                const base64Image = canvas.toDataURL('image/jpeg', 0.9);

                console.log('Photo captured, base64 length:', base64Image.length);

                // Show preview
                this.previewSrc = base64Image;
                this.captured = true;

                // Stop camera to save resources
                this.stopCamera();

                // Send to Livewire
                this.$wire.setCapturedPhoto(base64Image);

                console.log('Photo sent to Livewire');
            },

            retakePhoto() {
                console.log('Retaking photo...');
                this.captured = false;
                this.previewSrc = '';
                this.$wire.retry();
                this.$nextTick(() => this.initCamera());
            },

            stopCamera() {
                if (this.stream) {
                    this.stream.getTracks().forEach(track => {
                        track.stop();
                        console.log('Camera track stopped');
                    });
                    this.stream = null;
                }
                this.cameraReady = false;
            },

            // Cleanup when component is destroyed
            destroy() {
                this.stopCamera();
            }
        }));
    </script>
    @endscript
</div>