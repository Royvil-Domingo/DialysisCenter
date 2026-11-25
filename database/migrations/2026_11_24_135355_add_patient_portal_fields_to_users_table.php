<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SQLite has to rebuild the whole users table to change the role column.
     * That must happen outside a transaction with foreign keys switched off,
     * otherwise ON DELETE rules on patients/appointments/etc. can null out
     * rows while the table is being swapped.
     */
    public $withinTransaction = false;

    public function up(): void
    {
        $this->setRoleValues(['admin', 'staff', 'patient']);

        if (! Schema::hasColumn('users', 'patient_id')) {
            Schema::table('users', function (Blueprint $table) {
                // One login account per patient record (unique), nullable for staff/admin.
                $table->foreignId('patient_id')
                    ->nullable()
                    ->unique()
                    ->after('role')
                    ->constrained('patients')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('users', 'must_change_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('must_change_password')->default(false)->after('is_active');
            });
        }
    }

    public function down(): void
    {
        // Patient-role accounts cannot exist in the old schema. Remove only those
        // login accounts (patient records themselves are never touched).
        DB::table('users')->where('role', 'patient')->delete();

        if (Schema::hasColumn('users', 'patient_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['patient_id']);
                $table->dropUnique(['patient_id']);
                $table->dropColumn('patient_id');
            });
        }

        if (Schema::hasColumn('users', 'must_change_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('must_change_password');
            });
        }

        $this->setRoleValues(['admin', 'staff']);
    }

    /**
     * @param  list<string>  $values
     */
    private function setRoleValues(array $values): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $list = "'" . implode("','", $values) . "'";
            DB::statement("ALTER TABLE `users` MODIFY `role` ENUM({$list}) NOT NULL DEFAULT 'staff'");

            return;
        }

        Schema::disableForeignKeyConstraints();

        try {
            Schema::table('users', function (Blueprint $table) use ($values) {
                $table->enum('role', $values)->default('staff')->change();
            });
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};