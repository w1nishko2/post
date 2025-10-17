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
            'URL фото категории',
            'URL фото товара',
            'Характеристики (через ;)',
            'Количество',
            'Цена',
            'Активный (1/0)'
        ];
    }

    /**
     * Данные для шаблона
     */
    public function array(): array
    {
        return [
            // Добавьте свои товары ниже (каждый товар в отдельной строке)
            // ['Название товара', 'Описание', 'Артикул', 'Категория', 'URL фото категории', 'URL фото товара', 'Характеристики через ;', '1', '100', '1']
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
                $sheet->setCellValue('K2', '1. Заполните свои товары в строках ниже');
                $sheet->setCellValue('K3', '2. Сохраните файл');
                $sheet->setCellValue('K4', '3. Загрузите через форму');
                
                $sheet->setCellValue('K6', 'ОБЯЗАТЕЛЬНЫЕ ПОЛЯ:');
                $sheet->setCellValue('K7', '• Название товара');
                $sheet->setCellValue('K8', '• Артикул (уникальный)');
                
                $sheet->setCellValue('K10', 'НЕОБЯЗАТЕЛЬНЫЕ ПОЛЯ:');
                $sheet->setCellValue('K11', '• Описание');
                $sheet->setCellValue('K12', '• Категория (название)');
                $sheet->setCellValue('K13', '• URL фото категории');
                $sheet->setCellValue('K14', '• URL фото товара');
                $sheet->setCellValue('K15', '• Характеристики (через ;)');
                $sheet->setCellValue('K16', '• Количество (число)');
                $sheet->setCellValue('K17', '• Цена (число)');
                $sheet->setCellValue('K18', '• Активный (1 или 0)');
                
                $sheet->setCellValue('K20', 'ПРИМЕЧАНИЯ:');
                $sheet->setCellValue('K21', '• Если категория не существует,');
                $sheet->setCellValue('K22', '  она будет создана автоматически');
                $sheet->setCellValue('K23', '• Фото категории применится ко всем');
                $sheet->setCellValue('K24', '  товарам этой категории');
                
                // Стили для инструкций
                $sheet->getStyle('K1')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('K6')->getFont()->setBold(true);
                $sheet->getStyle('K10')->getFont()->setBold(true);
                $sheet->getStyle('K20')->getFont()->setBold(true);
                $sheet->getStyle('K1:K24')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0066CC'));
                
                // Устанавливаем ширину колонки K
                $sheet->getColumnDimension('K')->setWidth(30);
            }
        ];
    }
}