# Billing Flow Diagram - 3-Month Cycle Example

## Visual Timeline

```
Product: 100 BDT/month
Billing Cycle: 3 months
Customer: John Doe

┌─────────────────────────────────────────────────────────────────────────┐
│                         BILLING TIMELINE                                 │
└─────────────────────────────────────────────────────────────────────────┘

JUNE 2024 (Assigned Month)
═══════════════════════════════════════════════════════════════════════════
📅 assign_date: 2024-06-15
💰 Invoice: 300 BDT (Advance payment for Jun-Aug)
   ├─ Calculation: 100 BDT × 3 months
   ├─ subtotal: 300 BDT
   ├─ previous_due: 0 BDT
   └─ total_amount: 300 BDT

Status: ⚠️ UNPAID
next_due: 300 BDT


JULY 2024 (No Billing - Within Paid Period)
═══════════════════════════════════════════════════════════════════════════
📅 No new invoice
💰 Carry Forward: 300 BDT (if June unpaid)
   └─ Shows in monthly summary but no new charge


AUGUST 2024 (No Billing - Within Paid Period)
═══════════════════════════════════════════════════════════════════════════
📅 No new invoice
💰 Carry Forward: 300 BDT (if June unpaid)
   └─ Shows in monthly summary but no new charge


SEPTEMBER 2024 (First Due Month)
═══════════════════════════════════════════════════════════════════════════
📅 Months from assign_date: 3 (3 % 3 = 0 ✓ DUE MONTH)
💰 Invoice: 300 BDT or 600 BDT
   
   SCENARIO A: June was PAID
   ├─ New installment: 300 BDT (for Sep-Nov)
   ├─ previous_due: 0 BDT
   └─ total_amount: 300 BDT
   
   SCENARIO B: June was UNPAID
   ├─ New installment: 300 BDT (for Sep-Nov)
   ├─ previous_due: 300 BDT (from June)
   └─ total_amount: 600 BDT

Status: ⚠️ UNPAID (assuming no payment)
next_due: 600 BDT


OCTOBER 2024 (No Billing - Within Paid Period)
═══════════════════════════════════════════════════════════════════════════
📅 No new invoice
💰 Carry Forward: 600 BDT (if September unpaid)
   └─ Shows in monthly summary but no new charge


NOVEMBER 2024 (No Billing - Within Paid Period)
═══════════════════════════════════════════════════════════════════════════
📅 No new invoice
💰 Carry Forward: 600 BDT (if September unpaid)
   └─ Shows in monthly summary but no new charge


DECEMBER 2024 (Second Due Month)
═══════════════════════════════════════════════════════════════════════════
📅 Months from assign_date: 6 (6 % 3 = 0 ✓ DUE MONTH)
💰 Invoice: 300 BDT or 600 BDT or 900 BDT
   
   SCENARIO A: All previous invoices PAID
   ├─ New installment: 300 BDT (for Dec-Feb)
   ├─ previous_due: 0 BDT
   └─ total_amount: 300 BDT
   
   SCENARIO B: Only September UNPAID
   ├─ New installment: 300 BDT (for Dec-Feb)
   ├─ previous_due: 300 BDT (from September)
   └─ total_amount: 600 BDT
   
   SCENARIO C: Both June and September UNPAID
   ├─ New installment: 300 BDT (for Dec-Feb)
   ├─ previous_due: 600 BDT (300 from June + 300 from Sep)
   └─ total_amount: 900 BDT

Status: ⚠️ UNPAID (assuming no payment)
next_due: 900 BDT


JANUARY 2025 (No Billing - Within Paid Period)
═══════════════════════════════════════════════════════════════════════════
📅 No new invoice
💰 Carry Forward: 900 BDT (if December unpaid)
   └─ Shows in monthly summary but no new charge


FEBRUARY 2025 (No Billing - Within Paid Period)
═══════════════════════════════════════════════════════════════════════════
📅 No new invoice
💰 Carry Forward: 900 BDT (if December unpaid)
   └─ Shows in monthly summary but no new charge


MARCH 2025 (Third Due Month)
═══════════════════════════════════════════════════════════════════════════
📅 Months from assign_date: 9 (9 % 3 = 0 ✓ DUE MONTH)
💰 Invoice: 300 BDT + accumulated debt
   
   SCENARIO: All previous invoices UNPAID
   ├─ New installment: 300 BDT (for Mar-May)
   ├─ previous_due: 900 BDT (from Jun + Sep + Dec)
   └─ total_amount: 1,200 BDT

Status: ⚠️ UNPAID
next_due: 1,200 BDT
```

