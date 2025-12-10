# Billing Cycle Implementation - 3-Month Example

## Scenario Overview
Customer with a **3-month billing cycle** and a product costing **100 BDT per month**

## Timeline Breakdown

### June (Assigned Month)
- **Action**: Customer assigned product
- **Invoice**: 300 BDT (100 BDT × 3 months advance payment)
- **Logic**: `assign_date` = June, `billing_cycle_months` = 3
- **Code**: `calculateInstallmentAmount()` returns `monthlyPrice * billingCycle` for assigned month

### September (First Due Month)
- **Action**: First billing cycle completes
- **Invoice**: 300 BDT (new cycle payment)
- **Logic**: Months diff from June = 3, which is divisible by billing_cycle (3)
- **Code**: `shouldPayInMonth()` returns true because `monthsDiff % billingCycle == 0`

### December (If September Unpaid)
- **Action**: Second billing cycle + carry forward
- **Invoice**: 600 BDT total
  - 300 BDT (new cycle payment for Dec-Feb)
  - 300 BDT (carried forward from unpaid September)
- **Logic**: 
  - New installment: Months diff from June = 6, divisible by 3
  - Carry forward: `getCarriedForwardAmount()` sums all unpaid invoices
- **Code**: `calculateAmountsForCustomers()` adds both amounts

### March (If Still Unpaid)
- **Action**: Third billing cycle + accumulated carry forward
- **Invoice**: 900 BDT total
  - 300 BDT (new cycle payment for Mar-May)
  - 600 BDT (carried forward: 300 from Sep + 300 from Dec)
- **Logic**: Same as December, but with more accumulated debt

## Key Implementation Details

### Database Schema
```php
customer_to_products table:
- cp_id: Primary key
- c_id: Customer ID
- p_id: Product ID
- assign_date: When product was assigned (June in example)
- billing_cycle_months: 3 (for quarterly billing)
- due_date: Calculated as assign_date + billing_cycle_months
- status: 'active', 'pending', or 'expired'

invoices table:
- invoice_id: Primary key
- c_id: Customer ID
- issue_date: When invoice was created
- previous_due: Carried forward amount from previous unpaid invoices
- subtotal: Current period's charge
- total_amount: subtotal + previous_due
- received_amount: Total payments received
- next_due: Remaining unpaid amount
- status: 'unpaid', 'partial', 'paid', 'cancelled'
```

### Core Functions

#### 1. `shouldPayInMonth($assignDate, $billingCycle, $monthDate)`
Determines if a customer should be billed in a specific month:
- Returns `true` if it's the assigned month (advance payment)
- Returns `true` if months difference is divisible by billing cycle (due month)
- Returns `true` if month is after assign date (for carry forward display)

#### 2. `calculateInstallmentAmount($assignDate, $billingCycle, $monthlyPrice, $monthDate)`
Calculates the new installment for a specific month:
- **Assigned month**: Returns `monthlyPrice * billingCycle` (300 BDT)
- **Due months**: Returns `monthlyPrice * billingCycle` (300 BDT)
- **Carry forward months**: Returns `0` (no new charge, only carry forward)

#### 3. `getCarriedForwardAmount($cpId, $monthDate)`
Gets total unpaid amount from previous invoices:
```php
Invoice::where('cp_id', $cpId)
    ->whereIn('status', ['unpaid', 'partial', 'confirmed'])
    ->where('next_due', '>', 0)
    ->sum('next_due');
```

#### 4. `calculateAmountsForCustomers($customers, $month)`
Main calculation function:
```php
$installmentAmount = calculateInstallmentAmount(...);  // New charge
$carriedForwardAmount = getCarriedForwardAmount(...);  // Old debt
$customerTotalAmount = $installmentAmount + $carriedForwardAmount;
```

## Monthly Summary Display

The `getDynamicMonthlySummary()` function shows:
- **Assigned months**: Months when products were assigned (advance payment)
- **Due months**: Calculated based on billing cycles
- **Carry forward months**: Months with unpaid invoices

## Invoice Generation

When generating invoices via `createCustomerInvoice()`:
```php
$productAmount = $customer->monthly_price * $billingCycle;  // Current period
$previousDue = sum of all unpaid invoices;                   // Carry forward
$totalAmount = $productAmount + $previousDue;                // Total bill
```

## Payment Processing

When recording payments via `recordPayment()`:
- Updates `received_amount` on the invoice
- Recalculates `next_due` = `total_amount` - `received_amount`
- Updates status: 'paid', 'partial', or 'unpaid'
- Unpaid amounts automatically carry forward to next billing month

## Example Flow

```
June 2024:
  assign_date = 2024-06-15
  billing_cycle_months = 3
  Invoice: 300 BDT (advance for Jun-Aug)
  Status: unpaid

September 2024:
  Months diff = 3 (divisible by 3 = due month)
  New installment: 300 BDT (for Sep-Nov)
  Carry forward: 300 BDT (from June)
  Total: 600 BDT if June unpaid, or 300 BDT if June paid

December 2024:
  Months diff = 6 (divisible by 3 = due month)
  New installment: 300 BDT (for Dec-Feb)
  Carry forward: Sum of all unpaid (could be 0, 300, or 600)
  Total: Varies based on payment history

March 2025:
  Months diff = 9 (divisible by 3 = due month)
  New installment: 300 BDT (for Mar-May)
  Carry forward: Sum of all unpaid
  Total: Continues accumulating if unpaid
```

## Current Implementation Status

✅ **Implemented Features:**
- Advance payment calculation in assigned month
- Billing cycle-based due month calculation
- Carry forward of unpaid amounts
- Dynamic monthly summary generation
- Invoice generation with previous dues
- Payment tracking and status updates

⚠️ **Notes:**
- The system correctly handles multi-month billing cycles (1, 3, 6, 12 months)
- Unpaid amounts automatically carry forward to all subsequent months
- Monthly summary shows all relevant months (assigned, due, and carry forward)
- Invoice generation includes both new charges and carried forward amounts
