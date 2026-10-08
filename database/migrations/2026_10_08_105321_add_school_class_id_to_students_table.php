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
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('school_class_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // Existing students predate the concept of classes. Group them into one
        // class instead of leaving the column null, so nothing is lost.
        if (DB::table('students')->exists()) {
            DB::table('students')->update(['school_class_id' => $this->testClassId()]);
        }

        Schema::table('students', function (Blueprint $table) {
            $table->unsignedBigInteger('school_class_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_class_id');
        });
    }

    /**
     * Find or create the class that pre-existing rows are backfilled into.
     */
    private function testClassId(): int
    {
        return DB::table('school_classes')->where('name', 'Test class')->value('id')
            ?? DB::table('school_classes')->insertGetId([
                'name' => 'Test class',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    }
};