## Code Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    INVOICE GENERATION PROCESS                            │
└─────────────────────────────────────────────────────────────────────────┘

1. User clicks "Generate Invoices" for a month
   │
   ├─→ generateMonthInvoices(Request $request)
   │
   └─→ getDueCustomersForMonth($monthDate)
       │
       ├─→ Query customer_to_products table
       │   ├─ Filter by: status = 'active', is_active = 1
       │   ├─ Check: assign_date <= billing month
       │   └─ Calculate: (billing_month - assign_month) % billing_cycle == 0
       │
       └─→ Returns collection of due customers

2. For each due customer:
   │
   ├─→ Check if invoice already exists
   │   └─ If exists: Skip (skippedCount++)
   │
   └─→ createCustomerInvoice($customer, $monthDate)
       │
       ├─→ Calculate product amount
       │   └─ $productAmount = $customer->monthly_price * billing_cycle
       │
       ├─→ Get previous due amount
       │   └─ Query invoices where status != 'paid' AND next_due > 0
       │   └─ $previousDue = sum(next_due)
       │
       ├─→ Calculate total
       │   └─ $totalAmount = $productAmount + $previousDue
       │
       └─→ Create Invoice record
           ├─ invoice_number: Auto-generated (INV-2024-0001)
           ├─ cp_id: Customer product ID
           ├─ issue_date: Billing month date
           ├─ previous_due: Carried forward amount
           ├─ subtotal: Current period charge
           ├─ total_amount: subtotal + previous_due
           ├─ received_amount: 0
           ├─ next_due: total_amount
           └─ status: 'unpaid'
```

## Monthly Summary Calculation

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    MONTHLY SUMMARY DISPLAY                               │
└─────────────────────────────────────────────────────────────────────────┘

getDynamicMonthlySummary()
│
├─→ Get all assignment months
│   └─ SELECT DISTINCT DATE_FORMAT(assign_date, '%Y-%m') FROM customer_to_products
│
├─→ Calculate all due months
│   └─ For each assignment: assign_date + (n × billing_cycle_months)
│
├─→ For each month:
│   │
│   └─→ calculateNewMonthData($month)
│       │
│       ├─→ getCustomersForMonth($month)
│       │   ├─ Customers assigned in this month
│       │   ├─ Customers due in this month (billing cycle)
│       │   └─ Customers with unpaid invoices (carry forward)
│       │
│       └─→ calculateAmountsForCustomers($customers, $month)
│           │
│           ├─→ For each customer product:
│           │   │
│           │   ├─→ calculateInstallmentAmount()
│           │   │   ├─ If assigned month: monthly_price × billing_cycle
│           │   │   ├─ If due month: monthly_price × billing_cycle
│           │   │   └─ If carry forward month: 0
│           │   │
│           │   ├─→ getCarriedForwardAmount()
│           │   │   └─ SUM(next_due) from unpaid invoices
│           │   │
│           │   └─→ Total = installment + carried forward
│           │
│           └─→ Return totals for the month
│
└─→ Display in monthly summary table
```

## Payment Processing Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    PAYMENT RECORDING PROCESS                             │
└─────────────────────────────────────────────────────────────────────────┘

recordPayment(Request $request, $invoiceId)
│
├─→ Validate payment data
│   ├─ amount: required, numeric, min:0
│   ├─ payment_method: required
│   ├─ payment_date: required
│   └─ note: optional
│
├─→ Create Payment record
│   ├─ invoice_id
│   ├─ c_id: Customer ID
│   ├─ amount
│   ├─ payment_method
│   ├─ payment_date
│   └─ note
│
└─→ Update Invoice
    │
    ├─→ Calculate new amounts
    │   ├─ newReceivedAmount = received_amount + payment_amount
    │   ├─ newDue = total_amount - newReceivedAmount
    │   └─ If newDue < 0.01: newDue = 0 (handle floating point)
    │
    ├─→ Determine status
    │   ├─ If newDue == 0: status = 'paid'
    │   ├─ If newDue > 0 AND received > 0: status = 'partial'
    │   └─ Else: status = 'unpaid'
    │
    └─→ Update invoice record
        ├─ received_amount = newReceivedAmount
        ├─ next_due = newDue
        └─ status = calculated status

