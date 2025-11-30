<!-- Sample data for when no real bills exist -->
@for($month = 1; $month <= 12; $month++)
    @php
        $date = \Carbon\Carbon::create(($selectedYear ?? date('Y')), $month, 1);
        $isPaid = $month <= 7;
        $isPartial = $month >= 8 && $month <= 11;
        $isOverdue = $month == 12;
        
        $baseAmount = 1230.50;
        $previousDue = $month > 1 ? (($month - 1) * 230.50) : 0;
        $total = $baseAmount + $previousDue;
        $paidAmount = $isPaid ? $total : ($isPartial ? 1000.00 : 752.50);
        $dueAmount = $total - $paidAmount;
    @endphp
    <tr>
        <td>
            <strong>{{ $date->format('F Y') }}</strong><br>
            <small class="text-muted">Due: {{ $date->copy()->addMonth()->day(10)->format('d M Y') }}</small>
        </td>
        <td>
            <div class="services-tags">
                <div class="product-line">
                    <span class="badge bg-primary">Fast Speed</span>
                    <span class="badge bg-success">Gaming Boost</span>
                </div>
                <div class="product-line">
                    <small class="text-muted">৳800 + ৳200</small>
                </div>
            </div>
        </td>
        <td class="text-end">
            <span class="bill-amount">৳{{ number_format($baseAmount, 2) }}</span>
            <small class="text-muted d-block">(৳50 + ৳800 + ৳200 + VAT 7%)</small>
        </td>
        <td class="text-end">
            <span class="previous-due">৳{{ number_format($previousDue, 2) }}</span>
        </td>
        <td class="text-end">
            <strong class="total-amount">৳{{ number_format($total, 2) }}</strong>
        </td>
        <td class="text-end">
            <span class="received-amount-display">৳{{ number_format($paidAmount, 2) }}</span>
        </td>
        <td class="text-end">
            @if($dueAmount > 0)
                <span class="next-due text-danger">৳{{ number_format($dueAmount, 2) }}</span>
            @else
                <span class="next-due">৳{{ number_format($dueAmount, 2) }}</span>
            @endif
        </td>
        <td class="text-center">
            @if($isPaid)
                <span class="badge badge-paid">Paid</span>
            @elseif($isPartial)
                <span class="badge badge-pending">Partial</span>
            @else
                <span class="badge badge-overdue">Overdue</span>
            @endif
        </td>
    </tr>
@endfor