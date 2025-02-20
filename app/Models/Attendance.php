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
    protected $dates = [
        'check_in',
        'check_out',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    // Disable automatic timestamp updates for check_in and check_out
    public static $autoTimestampFields = ['created_at', 'updated_at'];

    public function rfidCard()
    {
        return $this->belongsTo(RfidCard::class, 'rfid_card', 'rfid_card');
    }

    public function student()
    {
        return $this->belongsTo(Student::class)->through('rfidCard');
    }

}