<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quantum_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('ibm_job_id')->nullable()->unique();
            $table->string('ibm_session_id')->nullable();
            $table->string('backend');
            $table->enum('primitive_type', ['sampler', 'estimator'])->default('sampler');
            $table->string('status')->default('pending');
            $table->json('payload')->nullable();
            $table->json('result')->nullable();
            $table->json('metadata')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('poll_count')->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('ibm_job_id');
            $table->index('status');
            $table->index('backend');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quantum_jobs');
    }
};
