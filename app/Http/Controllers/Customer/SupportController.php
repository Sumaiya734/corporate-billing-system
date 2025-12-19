<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    /**
     * Display the support center index page.
     */
    public function index()
    {
        $customer = Customer::where('user_id', Auth::id())->firstOrFail();
        
        // Get tickets for this customer
        $tickets = SupportTicket::where('customer_id', $customer->c_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Get ticket counts by status
        $openTickets = SupportTicket::where('customer_id', $customer->c_id)
            ->where('status', 'open')
            ->count();
            
        $resolvedTickets = SupportTicket::where('customer_id', $customer->c_id)
            ->where('status', 'resolved')
            ->count();
            
        $totalTickets = SupportTicket::where('customer_id', $customer->c_id)->count();
        
        // FAQ categories for software product billing
        $faqCategories = [
            'billing' => [
                'title' => 'Billing & Invoices',
                'icon' => 'fa-file-invoice-dollar',
                'color' => 'primary'
            ],
            'products' => [
                'title' => 'Products & Licenses',
                'icon' => 'fa-box',
                'color' => 'success'
            ],
            'technical' => [
                'title' => 'Technical Issues',
                'icon' => 'fa-cogs',
                'color' => 'warning'
            ],
            'account' => [
                'title' => 'Account & Access',
                'icon' => 'fa-user-lock',
                'color' => 'info'
            ],
        ];
        
        return view('customer.support.index', compact(
            'customer',
            'tickets',
            'openTickets',
            'resolvedTickets',
            'totalTickets',
            'faqCategories'
        ));
    }

    /**
     * Show the form for creating a new support ticket.
     */
    public function create()
    {
        $customer = Customer::where('user_id', Auth::id())->firstOrFail();
        
        // Categories specific to software product billing
        $categories = [
            'billing' => 'Billing & Invoice Issues',
            'license' => 'License Activation/Renewal',
            'product' => 'Product Features/Usage',
            'technical' => 'Technical Problems',
            'account' => 'Account Access Issues',
            'integration' => 'Integration/API Issues',
            'other' => 'Other Questions'
        ];
        
        $priorities = [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent'
        ];
        
        // Get customer's products for reference
        $customerProducts = $customer->customerproducts()
            ->with('product')
            ->where('is_active', 1)
            ->get();
        
        return view('customer.support.create', compact('customer', 'categories', 'priorities', 'customerProducts'));
    }

    /**
     * Store a newly created support ticket.
     */
    public function store(Request $request)
    {
        $customer = Customer::where('user_id', Auth::id())->firstOrFail();
        
        $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'required|in:billing,license,product,technical,account,integration,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'product_id' => 'nullable|exists:customer_to_products,cp_id',
            'description' => 'required|string|min:10',
        ]);
        
        $ticket = SupportTicket::create([
            'customer_id' => $customer->c_id,
            'ticket_number' => 'TICKET-' . strtoupper(uniqid()),
            'subject' => $request->subject,
            'category' => $request->category,
            'priority' => $request->priority,
            'product_id' => $request->product_id,
            'description' => $request->description,
            'status' => 'open',
        ]);
        
        return redirect()->route('customer.support.show', $ticket->id)
            ->with('success', 'Support ticket created successfully! Ticket ID: ' . $ticket->ticket_number);
    }

    /**
     * Display the specified support ticket.
     */
    public function show($id)
    {
        $customer = Customer::where('user_id', Auth::id())->firstOrFail();
        
        $ticket = SupportTicket::where('id', $id)
            ->where('customer_id', $customer->c_id)
            ->with('product')
            ->firstOrFail();
        
        // Simulated responses (in real app, you'd have a TicketResponse model)
        $responses = [
            [
                'id' => 1,
                'user_type' => 'customer',
                'user_name' => $customer->name,
                'message' => $ticket->description,
                'created_at' => $ticket->created_at,
                'is_staff' => false
            ],
            [
                'id' => 2,
                'user_type' => 'staff',
                'user_name' => 'Support Team',
                'message' => 'Thank you for contacting our software billing support. We have received your ticket and will get back to you within 24 hours.',
                'created_at' => $ticket->created_at->addHours(2),
                'is_staff' => true
            ],
        ];
        
        if ($ticket->status === 'resolved') {
            $responses[] = [
                'id' => 3,
                'user_type' => 'staff',
                'user_name' => 'Support Team',
                'message' => 'Your software billing issue has been resolved. If you have any further questions, please don\'t hesitate to contact us.',
                'created_at' => $ticket->updated_at,
                'is_staff' => true
            ];
        }
        
        return view('customer.support.show', compact('customer', 'ticket', 'responses'));
    }

    /**
     * Display FAQ page for software product billing.
     */
    public function faq()
    {
        $customer = Customer::where('user_id', Auth::id())->firstOrFail();
        
        $faqs = [
            'billing' => [
                [
                    'question' => 'How are software product licenses billed?',
                    'answer' => 'Software licenses are billed on a monthly/annual subscription basis. Invoices are generated automatically based on your subscription plan and number of licenses.'
                ],
                [
                    'question' => 'Can I change my billing cycle?',
                    'answer' => 'Yes, you can switch between monthly and annual billing from your account settings. Annual billing typically offers a discount compared to monthly payments.'
                ],
                [
                    'question' => 'What payment methods are accepted for software billing?',
                    'answer' => 'We accept credit/debit cards (Visa, MasterCard, American Express), bank transfers, and PayPal for software product payments.'
                ],
                [
                    'question' => 'How do I download invoices for tax purposes?',
                    'answer' => 'All invoices are available in your account dashboard under "Invoices". You can download PDF versions for accounting and tax purposes.'
                ],
            ],
            'products' => [
                [
                    'question' => 'How do I add more licenses to my software product?',
                    'answer' => 'Navigate to "My Products" and click "Add Licenses" on the product you want to expand. Additional licenses are prorated based on your billing cycle.'
                ],
                [
                    'question' => 'Can I downgrade my software plan?',
                    'answer' => 'Yes, you can downgrade your plan at any time. The changes will take effect at your next billing cycle. Contact support for assistance with plan changes.'
                ],
                [
                    'question' => 'What happens when my software license expires?',
                    'answer' => 'You will receive renewal notifications before expiration. If not renewed, access to the software will be suspended until payment is made.'
                ],
            ],
            'technical' => [
                [
                    'question' => 'How do I activate my software license?',
                    'answer' => 'After purchase, you will receive a license key via email. Enter this key in the software activation screen or through your account dashboard.'
                ],
                [
                    'question' => 'What if my software license key is not working?',
                    'answer' => 'Verify you are entering the correct key. If issues persist, contact support with your license key and purchase details for assistance.'
                ],
                [
                    'question' => 'How do I install software updates?',
                    'answer' => 'Active license holders receive automatic update notifications. Updates can be installed through the software\'s built-in update system or downloaded from your account.'
                ],
            ],
            'account' => [
                [
                    'question' => 'How do I reset my account password?',
                    'answer' => 'Click "Forgot Password" on the login page. You will receive an email with instructions to reset your password.'
                ],
                [
                    'question' => 'Can multiple users access my software billing account?',
                    'answer' => 'Yes, you can create sub-accounts for team members with different permission levels through the "Account Settings" section.'
                ],
                [
                    'question' => 'How do I update company billing information?',
                    'answer' => 'Update your company details, tax information, and billing address in the "Company Profile" section of your account settings.'
                ],
            ],
        ];
        
        return view('customer.support.faq', compact('customer', 'faqs'));
    }
}