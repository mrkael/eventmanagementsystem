<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'custom_url')) {
                $table->string('custom_url')->nullable()->unique()->after('slug');
            }
            if (! Schema::hasColumn('events', 'location')) {
                $table->string('location')->nullable()->after('description');
            }
            if (! Schema::hasColumn('events', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('banner_path');
            }
            if (! Schema::hasColumn('events', 'brand_color')) {
                $table->string('brand_color', 20)->default('#047857')->after('logo_path');
            }
            if (! Schema::hasColumn('events', 'registration_opens_at')) {
                $table->timestamp('registration_opens_at')->nullable()->after('published_at');
            }
            if (! Schema::hasColumn('events', 'registration_closes_at')) {
                $table->timestamp('registration_closes_at')->nullable()->after('registration_opens_at');
            }
        });

        Schema::create('event_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('template')->default('default');
            $table->string('status')->default('draft')->index();
            $table->json('settings')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('event_page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_page_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->json('settings')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        if (Schema::hasTable('registration_forms')) {
            try {
                Schema::table('registration_forms', fn (Blueprint $table) => $table->dropUnique('registration_forms_event_id_unique'));
            } catch (Throwable) {
                // The legacy unique may not exist on every environment.
            }
        }

        Schema::create('registration_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_form_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('label');
            $table->string('key');
            $table->string('placeholder')->nullable();
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable();
            $table->json('validation_rules')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestamps();
            $table->unique(['registration_form_id', 'key']);
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_form_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('early_bird_price', 10, 2)->nullable();
            $table->unsignedInteger('group_min_quantity')->nullable();
            $table->decimal('group_price', 10, 2)->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('available_quantity')->default(0);
            $table->timestamp('sales_start_at')->nullable()->index();
            $table->timestamp('sales_end_at')->nullable()->index();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['event_id', 'status']);
        });

        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('discount_type');
            $table->decimal('discount_value', 10, 2);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['event_id', 'code']);
        });

        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->restrictOnDelete();
            $table->foreignId('registration_form_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference_number')->unique();
            $table->string('full_name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->string('organization')->nullable();
            $table->string('designation')->nullable();
            $table->string('status')->default('confirmed')->index();
            $table->string('payment_status')->default('not_applicable')->index();
            $table->decimal('ticket_price', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2)->default(0);
            $table->string('promo_code')->nullable();
            $table->string('qr_token_hash', 64)->unique();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['event_id', 'ticket_id', 'status']);
        });

        Schema::create('registration_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_form_field_id')->nullable()->constrained()->nullOnDelete();
            $table->string('field_key');
            $table->string('field_label');
            $table->string('field_type');
            $table->json('value')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });

        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type')->default('confirmation')->index();
            $table->string('subject');
            $table->longText('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('session_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['event_session_id', 'ticket_id']);
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_out_at')->nullable();
            $table->foreignId('checked_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['event_session_id', 'registration_id']);
        });

        Schema::create('attendance_scan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action')->index();
            $table->string('result')->index();
            $table->string('message')->nullable();
            $table->string('scan_token_hash', 64)->nullable()->index();
            $table->string('device_name')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['event_id', 'event_session_id', 'created_at'], 'attendance_core_event_session_idx');
        });

        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->string('file_path')->nullable();
            $table->string('status')->default('pending')->index();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('processed_rows')->default(0);
            $table->json('summary')->nullable();
            $table->timestamps();
        });

        if (Schema::hasTable('event_sessions') && ! Schema::hasColumn('event_sessions', 'location')) {
            Schema::table('event_sessions', function (Blueprint $table) {
                $table->string('location')->nullable()->after('description');
            });
        }

        if (Schema::hasTable('event_sessions') && ! Schema::hasColumn('event_sessions', 'status')) {
            Schema::table('event_sessions', function (Blueprint $table) {
                $table->string('status')->default('active')->index();
                $table->boolean('one_time_check_in')->default(true);
                $table->boolean('checkout_enabled')->default(false);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('imports');
        Schema::dropIfExists('attendance_scan_logs');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('session_tickets');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('registration_answers');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('promo_codes');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('registration_form_fields');
        Schema::dropIfExists('event_page_sections');
        Schema::dropIfExists('event_pages');

        if (Schema::hasColumn('event_sessions', 'location')) {
            Schema::table('event_sessions', function (Blueprint $table) {
                $table->dropColumn('location');
            });
        }

        if (Schema::hasColumn('event_sessions', 'status')) {
            Schema::table('event_sessions', function (Blueprint $table) {
                $table->dropColumn(['status', 'one_time_check_in', 'checkout_enabled']);
            });
        }

        Schema::table('events', function (Blueprint $table) {
            foreach (['custom_url', 'location', 'logo_path', 'brand_color', 'registration_opens_at', 'registration_closes_at'] as $column) {
                if (Schema::hasColumn('events', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
