<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('fees_collections');
        Schema::dropIfExists('fees_receipts');
    }

    public function down(): void
    {
        // Old fees_collections / fees_receipts modules were removed.
        // Re-create from legacy migrations if rollback is ever required.
    }
};
