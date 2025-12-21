<?php

declare(strict_types=1);

return [
    // Face detection errors
    'file_not_found' => 'Archivo de imagen no encontrado.',
    'no_face_detected' => 'No se detectó ningún rostro en la imagen.',
    'multiple_faces' => 'Se detectaron múltiples rostros. Asegúrese de que solo sea visible un rostro.',
    'api_error' => 'Ocurrió un error con el servicio de reconocimiento facial.',
    'exception' => 'Ocurrió un error al detectar el rostro.',

    // Face quality errors
    'poor_quality' => 'La calidad del rostro es demasiado baja. Asegúrese de tener buena iluminación y mirar directamente a la cámara.',
    'too_blurry' => 'La imagen está demasiado borrosa. Mantenga la cámara firme.',
    'sunglasses_detected' => 'Por favor, quítese las gafas de sol.',
    'eyes_closed' => 'Por favor, mantenga los ojos abiertos.',

    // Image validation errors
    'file_too_large' => 'El tamaño del archivo de imagen debe ser menor a 2MB.',
    'invalid_image' => 'Archivo de imagen inválido.',
    'invalid_format' => 'La imagen debe estar en formato JPG o PNG.',
    'invalid_dimensions' => 'Las dimensiones de la imagen deben estar entre 48x48 y 4096x4096 píxeles.',

    // FaceSet errors
    'faceset_create_error' => 'Ocurrió un error al crear el FaceSet.',
    'faceset_add_error' => 'Ocurrió un error al agregar el rostro al FaceSet.',
    'faceset_get_error' => 'Ocurrió un error al obtener los detalles del FaceSet.',
    'faceset_add_warning' => 'No se pudo agregar el token facial al FaceSet. Expirará en 72 horas.',

    // Face comparison errors
    'compare_error' => 'Ocurrió un error con el servicio de reconocimiento facial.',
    'compare_exception' => 'Ocurrió un error al comparar rostros.',
];
