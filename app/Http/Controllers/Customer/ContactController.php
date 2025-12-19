<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Mail\ContactFormSubmitted;
use App\Models\SupportTicket;
use App\Models\Customer;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    /**
     * Display the contact page
     */
    public function index()
    {
        $user = Auth::user();
        $customer = Customer::where('user_id', $user->id)->firstOrFail();
        
        $faqs = [
            [
                'question' => 'What is your response time for support requests?',
                'answer' => 'We typically respond within 2-4 hours during business hours (9 AM - 6 PM, Sunday-Thursday). For urgent issues marked as priority, we guarantee a response within 1 hour.',
                'link' => route('customer.support.faq')
            ],
            [
                'question' => 'Do you offer 24/7 emergency support?',
                'answer' => 'Yes, we provide 24/7 emergency support for critical issues affecting production environments. Emergency support is available via phone at +880 XXXX-XXXXXX.',
                'link' => null
            ],
            [
                'question' => 'How can I track my support ticket status?',
                'answer' => 'After submitting your request, you\'ll receive a ticket number. You can track your ticket status through the customer portal under the "Support" section using this ticket number.',
                'link' => route('customer.support.index')
            ],
            [
                'question' => 'Can I upgrade or downgrade my service plan?',
                'answer' => 'Yes, you can modify your service plan anytime. Visit the "My Products" section in your dashboard to view available upgrade/downgrade options, or contact our sales team for assistance.',
                'link' => route('customer.customer-products.index')
            ],
        ];
        
        $departments = [
            [
                'name' => 'Technical Support',
                'desc' => 'System issues, connectivity problems',
                'email' => 'tech-support@nanosoft.com',
                'icon' => 'fa-headset',
                'bgClass' => 'bg-blue-soft',
                'textClass' => 'text-blue',
                'hours' => '24/7'
            ],
            [
                'name' => 'Sales & Billing',
                'desc' => 'Pricing, plans, invoices',
                'email' => 'billing@nanosoft.com',
                'icon' => 'fa-file-invoice-dollar',
                'bgClass' => 'bg-green-soft',
                'textClass' => 'text-green',
                'hours' => '9 AM - 6 PM'
            ],
            [
                'name' => 'General Inquiry',
                'desc' => 'Product information, partnerships',
                'email' => 'info@nanosoft.com',
                'icon' => 'fa-info-circle',
                'bgClass' => 'bg-orange-soft',
                'textClass' => 'text-orange',
                'hours' => '9 AM - 6 PM'
            ],
            [
                'name' => 'Feedback',
                'desc' => 'Suggestions, complaints, reviews',
                'email' => 'feedback@nanosoft.com',
                'icon' => 'fa-comment-dots',
                'bgClass' => 'bg-purple-soft',
                'textClass' => 'text-purple',
                'hours' => '10 AM - 4 PM'
            ],
        ];

        // Get recent tickets for this customer
        $recentTickets = $customer->supportTickets()
            ->latest()
            ->take(5)
            ->get();

        return view('customer.contact.index', compact(
            'faqs',
            'departments',
            'recentTickets',
            'customer'
        ));
    }

    /**
     * Handle contact form submission
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'department' => 'required|string|in:technical,sales,billing,partnership,feedback,training,other',
            'message' => 'required|string|min:10|max:5000',
            'priority' => 'nullable|boolean',
            'newsletter' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $customer = Customer::where('user_id', $user->id)->firstOrFail();
        
        // Create support ticket from contact form
        $ticket = SupportTicket::create([
            'customer_id' => $customer->c_id,
            'ticket_number' => 'TKT-' . strtoupper(Str::random(8)),
            'subject' => $validated['subject'],
            'description' => $validated['message'],
            'category' => $this->mapDepartmentToCategory($validated['department']),
            'priority' => $validated['priority'] ? 'high' : 'normal',
            'status' => 'open',
            'department' => $validated['department'],
            'contact_email' => $validated['email'],
            'contact_name' => $validated['name'],
        ]);

        // Send email notification
        try {
            Mail::to('support@nanosoft.com')
                ->cc([$validated['email'], $customer->user->email])
                ->send(new ContactFormSubmitted($ticket, $validated));
        } catch (\Exception $e) {
            Log::error('Failed to send contact email: ' . $e->getMessage());
        }

        // Handle newsletter subscription if requested
        if ($request->has('newsletter') && $request->boolean('newsletter')) {
            $this->subscribeToNewsletter($validated['email'], $validated['name']);
        }

        // Store success message in session
        $request->session()->flash('contact_success', true);
        $request->session()->flash('ticket_number', $ticket->ticket_number);

        return redirect()->route('customer.contact.index')
            ->with('success', 'Your message has been sent successfully! We\'ve created support ticket ' . $ticket->ticket_number . ' for tracking.');
    }

    /**
     * Map department to support ticket category
     */
    private function mapDepartmentToCategory($department)
    {
        $mapping = [
            'technical' => 'technical',
            'sales' => 'product',
            'billing' => 'billing',
            'partnership' => 'other',
            'feedback' => 'product',
            'training' => 'technical',
            'other' => 'other',
        ];

        return $mapping[$department] ?? 'other';
    }

    /**
     * Subscribe to newsletter
     */
    private function subscribeToNewsletter($email, $name)
    {
        // Implementation depends on your newsletter service
        // Example for Mailchimp, SendGrid, etc.
        
        // For now, just log it
        Log::info('Newsletter subscription requested', [
            'email' => $email,
            'name' => $name
        ]);
        
        // You can integrate with:
        // - Mailchimp API
        // - SendGrid Contacts API
        // - Custom database table
        // - Newsletter service provider
    }

    /**
     * Download company brochure
     */
    public function downloadBrochure()
    {
        $filePath = storage_path('app/public/brochures/nanosoft-brochure.pdf');
        
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'Brochure not available at the moment.');
        }

        return response()->download($filePath, 'Nanosoft-Company-Brochure.pdf');
    }

    /**
     * Schedule appointment form
     */
    public function scheduleAppointment(Request $request)
    {
        $validated = $request->validate([
            'appointment_type' => 'required|in:demo,training,consultation,support',
            'preferred_date' => 'required|date|after:today',
            'preferred_time' => 'required|date_format:H:i',
            'timezone' => 'required|string',
            'agenda' => 'required|string|min:10|max:1000',
            'participants' => 'nullable|integer|min:1|max:20',
        ]);

        $user = Auth::user();
        $customer = Customer::where('user_id', $user->id)->firstOrFail();

        // Create appointment request
        $appointment = [
            'customer_id' => $customer->c_id,
            'customer_name' => $customer->user->name,
            'customer_email' => $customer->user->email,
            // ... rest of the appointment data
        ];

        // In a real implementation, you would save this to a database
        // and send notifications to both customer and staff
        
        return response()->json([
            'success' => true,
            'message' => 'Appointment scheduled successfully! Our team will contact you to confirm the details.'
        ]);
    }

    /**
     * Get available time slots for appointments
     */
    public function getTimeSlots(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $date = \Carbon\Carbon::parse($date);
        
        // Business hours
        $startHour = 9;  // 9 AM
        $endHour = 18;   // 6 PM
        $interval = 60;  // 60 minutes per slot
        
        $slots = [];
        for ($hour = $startHour; $hour < $endHour; $hour++) {
            $slots[] = [
                'time' => sprintf('%02d:00', $hour),
                'display' => sprintf('%d:00 %s', $hour > 12 ? $hour - 12 : $hour, $hour >= 12 ? 'PM' : 'AM')
            ];
        }

        return response()->json([
            'slots' => $slots,
            'date' => $date->format('F j, Y'),
            'timezone' => 'Asia/Dhaka'
        ]);
    }

    /**
     * Emergency contact (for urgent issues)
     */
    public function emergencyContact(Request $request)
    {
        $validated = $request->validate([
            'issue' => 'required|string|min:10|max:1000',
            'urgency' => 'required|in:critical,high,medium',
            'contact_phone' => 'required|string|max:20',
            'best_time_to_call' => 'required|string',
        ]);

        $user = Auth::user();
        $customer = Customer::where('user_id', $user->id)->firstOrFail();

        // Create emergency ticket
        $ticket = SupportTicket::create([
            'customer_id' => $customer->c_id,
            'ticket_number' => 'EMG-' . strtoupper(Str::random(6)),
            'subject' => 'EMERGENCY: ' . substr($validated['issue'], 0, 100),
            'description' => $validated['issue'],
            'category' => 'technical',
            'priority' => 'urgent',
            'status' => 'open',
            'department' => 'technical',
            'contact_email' => $customer->user->email,
            'contact_name' => $customer->user->name,
            'contact_phone' => $validated['contact_phone'],
            'metadata' => [
                'emergency' => true,
                'urgency' => $validated['urgency'],
                'best_time_to_call' => $validated['best_time_to_call']
            ]
        ]);

        // Send immediate notification to emergency support team
        try {
            // In a real implementation, you would send SMS or make an API call
            // to alert the emergency support team
            Log::alert('EMERGENCY SUPPORT REQUEST', [
                'ticket_number' => $ticket->ticket_number,
                'customer' => $customer->name,
                'issue' => $validated['issue'],
                'urgency' => $validated['urgency']
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send emergency alert: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Emergency request received! Ticket #' . $ticket->ticket_number . ' has been created. Our emergency support team will contact you immediately.',
            'ticket_number' => $ticket->ticket_number
        ]);
    }

    /**
     * Get contact information
     */
    public function getContactInfo()
    {
        return response()->json([
            'phone' => '+880 XXX XXX XXX',
            'email' => 'support@nanosoft.com',
            'address' => 'Level 5, Dhaka 1212, Bangladesh',
            'business_hours' => 'Sun-Thu: 9AM-6PM, Fri-Sat: 10AM-4PM'
        ]);
    }
}