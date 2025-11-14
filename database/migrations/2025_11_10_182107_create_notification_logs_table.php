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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('type'); // 'push', 'email'
            $table->string('channel'); // 'fcm', 'smtp'
            $table->string('status'); // 'SENT', 'FAILED', 'PENDING'
            $table->string('recipient'); // email address or FCM token
            $table->string('subject')->nullable(); // for emails
            $table->text('title')->nullable(); // for push notifications
            $table->text('body')->nullable();
            $table->json('data')->nullable(); // additional payload data
            $table->text('error_message')->nullable(); // if status is FAILED
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'status']);
            $table->index(['type', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
