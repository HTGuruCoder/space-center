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

    // Camera access errors
    'camera_access_denied' => 'Acceso a la cámara denegado. Por favor, permita el acceso a la cámara.',
    'camera_permanently_denied' => 'El acceso a la cámara está bloqueado. Siga las instrucciones a continuación.',
    'camera_blocked_title' => 'El acceso a la cámara está bloqueado por su navegador.',
    'camera_blocked_instructions' => 'Debe permitir manualmente el acceso a la cámara en la configuración de su navegador:',
    'camera_allow_instructions' => 'Para usar el reconocimiento facial, debe permitir el acceso a la cámara en la configuración de su navegador.',
    'click_lock_icon' => 'Haga clic en el icono del candado',
    'in_address_bar' => 'en la barra de direcciones',
    'click_site_settings' => 'Haga clic en "Configuración del sitio" o "Permisos"',
    'change_camera_allow' => 'Busque "Cámara" y cambie de "Bloquear" a "Permitir"',
    'find_camera_allow' => 'Busque "Cámara" y cambie a "Permitir"',
    'click_retry' => 'Haga clic en el botón de abajo para reintentar',
    'refresh_page' => 'Actualice esta página',
    'retry_camera_access' => 'Reintentar acceso a la cámara',
    'refresh_page_btn' => 'Actualizar página',

    // Camera capture errors
    'no_face_in_capture' => 'No se detectó ningún rostro en la foto capturada. Intente de nuevo.',
    'multiple_faces_in_capture' => 'Múltiples rostros en la foto. Asegúrese de que solo usted sea visible.',
    'poor_face_quality' => 'Calidad de foto deficiente. Ajuste la posición, iluminación o ángulo del rostro.',
    'capture_failed' => 'Error al capturar la imagen. Intente de nuevo.',
    'upload_failed' => 'Error al subir la imagen. Intente de nuevo.',
    'image_too_small' => 'La imagen es muy pequeña. Se requiere mínimo 48x48 píxeles.',
    'image_too_large' => 'La imagen es muy grande. Máximo 4096x4096 píxeles permitido.',

    // Camera UI
    'initializing_camera' => 'Inicializando cámara...',
    'good_quality' => 'Buena calidad',
    'adjust_position' => 'Ajustar posición',
    'position_face' => 'Posicione su rostro',
    'capture_photo' => 'Capturar foto',
    'retake' => 'Volver a tomar',
    'photo_quality_warning' => 'La calidad de la foto puede mejorar',
    'move_closer' => 'Acérquese más a la cámara',
    'move_back' => 'Aléjese de la cámara',
    'center_face' => 'Centre su rostro en el encuadre',
    'look_at_camera' => 'Mire directamente a la cámara',
    'improve_lighting' => 'Mejore la iluminación o claridad de la imagen',
    'position_instructions' => 'Posicione su rostro en el centro. Quítese las gafas de sol y mire directamente a la cámara.',
];
