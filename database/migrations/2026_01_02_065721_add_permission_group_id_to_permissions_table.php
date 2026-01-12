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
        Schema::table('permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_group_id')->nullable()->after('guard_name');
            $table->string('description')->nullable()->after('permission_group_id');

            $table->foreign('permission_group_id')
                ->references('id')
                ->on('permission_groups')
                ->onDelete('set null');

            $table->index('permission_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['permission_group_id']);
            $table->dropColumn(['permission_group_id', 'description']);
        });
    }
};
