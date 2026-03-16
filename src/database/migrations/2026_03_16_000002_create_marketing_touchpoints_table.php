<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $visitorsTable = (string) config('marketing-touchpoints.tables.visitors', 'marketing_visitors');
        $touchpointsTable = (string) config('marketing-touchpoints.tables.touchpoints', 'marketing_touchpoints');

        Schema::create($touchpointsTable, function (Blueprint $table) use ($visitorsTable): void {
            $table->id();
            $table->foreignId('visitor_id')->constrained($visitorsTable)->cascadeOnDelete();
            $table->string('token')->index();
            $table->text('landing_url')->nullable();
            $table->string('path')->nullable()->index();
            $table->string('method', 16)->nullable();
            $table->text('referer')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_id')->nullable();
            $table->string('gclid')->nullable();
            $table->string('fbclid')->nullable();
            $table->string('msclkid')->nullable();
            $table->json('query')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists((string) config('marketing-touchpoints.tables.touchpoints', 'marketing_touchpoints'));
    }
};
