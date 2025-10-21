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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // اسم المستخدم
            $table->string('email')->unique();      // البريد الإلكتروني
            $table->string('username')->unique();   // اسم المستخدم الفريد
            $table->string('password');             // كلمة المرور
            $table->string('phone')->nullable();    // رقم الهاتف
            $table->string('address')->nullable();  // العنوان
            $table->date('birthdate')->nullable();  // تاريخ الميلاد
            $table->enum('gender', ['male', 'female'])->nullable(); // النوع
            $table->boolean('is_active')->default(true); // حالة التفعيل
            $table->timestamp('email_verified_at')->nullable(); // تحقق البريد
            $table->rememberToken();
            $table->timestamps();                   // created_at و updated_at
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
