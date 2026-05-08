<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('erp_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->default('info'); // info, warning, danger, success
            $table->string('icon', 50)->default('fa-bell');
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->unsignedBigInteger('user_id')->nullable(); // null = broadcast to all
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_notifications');
    }
};