Note: Unpaid amounts automatically carry forward to next billing month
      via getCarriedForwardAmount() function
```

## Database Relationships

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    DATABASE STRUCTURE                                    │
└─────────────────────────────────────────────────────────────────────────┘

customers
├─ c_id (PK)
├─ customer_id (unique)
├─ name
├─ email
├─ phone
└─ is_active
    │
    │ 1:N
    ↓
customer_to_products (cp)
├─ cp_id (PK)
├─ c_id (FK → customers)
├─ p_id (FK → products)
├─ assign_date ← CRITICAL: Starting point for billing
├─ billing_cycle_months ← CRITICAL: 1, 3, 6, or 12
├─ due_date (computed: assign_date + billing_cycle_months)
├─ status (active/pending/expired)
└─ is_active
    │
    │ 1:N
    ↓
invoices
├─ invoice_id (PK)
├─ invoice_number (unique)
├─ c_id (FK → customers)
├─ issue_date ← Month when invoice was created
├─ previous_due ← Carried forward from unpaid invoices
├─ subtotal ← Current period charge
├─ total_amount ← subtotal + previous_due
├─ received_amount ← Total payments received
├─ next_due ← Remaining unpaid (carries forward)
└─ status (unpaid/partial/paid/cancelled)
    │
    │ 1:N
    ↓
payments
├─ payment_id (PK)
├─ invoice_id (FK → invoices)
├─ c_id (FK → customers)
├─ amount
├─ payment_method
├─ payment_date
└─ note
```

## Key Formulas

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    CALCULATION FORMULAS                                  │
└─────────────────────────────────────────────────────────────────────────┘

1. Is Due Month?
   ─────────────
   months_diff = PERIOD_DIFF(billing_month, assign_month)
   is_due = (months_diff % billing_cycle_months) == 0
   
   Example:
   assign_month = 2024-06
   billing_month = 2024-09
   billing_cycle = 3
   months_diff = 3
   is_due = (3 % 3) == 0 → TRUE ✓

2. Installment Amount
   ──────────────────
   IF is_assigned_month OR is_due_month:
       installment = monthly_price × billing_cycle_months
   ELSE:
       installment = 0
   
   Example (3-month cycle, 100 BDT/month):
   June (assigned): 100 × 3 = 300 BDT
   September (due): 100 × 3 = 300 BDT
   October (carry): 0 BDT

3. Carried Forward Amount
   ──────────────────────
   carried_forward = SUM(next_due) 
                     FROM invoices 
                     WHERE cp_id = customer_product_id
                     AND status IN ('unpaid', 'partial', 'confirmed')
                     AND next_due > 0
   
   Example:
   June invoice: next_due = 300 BDT
   Sep invoice: next_due = 300 BDT
   carried_forward = 300 + 300 = 600 BDT

4. Total Invoice Amount
   ────────────────────
   total_amount = installment + carried_forward
   
   Example (December, all unpaid):
   installment = 300 BDT (Dec-Feb)
   carried_forward = 600 BDT (Jun + Sep)
   total_amount = 300 + 600 = 900 BDT

5. Invoice Status After Payment
   ─────────────────────────────
   new_received = received_amount + payment_amount
   new_due = total_amount - new_received
   
   IF new_due <= 0:
       status = 'paid'
   ELSE IF new_received > 0:
       status = 'partial'
   ELSE:
       status = 'unpaid'
```

## Summary

The system correctly implements your billing scenario:
- ✅ Advance payment in assigned month (June: 300 BDT)
- ✅ Regular billing at due months (September: 300 BDT)
- ✅ Carry forward unpaid amounts (December: 300 + 600 = 900 BDT)
- ✅ Accumulating debt over time (March: 300 + 900 = 1,200 BDT)
- ✅ Flexible billing cycles (1, 3, 6, 12 months)
- ✅ Partial payment tracking
- ✅ Dynamic monthly summary generation
