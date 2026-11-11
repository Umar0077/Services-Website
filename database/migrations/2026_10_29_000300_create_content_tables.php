<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Content model: blog, services, locations and CMS pages.
 * Public content tables get the shared SEO columns via Blueprint::seo() (registered in AppServiceProvider).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->seo();
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('status', 20)->default('draft')->index(); // draft | scheduled | published
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('is_featured')->default(false);
            $table->boolean('show_toc')->default(true);
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->json('faqs')->nullable();
            $table->string('primary_keyword')->nullable();
            $table->string('secondary_keywords')->nullable();
            $table->unsignedSmallInteger('reading_minutes')->default(1);
            $table->seo();
            $table->timestamp('content_updated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'published_at']);
        });

        Schema::create('post_tag', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['post_id', 'tag_id']);
        });

        Schema::create('post_related', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_post_id')->constrained('posts')->cascadeOnDelete();
            $table->primary(['post_id', 'related_post_id']);
        });

        Schema::create('post_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('nav_label')->nullable();
            $table->string('group', 20)->default('cooling')->index(); // cooling | heating | air | commercial
            $table->string('icon', 40)->default('snowflake');
            $table->string('short_description', 300);
            $table->string('hero_heading')->nullable();
            $table->text('hero_text')->nullable();
            $table->longText('overview')->nullable();
            $table->json('problems')->nullable();   // [{title, text}]
            $table->json('options')->nullable();    // [{title, text}]
            $table->json('signs')->nullable();      // [{text}]
            $table->longText('details')->nullable();
            $table->json('faqs')->nullable();       // [{question, answer}]
            $table->foreignId('hero_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->seo();
            $table->timestamp('content_updated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('service_related', function (Blueprint $table) {
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_service_id')->constrained('services')->cascadeOnDelete();
            $table->primary(['service_id', 'related_service_id']);
        });

        Schema::create('post_service', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->primary(['post_id', 'service_id']);
        });

        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->char('code', 2)->unique();
            $table->string('slug')->unique();
            $table->string('heading')->nullable();
            $table->text('intro')->nullable();
            $table->longText('climate')->nullable();
            $table->longText('body')->nullable();
            $table->json('faqs')->nullable();
            $table->foreignId('hero_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->boolean('is_published')->default(false)->index();
            // Search indexing requires a person to confirm the network actually serves this state.
            $table->boolean('coverage_verified')->default(false)->index();
            $table->boolean('is_featured')->default(false);
            $table->seo();
            $table->timestamp('content_updated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('state_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('heading')->nullable();
            $table->text('intro')->nullable();
            $table->longText('local_info')->nullable();
            $table->longText('body')->nullable();
            $table->json('faqs')->nullable();
            $table->foreignId('hero_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->boolean('is_published')->default(false)->index();
            $table->boolean('service_verified')->default(false)->index();
            $table->boolean('is_featured')->default(false);
            $table->seo();
            $table->timestamp('content_updated_at')->nullable();
            $table->timestamps();
            $table->unique(['state_id', 'slug']);
        });

        Schema::create('service_state', function (Blueprint $table) {
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('state_id')->constrained()->cascadeOnDelete();
            $table->primary(['service_id', 'state_id']);
        });

        Schema::create('city_service', function (Blueprint $table) {
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->primary(['city_id', 'service_id']);
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('template', 30)->default('standard'); // standard | legal | landing
            $table->string('system_key', 40)->nullable()->unique(); // about, privacy... (cannot be deleted)
            $table->string('heading')->nullable();
            $table->text('intro')->nullable();
            $table->longText('body')->nullable();
            $table->json('sections')->nullable(); // controlled section blocks
            $table->string('cta_heading')->nullable();
            $table->text('cta_text')->nullable();
            $table->string('cta_button_label')->nullable();
            $table->string('cta_button_url')->nullable();
            $table->foreignId('hero_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->boolean('is_published')->default(false)->index();
            $table->boolean('show_in_footer')->default(false);
            $table->seo();
            $table->timestamp('content_updated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['pages', 'city_service', 'service_state', 'cities', 'states', 'post_service', 'service_related',
            'services', 'post_revisions', 'post_related', 'post_tag', 'posts', 'tags', 'categories'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
