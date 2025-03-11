<?php

namespace App\Imports;

use App\Models\Equipment;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log; // ✅ Import Log correctly

class EquipmentImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        Log::info('CSV Row:', $row); // ✅ Log the actual data Laravel sees

        return new Equipment([
            'property_number' => $row['property_number'] ?? 'MISSING',
            'serial_number' => $row['serial_number'] ?? 'MISSING',
            'type' => $row['type'] ?? 'MISSING',
            'brand' => $row['brand'] ?? 'MISSING',
            'model' => $row['model'] ?? 'MISSING',
            'condition' => $row['condition'] ?? 0,
            'availability' => $row['availability'] ?? 0,
            'registered_date' => $row['registered_date'] ?? '0000-00-00',
        ]);
    }
}

