<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 32);
            $table->string('email')->nullable();
            $table->string('zip', 10)->nullable();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service_label')->nullable(); // snapshot in case the service is renamed
            $table->foreignId('state_id')->nullable()->constrained()->nullOnDelete();
            $table->string('city_name')->nullable();
            $table->string('property_type', 20)->nullable(); // residential | commercial
            $table->text('message')->nullable();
            $table->string('status', 20)->default('new')->index(); // new | contacted | qualified | closed | spam
            $table->text('internal_notes')->nullable();
            $table->boolean('consent')->default(false);
            $table->string('landing_page')->nullable();
            $table->string('referrer')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('gclid')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });

        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path')->unique();
            $table->string('to_path');
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        // Approved call-tracking numbers. Only these may replace the default phone via URL parameters.
        Schema::create('tracking_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('code', 40)->unique(); // value used in ?pn=code
            $table->string('number', 20);         // 10 US digits
            $table->string('source')->nullable(); // e.g. MarketCall campaign name
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 40);
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('description')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable()->index();
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        foreach (['activity_logs', 'tracking_numbers', 'settings', 'redirects', 'leads'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
