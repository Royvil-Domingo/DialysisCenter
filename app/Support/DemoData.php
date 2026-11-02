<?php

namespace App\Support;

class DemoData
{
    public static function clinic(): array
    {
        return [
            'name' => 'Dialysis Center Demo',
            'address' => 'Poblacion, San Ildefonso, Bulacan',
            'hours' => 'Mon–Sat, 6:00 AM – 6:00 PM',
            'phone' => '+63 (44) 123-4567',
            'email' => 'hello@dialysisdemo.local',
        ];
    }

    public static function patients(): array
    {
        return [
            [
                'id' => 1,
                'patient_code' => 'PT-1024A',
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'birthdate' => '1988-02-14',
                'sex' => 'female',
                'status' => 'active',
                'contact_number' => '0917-123-4567',
                'address' => 'San Ildefonso, Bulacan',
                'emergency_contact_name' => 'Juan Santos',
                'emergency_contact_number' => '0918-222-3344',
                'blood_type' => 'O+',
                'dialysis_access_type' => 'AV Fistula',
                'dry_weight_kg' => 58.5,
                'allergies' => 'Penicillin',
            ],
            [
                'id' => 2,
                'patient_code' => 'PT-2048B',
                'first_name' => 'Andres',
                'last_name' => 'Reyes',
                'birthdate' => '1965-11-08',
                'sex' => 'male',
                'status' => 'active',
                'contact_number' => '0928-331-5590',
                'address' => 'Poblacion, San Ildefonso',
                'emergency_contact_name' => 'Lina Reyes',
                'emergency_contact_number' => '0935-770-1988',
                'blood_type' => 'A+',
                'dialysis_access_type' => 'CVC',
                'dry_weight_kg' => 70.4,
                'allergies' => 'None noted',
            ],
            [
                'id' => 3,
                'patient_code' => 'PT-3301C',
                'first_name' => 'Cecilia',
                'last_name' => 'Dela Cruz',
                'birthdate' => '1972-07-02',
                'sex' => 'female',
                'status' => 'inactive',
                'contact_number' => '0947-555-0198',
                'address' => 'San Juan, Bulacan',
                'emergency_contact_name' => 'Martin Dela Cruz',
                'emergency_contact_number' => '0948-201-9910',
                'blood_type' => 'B-',
                'dialysis_access_type' => 'AV Graft',
                'dry_weight_kg' => 61.1,
                'allergies' => 'Shellfish',
            ],
            [
                'id' => 4,
                'patient_code' => 'PT-4886D',
                'first_name' => 'Rafael',
                'last_name' => 'Mendoza',
                'birthdate' => '1958-05-15',
                'sex' => 'male',
                'status' => 'active',
                'contact_number' => '0998-640-9921',
                'address' => 'Pulilan, Bulacan',
                'emergency_contact_name' => 'Diana Mendoza',
                'emergency_contact_number' => '0917-890-2210',
                'blood_type' => 'AB+',
                'dialysis_access_type' => 'AV Fistula',
                'dry_weight_kg' => 74.8,
                'allergies' => 'Latex',
            ],
        ];
    }

    public static function appointments(): array
    {
        return [
            [
                'id' => 1,
                'patient_id' => 1,
                'appointment_date' => '2026-10-20',
                'appointment_time' => '08:30:00',
                'status' => 'scheduled',
                'notes' => 'Routine dialysis session',
            ],
            [
                'id' => 2,
                'patient_id' => 2,
                'appointment_date' => '2026-10-20',
                'appointment_time' => '10:00:00',
                'status' => 'in_progress',
                'notes' => 'Follow-up review after treatment',
            ],
            [
                'id' => 3,
                'patient_id' => 4,
                'appointment_date' => '2026-10-21',
                'appointment_time' => '09:15:00',
                'status' => 'completed',
                'notes' => 'Completed with no incident',
            ],
        ];
    }

    public static function treatmentRecords(): array
    {
        return [
            [
                'id' => 1,
                'patient_id' => 1,
                'appointment_id' => 1,
                'session_date' => '2026-10-20',
                'status' => 'completed',
                'notes' => 'Weight stable. Dialyzer change completed.',
            ],
            [
                'id' => 2,
                'patient_id' => 2,
                'appointment_id' => 2,
                'session_date' => '2026-10-20',
                'status' => 'in_progress',
                'notes' => 'Vitals normal; continue observation.',
            ],
            [
                'id' => 3,
                'patient_id' => 4,
                'appointment_id' => 3,
                'session_date' => '2026-10-21',
                'status' => 'completed',
                'notes' => 'No complications. Education provided.',
            ],
        ];
    }

    public static function charges(): array
    {
        return [
            [
                'id' => 1,
                'patient_id' => 1,
                'description' => 'Dialysis session fee',
                'amount' => 2500.00,
                'charge_date' => '2026-10-20',
                'status' => 'unpaid',
            ],
            [
                'id' => 2,
                'patient_id' => 2,
                'description' => 'Supplemental medication',
                'amount' => 620.00,
                'charge_date' => '2026-10-20',
                'status' => 'paid',
            ],
            [
                'id' => 3,
                'patient_id' => 4,
                'description' => 'Care package and fluids',
                'amount' => 980.00,
                'charge_date' => '2026-10-21',
                'status' => 'waived',
            ],
        ];
    }

    public static function notifications(): array
    {
        return [
            [
                'id' => 1,
                'title' => 'Clinic Update',
                'message' => 'The dialysis clinic is operating on the regular Saturday schedule.',
                'audience' => 'all_staff',
                'created_at' => '2026-10-20 08:00:00',
            ],
            [
                'id' => 2,
                'title' => 'Safety Reminder',
                'message' => 'Double-check patient access and session notes before discharge.',
                'audience' => 'admin_only',
                'created_at' => '2026-10-19 16:20:00',
            ],
        ];
    }

    public static function stats(): array
    {
        $patients = self::patients();
        $appointments = self::appointments();
        $charges = self::charges();

        return [
            'totalPatients' => count(array_filter($patients, fn ($p) => $p['status'] === 'active')),
            'todaysAppointments' => count(array_filter($appointments, fn ($a) => $a['appointment_date'] === '2026-10-20')),
            'unpaidChargesTotal' => array_sum(array_map(fn ($charge) => $charge['status'] === 'unpaid' ? (float) $charge['amount'] : 0, $charges)),
            'unpaidChargesCount' => count(array_filter($charges, fn ($charge) => $charge['status'] === 'unpaid')),
            'notificationCount' => count(self::notifications()),
        ];
    }

    public static function patientById(int|string $id): ?array
    {
        foreach (self::patients() as $patient) {
            if ((string) $patient['id'] === (string) $id) {
                return $patient;
            }
        }

        return null;
    }

    public static function appointmentById(int|string $id): ?array
    {
        foreach (self::appointments() as $appointment) {
            if ((string) $appointment['id'] === (string) $id) {
                return $appointment;
            }
        }

        return null;
    }

    public static function treatmentRecordById(int|string $id): ?array
    {
        foreach (self::treatmentRecords() as $record) {
            if ((string) $record['id'] === (string) $id) {
                return $record;
            }
        }

        return null;
    }
}
