<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quantum_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('ibm_session_id')->unique();
            $table->string('backend');
            $table->string('status')->default('open');
            $table->boolean('accepting_jobs')->default(true);
            $table->unsignedInteger('max_ttl_seconds')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('ibm_session_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quantum_sessions');
    }
};
