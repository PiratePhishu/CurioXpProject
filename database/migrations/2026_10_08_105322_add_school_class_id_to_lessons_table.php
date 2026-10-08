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
        Schema::table('lessons', function (Blueprint $table) {
            $table->foreignId('school_class_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // Existing lessons predate the concept of classes. Group them into the
        // same class pre-existing students were backfilled into.
        if (DB::table('lessons')->exists()) {
            DB::table('lessons')->update(['school_class_id' => $this->testClassId()]);
        }

        Schema::table('lessons', function (Blueprint $table) {
            $table->unsignedBigInteger('school_class_id')->nullable(false)->change();
        });

        Schema::table('lessons', function (Blueprint $table) {
            // "code" only needs to be unique within a class now that a lesson
            // list gets copied into every newly created class.
            $table->dropUnique(['code']);
            $table->unique(['school_class_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropUnique(['school_class_id', 'code']);
            $table->unique('code');
        });

        Schema::table('lessons', function (Blueprint $table) {
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
