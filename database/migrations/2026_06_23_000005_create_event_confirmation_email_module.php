<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('confirmation')->index();
            $table->string('subject');
            $table->text('header_content')->nullable();
            $table->longText('body_content');
            $table->text('footer_content')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['event_id', 'type', 'deleted_at']);
        });

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email_type')->index();
            $table->string('recipient_email');
            $table->string('original_participant_email')->nullable();
            $table->string('sender_email')->nullable();
            $table->string('subject');
            $table->string('status')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index(['event_id', 'email_type', 'status']);
        });

        Schema::table('registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('registrations', 'qr_token')) {
                $table->string('qr_token')->nullable()->unique()->after('qr_token_hash');
            }
            if (! Schema::hasColumn('registrations', 'qr_code_data')) {
                $table->longText('qr_code_data')->nullable()->after('qr_token');
            }
            if (! Schema::hasColumn('registrations', 'qr_generated_at')) {
                $table->timestamp('qr_generated_at')->nullable()->after('qr_code_data');
            }
            if (! Schema::hasColumn('registrations', 'confirmation_email_sent_at')) {
                $table->timestamp('confirmation_email_sent_at')->nullable()->after('qr_generated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            foreach (['confirmation_email_sent_at', 'qr_generated_at', 'qr_code_data', 'qr_token'] as $column) {
                if (Schema::hasColumn('registrations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('event_email_templates');
    }
};
