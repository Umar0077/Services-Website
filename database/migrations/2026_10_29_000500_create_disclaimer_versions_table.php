<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Every change to the client-approved disclaimer is kept here (who, when, full text). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disclaimer_versions', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->string('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disclaimer_versions');
    }
};
