<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Define the admin settings that need to be added
        $adminSettings = [
            [
                'key' => 'company_name',
                'value' => 'NetBill Internet Services',
                'description' => 'Company name for invoices and communications',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'company_email',
                'value' => 'billing@netbillbd.com',
                'description' => 'Email address for billing communications',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'invoice_prefix',
                'value' => 'INV-',
                'description' => 'Prefix for invoice numbers',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'invoice_start_number',
                'value' => '1001',
                'description' => 'Starting number for invoice numbering',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'company_address',
                'value' => '123 Business Street, Dhaka, Bangladesh',
                'description' => 'Company address for invoices',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'tax_enabled',
                'value' => '0',
                'description' => 'Enable tax calculation on invoices',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'tax_rate',
                'value' => '0',
                'description' => 'Default tax rate percentage',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'tax_types',
                'value' => '[{"name":"VAT","rate":""}]',
                'description' => 'Available tax types',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'payment_terms',
                'value' => '30',
                'description' => 'Default payment terms in days',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'currency',
                'value' => 'USD',
                'description' => 'Default currency for invoices',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'late_fee_enabled',
                'value' => '0',
                'description' => 'Enable late payment fees',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'late_fee_type',
                'value' => 'percentage',
                'description' => 'Type of late fee (percentage or fixed)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'late_fee_amount',
                'value' => '0',
                'description' => 'Late fee amount',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'auto_reminders',
                'value' => '0',
                'description' => 'Send automatic payment reminders',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'payment_methods',
                'value' => '["bank_transfer"]',
                'description' => 'Enabled payment methods',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'bank_details',
                'value' => 'Bank: ABC Bank Ltd.\nAccount Name: NetBill Internet Services\nAccount Number: 1234567890\nRouting Number: 987654321',
                'description' => 'Bank account details for payments',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'notify_new_invoice',
                'value' => '1',
                'description' => 'Notify on new invoice creation',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'notify_payment_received',
                'value' => '1',
                'description' => 'Notify when payment is received',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'notify_overdue_invoice',
                'value' => '1',
                'description' => 'Notify on overdue invoices',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'notification_email',
                'value' => 'notifications@netbillbd.com',
                'description' => 'Email for system notifications',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'invoice_theme',
                'value' => 'light',
                'description' => 'Default invoice theme',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'invoice_footer',
                'value' => 'Thank you for your business!',
                'description' => 'Footer text for invoices',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert the settings, ignoring duplicates
        foreach ($adminSettings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Define the keys to remove
        $keysToRemove = [
            'company_name',
            'company_email',
            'invoice_prefix',
            'invoice_start_number',
            'company_address',
            'tax_enabled',
            'tax_rate',
            'tax_types',
            'payment_terms',
            'currency',
            'late_fee_enabled',
            'late_fee_type',
            'late_fee_amount',
            'auto_reminders',
            'payment_methods',
            'bank_details',
            'notify_new_invoice',
            'notify_payment_received',
            'notify_overdue_invoice',
            'notification_email',
            'invoice_theme',
            'invoice_footer',
        ];

        // Remove the settings
        DB::table('system_settings')->whereIn('key', $keysToRemove)->delete();
    }
};