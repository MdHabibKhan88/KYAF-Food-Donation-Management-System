<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_accounts', function (Blueprint $t) {
            $t->string('profile_photo_path', 255)->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('user_accounts', function (Blueprint $t) {
            $t->dropColumn('profile_photo_path');
        });
    }
};