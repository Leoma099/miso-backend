<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowNotification extends Model
{
    protected $fillable = [
        'notified_to',
        'notified_by',
        'message',
        'data',
        'is_read',
    ];

    public function notifiedTo()
    {
        return $this->belongsTo(Account::class, 'notified_to');
    }

    public function notifiedBy()
    {
        return $this->belongsTo(Account::class, 'notified_by');
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'data' => $this->data
                ? json_decode($this->data)
                : null,
            'is_read' => $this->is_read,
            'notified_by' => [
                'id' => $this->notified_by,
                'name' => $this->notifiedBy->full_name
            ],
        ];
    }
}
