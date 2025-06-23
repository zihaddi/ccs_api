<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            // WCAG compliance details
            $table->string('wcag_version')->nullable();
            $table->string('compliance_level')->nullable();
            $table->json('standards_checked')->nullable();

            // Scan metrics
            $table->integer('errors_count')->default(0);
            $table->integer('warnings_count')->default(0);
            $table->integer('notices_count')->default(0);
            $table->integer('pages_scanned')->default(0);
            $table->integer('pages_with_issues')->default(0);

            // Scan configuration
            $table->string('scan_type')->nullable(); // 'single' or 'site'
            $table->text('scanned_url');
            $table->json('scan_options')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->float('scan_duration')->nullable(); // in seconds

            // Issue categorization
            $table->json('issue_categories')->nullable(); // Count of issues by category
            $table->json('wcag_violations')->nullable(); // Specific WCAG criteria violations
            $table->json('compliance_status')->nullable(); // Overall compliance status for different standards

            // User tracking
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('modified_by')->nullable();

            // Add foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['created_by']);
            $table->dropForeign(['modified_by']);

            // Drop columns
            $table->dropColumn([
                'wcag_version',
                'compliance_level',
                'standards_checked',
                'errors_count',
                'warnings_count',
                'notices_count',
                'pages_scanned',
                'pages_with_issues',
                'scan_type',
                'scanned_url',
                'scan_options',
                'completed_at',
                'scan_duration',
                'issue_categories',
                'wcag_violations',
                'compliance_status',
                'created_by',
                'modified_by'
            ]);
        });
    }
};
