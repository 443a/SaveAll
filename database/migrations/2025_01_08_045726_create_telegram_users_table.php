<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTelegramUsersTable extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('chat_id')->unique(); // Unique chat ID from Telegram
            $table->string('first_name')->nullable(); // User's first name
            $table->string('last_name')->nullable(); // User's last name
            $table->string('username')->nullable(); // User's Telegram username
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_users');
    }
}
