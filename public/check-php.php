<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Проверка настроек PHP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .check-item {
            margin: 15px 0;
            padding: 15px;
            border-left: 4px solid #2196F3;
            background: #f9f9f9;
        }
        .check-item.success {
            border-left-color: #4CAF50;
            background: #e8f5e9;
        }
        .check-item.warning {
            border-left-color: #FF9800;
            background: #fff3e0;
        }
        .check-item.error {
            border-left-color: #f44336;
            background: #ffebee;
        }
        .label {
            font-weight: bold;
            color: #333;
        }
        .value {
            color: #666;
            margin-left: 10px;
        }
        .status {
            float: right;
            font-weight: bold;
        }
        .status.ok { color: #4CAF50; }
        .status.warn { color: #FF9800; }
        .status.fail { color: #f44336; }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .delete-notice {
            background: #f44336;
            color: white;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Проверка настроек PHP для импорта</h1>
        
        <div class="delete-notice">
            ⚠️ УДАЛИТЕ ЭТОТ ФАЙЛ ПОСЛЕ ПРОВЕРКИ! ⚠️
        </div>

        <?php
        // Получаем критические настройки
        $max_execution_time = ini_get('max_execution_time');
        $max_input_time = ini_get('max_input_time');
        $memory_limit = ini_get('memory_limit');
        $post_max_size = ini_get('post_max_size');
        $upload_max_filesize = ini_get('upload_max_filesize');
        
        // Функция для проверки
        function checkSetting($label, $value, $expected, $type = 'exact') {
            if ($type === 'exact') {
                $status = ($value == $expected);
            } elseif ($type === 'min') {
                $numValue = preg_replace('/[^0-9]/', '', $value);
                $numExpected = preg_replace('/[^0-9]/', '', $expected);
                $status = ($numValue >= $numExpected);
            } else {
                $status = !empty($value);
            }
            
            $class = $status ? 'success' : ($value == '0' || strpos($value, '-1') !== false ? 'success' : 'error');
            $statusText = $status ? 'OK' : ($value == '0' || strpos($value, '-1') !== false ? 'OK (unlimited)' : 'НУЖНО ИСПРАВИТЬ');
            $statusClass = $status ? 'ok' : ($value == '0' || strpos($value, '-1') !== false ? 'ok' : 'fail');
            
            echo "<div class='check-item {$class}'>";
            echo "<span class='label'>{$label}:</span>";
            echo "<span class='value'>{$value}</span>";
            echo "<span class='status {$statusClass}'>{$statusText}</span>";
            echo "</div>";
        }
        ?>

        <h2>⏱️ Настройки времени выполнения</h2>
        <?php
        checkSetting('max_execution_time', $max_execution_time, '0', 'exact');
        checkSetting('max_input_time', $max_input_time, '0', 'exact');
        ?>

        <h2>💾 Настройки памяти</h2>
        <?php
        checkSetting('memory_limit', $memory_limit, '-1', 'exact');
        ?>

        <h2>📤 Настройки загрузки</h2>
        <?php
        checkSetting('post_max_size', $post_max_size, '256M', 'min');
        checkSetting('upload_max_filesize', $upload_max_filesize, '256M', 'min');
        ?>

        <h2>📊 Дополнительная информация</h2>
        <div class="check-item">
            <span class="label">PHP версия:</span>
            <span class="value"><?php echo PHP_VERSION; ?></span>
        </div>
        <div class="check-item">
            <span class="label">Server API:</span>
            <span class="value"><?php echo php_sapi_name(); ?></span>
        </div>
        <div class="check-item">
            <span class="label">Загруженные расширения:</span>
            <span class="value">
                <?php 
                $extensions = ['curl', 'gd', 'mbstring', 'zip', 'pdo', 'json'];
                foreach ($extensions as $ext) {
                    $loaded = extension_loaded($ext);
                    echo $ext . ': ' . ($loaded ? '✅' : '❌') . ' ';
                }
                ?>
            </span>
        </div>

        <div class="warning-box">
            <strong>💡 Рекомендации:</strong><br><br>
            
            <?php if ($max_execution_time != '0'): ?>
            ⚠️ <strong>max_execution_time</strong> должен быть <strong>0</strong> (без ограничений)<br>
            <?php endif; ?>
            
            <?php if ($max_input_time != '0'): ?>
            ⚠️ <strong>max_input_time</strong> должен быть <strong>0</strong> (без ограничений)<br>
            <?php endif; ?>
            
            <?php if ($memory_limit != '-1' && !preg_match('/^[0-9]+G/', $memory_limit)): ?>
            ⚠️ <strong>memory_limit</strong> должен быть <strong>-1</strong> (без ограничений) или минимум <strong>512M</strong><br>
            <?php endif; ?>
            
            <?php if ($max_execution_time == '0' && $max_input_time == '0' && ($memory_limit == '-1' || preg_match('/^[0-9]+G/', $memory_limit))): ?>
            ✅ <strong>Все настройки оптимальны!</strong> Импорт будет работать без ограничений по времени.
            <?php endif; ?>
        </div>

        <h2>🛠️ Как исправить:</h2>
        <div class="check-item">
            <strong>1. Через .htaccess</strong> (если mod_php):<br>
            <pre style="background: #f5f5f5; padding: 10px; overflow-x: auto;">
php_value max_execution_time 0
php_value max_input_time 0
php_value memory_limit -1
            </pre>
        </div>

        <div class="check-item">
            <strong>2. Через .user.ini</strong> (если PHP-FPM/FastCGI):<br>
            <pre style="background: #f5f5f5; padding: 10px; overflow-x: auto;">
max_execution_time = 0
max_input_time = 0
memory_limit = -1
            </pre>
        </div>

        <div class="check-item">
            <strong>3. Через панель хостинга</strong>:<br>
            Найдите "PHP настройки" или "php.ini" и установите указанные значения.
        </div>

        <div class="delete-notice">
            ⚠️ НЕ ЗАБУДЬТЕ УДАЛИТЬ ЭТОТ ФАЙЛ! ⚠️<br>
            <small>Он содержит информацию о конфигурации сервера</small>
        </div>
    </div>
</body>
</html>
