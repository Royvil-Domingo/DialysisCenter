<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Charge extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id', 'treatment_record_id', 'description', 'amount',
        'charge_date', 'status', 'recorded_by',
    ];

    protected $casts = [
        'charge_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function treatmentRecord()
    {
        return $this->belongsTo(TreatmentRecord::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
