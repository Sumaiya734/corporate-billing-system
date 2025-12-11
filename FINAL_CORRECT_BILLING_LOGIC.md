# Final Correct Billing Logic - Summary

## Overview
The billing system creates invoices for EVERY month, with proper carry-forward of unpaid amounts.

## Key Principles

1. **Invoices are created for EVERY month** (not just billing cycle months)
2. **Subtotal is added only at specific times:**
   - First month (assign month): May
   - Start of new periods: November, May (next year), etc.
   - NOT at the end of periods: August, February, etc.
3. **Previous due always carries forward** from the previous month's `next_due`
4. **Total amount** = `subtotal` + `previous_due`

## Example: 3-Month Billing Cycle (May 2025 start)

### Billing Periods
- **Period 1**: May - August (May gets subtotal, August is end of period)
- **Period 2**: November - February (November gets subtotal, February is end of period)
- **Period 3**: May - August (next year)

### Monthly Breakdown

| Month | Type | Subtotal | Previous Due | Total | Notes |
|-------|------|----------|--------------|-------|-------|
| **May 2025** | Assign Month | 2,000 | 0 | **2,000** | First billing - add subtotal |
| **June 2025** | Carry Forward | 0 | 2,000 | **2,000** | Carry forward from May |
| **July 2025** | Carry Forward | 0 | 2,000 | **2,000** | Carry forward |
| **August 2025** | Due Month (End) | 0 | 2,000 | **2,000** | End of Period 1 - NO new subtotal |
| **September 2025** | Carry Forward | 0 | 2,000 | **2,000** | Carry forward from August |
| **October 2025** | Carry Forward | 0 | 2,000 | **2,000** | Carry forward |
| **November 2025** | New Period Start | 2,000 | 2,000 | **4,000** | Start of Period 2 - ADD subtotal |
| **December 2025** | Carry Forward | 0 | 4,000 | **4,000** | Carry forward from November |

## Pattern Recognition

### When to Add Subtotal:
- **Month 0** (May): First billing - YES
- **Month 3** (August): Period 1 end - NO
- **Month 6** (November): Period 2 start - YES
- **Month 9** (February): Period 2 end - NO
- **Month 12** (May next year): Period 3 start - YES

**Formula**: 
```
monthsSinceAssign = current_month - assign_month
periodNumber = monthsSinceAssign / billing_cycle

if monthsSinceAssign == 0:
    add_subtotal = TRUE  // First month
else if (monthsSinceAssign % billing_cycle) == 0:
    if periodNumber % 2 == 1:
        add_subtotal = FALSE  // End of period (odd period numbers)
    else:
        add_subtotal = TRUE   // Start of new period (even period numbers)
else:
    add_subtotal = FALSE  // Regular carry-forward month
```

## Database Structure

### Invoices Table
```
invoice_id | invoice_number | cp_id | issue_date | previous_due | subtotal | total_amount | received_amount | next_due | status
-----------|----------------|-------|------------|--------------|----------|--------------|-----------------|----------|--------
1          | INV-25-05-0001 | 29    | 2025-05-09 | 0.00         | 2000.00  | 2000.00      | 0.00            | 2000.00  | unpaid
2          | INV-25-06-0002 | 29    | 2025-06-09 | 2000.00      | 0.00     | 2000.00      | 0.00            | 2000.00  | unpaid
3          | INV-25-07-0003 | 29    | 2025-07-09 | 2000.00      | 0.00     | 2000.00      | 0.00            | 2000.00  | unpaid
4          | INV-25-08-0004 | 29    | 2025-08-09 | 2000.00      | 0.00     | 2000.00      | 0.00            | 2000.00  | unpaid
5          | INV-25-09-0005 | 29    | 2025-09-09 | 2000.00      | 0.00     | 2000.00      | 0.00            | 2000.00  | unpaid
6          | INV-25-10-0006 | 29    | 2025-10-09 | 2000.00      | 0.00     | 2000.00      | 0.00            | 2000.00  | unpaid
7          | INV-25-11-0007 | 29    | 2025-11-09 | 2000.00      | 2000.00  | 4000.00      | 0.00            | 4000.00  | unpaid
8          | INV-25-12-0008 | 29    | 2025-12-09 | 4000.00      | 0.00     | 4000.00      | 0.00            | 4000.00  | unpaid
```

## Monthly Summary Display

The monthly summary shows the latest invoice for each customer in each month:

```
Month          | Total Amount | Status
---------------|--------------|--------
May 2025       | 2,000 BDT    | Unpaid
June 2025      | 2,000 BDT    | Unpaid
July 2025      | 2,000 BDT    | Unpaid
August 2025    | 2,000 BDT    | Unpaid
September 2025 | 2,000 BDT    | Unpaid
October 2025   | 2,000 BDT    | Unpaid
November 2025  | 4,000 BDT    | Unpaid
December 2025  | 4,000 BDT    | Unpaid
```

## Payment Handling

When a payment is made:
1. Update the invoice's `received_amount`
2. Recalculate `next_due` = `total_amount` - `received_amount`
3. Update `status` (unpaid → partial → paid)
4. Next month's invoice will use the updated `next_due` as `previous_due`

### Example: Payment in August
If customer pays 1,000 BDT in August:
- August invoice: received_amount = 1,000, next_due = 1,000
- September invoice: previous_due = 1,000 (not 2,000)

## Implementation Files

1. **create_all_monthly_invoices.php** - Script to generate all monthly invoices
2. **BillingController.php** - Controller with billing logic
3. **calculateAmountsForCustomers()** - Shows latest invoice per customer
4. **getCustomersForMonth()** - Returns customers with billing activity

## Key Takeaways

✅ **Every month gets an invoice** (not just billing cycle months)
✅ **Subtotal added only at period starts** (May, November, etc.)
✅ **Previous due always carries forward** (month-to-month)
✅ **End of period months** (August, February) do NOT get new subtotal
✅ **Monthly summary shows latest invoice** to avoid double-counting

The system now correctly implements the billing logic where unpaid amounts carry forward month-to-month, and new charges are added only at the start of new billing periods!
