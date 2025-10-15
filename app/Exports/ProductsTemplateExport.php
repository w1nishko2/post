<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ProductsTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    /**
     * Заголовки колонок для шаблона
     */
    public function headings(): array
    {
        return [
            'Название товара',
            'Описание', 
            'Артикул',
            'Категория',
            'URL фото',
            'Характеристики (через ;)',
            'Количество',
            'Цена',
            'Активный (1/0)'
        ];
    }

    /**
     * Данные-примеры для шаблона
     */
    public function array(): array
    {
        return [
            // Пример 1 - Полностью заполненный товар (удалите эту строку)
            [
                'Смартфон Samsung Galaxy',
                'Современный смартфон с отличной камерой и производительностью',
                'SM001',
                'Электроника',
                'https://example.com/smartphone.jpg',
                'Диагональ: 6.1 дюйма; ОЗУ: 8 ГБ; Накопитель: 128 ГБ; Камера: 50 Мп',
                '25',
                '45000',
                '1'
            ],
            // Пример 2 - Товар с минимальным набором полей (удалите эту строку)
            [
                'Наушники Bluetooth',
                '',
                'BT002',
                'Аксессуары', 
                '',
                'Тип: накладные; Время работы: 30 часов; Bluetooth: 5.0',
                '15',
                '3500.50',
                '1'
            ],
            // Пример 3 - Неактивный товар без категории (удалите эту строку)
            [
                'Планшет iPad Air',
                'Планшет для работы и развлечений',
                'TB003',
                '',
                'https://example.com/ipad.jpg',
                'Диагональ: 10.9 дюйма; Чип: M1; Накопитель: 64 ГБ',
                '0',
                '55000',
                '0'
            ],
            // Добавьте свои товары ниже (каждый товар в отдельной строке)
            // ['Название вашего товара', 'Описание', 'Уникальный артикул', 'Категория', '', '', '1', '100', '1']
        ];
    }

    /**
     * Стили для Excel файла
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Стиль для заголовков
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            // Стиль для примеров данных (теперь строки 2-4)
            '2:4' => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E7F3FF'],
                ],
            ],
        ];
    }

    /**
     * Название листа Excel
     */
    public function title(): string
    {
        return 'Шаблон товаров';
    }

    /**
     * События для добавления инструкций
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Добавляем инструкции в отдельные ячейки, начиная с колонки K
                $sheet = $event->sheet->getDelegate();
                
                // Инструкции в колонке K (чтобы не мешать данным)
                $sheet->setCellValue('K1', 'ИНСТРУКЦИЯ:');
                $sheet->setCellValue('K2', '1. Удалите примеры товаров (строки 2-4)');
                $sheet->setCellValue('K3', '2. Заполните свои товары');
                $sheet->setCellValue('K4', '3. Сохраните файл');
                $sheet->setCellValue('K5', '4. Загрузите через форму');
                
                $sheet->setCellValue('K7', 'ОБЯЗАТЕЛЬНЫЕ ПОЛЯ:');
                $sheet->setCellValue('K8', '• Название товара');
                $sheet->setCellValue('K9', '• Артикул (уникальный)');
                
                $sheet->setCellValue('K11', 'НЕОБЯЗАТЕЛЬНЫЕ ПОЛЯ:');
                $sheet->setCellValue('K12', '• Описание');
                $sheet->setCellValue('K13', '• Категория (название)');
                $sheet->setCellValue('K14', '• URL фото');
                $sheet->setCellValue('K15', '• Характеристики (через ;)');
                $sheet->setCellValue('K16', '• Количество (число)');
                $sheet->setCellValue('K17', '• Цена (число)');
                $sheet->setCellValue('K18', '• Активный (1 или 0)');
                
                // Стили для инструкций
                $sheet->getStyle('K1')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('K7')->getFont()->setBold(true);
                $sheet->getStyle('K11')->getFont()->setBold(true);
                $sheet->getStyle('K1:K18')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0066CC'));
                
                // Устанавливаем ширину колонки K
                $sheet->getColumnDimension('K')->setWidth(30);
            }
        ];
    }
}