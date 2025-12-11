<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use Carbon\Carbon;

echo "=== CREATING CORRECT INVOICES ===\n\n";

// Delete all existing invoices
Invoice::query()->delete();
echo "Deleted all existing invoices\n\n";

// Create May invoice (Period: May-Aug)
$mayInvoice = Invoice::create([
    'invoice_number' => 'INV-25-05-0001',
    'cp_id' => 29,
    'issue_date' => '2025-05-09',
    'previous_due' => 0,
    'subtotal' => 2000,
    'total_amount' => 2000,
    'received_amount' => 0,
    'next_due' => 2000,
    'status' => 'unpaid',
    'notes' => 'Billing period: May-Aug 2025',
    'created_by' => 1
]);
echo "✓ Created May invoice: 2,000 BDT (Period: May-Aug)\n\n";

// Create August invoice (SAME Period: May-Aug, replaces May)
// Cancel May invoice since August is for the same period
$mayInvoice->update(['status' => 'cancelled', 'notes' => 'Replaced by August invoice (same period)']);

$augInvoice = Invoice::create([
    'invoice_number' => 'INV-25-08-0001',
    'cp_id' => 29,
    'issue_date' => '2025-08-09',
    'previous_due' => 0,
    'subtotal' => 2000,
    'total_amount' => 2000,
    'received_amount' => 0,
    'next_due' => 2000,
    'status' => 'unpaid',
    'notes' => 'Billing period: May-Aug 2025 (replaces May invoice)',
    'created_by' => 1
]);
echo "✓ Created August invoice: 2,000 BDT (Period: May-Aug, replaces May)\n";
echo "  Cancelled May invoice\n\n";

// Create November invoice (NEW Period: Aug-Nov)
// Include previous_due from August (only unpaid, non-cancelled invoices)
$previousDue = Invoice::where('cp_id', 29)
    ->where('status', '!=', 'cancelled')
    ->where('status', '!=', 'paid')
    ->where('next_due', '>', 0)
    ->sum('next_due');

$novInvoice = Invoice::create([
    'invoice_number' => 'INV-25-11-0001',
    'cp_id' => 29,
    'issue_date' => '2025-11-09',
    'previous_due' => $previousDue,
    'subtotal' => 2000,
    'total_amount' => 2000 + $previousDue,
    'received_amount' => 0,
    'next_due' => 2000 + $previousDue,
    'status' => 'unpaid',
    'notes' => 'Billing period: Aug-Nov 2025',
    'created_by' => 1
]);
echo "✓ Created November invoice: " . number_format(2000 + $previousDue, 0) . " BDT\n";
echo "  Subtotal: 2,000 BDT (Period: Aug-Nov)\n";
echo "  Previous Due: " . number_format($previousDue, 0) . " BDT\n\n";

echo "=== DONE ===\n";
echo "Expected monthly summary:\n";
echo "  May: 2,000 BDT\n";
echo "  August: 2,000 BDT\n";
echo "  September: 2,000 BDT\n";
echo "  October: 2,000 BDT\n";
echo "  November: 4,000 BDT\n";
echo "  December: 4,000 BDT\n";
