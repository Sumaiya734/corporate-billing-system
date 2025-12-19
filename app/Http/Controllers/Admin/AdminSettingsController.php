<?php
// app/Http/Controllers/Admin/BillingSettingsController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
class AdminSettingsController extends Controller
{
    public function index(Request $request)
    {
        // Get admin statistics
        $stats = [
            'total_invoices' => Invoice::count(),
            'paid_invoices' => Invoice::where('status', 'paid')->count(),
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
            'overdue_invoices' => Invoice::where('status', 'overdue')->count(),
        ];

        // Get all settings from database
        $dbSettings = DB::table('system_settings')->pluck('value', 'key')->toArray();
        
        // Decode JSON values
        foreach ($dbSettings as $key => $value) {
            if (is_string($value) && (substr($value, 0, 1) === '[' || substr($value, 0, 1) === '{')) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $dbSettings[$key] = $decoded;
                }
            }
        }

        $settings = [
            // Admin Information
            'admin_name' => $dbSettings['admin_name'] ?? config('billing.admin_name', Auth::user()->name ?? ''),
            'admin_email' => $dbSettings['admin_email'] ?? config('billing.admin_email', Auth::user()->email ?? ''),
            'admin_phone' => $dbSettings['admin_phone'] ?? config('billing.admin_phone', ''),
            'admin_role' => $dbSettings['admin_role'] ?? config('billing.admin_role', 'System Administrator'),
            'admin_avatar' => $dbSettings['admin_avatar'] ?? config('billing.admin_avatar', ''),
            'admin_signature' => $dbSettings['admin_signature'] ?? config('billing.admin_signature', ''),
            'last_login' => config('billing.last_login', Auth::user()->last_login_at?->format('M d, Y H:i') ?? 'Never'),
            'account_created' => config('billing.account_created', Auth::user()->created_at?->format('M d, Y') ?? 'N/A'),
            'system_version' => $dbSettings['system_version'] ?? config('billing.system_version', '1.0.0'),

            // Company Information
            'company_name' => $dbSettings['company_name'] ?? config('billing.company_name', ''),
            'company_email' => $dbSettings['company_email'] ?? config('billing.company_email', ''),
            'company_phone' => $dbSettings['company_phone'] ?? config('billing.company_phone', ''),
            'company_website' => $dbSettings['company_website'] ?? config('billing.company_website', ''),
            'company_address' => $dbSettings['company_address'] ?? config('billing.company_address', ''),
            'company_logo' => $dbSettings['company_logo'] ?? config('billing.company_logo', ''),
            'invoice_prefix' => $dbSettings['invoice_prefix'] ?? config('billing.invoice_prefix', 'INV-'),
            'invoice_start_number' => $dbSettings['invoice_start_number'] ?? config('billing.invoice_start_number', 1001),

            // Tax Settings
            'tax_enabled' => $dbSettings['tax_enabled'] ?? config('billing.tax_enabled', false),
            'tax_rate' => $dbSettings['tax_rate'] ?? config('billing.tax_rate', 0),
            'tax_types' => $dbSettings['tax_types'] ?? config('billing.tax_types', [['name' => 'VAT', 'rate' => '']]),

            // Invoice & Payment Settings
            'payment_terms' => $dbSettings['payment_terms'] ?? config('billing.payment_terms', 30),
            'currency' => $dbSettings['currency'] ?? config('billing.currency', 'USD'),
            'late_fee_enabled' => $dbSettings['late_fee_enabled'] ?? config('billing.late_fee_enabled', false),
            'late_fee_type' => $dbSettings['late_fee_type'] ?? config('billing.late_fee_type', 'percentage'),
            'late_fee_amount' => $dbSettings['late_fee_amount'] ?? config('billing.late_fee_amount', 0),
            'auto_reminders' => $dbSettings['auto_reminders'] ?? config('billing.auto_reminders', false),

            // Payment Methods
            'payment_methods' => $dbSettings['payment_methods'] ?? config('billing.payment_methods', []),
            'bank_details' => $dbSettings['bank_details'] ?? config('billing.bank_details', ''),

            // Notification Settings
            'notify_new_invoice' => $dbSettings['notify_new_invoice'] ?? config('billing.notify_new_invoice', false),
            'notify_payment_received' => $dbSettings['notify_payment_received'] ?? config('billing.notify_payment_received', false),
            'notify_overdue_invoice' => $dbSettings['notify_overdue_invoice'] ?? config('billing.notify_overdue_invoice', false),
            'notification_email' => $dbSettings['notification_email'] ?? config('billing.notification_email', ''),

            // Invoice Template
            'invoice_theme' => $dbSettings['invoice_theme'] ?? config('billing.invoice_theme', 'light'),
            'invoice_footer' => $dbSettings['invoice_footer'] ?? config('billing.invoice_footer', ''),
            'invoice_notes' => $dbSettings['invoice_notes'] ?? config('billing.invoice_notes', ''),
        ];

        return view('admin.settings.admin-settings', compact('settings', 'stats'));
    }
    public function update(Request $request)
    {
        $validated = $request->validate([
            // Admin Information
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email',
            'admin_phone' => 'nullable|string|max:20',
            'admin_role' => 'nullable|string|max:100',
            'admin_avatar' => 'nullable|image|max:2048',
            'admin_signature' => 'nullable|image|max:512',
            'remove_admin_avatar' => 'nullable|boolean',
            'remove_admin_signature' => 'nullable|boolean',

            // Company Information
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email',
            'company_phone' => 'nullable|string|max:20',
            'company_website' => 'nullable|url|max:255',
            'company_address' => 'nullable|string',
            'company_logo' => 'nullable|image|max:2048',
            'remove_company_logo' => 'nullable|boolean',
            'invoice_prefix' => 'nullable|string|max:10',
            'invoice_start_number' => 'nullable|integer|min:1',

            // Tax Settings
            'tax_enabled' => 'boolean',
            'tax_rate' => 'numeric|min:0|max:100',
            'tax_types' => 'nullable|array',
            'tax_types.*.name' => 'required_with:tax_types|string',
            'tax_types.*.rate' => 'required_with:tax_types|numeric|min:0|max:100',

            // Invoice & Payment Settings
            'payment_terms' => 'integer|min:1',
            'currency' => 'required|string|size:3',
            'late_fee_enabled' => 'boolean',
            'late_fee_type' => 'required_if:late_fee_enabled,1|in:percentage,fixed',
            'late_fee_amount' => 'required_if:late_fee_enabled,1|numeric|min:0',
            'auto_reminders' => 'boolean',

            // Payment Methods
            'payment_methods' => 'nullable|array',
            'payment_methods.*' => 'string',
            'bank_details' => 'nullable|string',

            // Notification Settings
            'notify_new_invoice' => 'boolean',
            'notify_payment_received' => 'boolean',
            'notify_overdue_invoice' => 'boolean',
            'notification_email' => 'nullable|email',

            // Invoice Template
            'invoice_theme' => 'in:light,dark,modern,classic,professional,minimal',
            'invoice_footer' => 'nullable|string',
            'invoice_notes' => 'nullable|string',
        ]);

        // Handle file uploads
        $this->handleFileUploads($request, $validated);

        // Update user information if needed
        // if (Auth::check()) {
        //     $user = Auth::user();
        //     $user->name = $validated['admin_name'];
        //     $user->email = $validated['admin_email'];
        //     if (isset($validated['admin_phone'])) {
        //         $user->phone = $validated['admin_phone'];
        //     }
        //     $user->save();
        // }

        // Store settings in database
        foreach ($validated as $key => $value) {
            if (in_array($key, ['admin_avatar', 'admin_signature', 'company_logo', 'remove_admin_avatar', 
                'remove_admin_signature', 'remove_company_logo'])) {
                continue; // Skip file-related fields
            }
            
            // Convert arrays to JSON for storage
            $valueToStore = is_array($value) ? json_encode($value) : $value;
            
            // Store in database
            DB::table('system_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'key' => $key,
                    'value' => $valueToStore,
                    'updated_at' => now()
                ]
            );
        }
        
        Cache::forget('billing_settings');
        return redirect()->route('admin.admin-settings.index')
            ->with('success', 'Billing settings updated successfully.');
    }

    private function handleFileUploads(Request $request, array &$validated)
    {
        // Handle admin avatar
        if ($request->hasFile('admin_avatar')) {
            $path = $request->file('admin_avatar')->store('avatars', 'public');
            $validated['admin_avatar'] = $path;
            
            // Delete old avatar if exists
            $oldAvatar = config('billing.admin_avatar');
            if ($oldAvatar && Storage::disk('public')->exists($oldAvatar)) {
                Storage::disk('public')->delete($oldAvatar);
            }
        } elseif ($request->input('remove_admin_avatar')) {
            $oldAvatar = config('billing.admin_avatar');
            if ($oldAvatar && Storage::disk('public')->exists($oldAvatar)) {
                Storage::disk('public')->delete($oldAvatar);
            }
            $validated['admin_avatar'] = null;
        }

        // Handle admin signature
        if ($request->hasFile('admin_signature')) {
            $path = $request->file('admin_signature')->store('signatures', 'public');
            $validated['admin_signature'] = $path;
            
            // Delete old signature if exists
            $oldSignature = config('billing.admin_signature');
            if ($oldSignature && Storage::disk('public')->exists($oldSignature)) {
                Storage::disk('public')->delete($oldSignature);
            }
        } elseif ($request->input('remove_admin_signature')) {
            $oldSignature = config('billing.admin_signature');
            if ($oldSignature && Storage::disk('public')->exists($oldSignature)) {
                Storage::disk('public')->delete($oldSignature);
            }
            $validated['admin_signature'] = null;
        }

        // Handle company logo
        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('logos', 'public');
            $validated['company_logo'] = $path;
            
            // Delete old logo if exists
            $oldLogo = config('billing.company_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
        } elseif ($request->input('remove_company_logo')) {
            $oldLogo = config('billing.company_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            $validated['company_logo'] = null;
        }
    }
}