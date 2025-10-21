<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Доступные цветовые схемы
    |--------------------------------------------------------------------------
    |
    | Здесь определены все доступные цветовые схемы для пользователей.
    | Каждая схема содержит набор CSS переменных и описательную информацию.
    |
    */

    'available' => [

        // Стандартная серая схема (по умолчанию)
        'gray' => [
            'name' => 'Серая',
            'description' => 'Классическая серая схема - сдержанная и профессиональная',
            'preview_color' => '#808080',
            'colors' => [
                '--color-primary' => '#808080',
                '--color-accent' => '#808080',
                '--color-accent-dark' => '#666666',
                '--color-accent-light' => '#f8f9fa',
                '--color-accent-border' => '#e5e5e5',
            ],
        ],

        // Синяя схема
        'blue' => [
            'name' => 'Синяя',
            'description' => 'Современная синяя схема - надежная и технологичная',
            'preview_color' => '#3b82f6',
            'colors' => [
                '--color-primary' => '#3b82f6',
                '--color-accent' => '#3b82f6',
                '--color-accent-dark' => '#1e40af',
                '--color-accent-light' => '#eff6ff',
                '--color-accent-border' => '#bfdbfe',
            ],
        ],

        // Зеленая схема
        'green' => [
            'name' => 'Зеленая',
            'description' => 'Природная зеленая схема - свежая и экологичная',
            'preview_color' => '#22c55e',
            'colors' => [
                '--color-primary' => '#22c55e',
                '--color-accent' => '#22c55e',
                '--color-accent-dark' => '#15803d',
                '--color-accent-light' => '#f0fdf4',
                '--color-accent-border' => '#86efac',
            ],
        ],

        // Фиолетовая схема
        'purple' => [
            'name' => 'Фиолетовая',
            'description' => 'Креативная фиолетовая схема - стильная и современная',
            'preview_color' => '#8b5cf6',
            'colors' => [
                '--color-primary' => '#8b5cf6',
                '--color-accent' => '#8b5cf6',
                '--color-accent-dark' => '#7c3aed',
                '--color-accent-light' => '#f3f4f6',
                '--color-accent-border' => '#c4b5fd',
            ],
        ],

        // Красная схема
        'red' => [
            'name' => 'Красная',
            'description' => 'Энергичная красная схема - яркая и динамичная',
            'preview_color' => '#ef4444',
            'colors' => [
                '--color-primary' => '#ef4444',
                '--color-accent' => '#ef4444',
                '--color-accent-dark' => '#dc2626',
                '--color-accent-light' => '#fef2f2',
                '--color-accent-border' => '#fca5a5',
            ],
        ],

        // Оранжевая схема
        'orange' => [
            'name' => 'Оранжевая',
            'description' => 'Теплая оранжевая схема - дружелюбная и позитивная',
            'preview_color' => '#f97316',
            'colors' => [
                '--color-primary' => '#f97316',
                '--color-accent' => '#f97316',
                '--color-accent-dark' => '#ea580c',
                '--color-accent-light' => '#fff7ed',
                '--color-accent-border' => '#fed7aa',
            ],
        ],

        // Розовая схема
        'pink' => [
            'name' => 'Розовая',
            'description' => 'Нежная розовая схема - мягкая и элегантная',
            'preview_color' => '#ec4899',
            'colors' => [
                '--color-primary' => '#ec4899',
                '--color-accent' => '#ec4899',
                '--color-accent-dark' => '#be185d',
                '--color-accent-light' => '#fdf2f8',
                '--color-accent-border' => '#f9a8d4',
            ],
        ],

        // Тёмно-синяя схема
        'indigo' => [
            'name' => 'Индиго',
            'description' => 'Глубокая индиго схема - мудрая и спокойная',
            'preview_color' => '#6366f1',
            'colors' => [
                '--color-primary' => '#6366f1',
                '--color-accent' => '#6366f1',
                '--color-accent-dark' => '#4338ca',
                '--color-accent-light' => '#eef2ff',
                '--color-accent-border' => '#a5b4fc',
            ],
        ],

        // Бирюзовая схема
        'teal' => [
            'name' => 'Бирюзовая',
            'description' => 'Освежающая бирюзовая схема - спокойная и уравновешенная',
            'preview_color' => '#14b8a6',
            'colors' => [
                '--color-primary' => '#14b8a6',
                '--color-accent' => '#14b8a6',
                '--color-accent-dark' => '#0f766e',
                '--color-accent-light' => '#f0fdfa',
                '--color-accent-border' => '#7dd3fc',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Схема по умолчанию
    |--------------------------------------------------------------------------
    |
    | Схема, которая будет использоваться для новых пользователей
    | и в качестве fallback если выбранная схема не найдена.
    |
    */

    'default' => 'gray',

];