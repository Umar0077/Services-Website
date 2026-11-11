<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Indexes for the columns admin tables sort and filter by, so each page of a list loads quickly. */
return new class extends Migration
{
    private array $indexes = [
        'posts' => [['updated_at'], ['category_id', 'status'], ['author_id', 'status']],
        'media' => [['created_at'], ['filename']],
        'cities' => [['name'], ['state_id', 'is_published']],
        'states' => [['name']],
        'leads' => [['status', 'created_at'], ['service_id'], ['state_id']],
        'redirects' => [['updated_at']],
        'pages' => [['title']],
        'services' => [['sort_order']],
        'activity_logs' => [['action', 'created_at'], ['user_id', 'created_at']],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $sets) {
            Schema::table($table, function (Blueprint $t) use ($table, $sets) {
                foreach ($sets as $columns) {
                    $t->index($columns, $table.'_'.implode('_', $columns).'_admin_idx');
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $sets) {
            Schema::table($table, function (Blueprint $t) use ($table, $sets) {
                foreach ($sets as $columns) {
                    $t->dropIndex($table.'_'.implode('_', $columns).'_admin_idx');
                }
            });
        }
    }
};
