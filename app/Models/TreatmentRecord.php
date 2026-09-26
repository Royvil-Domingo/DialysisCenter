<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id', 'appointment_id', 'session_date', 'pre_weight_kg', 'post_weight_kg',
        'duration_minutes', 'blood_pressure_pre', 'blood_pressure_post',
        'dialyzer_used', 'complications', 'status', 'recorded_by',
    ];

    protected $casts = [
        'session_date' => 'date',
        'pre_weight_kg' => 'decimal:2',
        'post_weight_kg' => 'decimal:2',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function charge()
    {
        return $this->hasOne(Charge::class);
    }
}
