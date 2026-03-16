<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $visitorsTable = (string) config('marketing-touchpoints.tables.visitors', 'marketing_visitors');
        $conversionsTable = (string) config('marketing-touchpoints.tables.conversions', 'marketing_conversions');

        Schema::create($conversionsTable, function (Blueprint $table) use ($visitorsTable): void {
            $table->id();
            $table->foreignId('visitor_id')->constrained($visitorsTable)->cascadeOnDelete();
            $table->string('token')->index();
            $table->string('order_table');
            $table->string('order_primary_key');
            $table->string('order_identifier')->index();
            $table->json('meta')->nullable();
            $table->timestamp('linked_at')->index();
            $table->timestamps();

            $table->unique(
                ['visitor_id', 'order_table', 'order_primary_key', 'order_identifier'],
                'marketing_unique_visitor_order'
            );
            $table->index(['order_table', 'order_identifier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists((string) config('marketing-touchpoints.tables.conversions', 'marketing_conversions'));
    }
};
