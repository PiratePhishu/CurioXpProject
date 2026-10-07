<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Encryption uses a random IV per value, so the same email never
            // produces the same ciphertext twice. A DB-level unique index on
            // ciphertext can't enforce uniqueness; it's enforced in app code instead.
            // It also has to go before widening the column, since MySQL refuses
            // to change a uniquely-indexed column to text/blob.
            $table->dropUnique(['email']);
        });

        Schema::table('students', function (Blueprint $table) {
            // Encrypted ciphertext is far longer than the plaintext it replaces,
            // so these columns need room beyond a varchar(255).
            $table->text('name')->change();
            $table->text('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('name')->change();
            $table->string('email')->nullable()->change();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->unique('email');
        });
    }
};
