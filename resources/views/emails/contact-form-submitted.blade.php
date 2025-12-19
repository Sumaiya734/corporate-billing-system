<!DOCTYPE html>
<html>
<head>
    <title>New Support Ticket</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .footer {
            background: #f1f1f1;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .ticket-info {
            background: #e9f7fe;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .details-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .details-table tr:last-child td {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #555;
            width: 150px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Support Ticket Received</h1>
            <p>Ticket #{{ $ticket->ticket_number }}</p>
        </div>
        
        <div class="content">
            <p>Hello Support Team,</p>
            
            <p>A new support ticket has been submitted through the customer contact form:</p>
            
            <div class="ticket-info">
                <h3>Ticket Details</h3>
                <table class="details-table">
                    <tr>
                        <td class="label">Ticket Number:</td>
                        <td>{{ $ticket->ticket_number }}</td>
                    </tr>
                    <tr>
                        <td class="label">Subject:</td>
                        <td>{{ $ticket->subject }}</td>
                    </tr>
                    <tr>
                        <td class="label">Category:</td>
                        <td>{{ ucfirst($ticket->category) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Priority:</td>
                        <td>{{ ucfirst($ticket->priority) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Department:</td>
                        <td>{{ ucfirst($ticket->department) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Status:</td>
                        <td>{{ ucfirst($ticket->status) }}</td>
                    </tr>
                </table>
            </div>
            
            <h3>Customer Information</h3>
            <table class="details-table">
                <tr>
                    <td class="label">Name:</td>
                    <td>{{ $data['name'] }}</td>
                </tr>
                <tr>
                    <td class="label">Email:</td>
                    <td>{{ $data['email'] }}</td>
                </tr>
                @if(isset($ticket->customer))
                <tr>
                    <td class="label">Customer ID:</td>
                    <td>{{ $ticket->customer->customer_id }}</td>
                </tr>
                @endif
            </table>
            
            <h3>Message</h3>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; border-left: 3px solid #007bff;">
                {!! nl2br(e($ticket->description)) !!}
            </div>
            
            <p style="text-align: center;">
                <a href="{{ url('/admin/support/' . $ticket->id) }}" class="btn">View Ticket in Admin Panel</a>
            </p>
            
            <p>Please respond to this ticket as soon as possible according to the priority level.</p>
        </div>
        
        <div class="footer">
            <p>This is an automated message from the Nanosoft Billing System.</p>
            <p>If you believe this message was sent in error, please contact support@nanosoft.com</p>
        </div>
    </div>
</body>
</html>