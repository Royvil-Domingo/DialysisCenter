<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_STAFF = 'staff';
    public const ROLE_PATIENT = 'patient';
    public const STAFF_ROLES = [self::ROLE_ADMIN, self::ROLE_STAFF];

    /**
     * The attributes that are mass assignable.
     *
     * patient_id and must_change_password are deliberately NOT listed: they are
     * only ever set by the patient-account service, never from request data.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'contact_number',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isPatient(): bool
    {
        return $this->role === self::ROLE_PATIENT;
    }

    /** Admin or staff (anyone allowed in the management area). */
    public function isStaffMember(): bool
    {
        return in_array($this->role, self::STAFF_ROLES, true);
    }

    public function scopeStaffMembers(Builder $query): Builder
    {
        return $query->whereIn('role', self::STAFF_ROLES);
    }

    /** The patient record this portal account belongs to (patients only). */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function patientsRegistered()
    {
        return $this->hasMany(Patient::class, 'registered_by');
    }

    public function appointmentsCreated()
    {
        return $this->hasMany(Appointment::class, 'created_by');
    }

    public function appointmentsAssigned()
    {
        return $this->hasMany(Appointment::class, 'staff_id');
    }
}