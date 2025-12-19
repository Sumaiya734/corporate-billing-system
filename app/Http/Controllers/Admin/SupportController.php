<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class SupportController extends Controller
{
    /**
     * Display a listing of support tickets
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with(['customer.user']);
        
        // Apply filters
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('priority') && $request->priority != '') {
            $query->where('priority', $request->priority);
        }
        
        if ($request->has('category') && $request->category != '') {
            $query->where('category', $request->category);
        }
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Order by newest first
        $tickets = $query->latest()->paginate(15);
        
        // Get statistics
        $stats = [
            'total' => SupportTicket::count(),
            'open' => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'resolved' => SupportTicket::where('status', 'resolved')->count(),
            'urgent' => SupportTicket::where('priority', 'urgent')->count(),
        ];
        
        return view('admin.support.index', compact('tickets', 'stats'));
    }
    
    /**
     * Display the specified support ticket
     */
    public function show($id)
    {
        $ticket = SupportTicket::with(['customer.user'])->findOrFail($id);
        
        return view('admin.support.show', compact('ticket'));
    }
    
    /**
     * Update the status of a support ticket
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        $ticket = SupportTicket::findOrFail($id);
        $ticket->status = $request->status;
        $ticket->save();
        
        // Here you could add logic to send email notifications to the customer
        
        return redirect()->back()->with('success', 'Ticket status updated successfully!');
    }
    
    /**
     * Update the priority of a support ticket
     */
    public function updatePriority(Request $request, $id)
    {
        $request->validate([
            'priority' => 'required|in:low,medium,high,urgent'
        ]);
        
        $ticket = SupportTicket::findOrFail($id);
        $ticket->priority = $request->priority;
        $ticket->save();
        
        return redirect()->back()->with('success', 'Ticket priority updated successfully!');
    }
    
    /**
     * Get recent support tickets for dashboard widget
     */
    public function getRecentTickets(Request $request)
    {
        $limit = $request->get('limit', 5);
        
        $tickets = SupportTicket::with(['customer'])
            ->latest()
            ->take($limit)
            ->get();
            
        return response()->json($tickets);
    }
}