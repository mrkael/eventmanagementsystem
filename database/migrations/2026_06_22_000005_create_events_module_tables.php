<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('event_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('event_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('venue_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('event_status_id')->constrained()->restrictOnDelete();
            $table->foreignId('event_configuration_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->index();
            $table->unsignedInteger('capacity')->default(0);
            $table->boolean('is_registration_enabled')->default(false);
            $table->boolean('is_public')->default(false);
            $table->string('status_key')->default('draft')->index();
            $table->string('banner_path')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_page_version_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status_key', 'starts_at']);
            $table->index(['event_category_id', 'starts_at']);
            $table->index(['venue_id', 'starts_at']);
        });

        Schema::create('event_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('venue_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->index();
            $table->unsignedInteger('capacity')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('event_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });

        Schema::create('event_page_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->string('status')->default('draft')->index();
            $table->json('sections');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['event_id', 'version']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->foreign('published_page_version_id')->references('id')->on('event_page_versions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['published_page_version_id']);
        });

        Schema::dropIfExists('event_page_versions');
        Schema::dropIfExists('event_documents');
        Schema::dropIfExists('event_sessions');
        Schema::dropIfExists('events');
    }
};
