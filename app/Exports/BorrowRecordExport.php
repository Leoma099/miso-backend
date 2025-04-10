<?php

namespace App\Exports;

use App\Models\Borrow;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BorrowRecordExport implements FromView
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $query = Borrow::with(['equipment', 'account.user']);

        if (!empty($this->filters['date_borrow']) && !empty($this->filters['date_return'])) {
            $query->whereBetween('date_borrow', [$this->filters['date_borrow'], $this->filters['date_return']]);
        }

        if (!empty($this->filters['office_name'])) {
            $query->whereHas('account', function ($q) {
                $q->where('office_name', $this->filters['office_name']);
            });
        }

        if (!empty($this->filters['full_name'])) {
            $query->whereHas('account', function ($q) {
                $q->where('full_name', $this->filters['full_name']);
            });
        }

        if (!empty($this->filters['property_number'])) {
            $query->where('property_number', 'LIKE', '%' . $this->filters['property_number'] . '%');
        }

        if (!empty($this->filters['type'])) {
            $query->whereHas('equipment', function ($q) {
                $q->where('type', 'LIKE', '%' . $this->filters['type'] . '%');
            });
        }

        $records = $query->get();

        return view('exports.borrow-records', ['records' => $records]);
    }
}