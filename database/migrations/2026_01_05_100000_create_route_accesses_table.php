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
        Schema::create('route_accesses', function (Blueprint $table) {
            $table->id();
            $table->string('route_name')->unique();
            $table->string('route_uri')->nullable();
            $table->string('route_method')->nullable();
            $table->string('permission_name')->nullable();
            $table->foreignId('permission_id')->nullable()->constrained('permissions')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(false); // If true, no permission check
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['route_name', 'is_active']);
            $table->index('permission_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_accesses');
    }
};
