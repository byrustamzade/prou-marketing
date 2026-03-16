<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $visitorsTable = (string) config('marketing-touchpoints.tables.visitors', 'marketing_visitors');

        Schema::create($visitorsTable, function (Blueprint $table): void {
            $table->id();
            $table->string('token')->unique();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists((string) config('marketing-touchpoints.tables.visitors', 'marketing_visitors'));
    }
};
