<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rfid_card',
        'check_in',
        'check_out',
        'status',
    ];

    public function rfidCard()
    {
        return $this->belongsTo(RfidCard::class, 'rfid_card', 'rfid_card');
    }

}