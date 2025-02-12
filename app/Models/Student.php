<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nis',
        'name',
        'gender',
        'birth_date',
        'birth_place',
        'address',
        'phone',
        'email',
        'religion',
        'blood_type',
        'father_name',
        'mother_name',
        'guardian_name',
        'father_occupation',
        'mother_occupation',
        'entry_year',
    ];

    public function rfidCard()
    {
        return $this->hasOne(RfidCard::class, 'student_id', 'id');
    }


}