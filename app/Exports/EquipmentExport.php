<?php

namespace App\Exports;

use App\Models\Equipment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EquipmentExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Equipment::select(
            'property_number',
            'serial_number',
            'type',
            'brand',
            'model',
            'condition',
            'availability',
            'registered_date'
        )->get();
    }

    public function headings(): array
    {
        return [
            'Property Number',
            'Serial Number',
            'Type',
            'Brand',
            'Model',
            'Condition',
            'Availability',
            'Registered Date'
        ];
    }
}
