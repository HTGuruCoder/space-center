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
];
