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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_school_class_id')->nullable()->after('id')
                ->constrained('school_classes')->nullOnDelete();
        });

        $classId = DB::table('school_classes')->orderBy('id')->value('id');

        if ($classId) {
            DB::table('users')->update(['current_school_class_id' => $classId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_school_class_id');
        });
    }
};
