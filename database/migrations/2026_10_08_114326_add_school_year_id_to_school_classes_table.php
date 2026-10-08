<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->foreignId('school_year_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        if (DB::table('school_classes')->exists()) {
            DB::table('school_classes')->update(['school_year_id' => $this->currentAcademicYearId()]);
        }

        Schema::table('school_classes', function (Blueprint $table) {
            $table->unsignedBigInteger('school_year_id')->nullable(false)->change();
        });

        Schema::table('school_classes', function (Blueprint $table) {
            // A class name only needs to be unique within its own school year, so
            // the same class name can be reused the following year.
            $table->dropUnique(['name']);
            $table->unique(['school_year_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropUnique(['school_year_id', 'name']);
            $table->unique('name');
        });

        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_year_id');
        });
    }

    /**
     * Find or create the school year that pre-existing classes are backfilled into.
     */
    private function currentAcademicYearId(): int
    {
        $now = now();
        $startYear = $now->month >= 8 ? $now->year : $now->year - 1;
        $label = "{$startYear}/".($startYear + 1);

        return DB::table('school_years')->where('name', $label)->value('id')
            ?? DB::table('school_years')->insertGetId([
                'name' => $label,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    }
};
