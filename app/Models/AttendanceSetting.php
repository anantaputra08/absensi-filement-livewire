<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendanceSetting extends Model
{
    use HasFactory;

    protected $table = 'attendance_settings';
    protected $fillable = ['check_in_time', 'check_out_time', 'check_in_max_time', 'check_out_min_time'];

    public static function getFirstSetting()
    {
        return self::first();
    }

    public static function checkInTime()
    {
        $setting = self::getFirstSetting();
        return $setting ? $setting->check_in_time : null;
    }

    public static function checkOutTime()
    {
        $setting = self::getFirstSetting();
        return $setting ? $setting->check_out_time : null;
    }

    public static function checkInMaxTime()
    {
        $setting = self::getFirstSetting();
        return $setting ? $setting->check_in_max_time : '08:00:00';
    }

    public static function checkOutMinTime()
    {
        $setting = self::getFirstSetting();
        return $setting ? $setting->check_out_min_time : '15:00:00';
    }

    public static function isCheckInTime()
    {
        $now = Carbon::now()->format('H:i:s');
        return $now <= self::checkInMaxTime();
    }

    public static function isCheckOutTime()
    {
        $now = Carbon::now()->format('H:i:s');
        return $now >= self::checkOutMinTime();
    }

    public static function isAttendanceTime()
    {
        return self::isCheckInTime() && !self::isCheckOutTime();
    }

    public static function isAttendanceClosed()
    {
        return self::isCheckOutTime();
    }

    public static function isLate()
    {
        $now = Carbon::now()->format('H:i:s');
        return $now > self::checkInMaxTime();
    }

    
}