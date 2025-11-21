@props(['wireModel' => 'photo'])

<div
    x-data="faceCapture('{{ $wireModel }}')"
    x-init="init()"
    @destroy="destroy()"
    class="w-full"
>
    {{-- Loading State --}}
    <div x-show="isLoading" class="text-center py-8">
        <span class="loading loading-spinner loading-lg text-primary"></span>
        <p class="mt-4 text-sm">{{ __('Initializing camera...') }}</p>
    </div>

    {{-- Error State --}}
    <div x-show="error && !isLoading" class="alert alert-error mb-4" x-cloak>
        <x-icon name="mdi.alert-circle" class="w-5 h-5" />
        <span x-text="error === 'camera_access_denied' ? '{{ __('Camera access denied. Please allow camera access.') }}' :
                      error === 'no_face_detected' ? '{{ __('No face detected. Please position your face in the camera.') }}' :
                      error === 'multiple_faces' ? '{{ __('Multiple faces detected. Please ensure only one person is visible.') }}' :
                      error === 'poor_face_quality' ? '{{ __('Poor photo quality. Please adjust position, lighting, or face angle.') }}' :
                      error === 'no_face_in_capture' ? '{{ __('No face detected in captured photo. Please try again.') }}' :
                      error === 'multiple_faces_in_capture' ? '{{ __('Multiple faces in photo. Ensure only you are visible.') }}' :
                      error === 'file_too_large' ? '{{ __('Image file size must be less than 2MB.') }}' :
                      error === 'image_too_small' ? '{{ __('Image is too small. Minimum 48x48 pixels required.') }}' :
                      error === 'image_too_large' ? '{{ __('Image is too large. Maximum 4096x4096 pixels allowed.') }}' :
                      error === 'capture_failed' ? '{{ __('Failed to capture image. Please try again.') }}' :
                      error === 'upload_failed' ? '{{ __('Failed to upload image. Please try again.') }}' :
                      '{{ __('An error occurred. Please try again.') }}'"></span>
    </div>

    {{-- Quality Warning (advisory feedback) --}}
    <div x-show="showQualityWarning && !error && !isLoading && !capturedImage" class="alert alert-warning mb-4" x-cloak>
        <x-icon name="mdi.alert" class="w-5 h-5" />
        <div>
            <span class="font-semibold">{{ __('Photo quality can be improved') }}</span>
            <ul class="text-sm mt-1 ml-4 list-disc">
                <li x-show="qualityIssues.includes('face_too_small')">{{ __('Move closer to the camera') }}</li>
                <li x-show="qualityIssues.includes('face_too_close')">{{ __('Move back from the camera') }}</li>
                <li x-show="qualityIssues.includes('face_not_centered')">{{ __('Center your face in the frame') }}</li>
                <li x-show="qualityIssues.includes('face_not_frontal')">{{ __('Look directly at the camera') }}</li>
                <li x-show="qualityIssues.includes('low_confidence')">{{ __('Improve lighting or image clarity') }}</li>
            </ul>
        </div>
    </div>

    {{-- Camera View --}}
    <div x-show="!capturedImage && !isLoading" class="relative" x-cloak>
        <div class="relative aspect-video bg-base-300 rounded-lg overflow-hidden">
            <video
                x-ref="video"
                autoplay
                playsinline
                class="w-full h-full object-cover"
            ></video>
            <canvas
                x-ref="canvas"
                class="absolute inset-0 w-full h-full"
            ></canvas>

            {{-- Face Detection Indicator --}}
            <div class="absolute top-4 left-4">
                <div
                    class="badge gap-2"
                    :class="faceDetected && faceQuality === 'good' ? 'badge-success' :
                            faceDetected && faceQuality === 'poor' ? 'badge-warning' :
                            'badge-error'"
                >
                    <span class="w-2 h-2 rounded-full animate-pulse"
                          :class="faceDetected && faceQuality === 'good' ? 'bg-success-content' :
                                  faceDetected && faceQuality === 'poor' ? 'bg-warning-content' :
                                  'bg-error-content'"></span>
                    <span x-text="faceDetected && faceQuality === 'good' ? '{{ __('Good quality') }}' :
                                  faceDetected && faceQuality === 'poor' ? '{{ __('Adjust position') }}' :
                                  '{{ __('Position your face') }}'"></span>
                </div>
            </div>
        </div>

        {{-- Capture Button --}}
        <div class="flex justify-center mt-4">
            <button
                type="button"
                @click="capture()"
                :disabled="!faceDetected || faceQuality === 'poor'"
                class="btn btn-primary btn-lg"
            >
                <x-icon name="mdi.camera" class="w-6 h-6" />
                {{ __('Capture Photo') }}
            </button>
        </div>
    </div>

    {{-- Preview --}}
    <div x-show="capturedImage" class="relative" x-cloak>
        <img
            :src="capturedImage"
            alt="Captured photo"
            class="w-full aspect-video object-cover rounded-lg"
        />

        <div class="flex gap-2 justify-center mt-4">
            <button
                type="button"
                @click="retake()"
                class="btn btn-outline"
            >
                <x-icon name="mdi.camera-retake" class="w-5 h-5" />
                {{ __('Retake') }}
            </button>
        </div>
    </div>

    {{-- Instructions --}}
    <div class="mt-4 p-3 bg-info/10 border border-info/30 rounded-lg">
        <p class="text-sm flex items-start gap-2">
            <x-icon name="mdi.lightbulb" class="w-4 h-4 text-info mt-0.5 flex-shrink-0" />
            <span>{{ __('Position your face in the center. Remove sunglasses and look directly at the camera.') }}</span>
        </p>
    </div>
</div>
