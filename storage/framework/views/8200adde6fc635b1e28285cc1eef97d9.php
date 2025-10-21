<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="theme-color" content="#ffffff">
    <meta name="msapplication-navbutton-color" content="#ffffff">
    <meta name="apple-mobile-web-app-status-bar-style" content="light-content">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-touch-fullscreen" content="yes">
    <title><?php echo $__env->yieldContent('title', 'Mini App'); ?></title>
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- Mini App Styles ONLY (без app.css!) -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/mini-app.css']); ?>
    
    <!-- Дополнительные стили для мини-приложения -->
    <?php echo $__env->yieldPushContent('styles'); ?>
    
    <!-- Telegram WebApp JS -->
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</head>
<body class="mini-app-body">
    <?php echo $__env->yieldContent('content'); ?>
    
    <!-- Mini App JS -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/mini-app.js']); ?>
    
    <!-- Дополнительные скрипты для мини-приложения -->
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH C:\OSPanel\domains\post\resources\views/layouts/mini-app.blade.php ENDPATH**/ ?>