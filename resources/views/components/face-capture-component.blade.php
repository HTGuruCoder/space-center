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
        <p class="mt-4 text-sm">{{ __('face.initializing_camera') }}</p>
    </div>

    {{-- Error State --}}
    <div x-show="error && !isLoading" x-cloak>
        <div class="alert alert-error mb-4">
            <x-icon name="mdi.alert-circle" class="w-5 h-5" />
            <span x-text="error === 'camera_access_denied' ? '{{ __('face.camera_access_denied') }}' :
                          error === 'camera_permanently_denied' ? '{{ __('face.camera_permanently_denied') }}' :
                          error === 'no_face_detected' ? '{{ __('face.no_face_detected') }}' :
                          error === 'multiple_faces' ? '{{ __('face.multiple_faces') }}' :
                          error === 'poor_face_quality' ? '{{ __('face.poor_face_quality') }}' :
                          error === 'no_face_in_capture' ? '{{ __('face.no_face_in_capture') }}' :
                          error === 'multiple_faces_in_capture' ? '{{ __('face.multiple_faces_in_capture') }}' :
                          error === 'file_too_large' ? '{{ __('face.file_too_large') }}' :
                          error === 'image_too_small' ? '{{ __('face.image_too_small') }}' :
                          error === 'image_too_large' ? '{{ __('face.image_too_large') }}' :
                          error === 'capture_failed' ? '{{ __('face.capture_failed') }}' :
                          error === 'upload_failed' ? '{{ __('face.upload_failed') }}' :
                          '{{ __('face.exception') }}'"></span>
        </div>

        {{-- Retry button for camera access denied --}}
        <div x-show="error === 'camera_access_denied' || error === 'camera_permanently_denied'" class="text-center">
            <div class="bg-base-200 rounded-lg p-6 mb-4">
                <x-icon name="mdi.camera-off" class="w-16 h-16 mx-auto text-base-content/50 mb-4" />

                {{-- Instructions for first-time denial --}}
                <div x-show="error === 'camera_access_denied'">
                    <p class="text-sm text-base-content/70 mb-4">
                        {{ __('face.camera_allow_instructions') }}
                    </p>
                    <ol class="text-sm text-left text-base-content/70 mb-4 space-y-1 max-w-sm mx-auto">
                        <li>1. {{ __('face.click_lock_icon') }} {{ __('face.in_address_bar') }}</li>
                        <li>2. {{ __('face.find_camera_allow') }}</li>
                        <li>3. {{ __('face.click_retry') }}</li>
                    </ol>
                    <button
                        type="button"
                        @click="retryCamera()"
                        class="btn btn-primary"
                    >
                        <x-icon name="mdi.refresh" class="w-5 h-5" />
                        {{ __('face.retry_camera_access') }}
                    </button>
                </div>

                {{-- Instructions for permanently denied --}}
                <div x-show="error === 'camera_permanently_denied'">
                    <p class="text-sm text-base-content/70 mb-4 font-semibold">
                        {{ __('face.camera_blocked_title') }}
                    </p>
                    <p class="text-sm text-base-content/70 mb-4">
                        {{ __('face.camera_blocked_instructions') }}
                    </p>
                    <ol class="text-sm text-left text-base-content/70 mb-4 space-y-2 max-w-sm mx-auto">
                        <li class="flex items-start gap-2">
                            <span class="font-bold">1.</span>
                            <span>{{ __('face.click_lock_icon') }} <x-icon name="mdi.lock" class="w-4 h-4 inline" /> {{ __('face.in_address_bar') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="font-bold">2.</span>
                            <span>{{ __('face.click_site_settings') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="font-bold">3.</span>
                            <span>{{ __('face.change_camera_allow') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="font-bold">4.</span>
                            <span>{{ __('face.refresh_page') }}</span>
                        </li>
                    </ol>
                    <button
                        type="button"
                        @click="window.location.reload()"
                        class="btn btn-primary"
                    >
                        <x-icon name="mdi.refresh" class="w-5 h-5" />
                        {{ __('face.refresh_page_btn') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Quality Warning (advisory feedback) --}}
    <div x-show="showQualityWarning && !error && !isLoading && !capturedImage" class="alert alert-warning mb-4" x-cloak>
        <x-icon name="mdi.alert" class="w-5 h-5" />
        <div>
            <span class="font-semibold">{{ __('face.photo_quality_warning') }}</span>
            <ul class="text-sm mt-1 ml-4 list-disc">
                <li x-show="qualityIssues.includes('face_too_small')">{{ __('face.move_closer') }}</li>
                <li x-show="qualityIssues.includes('face_too_close')">{{ __('face.move_back') }}</li>
                <li x-show="qualityIssues.includes('face_not_centered')">{{ __('face.center_face') }}</li>
                <li x-show="qualityIssues.includes('face_not_frontal')">{{ __('face.look_at_camera') }}</li>
                <li x-show="qualityIssues.includes('low_confidence')">{{ __('face.improve_lighting') }}</li>
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
                    <span x-text="faceDetected && faceQuality === 'good' ? '{{ __('face.good_quality') }}' :
                                  faceDetected && faceQuality === 'poor' ? '{{ __('face.adjust_position') }}' :
                                  '{{ __('face.position_face') }}'"></span>
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
                {{ __('face.capture_photo') }}
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
                {{ __('face.retake') }}
            </button>
        </div>
    </div>

    {{-- Instructions --}}
    <div class="mt-4 p-3 bg-info/10 border border-info/30 rounded-lg">
        <p class="text-sm flex items-start gap-2">
            <x-icon name="mdi.lightbulb" class="w-4 h-4 text-info mt-0.5 shrink-0" />
            <span>{{ __('face.position_instructions') }}</span>
        </p>
    </div>
</div>
