<?php

namespace App\Exports;

use App\Models\TelegramBot;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ProductsDataExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithMapping, WithColumnFormatting, WithCustomCsvSettings
{
    protected $telegramBot;

    public function __construct(TelegramBot $telegramBot)
    {
        $this->telegramBot = $telegramBot;
    }

    /**
     * Получение коллекции товаров для экспорта
     */
    public function collection()
    {
        return $this->telegramBot->products()
            ->with(['category'])
            ->orderBy('id')
            ->get();
    }

    /**
     * Заголовки колонок (точно как в шаблоне)
     */
    public function headings(): array
    {
        return [
            mb_convert_encoding('Название товара', 'UTF-8', 'UTF-8'),
            mb_convert_encoding('Описание', 'UTF-8', 'UTF-8'), 
            mb_convert_encoding('Артикул', 'UTF-8', 'UTF-8'),
            mb_convert_encoding('Категория', 'UTF-8', 'UTF-8'),
            mb_convert_encoding('URL фото категории', 'UTF-8', 'UTF-8'),
            mb_convert_encoding('URL фото товара', 'UTF-8', 'UTF-8'),
            mb_convert_encoding('Характеристики (через ;)', 'UTF-8', 'UTF-8'),
            mb_convert_encoding('Количество', 'UTF-8', 'UTF-8'),
            mb_convert_encoding('Цена', 'UTF-8', 'UTF-8'),
            mb_convert_encoding('Наценка (%)', 'UTF-8', 'UTF-8'),
            mb_convert_encoding('Активный (1/0)', 'UTF-8', 'UTF-8')
        ];
    }

    /**
     * Маппинг данных для каждой строки (точно как в шаблоне)
     */
    public function map($product): array
    {
        return [
            $this->formatText($product->name),
            $this->formatText($product->description),
            $this->formatText($product->article),
            $this->formatText($product->category ? $product->category->name : ''),
            $product->category ? $product->category->photo_url : '',
            $product->photo_url ?: '',
            $this->formatText($product->specifications),
            $product->quantity ?: 0,
            $product->price ?: 0,
            $product->markup_percentage ?: 0,
            $product->is_active ? '1' : '0'
        ];
    }

    /**
     * Форматирование текста для корректного отображения в Excel
     */
    private function formatText($text): string
    {
        if (is_null($text)) {
            return '';
        }
        
        if (is_array($text)) {
            $text = implode('; ', $text);
        }
        
        if (!is_string($text)) {
            $text = (string) $text;
        }
        
        // Убираем лишние пробелы и переносы строк
        $text = trim(preg_replace('/\s+/', ' ', $text));
        
        // Обеспечиваем корректную кодировку UTF-8
        return mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    }

    /**
     * Название листа Excel
     */
    public function title(): string
    {
        return 'Товары';
    }

    /**
     * Стили для Excel файла
     */
    public function styles(Worksheet $sheet)
    {
        // Стиль для заголовков
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => '2563EB'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Выравнивание текста в ячейках
        $sheet->getStyle('A:K')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A:K')->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

        // Границы для всех ячеек с данными
        $lastRow = $sheet->getHighestRow();
        if ($lastRow > 1) {
            $sheet->getStyle('A1:K' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'CCCCCC'],
                    ],
                ],
            ]);
        }

        // Устанавливаем ширину колонок
        $sheet->getColumnDimension('A')->setWidth(25);  // Название товара
        $sheet->getColumnDimension('B')->setWidth(35);  // Описание
        $sheet->getColumnDimension('C')->setWidth(15);  // Артикул
        $sheet->getColumnDimension('D')->setWidth(20);  // Категория
        $sheet->getColumnDimension('E')->setWidth(25);  // URL фото категории
        $sheet->getColumnDimension('F')->setWidth(25);  // URL фото товара
        $sheet->getColumnDimension('G')->setWidth(30);  // Характеристики
        $sheet->getColumnDimension('H')->setWidth(12);  // Количество
        $sheet->getColumnDimension('I')->setWidth(12);  // Цена
        $sheet->getColumnDimension('J')->setWidth(15);  // Наценка (%)
        $sheet->getColumnDimension('K')->setWidth(15);  // Активный

        // Выравнивание для числовых колонок
        $sheet->getStyle('H:J')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('K')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return $sheet;
    }

    /**
     * Форматирование колонок для корректного отображения русского текста
     */
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // Название товара - текст
            'B' => NumberFormat::FORMAT_TEXT, // Описание - текст
            'C' => NumberFormat::FORMAT_TEXT, // Артикул - текст
            'D' => NumberFormat::FORMAT_TEXT, // Категория - текст
            'E' => NumberFormat::FORMAT_TEXT, // URL фото категории - текст
            'F' => NumberFormat::FORMAT_TEXT, // URL фото товара - текст
            'G' => NumberFormat::FORMAT_TEXT, // Характеристики - текст
            'H' => NumberFormat::FORMAT_NUMBER, // Количество - число
            'I' => NumberFormat::FORMAT_NUMBER_00, // Цена - число с 2 знаками
            'J' => NumberFormat::FORMAT_NUMBER_00, // Наценка (%) - число с 2 знаками
            'K' => NumberFormat::FORMAT_TEXT, // Активный - текст
        ];
    }

    /**
     * Настройки CSV для корректного отображения русского текста
     */
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'enclosure' => '"',
            'line_ending' => "\r\n",
            'use_bom' => true,
            'include_separator_line' => false,
            'excel_compatibility' => true,
            'output_encoding' => 'UTF-8',
        ];
    }
}