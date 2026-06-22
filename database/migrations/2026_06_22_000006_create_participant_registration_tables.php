<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('access_mode')->default('public')->index();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('requires_approval')->default(false);
            $table->boolean('allow_waitlist')->default(true);
            $table->boolean('is_multi_step')->default(false);
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('event_id');
        });

        Schema::create('registration_question_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_form_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('step_number')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('registration_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_question_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('label');
            $table->string('key');
            $table->text('help_text')->nullable();
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable();
            $table->json('validation_rules')->nullable();
            $table->json('conditional_logic')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['registration_form_id', 'key']);
            $table->index(['registration_form_id', 'sort_order']);
        });

        Schema::create('registration_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('email')->index();
            $table->string('token')->unique();
            $table->string('status')->default('pending')->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('participant_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_form_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('registration_invite_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->string('organization')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('source')->default('public')->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['event_id', 'status']);
            $table->index(['event_id', 'email']);
        });

        Schema::create('participant_registration_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_registration_id');
            $table->foreignId('registration_question_id')->nullable();
            $table->string('question_key');
            $table->string('question_label');
            $table->string('question_type');
            $table->json('value')->nullable();
            $table->timestamps();

            $table->foreign('participant_registration_id', 'pra_registration_fk')->references('id')->on('participant_registrations')->cascadeOnDelete();
            $table->foreign('registration_question_id', 'pra_question_fk')->references('id')->on('registration_questions')->nullOnDelete();
        });

        Schema::create('registration_answer_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_registration_answer_id');
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();

            $table->foreign('participant_registration_answer_id', 'raf_answer_fk')->references('id')->on('participant_registration_answers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_answer_files');
        Schema::dropIfExists('participant_registration_answers');
        Schema::dropIfExists('participant_registrations');
        Schema::dropIfExists('registration_invites');
        Schema::dropIfExists('registration_questions');
        Schema::dropIfExists('registration_question_groups');
        Schema::dropIfExists('registration_forms');
    }
};
