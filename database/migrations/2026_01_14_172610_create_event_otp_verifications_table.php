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
        if (!Schema::hasTable('event_otp_verifications')) {
            Schema::create('event_otp_verifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('event_id');
                $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
                $table->string('email');
                $table->string('otp_code', 6);
                $table->boolean('is_verified')->default(false);
                $table->timestamp('expires_at');
                $table->timestamp('verified_at')->nullable();
                $table->ipAddress('ip_address')->nullable();
                $table->timestamps();
                
                $table->index(['event_id', 'email', 'otp_code']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_otp_verifications');
    }
};
