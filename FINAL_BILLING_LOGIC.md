# Final Billing Logic - Complete Explanation

## Overview
The billing system creates invoices at billing cycle intervals and carries forward unpaid amounts to subsequent invoices.

## Example: Customer with 3-Month Billing Cycle

**Customer Details:**
- Assigned Date: May 2025
- Billing Cycle: 3 months
- Monthly Price: 2,000 BDT

### Invoice Creation Timeline

#### May 2025 (Month 0 - Assigned Month)
**Action:** Create NEW invoice
- `subtotal`: 2,000 BDT (current charge)
- `previous_due`: 0 BDT
- `total_amount`: 2,000 BDT
- `received_amount`: 0 BDT
- `next_due`: 2,000 BDT
- `status`: unpaid

**Monthly Summary Shows:** 2,000 BDT (from May invoice)

---

#### June 2025 (Month 1 - Within Paid Period)
**Action:** NO new invoice created
- Customer is within the paid period (May-July)
- May invoice remains unpaid

**Monthly Summary Shows:** Nothing (no billing activity)

---

#### July 2025 (Month 2 - Within Paid Period)
**Action:** NO new invoice created
- Customer is within the paid period (May-July)
- May invoice remains unpaid

**Monthly Summary Shows:** Nothing (no billing activity)

---

#### August 2025 (Month 3 - Next Billing Cycle)
**Action:** Create NEW invoice
- `subtotal`: 2,000 BDT (current charge for Aug-Oct)
- `previous_due`: 0 BDT (May invoice is separate, not included)
- `total_amount`: 2,000 BDT
- `received_amount`: 0 BDT
- `next_due`: 2,000 BDT
- `status`: unpaid

**Note:** May invoice (2,000 BDT) is still unpaid and exists separately

**Monthly Summary Shows:** 2,000 BDT (from August invoice - the latest one)

---

#### September 2025 (Month 4 - Carry Forward)
**Action:** NO new invoice created
- August invoice remains unpaid
- This is a carry-forward month

**Monthly Summary Shows:** 2,000 BDT (from August invoice - still the latest)

---

#### October 2025 (Month 5 - Carry Forward)
**Action:** NO new invoice created
- August invoice remains unpaid
- This is a carry-forward month

**Monthly Summary Shows:** 2,000 BDT (from August invoice - still the latest)

---

#### November 2025 (Month 6 - Next Billing Cycle)
**Action:** Create NEW invoice
- `subtotal`: 2,000 BDT (current charge for Nov-Jan)
- `previous_due`: 2,000 BDT (from August invoice's next_due)
- `total_amount`: 4,000 BDT
- `received_amount`: 0 BDT
- `next_due`: 4,000 BDT
- `status`: unpaid

**Note:** 
- May invoice (2,000 BDT) is still unpaid separately
- August invoice (2,000 BDT) is now included in November's previous_due
- Total debt: May (2,000) + November (4,000) = 6,000 BDT actual debt

**Monthly Summary Shows:** 4,000 BDT (from November invoice - the latest one)

---

#### December 2025 (Month 7 - Carry Forward)
**Action:** NO new invoice created
- November invoice remains unpaid
- This is a carry-forward month

**Monthly Summary Shows:** 4,000 BDT (from November invoice - still the latest)

---

## Key Logic Points

### 1. Invoice Creation
Invoices are created ONLY in:
- **Assigned month** (Month 0)
- **Billing cycle months** (Month 3, 6, 9, 12, etc.)

Formula: `(months_since_assignment % billing_cycle_months) == 0`

### 2. Previous Due Calculation
When creating a new invoice:
```php
$previousDue = Invoice::where('cp_id', $customer->cp_id)
    ->where('status', '!=', 'paid')
    ->where('next_due', '>', 0)
    ->sum('next_due');
```

This sums ALL unpaid invoices for this customer-product.

### 3. Monthly Summary Display
For each month, show:
- **Customers who have invoices issued in that month** (new billing)
- **Customers who have unpaid invoices from previous months** (carry forward)

For each customer, show their **LATEST invoice** to avoid double-counting:
```php
$latestInvoice = Invoice::where('cp_id', $cp_id)
    ->where('issue_date', '<=', $monthDate)
    ->orderBy('issue_date', 'desc')
    ->first();
```

### 4. Carry Forward Months
In months between billing cycles:
- NO new invoice is created
- The latest unpaid invoice is shown in the monthly summary
- The amounts remain the same until the next billing cycle

## Database Structure

### Invoices Table
```
invoice_id | cp_id | issue_date | previous_due | subtotal | total_amount | received_amount | next_due | status
-----------|-------|------------|--------------|----------|--------------|-----------------|----------|--------
1          | 1     | 2025-05-01 | 0.00         | 2000.00  | 2000.00      | 0.00            | 2000.00  | unpaid
2          | 1     | 2025-08-01 | 0.00         | 2000.00  | 2000.00      | 0.00            | 2000.00  | unpaid
3          | 1     | 2025-11-01 | 2000.00      | 2000.00  | 4000.00      | 0.00            | 4000.00  | unpaid
```

**Note:** Invoice #1 (May) remains separate. Invoice #3 (November) includes Invoice #2 (August) in its previous_due.

## Monthly Summary Calculation

### May 2025
- Customers: Those assigned in May
- Latest Invoice: May invoice (2,000)
- **Display: 2,000 BDT**

### August 2025
- Customers: Those with billing cycle due in August
- Latest Invoice: August invoice (2,000)
- **Display: 2,000 BDT**

### September 2025
- Customers: Those with unpaid August invoice
- Latest Invoice: August invoice (2,000)
- **Display: 2,000 BDT**

### October 2025
- Customers: Those with unpaid August invoice
- Latest Invoice: August invoice (2,000)
- **Display: 2,000 BDT**

### November 2025
- Customers: Those with billing cycle due in November
- Latest Invoice: November invoice (4,000)
- **Display: 4,000 BDT**

### December 2025
- Customers: Those with unpaid November invoice
- Latest Invoice: November invoice (4,000)
- **Display: 4,000 BDT**

## Important Notes

1. **Multiple Unpaid Invoices:** A customer can have multiple unpaid invoices (May, August, November). The monthly summary shows only the LATEST one to avoid confusion.

2. **Actual Total Debt:** To see the actual total debt, sum ALL unpaid invoices:
   ```sql
   SELECT SUM(next_due) FROM invoices WHERE cp_id = X AND status != 'paid'
   ```

3. **Previous Due Accumulation:** When creating a new invoice, `previous_due` includes ALL previous unpaid amounts, not just the immediately previous invoice.

4. **Payment Handling:** When a payment is made:
   - It's applied to the invoice
   - `received_amount` increases
   - `next_due` decreases
   - `status` updates (unpaid → partial → paid)

5. **Carry Forward Display:** The monthly summary shows carry-forward amounts so you can see which customers have unpaid bills even in non-billing months.

## Expected Behavior After Fix

✅ **May**: Shows 2,000 BDT (new invoice)
✅ **August**: Shows 2,000 BDT (new invoice)
✅ **September**: Shows 2,000 BDT (August invoice carried forward)
✅ **October**: Shows 2,000 BDT (August invoice carried forward)
✅ **November**: Shows 4,000 BDT (new invoice with previous_due)
✅ **December**: Shows 4,000 BDT (November invoice carried forward)

The amounts now properly accumulate and show the correct totals!
