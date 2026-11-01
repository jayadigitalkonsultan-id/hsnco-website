<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('practice_areas')->whereIn('status', ['review', 'archived'])->update([
            'status' => 'draft',
        ]);
    }

    public function down(): void
    {
        // Old states cannot be inferred from drafts. Revision history is retained.
    }
};
