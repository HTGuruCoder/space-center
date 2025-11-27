<?php

declare(strict_types=1);

return [
    // Face detection errors
    'file_not_found' => 'Image file not found.',
    'no_face_detected' => 'No face detected in the image.',
    'multiple_faces' => 'Multiple faces detected. Please ensure only one face is visible.',
    'api_error' => 'An error occurred with the face recognition service.',
    'exception' => 'An error occurred while detecting face.',

    // Face quality errors
    'poor_quality' => 'Face quality is too low. Please ensure good lighting and face the camera directly.',
    'too_blurry' => 'Image is too blurry. Please hold the camera steady.',
    'sunglasses_detected' => 'Please remove sunglasses.',
    'eyes_closed' => 'Please keep your eyes open.',

    // Image validation errors
    'file_too_large' => 'Image file size must be less than 2MB.',
    'invalid_image' => 'Invalid image file.',
    'invalid_format' => 'Image must be in JPG or PNG format.',
    'invalid_dimensions' => 'Image dimensions must be between 48x48 and 4096x4096 pixels.',

    // FaceSet errors
    'faceset_create_error' => 'An error occurred while creating FaceSet.',
    'faceset_add_error' => 'An error occurred while adding face to FaceSet.',
    'faceset_get_error' => 'An error occurred while getting FaceSet details.',
    'faceset_add_warning' => 'Face token could not be added to FaceSet. It will expire in 72 hours.',

    // Face comparison errors
    'compare_error' => 'An error occurred with the face recognition service.',
    'compare_exception' => 'An error occurred while comparing faces.',

    // Camera access errors
    'camera_access_denied' => 'Camera access denied. Please allow camera access.',
    'camera_permanently_denied' => 'Camera access is blocked. Please follow the instructions below.',
    'camera_blocked_title' => 'Camera access is blocked by your browser.',
    'camera_blocked_instructions' => 'You need to manually allow camera access in your browser settings:',
    'camera_allow_instructions' => 'To use facial recognition, you need to allow camera access in your browser settings.',
    'click_lock_icon' => 'Click the lock icon',
    'in_address_bar' => 'in the address bar',
    'click_site_settings' => 'Click "Site settings" or "Permissions"',
    'change_camera_allow' => 'Find "Camera" and change from "Block" to "Allow"',
    'find_camera_allow' => 'Find "Camera" and change to "Allow"',
    'click_retry' => 'Click the button below to retry',
    'refresh_page' => 'Refresh this page',
    'retry_camera_access' => 'Retry Camera Access',
    'refresh_page_btn' => 'Refresh Page',

    // Camera capture errors
    'no_face_in_capture' => 'No face detected in captured photo. Please try again.',
    'multiple_faces_in_capture' => 'Multiple faces in photo. Ensure only you are visible.',
    'poor_face_quality' => 'Poor photo quality. Please adjust position, lighting, or face angle.',
    'capture_failed' => 'Failed to capture image. Please try again.',
    'upload_failed' => 'Failed to upload image. Please try again.',
    'image_too_small' => 'Image is too small. Minimum 48x48 pixels required.',
    'image_too_large' => 'Image is too large. Maximum 4096x4096 pixels allowed.',

    // Camera UI
    'initializing_camera' => 'Initializing camera...',
    'good_quality' => 'Good quality',
    'adjust_position' => 'Adjust position',
    'position_face' => 'Position your face',
    'capture_photo' => 'Capture Photo',
    'retake' => 'Retake',
    'photo_quality_warning' => 'Photo quality can be improved',
    'move_closer' => 'Move closer to the camera',
    'move_back' => 'Move back from the camera',
    'center_face' => 'Center your face in the frame',
    'look_at_camera' => 'Look directly at the camera',
    'improve_lighting' => 'Improve lighting or image clarity',
    'position_instructions' => 'Position your face in the center. Remove sunglasses and look directly at the camera.',
];
