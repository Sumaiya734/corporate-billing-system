<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\ServicePackage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use PDF;

class BillingController extends Controller
{

        /**
     * Display billing invoices summary page
     */
    public function billingInvoices()
    {
        $billingData = [
            [
                'month' => 'January 2024',
                'total_customers' => 50,
                'total_amount' => 45000,
                'received' => 45000,
                'due' => 0,
                'status' => 'All Paid'
            ],
            [
                'month' => 'February 2024',
                'total_customers' => 55,
                'total_amount' => 49500,
                'received' => 46000,
                'due' => 3500,
                'status' => 'Pending'
            ],
            [
                'month' => 'March 2024',
                'total_customers' => 58,
                'total_amount' => 52000,
                'received' => 48000,
                'due' => 4000,
                'status' => 'Pending'
            ],
            [
                'month' => 'April 2024',
                'total_customers' => 60,
                'total_amount' => 55000,
                'received' => 50000,
                'due' => 5000,
                'status' => 'Overdue'
            ],
        ];

        return view('admin.billing.billing-invoices', compact('billingData'));
    }

    /**
     * Display monthly bills page
     */
    public function monthlyBills(Request $request)
{
    $month = $request->query('month', 'January 2024');

    
        $stats = [
            'total_revenue' => 45250,
            'pending_bills' => 12,
            'paid_bills' => 38,
            'avg_bill_amount' => 905
        ];

        $bills = [
            [
                'id' => 1,
                'invoice_id' => 'INV-2024-001',
                'customer' => [
                    'name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                    'phone' => '+8801712345678',
                    'address' => 'Gulshan, Dhaka'
                ],
                'services' => [
                    ['name' => 'Basic Speed', 'price' => 500]
                ],
                'due_date' => '2024-02-05',
                'status' => 'paid',
                'amount' => 535
            ],
        ];

        return view('admin.billing.monthly-bills', compact('stats', 'bills'));
    }

    /**
     * Display all invoices page
     */
    public function allInvoices()
    {
        $stats = [
            'total_invoices' => 215,
            'pending_invoices' => 28,
            'paid_invoices' => 172,
            'total_revenue' => 185250
        ];

        $invoices = [
            [
                'id' => 1,
                'invoice_id' => 'INV-2024-001',
                'customer' => [
                    'name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                    'phone' => '+8801712345678',
                    'address' => 'Gulshan, Dhaka'
                ],
                'services' => [
                    ['name' => 'Basic Speed', 'price' => 500]
                ],
                'issue_date' => '2024-01-01',
                'due_date' => '2024-01-05',
                'status' => 'paid',
                'amount' => 535
            ],
            [
                'id' => 2,
                'invoice_id' => 'INV-2024-002',
                'customer' => [
                    'name' => 'Alice Smith',
                    'email' => 'alice.smith@example.com',
                    'phone' => '+8801812345679',
                    'address' => 'Uttara, Dhaka'
                ],
                'services' => [
                    ['name' => 'Fast Speed', 'price' => 800],
                    ['name' => 'Gaming Boost', 'price' => 200]
                ],
                'issue_date' => '2024-01-01',
                'due_date' => '2024-01-05',
                'status' => 'pending',
                'amount' => 1070
            ],
            [
                'id' => 3,
                'invoice_id' => 'INV-2023-125',
                'customer' => [
                    'name' => 'Bob Johnson',
                    'email' => 'bob.johnson@example.com',
                    'phone' => '+8801912345680',
                    'address' => 'Banani, Dhaka'
                ],
                'services' => [
                    ['name' => 'Super Speed', 'price' => 1200],
                    ['name' => 'Streaming Plus', 'price' => 150]
                ],
                'issue_date' => '2023-12-01',
                'due_date' => '2023-12-25',
                'status' => 'overdue',
                'amount' => 1445
            ],
            [
                'id' => 4,
                'invoice_id' => 'INV-2023-098',
                'customer' => [
                    'name' => 'Carol White',
                    'email' => 'carol.white@example.com',
                    'phone' => '+8801612345681',
                    'address' => 'Dhanmondi, Dhaka'
                ],
                'services' => [
                    ['name' => 'Fast Speed', 'price' => 800]
                ],
                'issue_date' => '2023-11-01',
                'due_date' => '2023-11-05',
                'status' => 'paid',
                'amount' => 856
            ],
            [
                'id' => 5,
                'invoice_id' => 'INV-2023-076',
                'customer' => [
                    'name' => 'David Green',
                    'email' => 'david.green@example.com',
                    'phone' => '+8801512345682',
                    'address' => 'Mirpur, Dhaka'
                ],
                'services' => [
                    ['name' => 'Super Speed', 'price' => 1200],
                    ['name' => 'Family Pack', 'price' => 300]
                ],
                'issue_date' => '2023-10-01',
                'due_date' => '2023-10-05',
                'status' => 'paid',
                'amount' => 1605
            ]
        ];

        return view('admin.billing.all-invoices', compact('stats', 'invoices'));
    }

