<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organiser_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('organiser_profile_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organiser_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['organiser_profile_id', 'user_id']);
        });

        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'organiser_profile_id')) {
                $table->foreignId('organiser_profile_id')->nullable()->after('organizer_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('events', 'payment_tax_percentage')) {
                $table->decimal('payment_tax_percentage', 5, 2)->default(0)->after('capacity');
            }
            if (! Schema::hasColumn('events', 'allow_promo_code')) {
                $table->boolean('allow_promo_code')->default(true)->after('payment_tax_percentage');
            }
            if (! Schema::hasColumn('events', 'allow_duplicate_email')) {
                $table->boolean('allow_duplicate_email')->default(false)->after('allow_promo_code');
            }
            if (! Schema::hasColumn('events', 'sender_name')) {
                $table->string('sender_name')->nullable()->after('allow_duplicate_email');
            }
            if (! Schema::hasColumn('events', 'sender_email')) {
                $table->string('sender_email')->nullable()->after('sender_name');
            }
        });

        Schema::table('tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('tickets', 'currency')) {
                $table->string('currency', 3)->default('MYR')->after('description');
            }
            if (! Schema::hasColumn('tickets', 'min_quantity')) {
                $table->unsignedInteger('min_quantity')->default(1)->after('currency');
            }
            if (! Schema::hasColumn('tickets', 'max_quantity')) {
                $table->unsignedInteger('max_quantity')->default(1)->after('min_quantity');
            }
            if (! Schema::hasColumn('tickets', 'is_hidden')) {
                $table->boolean('is_hidden')->default(false)->after('max_quantity');
            }
        });

        Schema::table('email_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('email_templates', 'name')) {
                $table->string('name')->nullable()->after('type');
            }
            if (! Schema::hasColumn('email_templates', 'preheader')) {
                $table->string('preheader')->nullable()->after('subject');
            }
        });

        Schema::create('contact_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('mobile_number')->nullable();
            $table->string('organization')->nullable();
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('secretary_name')->nullable();
            $table->string('secretary_email')->nullable();
            $table->string('email_status')->default('unverified')->index();
            $table->json('extra')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contact_contact_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_group_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['contact_id', 'contact_group_id']);
        });

        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('email_template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type')->default('edm')->index();
            $table->string('sender_name');
            $table->string('sender_email');
            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->json('recipient_filters')->nullable();
            $table->unsignedInteger('recipient_count')->default(0);
            $table->string('status')->default('draft')->index();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('event_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('module')->index();
            $table->json('selected_columns');
            $table->boolean('show_on_overview')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_reports');
        Schema::dropIfExists('email_campaigns');
        Schema::dropIfExists('contact_contact_group');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('contact_groups');
        Schema::dropIfExists('organiser_profile_user');
        Schema::dropIfExists('organiser_profiles');
    }
};
