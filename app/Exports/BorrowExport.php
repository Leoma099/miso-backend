<?php

namespace App\Exports;

use App\Models\Borrow;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class BorrowExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithMapping, WithColumnFormatting
{
    public function collection()
    {
        return Borrow::with('equipment')->get();
    }

    public function headings(): array
    {
        return [
            'BORROWER NAME',
            'EQUIPMENT TYPE',
            'BRAND',
            'MODEL',
            'CONDITION',
            'STATUS',
            'DATE BORROWED',
            'DATE RETURNED',
            'OFFICE NAME',
            'PROPERTY NUMBER',
            'SERIAL NUMBER',
        ];
    }

    public function map($borrow): array
    {
        return [
            $borrow->full_name,
            optional($borrow->equipment)->type ?? 'N/A',
            optional($borrow->equipment)->brand ?? 'N/A',
            optional($borrow->equipment)->model ?? 'N/A',
            optional($borrow->equipment)->condition ?? 'N/A',
            $borrow->status,
            $borrow->date_borrow ? \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($borrow->date_borrow) : '',
            $borrow->date_return ? \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($borrow->date_return) : '',
            $borrow->office_name,
            optional($borrow->equipment)->property_number ?? 'N/A',
            optional($borrow->equipment)->serial_number ?? 'N/A',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30, // BORROWER NAME
            'B' => 20, // EQUIPMENT TYPE
            'C' => 20, // BRAND
            'D' => 20, // MODEL
            'E' => 15, // CONDITION
            'F' => 15, // STATUS
            'G' => 22, // DATE BORROWED
            'H' => 22, // DATE RETURNED
            'I' => 25, // OFFICE NAME
            'J' => 25, // PROPERTY NUMBER
            'K' => 25, // SERIAL NUMBER
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Bold and center headers
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        $sheet->getStyle('A1:K1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Auto-size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set row height for better spacing
        foreach (range(1, 100) as $row) { 
            $sheet->getRowDimension($row)->setRowHeight(20);
        }
    }

    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_DATE_YYYYMMDD, // DATE BORROWED
            'H' => NumberFormat::FORMAT_DATE_YYYYMMDD, // DATE RETURNED
        ];
    }
}
