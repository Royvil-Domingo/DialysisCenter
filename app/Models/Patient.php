<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_code', 'first_name', 'last_name', 'birthdate', 'sex',
        'contact_number', 'address', 'emergency_contact_name', 'emergency_contact_number',
        'blood_type', 'dialysis_access_type', 'dry_weight_kg', 'allergies',
        'status', 'registered_by',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'dry_weight_kg' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($patient) {
            $patient->update([
                'patient_code' => 'PT-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)),
            ]);
        });
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function treatmentRecords()
    {
        return $this->hasMany(TreatmentRecord::class);
    }

    public function charges()
    {
        return $this->hasMany(Charge::class);
    }
}