<?php

return [
    'checkout' => [
        'title' => 'Actualizaciones y ofertas opcionales',
        'email' => 'Quiero recibir por correo ayuda de configuración, recordatorios de renovación y ofertas ocasionales.',
        'whatsapp' => 'Quiero recibir por WhatsApp ayuda de configuración, recordatorios de renovación y ofertas ocasionales.',
        'ads' => 'Permito que mis datos de contacto se usen para audiencias publicitarias personalizadas.',
        'optional' => 'Opcional. No afecta a tu pedido y puedes darte de baja en cualquier momento.',
        'privacy' => 'Política de privacidad',
    ],
    'privacy' => [
        'title' => 'Preferencias de marketing y conservación',
        'text' => 'Solo enviamos marketing por correo o WhatsApp en los canales que selecciones expresamente. Solo usamos datos de contacto para audiencias publicitarias con un permiso separado. Puedes retirar el permiso en cualquier momento. Los datos de compras incompletas se conservan hasta :draft_days días. Los datos de entrega se eliminan tras :delivery_days días; solo queda una clave mínima sin datos de contacto para evitar duplicados.',
    ],
    'unsubscribe' => ['title' => 'Suscripción cancelada', 'text' => 'Hemos guardado tu preferencia. No recibirás más mensajes de marketing por el canal seleccionado.', 'home' => 'Volver al inicio', 'confirm_title' => 'Gestionar preferencias de marketing', 'confirm_text' => 'Confirma que quieres detener el marketing por correo, WhatsApp y audiencias personalizadas asociado a este contacto.', 'confirm_button' => 'Detener marketing', 'keep_button' => 'Mantener preferencias'],
    'email' => ['permission' => 'Recibes este mensaje porque seleccionaste este canal de marketing durante la compra.', 'unsubscribe' => 'Darse de baja'],
    'workflows' => [
        'abandoned' => ['subject' => '¿Necesitas ayuda para terminar tu pedido de Opplex?', 'body' => 'Hola :name, el plan que seleccionaste sigue pendiente. Revisa los datos o pide ayuda a nuestro equipo antes de hacer el pedido.', 'cta' => 'Revisar planes'],
        'onboarding' => ['subject' => 'Configura tu suscripción :package', 'body' => 'Hola :name, tu suscripción está activa. Sigue nuestra guía para :device y contacta con soporte si necesitas ayuda para iniciar sesión.', 'cta' => 'Abrir guía de configuración', 'default_device' => 'dispositivo'],
        'renewal' => ['subject' => 'Tu plan :package caduca pronto', 'body' => 'Hola :name, tu plan :package caduca el :expiry. Revisa el plan y el precio actuales antes de renovar.', 'cta' => 'Ver opciones de renovación'],
        'referral' => ['subject' => 'Comparte Opplex con alguien de confianza', 'body' => 'Hola :name, tu enlace personal de recomendación está listo. Compártelo solo con personas que hayan pedido una recomendación. Cualquier recompensa está sujeta a las condiciones vigentes aprobadas.', 'cta' => 'Abrir enlace de recomendación'],
    ],
];
