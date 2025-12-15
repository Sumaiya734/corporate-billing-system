<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Get payment history for an invoice
     */
    public function getInvoicePayments($invoiceId)
    {
        try {
            $invoice = Invoice::with(['customerProduct.customer', 'payments'])
                ->findOrFail($invoiceId);

            $customer = $invoice->customerProduct ? $invoice->customerProduct->customer : null;
            
            $payments = Payment::with('collector')
                ->where('invoice_id', $invoiceId)
                ->orderBy('payment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($payment) {
                    // Format the payment data to match what the frontend expects
                    return [
                        'id' => $payment->payment_id,
                        'payment_id' => $payment->payment_id,
                        'invoice_id' => $payment->invoice_id,
                        'amount' => $payment->amount,
                        'payment_method' => $payment->payment_method,
                        'payment_date' => $payment->payment_date,
                        'note' => $payment->notes,
                        'notes' => $payment->notes,
                        'collected_by' => $payment->collected_by,
                        'created_at' => $payment->created_at,
                        'updated_at' => $payment->updated_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $customer ? $customer->name : 'Unknown',
                'total_amount' => $invoice->total_amount,
                'received_amount' => $invoice->received_amount ?? 0,
                'next_due' => $invoice->next_due ?? 0,
                'payments' => $payments
            ]);

        } catch (\Exception $e) {
            Log::error('PaymentController@getInvoicePayments error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load payment history'
            ], 500);
        }

    }

    public function store(Request $request)    {
        $data = $request->validate([
            'invoice_id' => ['required', 'integer', 'exists:invoices,invoice_id'],
            'c_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,bank_transfer,mobile_banking,card,online'],
            'payment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            return DB::transaction(function () use ($data) {
                $invoice = Invoice::lockForUpdate()->where('invoice_id', $data['invoice_id'])->firstOrFail();

                // Check if the invoice's month is closed
                $invoiceMonth = \Carbon\Carbon::parse($invoice->issue_date)->format('Y-m');
                $isMonthClosed = \App\Models\BillingPeriod::isMonthClosed($invoiceMonth);
                
                if ($isMonthClosed) {
                    abort(422, 'Cannot add payment. This billing month has been closed. All dues have been carried forward to the next month.');
                }

                // Ensure the invoice belongs to the same customer
                if ((int)$invoice->c_id !== (int)$data['c_id']) {
                    abort(422, 'Invoice/customer mismatch.');
                }

                $total = (float) $invoice->total_amount;
                $received = (float) $invoice->received_amount;
                $due = max(0.0, $total - $received);
                $amount = (float) $data['amount'];

                if ($amount > $due + 0.0001) {
                    abort(422, 'Payment exceeds due amount.');
                }

                // Create payment
                $payment = Payment::create([
                    'invoice_id' => $invoice->invoice_id,
                    'c_id' => $data['c_id'],
                    'amount' => $amount,
                    'payment_method' => $data['payment_method'],
                    'payment_date' => $data['payment_date'],
                    'notes' => $data['notes'] ?? null,
                    'collected_by' => Auth::id(),
                    'status' => 'completed',
                ]);
                // Update invoice aggregates
                $invoice->received_amount = $received + $amount;
                $invoice->next_due = max(0.0, $total - $invoice->received_amount);

                if ($invoice->next_due <= 0.0001) {
                    $invoice->status = 'paid';
                } elseif ($invoice->received_amount > 0.0001) {
                    $invoice->status = 'partial';
                } else {
                    $invoice->status = 'unpaid';
                }

                $invoice->save();

                return redirect()
                    ->back()
                    ->with('success', 'Payment recorded successfully.');
            });
        } catch (\Throwable $e) {
            Log::error('PaymentController@store error: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', $e->getMessage() ?: 'Failed to record payment.');
        }
    }
    public function recordPayment(Request $request, $invoiceId)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:0', // Accept any amount >= 0
            // Accept the same methods used in the UI and Payment model
            'payment_method' => 'required|in:cash,bank_transfer,mobile_banking,card,online',
            'payment_date'   => 'required|date',
            // UI uses 'notes' field name; transaction_id isn't used in current UI
            'notes'          => 'nullable|string|max:1000',
        ]);

        $invoice = Invoice::findOrFail($invoiceId);

        // Accept any amount - no rounding
        $amount = floatval($request->amount);
        $due = floatval($invoice->next_due ?? $invoice->total_amount);

        // Validate: amount must be between 0 and due amount
        if ($amount < 0) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount cannot be negative'
            ], 422);
        }

        if ($amount > $due) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount (৳' . number_format($amount, 0) . ') cannot exceed due amount (৳' . number_format($due, 0) . ')'
            ], 422);
        }

        DB::transaction(function () use ($invoice, $request, $amount) {
            // 1. Record in payments table
            Payment::create([
                'invoice_id'     => $invoice->invoice_id,
                'c_id'           => $invoice->c_id,
                'amount'         => $amount,
                'payment_method' => $request->payment_method,
                'payment_date'   => $request->payment_date,
                'notes'          => $request->notes ?? null,
                'collected_by'   => Auth::id(),
                'status'         => 'completed',
            ]);
            // 2. Update invoice
            $newReceived = ($invoice->received_amount ?? 0) + $amount;
            $newDue = $invoice->total_amount - $newReceived;

            // Handle floating point precision - consider amounts less than 0 as zero
            if ($newDue < 0) {
                $newDue = 0;
                $status = 'paid';
            } else {
                $status = $newReceived > 0 ? 'partial' : 'unpaid';
            }

            $invoice->update([
                'received_amount' => $newReceived,
                'next_due'        => $newDue,
                'status'          => $status,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Payment of ৳' . number_format($amount, 0) . ' recorded successfully!'
        ]);
    }
}