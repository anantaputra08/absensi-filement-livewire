<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class AttendanceSetting extends Model
{
    use HasFactory;

    protected $table = 'attendance_settings';
    
    protected $fillable = [
        'check_in_time',
        'check_out_time',
        'check_in_max_time',
        'check_out_min_time'
    ];

    protected $casts = [
        'check_in_time' => 'string',
        'check_out_time' => 'string',
        'check_in_max_time' => 'string',
        'check_out_min_time' => 'string'
    ];

    /**
     * Get the first attendance setting
     * 
     * @return AttendanceSetting|null
     */
    public static function getFirstSetting()
    {
        try {
            return self::first() ?? self::createDefaultSetting();
        } catch (Exception $e) {
            Log::error('Error getting attendance setting: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create default setting if none exists
     * 
     * @return AttendanceSetting
     */
    protected static function createDefaultSetting()
    {
        return self::create([
            'check_in_time' => '07:00:00',
            'check_out_time' => '16:00:00',
            'check_in_max_time' => '08:00:00',
            'check_out_min_time' => '15:00:00'
        ]);
    }

    /**
     * Get check in time
     * 
     * @return string
     */
    public static function checkInTime()
    {
        $setting = self::getFirstSetting();
        return $setting ? $setting->check_in_time : '07:00:00';
    }

    /**
     * Get check out time
     * 
     * @return string
     */
    public static function checkOutTime()
    {
        $setting = self::getFirstSetting();
        return $setting ? $setting->check_out_time : '16:00:00';
    }

    /**
     * Get maximum check in time
     * 
     * @return string
     */
    public static function checkInMaxTime()
    {
        $setting = self::getFirstSetting();
        return $setting ? $setting->check_in_max_time : '08:00:00';
    }

    /**
     * Get minimum check out time
     * 
     * @return string
     */
    public static function checkOutMinTime()
    {
        $setting = self::getFirstSetting();
        return $setting ? $setting->check_out_time : '15:00:00';
    }

    /**
     * Check if current time is within check in period
     * 
     * @return bool
     */
    public static function isCheckInTime()
    {
        try {
            $now = Carbon::now();
            $maxTime = Carbon::createFromTimeString(self::checkInMaxTime());
            
            return $now->lte($maxTime);
        } catch (Exception $e) {
            Log::error('Error checking in time: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if current time is within check out period
     * 
     * @return bool
     */
    public static function isCheckOutTime()
    {
        try {
            $now = Carbon::now();
            $minTime = Carbon::createFromTimeString(self::checkOutMinTime());
            
            return $now->gte($minTime);
        } catch (Exception $e) {
            Log::error('Error checking out time: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if current time is within attendance period
     * 
     * @return bool
     */
    public static function isAttendanceTime()
    {
        return self::isCheckInTime() && !self::isCheckOutTime();
    }

    /**
     * Check if attendance is closed
     * 
     * @return bool
     */
    public static function isAttendanceClosed()
    {
        return self::isCheckOutTime();
    }

    /**
     * Check if current time is late
     * 
     * @return bool
     */
    public static function isLate()
    {
        try {
            $now = Carbon::now();
            $maxTime = Carbon::createFromTimeString(self::checkInMaxTime());
            
            return $now->gt($maxTime);
        } catch (Exception $e) {
            Log::error('Error checking late status: ' . $e->getMessage());
            return false;
        }
    }
}