    /**
     * Generate bill for a customer - SHOWS THE GENERATE BILL PAGE
     */
    public function generateBill($id)
    {
        try {
            $customer = Customer::with('user')->find($id);
            
            if (!$customer) {
                $customer = $this->createDemoCustomer($id);
            }

            // Return the generate-bill view
            return view('admin.billing.generate-bill', compact('customer'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading generate bill page: ' . $e->getMessage());
        }
    }

    /**
     * View bill details - SHOWS THE VIEW BILL PAGE
     */
    public function viewBill($id)
    {
        try {
            $customer = Customer::with('user')->find($id);
            
            if (!$customer) {
                $customer = $this->createDemoCustomer($id);
            }

            $invoice = [
                'id' => $id,
                'invoice_id' => 'INV-2024-00' . $id,
                'customer' => [
                    'name' => $customer->user->name ?? 'Demo Customer',
                    'email' => $customer->user->email ?? 'demo@example.com',
                    'phone' => $customer->phone ?? '+8801712345678',
                    'address' => $customer->address ?? 'Demo Address, Dhaka'
                ],
                'services' => [
                    ['name' => 'Basic Speed', 'price' => 500]
                ],
                'issue_date' => '2024-01-01',
                'due_date' => '2024-01-05',
                'status' => 'paid',
                'amount' => 535,
                'breakdown' => [
                    'service_charge' => 50,
                    'regular_package' => 500,
                    'special_packages' => 0,
                    'vat' => 38.5,
                    'discount' => 0,
                    'total' => 535
                ]
            ];

            return view('admin.billing.view-bill', compact('invoice', 'customer'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading bill: ' . $e->getMessage());
        }
    }

    /**
     * View invoice details - SHOWS THE VIEW INVOICE PAGE
     */
    public function viewInvoice($id)
    {
        try {
            $customer = Customer::with('user')->find($id);
            
            if (!$customer) {
                $customer = $this->createDemoCustomer($id);
            }

            $invoice = [
                'id' => $id,
                'invoice_id' => 'INV-2024-00' . $id,
                'customer' => [
                    'name' => $customer->user->name ?? 'Demo Customer',
                    'email' => $customer->user->email ?? 'demo@example.com',
                    'phone' => $customer->phone ?? '+8801712345678',
                    'address' => $customer->address ?? 'Demo Address, Dhaka'
                ],
                'services' => [
                    ['name' => 'Basic Speed', 'price' => 500]
                ],
                'issue_date' => '2024-01-01',
                'due_date' => '2024-01-05',
                'status' => 'paid',
                'amount' => 535,
                'breakdown' => [
                    'service_charge' => 50,
                    'regular_package' => 500,
                    'special_packages' => 0,
                    'vat' => 38.5,
                    'discount' => 0,
                    'total' => 535
                ]
            ];

            return view('admin.billing.view-invoice', compact('invoice', 'customer'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading invoice: ' . $e->getMessage());
        }
    }

    /**
     * Process bill generation (form submission)
     */
    public function processBillGeneration(Request $request, $id)
    {
        $request->validate([
            'billing_month' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
        ]);

        try {
            $customer = Customer::with('user')->find($id);
            
            if (!$customer) {
                return redirect()->back()->with('error', 'Customer not found.');
            }

            // Here you would save the bill to database
            // For now, we'll just show a success message
            
            return redirect()->route('admin.billing.monthly-bills')
                ->with('success', 'Bill generated successfully for ' . ($customer->user->name ?? 'Customer'));
                
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error generating bill: ' . $e->getMessage());
        }
    }

    /**
     * Create demo customer for testing
     */
    private function createDemoCustomer($id)
    {
        $demoCustomers = [
            1 => ['name' => 'John Doe', 'email' => 'john.doe@example.com', 'phone' => '+8801712345678', 'address' => 'Gulshan, Dhaka'],
            2 => ['name' => 'Alice Smith', 'email' => 'alice.smith@example.com', 'phone' => '+8801812345679', 'address' => 'Uttara, Dhaka'],
            3 => ['name' => 'Bob Johnson', 'email' => 'bob.johnson@example.com', 'phone' => '+8801912345680', 'address' => 'Banani, Dhaka'],
            4 => ['name' => 'Carol White', 'email' => 'carol.white@example.com', 'phone' => '+8801612345681', 'address' => 'Dhanmondi, Dhaka'],
            5 => ['name' => 'David Green', 'email' => 'david.green@example.com', 'phone' => '+8801512345682', 'address' => 'Mirpur, Dhaka'],
        ];

        $demoData = $demoCustomers[$id] ?? ['name' => 'Demo Customer', 'email' => 'demo@example.com', 'phone' => '+8801700000000', 'address' => 'Demo Address, Dhaka'];

        $customer = new \stdClass();
        $customer->id = $id;
        $customer->phone = $demoData['phone'];
        $customer->address = $demoData['address'];
        
        $user = new \stdClass();
        $user->name = $demoData['name'];
        $user->email = $demoData['email'];
        
        $customer->user = $user;

        return $customer;
    }

    /**
     * Create new invoice
     */
    public function createInvoice(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,paid,overdue',
            'regular_package' => 'required|numeric',
            'special_packages' => 'array',
            'discount' => 'numeric|min:0|max:100'
        ]);

        try {
            $serviceCharge = 50;
            $regularPackageAmount = $request->regular_package;
            $specialPackagesAmount = array_sum($request->special_packages ?? []);
            $vatRate = 0.07;
            $discountRate = $request->discount / 100;
            
            $subtotal = $serviceCharge + $regularPackageAmount + $specialPackagesAmount;
            $vatAmount = $subtotal * $vatRate;
            $discountAmount = $subtotal * $discountRate;
            $totalAmount = $subtotal + $vatAmount - $discountAmount;

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'invoice_id' => 'INV-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update invoice
     */
    public function updateInvoice(Request $request, $invoiceId)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,overdue',
            'due_date' => 'required|date',
        ]);

        try {
            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete invoice
     */
    public function deleteInvoice($invoiceId)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export invoices
     */
    public function exportInvoices(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,pdf',
            'type' => 'required|in:all,paid,pending,overdue'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Export functionality will be implemented soon'
        ]);
    }

    /**
     * Get invoice data for editing (AJAX)
     */
    public function getInvoiceData($invoiceId)
    {
        try {
            $invoice = [
                'id' => $invoiceId,
                'customer_id' => 1,
                'issue_date' => '2024-01-01',
                'due_date' => '2024-01-05',
                'status' => 'paid',
                'regular_package_amount' => 800,
                'special_packages' => [200],
                'discount' => 0,
                'notes' => 'Monthly internet bill'
            ];

            return response()->json([
                'success' => true,
                'invoice' => $invoice
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $year = date('Y');
        $lastInvoice = Invoice::whereYear('created_at', $year)->latest()->first();
        
        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, -3));
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
        
        return $prefix . '-' . $year . '-' . $newNumber;
    }
   public function profile($id)
    {
         echo "Debug: Profile method called with ID: " . $id . "<br>";
        // Static customer data for frontend demonstration
        $customers = [
            1 => [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+8801712345678',
                'address' => 'Gulshan, Dhaka',
                'connection_address' => 'House 12, Road 5, Gulshan 1',
                'join_date' => '2023-01-15',
                'status' => 'Active',
                'id_type' => 'nid',
                'id_number' => '1990123456789',
                'package' => 'Basic Speed',
                'monthly_bill' => '৳550',
                'billing_history' => [
                    ['month' => 'January 2024', 'amount' => '৳550', 'status' => 'Paid', 'due_date' => '2024-01-05'],
                    ['month' => 'December 2023', 'amount' => '৳550', 'status' => 'Paid', 'due_date' => '2023-12-05'],
                    ['month' => 'November 2023', 'amount' => '৳550', 'status' => 'Paid', 'due_date' => '2023-11-05'],
                ]
            ],
            2 => [
                'id' => 2,
                'name' => 'Alice Smith',
                'email' => 'alice.smith@example.com',
                'phone' => '+8801812345679',
                'address' => 'Uttara, Dhaka',
                'connection_address' => 'House 25, Sector 7, Uttara',
                'join_date' => '2023-02-20',
                'status' => 'Active',
                'id_type' => 'passport',
                'id_number' => 'AB1234567',
                'package' => 'Fast Speed + Gaming Boost',
                'monthly_bill' => '৳1,050',
                'billing_history' => [
                    ['month' => 'January 2024', 'amount' => '৳1,050', 'status' => 'Pending', 'due_date' => '2024-01-05'],
                    ['month' => 'December 2023', 'amount' => '৳1,050', 'status' => 'Paid', 'due_date' => '2023-12-05'],
                ]
            ],
            3 => [
                'id' => 3,
                'name' => 'Bob Johnson',
                'email' => 'bob.johnson@example.com',
                'phone' => '+8801912345680',
                'address' => 'Banani, Dhaka',
                'connection_address' => 'House 8, Road 11, Banani',
                'join_date' => '2023-03-10',
                'status' => 'Active',
                'id_type' => 'driving_license',
                'id_number' => 'DL789456123',
                'package' => 'Super Speed + Streaming Plus',
                'monthly_bill' => '৳1,400',
                'billing_history' => [
                    ['month' => 'January 2024', 'amount' => '৳1,900', 'status' => 'Overdue', 'due_date' => '2024-01-05'],
                    ['month' => 'December 2023', 'amount' => '৳1,400', 'status' => 'Paid', 'due_date' => '2023-12-05'],
                ]
            ],
            4 => [
                'id' => 4,
                'name' => 'Carol White',
                'email' => 'carol.white@example.com',
                'phone' => '+8801612345681',
                'address' => 'Dhanmondi, Dhaka',
                'connection_address' => 'House 15, Road 2, Dhanmondi',
                'join_date' => '2023-04-05',
                'status' => 'Active',
                'id_type' => 'nid',
                'id_number' => '1995123456789',
                'package' => 'Fast Speed',
                'monthly_bill' => '৳850',
                'billing_history' => [
                    ['month' => 'January 2024', 'amount' => '৳850', 'status' => 'Paid', 'due_date' => '2024-01-05'],
                    ['month' => 'December 2023', 'amount' => '৳850', 'status' => 'Paid', 'due_date' => '2023-12-05'],
                ]
            ],
            5 => [
                'id' => 5,
                'name' => 'David Green',
                'email' => 'david.green@example.com',
                'phone' => '+8801512345682',
                'address' => 'Mirpur, Dhaka',
                'connection_address' => 'House 30, Section 2, Mirpur',
                'join_date' => '2023-05-12',
                'status' => 'Active',
                'id_type' => 'nid',
                'id_number' => '1992123456789',
                'package' => 'Super Speed + Family Pack',
                'monthly_bill' => '৳1,550',
                'billing_history' => [
                    ['month' => 'January 2024', 'amount' => '৳1,550', 'status' => 'Paid', 'due_date' => '2024-01-05'],
                    ['month' => 'December 2023', 'amount' => '৳1,550', 'status' => 'Paid', 'due_date' => '2023-12-05'],
                ]
            ]
        ];

        // Check if customer exists in our static data
        if (!isset($customers[$id])) {
            abort(404, 'Customer not found');
        }

        $customer = $customers[$id];

        // Ensure billing_history is always an array
        if (!isset($customer['billing_history']) || !is_array($customer['billing_history'])) {
            $customer['billing_history'] = [];
        }

        return view('admin.customers.profile', compact('customer'));
    }


}