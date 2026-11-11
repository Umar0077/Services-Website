<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blog posts can be written in the visual editor ("rich") or as raw HTML ("html").
 * In HTML mode the code is kept verbatim in body_html and copied into body on save, so rendering, search,
 * revisions and SEO checks all keep working from one column. Output is always sanitized.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('body_format', 10)->default('rich')->after('body');
            $table->longText('body_html')->nullable()->after('body_format');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['body_format', 'body_html']);
        });
    }
};
