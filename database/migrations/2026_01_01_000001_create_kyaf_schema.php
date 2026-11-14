<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $t) {
            $t->smallIncrements('role_id');
            $t->string('role_name', 30)->unique();
            $t->string('description', 200)->nullable();
        });

        Schema::create('user_accounts', function (Blueprint $t) {
            $t->id('user_id');
            $t->unsignedSmallInteger('role_id');
            $t->string('full_name', 100);
            $t->string('email', 150)->unique();
            $t->string('phone', 14)->unique();
            $t->string('password_hash', 255);
            $t->enum('status', ['Active', 'Inactive', 'Suspended', 'Locked', 'PendingActivation'])->default('Active');
            $t->unsignedTinyInteger('failed_login_count')->default(0);
            $t->dateTime('locked_until')->nullable();
            $t->dateTime('last_login_at')->nullable();
            $t->timestamps();
            $t->foreign('role_id')->references('role_id')->on('roles')->restrictOnDelete();
        });

        // Added for BR-27 (a new password must differ from the last three). Not one of the 21 SRS entities.
        Schema::create('password_histories', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->string('password_hash', 255);
            $t->dateTime('created_at');
            $t->foreign('user_id')->references('user_id')->on('user_accounts')->restrictOnDelete();
        });

        Schema::create('password_reset_tokens', function (Blueprint $t) {
            $t->id('token_id');
            $t->unsignedBigInteger('user_id')->index();
            $t->char('token_hash', 64)->unique();
            $t->dateTime('expires_at');
            $t->dateTime('used_at')->nullable();
            $t->dateTime('created_at');
            $t->foreign('user_id')->references('user_id')->on('user_accounts')->restrictOnDelete();
        });

        Schema::create('system_settings', function (Blueprint $t) {
            $t->string('setting_key', 60)->primary();
            $t->string('setting_value', 255);
            $t->string('description', 200)->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->dateTime('updated_at');
            $t->foreign('updated_by')->references('user_id')->on('user_accounts')->restrictOnDelete();
        });

        Schema::create('districts', function (Blueprint $t) {
            $t->smallIncrements('district_id');
            $t->string('name_en', 60)->unique();
            $t->string('name_bn', 60);
            $t->string('division_name', 40)->index();
        });

        Schema::create('upazilas', function (Blueprint $t) {
            $t->increments('upazila_id');
            $t->unsignedSmallInteger('district_id');
            $t->string('name_en', 60);
            $t->string('name_bn', 60);
            $t->unique(['district_id', 'name_en']);
            $t->foreign('district_id')->references('district_id')->on('districts')->restrictOnDelete();
        });

        Schema::create('thanas', function (Blueprint $t) {
            $t->increments('thana_id');
            $t->unsignedInteger('upazila_id');
            $t->unsignedInteger('area_id')->nullable()->index();
            $t->string('name_en', 60);
            $t->string('name_bn', 60);
            $t->unique(['upazila_id', 'name_en']);
            $t->foreign('upazila_id')->references('upazila_id')->on('upazilas')->restrictOnDelete();
        });

        Schema::create('donors', function (Blueprint $t) {
            $t->string('donor_id', 10)->primary();
            $t->unsignedBigInteger('user_id')->unique();
            $t->enum('donor_type', ['Individual', 'Restaurant', 'Organization'])->default('Individual');
            $t->string('organization_name', 120)->nullable();
            $t->unsignedInteger('thana_id');
            $t->string('address_line', 250);
            $t->dateTime('registered_at');
            $t->foreign('user_id')->references('user_id')->on('user_accounts')->restrictOnDelete();
            $t->foreign('thana_id')->references('thana_id')->on('thanas')->restrictOnDelete();
        });

        Schema::create('area_managers', function (Blueprint $t) {
            $t->id('manager_id');
            $t->unsignedBigInteger('user_id')->unique();
            $t->string('nid_number', 17)->unique();
            $t->dateTime('appointed_at');
            $t->enum('status', ['Active', 'Inactive'])->default('Active');
            $t->foreign('user_id')->references('user_id')->on('user_accounts')->restrictOnDelete();
        });

        Schema::create('service_areas', function (Blueprint $t) {
            $t->increments('area_id');
            $t->string('area_code', 12)->unique();
            $t->string('area_name', 100);
            $t->unsignedSmallInteger('district_id');
            $t->unsignedBigInteger('manager_id')->nullable()->unique();
            $t->enum('status', ['Active', 'Inactive'])->default('Active');
            $t->dateTime('created_at');
            $t->foreign('district_id')->references('district_id')->on('districts')->restrictOnDelete();
            $t->foreign('manager_id')->references('manager_id')->on('area_managers')->restrictOnDelete();
        });

        Schema::table('thanas', function (Blueprint $t) {
            $t->foreign('area_id')->references('area_id')->on('service_areas')->restrictOnDelete();
        });

        Schema::create('staff_applications', function (Blueprint $t) {
            $t->id('application_id');
            $t->string('applicant_name', 100);
            $t->string('email', 150)->index();
            $t->string('phone', 14);
            $t->string('nid_number', 17);
            $t->date('date_of_birth');
            $t->string('education_level', 60);
            $t->string('experience_summary', 1000)->nullable();
            $t->unsignedInteger('thana_id');
            $t->unsignedInteger('area_id');
            $t->string('cv_file_path', 255)->nullable();
            $t->enum('status', ['Submitted', 'UnderReview', 'Approved', 'Rejected', 'Registered', 'Expired'])->default('Submitted');
            $t->unsignedBigInteger('reviewed_by')->nullable();
            $t->string('review_remarks', 500)->nullable();
            $t->char('invitation_token_hash', 64)->nullable()->unique();
            $t->dateTime('invitation_expires_at')->nullable();
            $t->dateTime('submitted_at');
            $t->dateTime('reviewed_at')->nullable();
            $t->foreign('thana_id')->references('thana_id')->on('thanas')->restrictOnDelete();
            $t->foreign('area_id')->references('area_id')->on('service_areas')->restrictOnDelete();
            $t->foreign('reviewed_by')->references('user_id')->on('user_accounts')->restrictOnDelete();
        });

        Schema::create('staff', function (Blueprint $t) {
            $t->id('staff_id');
            $t->unsignedBigInteger('user_id')->unique();
            $t->unsignedBigInteger('manager_id')->index();
            $t->unsignedBigInteger('application_id')->nullable()->unique();
            $t->dateTime('joined_at');
            $t->enum('status', ['Active', 'Suspended', 'Removed'])->default('Active');
            $t->foreign('user_id')->references('user_id')->on('user_accounts')->restrictOnDelete();
            $t->foreign('manager_id')->references('manager_id')->on('area_managers')->restrictOnDelete();
            $t->foreign('application_id')->references('application_id')->on('staff_applications')->restrictOnDelete();
        });

        Schema::create('donations', function (Blueprint $t) {
            $t->id('donation_id');
            $t->string('donation_code', 16)->unique();
            $t->string('donor_id', 10)->index();
            $t->unsignedInteger('area_id')->index();
            $t->string('food_name', 100);
            $t->enum('food_category', ['Cooked', 'Packaged', 'Raw', 'Bakery']);
            $t->unsignedInteger('quantity_meals');
            $t->date('expiry_date');
            $t->time('expiry_time');
            $t->string('description', 500)->nullable();
            $t->unsignedInteger('pickup_thana_id');
            $t->string('pickup_address', 250);
            $t->string('contact_phone', 14);
            $t->string('photo_path', 255)->nullable();
            $t->unsignedBigInteger('assigned_staff_id')->nullable()->index();
            $t->enum('status', ['Submitted', 'Assigned', 'Verified', 'Approved', 'Collected', 'Distributed', 'Completed', 'Rejected', 'Cancelled', 'Expired'])->default('Submitted')->index();
            $t->dateTime('submitted_at');
            $t->dateTime('updated_at');
            $t->foreign('donor_id')->references('donor_id')->on('donors')->restrictOnDelete();
            $t->foreign('area_id')->references('area_id')->on('service_areas')->restrictOnDelete();
            $t->foreign('pickup_thana_id')->references('thana_id')->on('thanas')->restrictOnDelete();
            $t->foreign('assigned_staff_id')->references('staff_id')->on('staff')->restrictOnDelete();
        });

        $statuses = ['Submitted', 'Assigned', 'Verified', 'Approved', 'Collected', 'Distributed', 'Completed', 'Rejected', 'Cancelled', 'Expired'];

        Schema::create('donation_status_histories', function (Blueprint $t) use ($statuses) {
            $t->id('history_id');
            $t->unsignedBigInteger('donation_id')->index();
            $t->enum('from_status', $statuses)->nullable();
            $t->enum('to_status', $statuses);
            $t->unsignedBigInteger('changed_by')->nullable();
            $t->string('remarks', 500)->nullable();
            $t->dateTime('changed_at');
            $t->foreign('donation_id')->references('donation_id')->on('donations')->restrictOnDelete();
            $t->foreign('changed_by')->references('user_id')->on('user_accounts')->restrictOnDelete();
        });

        Schema::create('verifications', function (Blueprint $t) {
            $t->id('verification_id');
            $t->unsignedBigInteger('donation_id')->index();
            $t->unsignedBigInteger('staff_id')->index();
            $t->enum('result', ['Verified', 'Rejected']);
            $t->enum('food_condition', ['Good', 'Acceptable', 'Unsafe']);
            $t->unsignedInteger('verified_quantity_meals');
            $t->string('remarks', 500)->nullable();
            $t->dateTime('verified_at');
            $t->foreign('donation_id')->references('donation_id')->on('donations')->restrictOnDelete();
            $t->foreign('staff_id')->references('staff_id')->on('staff')->restrictOnDelete();
        });

        Schema::create('approvals', function (Blueprint $t) {
            $t->id('approval_id');
            $t->unsignedBigInteger('donation_id')->index();
            $t->unsignedBigInteger('decided_by')->index();
            $t->enum('decision', ['Approved', 'Rejected']);
            $t->string('remarks', 500)->nullable();
            $t->dateTime('decided_at');
            $t->foreign('donation_id')->references('donation_id')->on('donations')->restrictOnDelete();
            $t->foreign('decided_by')->references('user_id')->on('user_accounts')->restrictOnDelete();
        });

        Schema::create('collections', function (Blueprint $t) {
            $t->id('collection_id');
            $t->unsignedBigInteger('donation_id')->unique();
            $t->unsignedBigInteger('staff_id')->index();
            $t->dateTime('scheduled_at');
            $t->dateTime('collected_at')->nullable();
            $t->unsignedInteger('collected_quantity_meals')->nullable();
            $t->enum('status', ['Scheduled', 'Collected', 'Failed'])->default('Scheduled');
            $t->string('remarks', 500)->nullable();
            $t->foreign('donation_id')->references('donation_id')->on('donations')->restrictOnDelete();
            $t->foreign('staff_id')->references('staff_id')->on('staff')->restrictOnDelete();
        });

        Schema::create('distributions', function (Blueprint $t) {
            $t->id('distribution_id');
            $t->unsignedBigInteger('donation_id')->index();
            $t->unsignedBigInteger('staff_id')->index();
            $t->string('point_name', 100);
            $t->string('point_address', 250);
            $t->unsignedInteger('thana_id');
            $t->dateTime('scheduled_start');
            $t->dateTime('scheduled_end');
            $t->unsignedInteger('meals_planned');
            $t->unsignedInteger('meals_distributed')->default(0);
            $t->boolean('is_public')->default(false);
            $t->enum('status', ['Planned', 'Ongoing', 'Completed', 'Cancelled'])->default('Planned');
            $t->string('remarks', 500)->nullable();
            $t->dateTime('completed_at')->nullable();
            $t->foreign('donation_id')->references('donation_id')->on('donations')->restrictOnDelete();
            $t->foreign('staff_id')->references('staff_id')->on('staff')->restrictOnDelete();
            $t->foreign('thana_id')->references('thana_id')->on('thanas')->restrictOnDelete();
        });

        Schema::create('notifications', function (Blueprint $t) {
            $t->id('notification_id');
            $t->unsignedBigInteger('user_id')->index();
            $t->unsignedBigInteger('donation_id')->nullable()->index();
            $t->string('type', 30);
            $t->string('title', 120);
            $t->string('message', 500);
            $t->enum('channel', ['InApp', 'Email', 'SMS'])->default('InApp');
            $t->enum('delivery_status', ['Pending', 'Sent', 'Failed'])->default('Sent');
            $t->boolean('is_read')->default(false);
            $t->dateTime('created_at');
            $t->dateTime('read_at')->nullable();
            $t->foreign('user_id')->references('user_id')->on('user_accounts')->restrictOnDelete();
            $t->foreign('donation_id')->references('donation_id')->on('donations')->restrictOnDelete();
        });

        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id('log_id');
            $t->unsignedBigInteger('user_id')->nullable()->index();
            $t->string('action_type', 40)->index();
            $t->string('entity_name', 40);
            $t->string('entity_id', 40)->nullable();
            $t->json('old_value')->nullable();
            $t->json('new_value')->nullable();
            $t->enum('result', ['Success', 'Failure'])->default('Success');
            $t->string('ip_address', 45);
            $t->string('user_agent', 255)->nullable();
            $t->dateTime('created_at')->index();
            $t->foreign('user_id')->references('user_id')->on('user_accounts')->restrictOnDelete();
        });

        Schema::create('contact_messages', function (Blueprint $t) {
            $t->id('message_id');
            $t->string('sender_name', 100);
            $t->string('email', 150);
            $t->string('phone', 14)->nullable();
            $t->string('subject', 150);
            $t->string('message_body', 2000);
            $t->enum('status', ['New', 'InProgress', 'Resolved'])->default('New');
            $t->unsignedBigInteger('handled_by')->nullable();
            $t->dateTime('created_at');
            $t->foreign('handled_by')->references('user_id')->on('user_accounts')->restrictOnDelete();
        });

        $this->addChecks();
    }

    /** CHECK constraints from the SRS data dictionary (enforced by MySQL 8.0.16+ / MariaDB 10.2+; ignored silently elsewhere). */
    private function addChecks(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }
        $checks = [
            'ALTER TABLE donations ADD CONSTRAINT chk_donation_qty CHECK (quantity_meals >= 1)',
            'ALTER TABLE distributions ADD CONSTRAINT chk_dist_planned CHECK (meals_planned > 0)',
            'ALTER TABLE distributions ADD CONSTRAINT chk_dist_done CHECK (meals_distributed <= meals_planned)',
            'ALTER TABLE distributions ADD CONSTRAINT chk_dist_time CHECK (scheduled_end > scheduled_start)',
        ];
        foreach ($checks as $sql) {
            try {
                DB::statement($sql);
            } catch (\Throwable $e) {
                // Older servers without CHECK support: the service layer enforces the same rules.
            }
        }
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach (['contact_messages', 'audit_logs', 'notifications', 'distributions', 'collections', 'approvals', 'verifications',
            'donation_status_histories', 'donations', 'staff', 'staff_applications', 'service_areas', 'area_managers', 'donors',
            'thanas', 'upazilas', 'districts', 'system_settings', 'password_reset_tokens', 'password_histories', 'user_accounts', 'roles'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();
    }
};
