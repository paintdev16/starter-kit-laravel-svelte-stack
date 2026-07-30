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
        Schema::table('oauth_providers', function (Blueprint $table) {
            $table->boolean('show_on_login')->default(true)->after('enabled');
        });
    }

    public function down(): void
    {
        Schema::table('oauth_providers', function (Blueprint $table) {
            $table->dropColumn('show_on_login');
        });
    }
};
