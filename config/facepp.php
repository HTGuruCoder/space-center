<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Face++ API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Face++ facial recognition API
    | Get your API key and secret from: https://console.faceplusplus.com/
    |
    */

    'api_key' => env('FACEPP_API_KEY'),
    'api_secret' => env('FACEPP_API_SECRET'),
    'api_url' => env('FACEPP_API_URL', 'https://api-us.faceplusplus.com/facepp/v3'),

    /*
    |--------------------------------------------------------------------------
    | Face++ Detection Settings
    |--------------------------------------------------------------------------
    */

    'return_attributes' => 'gender,age,smiling,headpose,facequality,blur,eyestatus,emotion,ethnicity,beauty,mouthstatus,eyegaze,skinstatus',

    'face_quality_threshold' => 70, // Minimum face quality score (0-100)
    'blur_threshold' => 50, // Maximum blur level (0-100, lower is better)

];
