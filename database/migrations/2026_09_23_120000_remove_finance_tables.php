<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
    }

    public function down(): void
    {
        // Finance is intentionally removed and is not recreated.
    }
};
