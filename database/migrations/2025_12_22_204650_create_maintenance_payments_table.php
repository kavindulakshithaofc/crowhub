<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('maintenance_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('maintenance_contracts')->cascadeOnDelete();
            $table->date('for_month');
            $table->decimal('amount', 12, 2);
            $table->date('paid_date')->default(DB::raw('CURRENT_DATE'));
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['contract_id', 'for_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_payments');
    }
};
