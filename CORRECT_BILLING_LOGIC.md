# Correct Billing Logic - Final Understanding

## Customer Example
- **Customer**: Imteaz
- **Assign Date**: May 9, 2025
- **Due Date**: August 9, 2025 (3 months later)
- **Billing Cycle**: 3 months
- **Subtotal per cycle**: 2,000 BDT

## Billing Periods

### Period 1: May 9 - August 9, 2025
**Payment Window**: May 9 to August 9

- **May Invoice** (INV-25-05-0001):
  - `subtotal`: 2,000 BDT (for May-Aug period)
  - `previous_due`: 0 BDT
  - `total_amount`: 2,000 BDT
  - **Purpose**: Advance payment option (customer can pay anytime from May 9 to Aug 9)

- **August Invoice** (INV-25-08-0001):
  - `subtotal`: 2,000 BDT (SAME period: May-Aug)
  - `previous_due`: 2,000 BDT (from unpaid May invoice)
  - `total_amount`: 4,000 BDT
  - **Purpose**: Reminder/alternative invoice (customer missed the payment deadline)
  - **Note**: Both May and August invoices exist because customer didn't pay on time

### Period 2: August 9 - November 9, 2025
**Payment Window**: August 9 to November 9

- **August Invoice** (already created above, but also serves as advance for Period 2)
  - Actually, this is confusing...

Wait, let me reconsider. If:
- assign_date = May 9
- due_date = August 9
- billing_cycle = 3 months

Then the billing periods are:
1. **Period 1**: May 9 - August 9 (3 months)
2. **Period 2**: August 9 - November 9 (3 months)
3. **Period 3**: November 9 - February 9 (3 months)

So:
- **May invoice** = Bill for Period 1 (May-Aug)
- **August invoice** = Bill for Period 2 (Aug-Nov) + unpaid from Period 1
- **November invoice** = Bill for Period 3 (Nov-Feb) + unpaid from Periods 1 & 2

This means:
- **May**: subtotal 2,000 (Period 1) + previous_due 0 = **2,000**
- **August**: subtotal 2,000 (Period 2) + previous_due 2,000 (Period 1 unpaid) = **4,000**
- **November**: subtotal 2,000 (Period 3) + previous_due 6,000 (Periods 1 & 2 unpaid) = **8,000**

## Current Invoice Status

```
May 2025:
  Invoice: INV-25-05-0001
  Subtotal: 2,000 BDT (Period 1: May-Aug)
  Previous Due: 0 BDT
  Total: 2,000 BDT
  Status: unpaid

August 2025:
  Invoice: INV-25-08-0001
  Subtotal: 2,000 BDT (Period 2: Aug-Nov)
  Previous Due: 2,000 BDT (Period 1 unpaid)
  Total: 4,000 BDT
  Status: unpaid

November 2025:
  Invoice: INV-25-11-0001
  Subtotal: 2,000 BDT (Period 3: Nov-Feb)
  Previous Due: 6,000 BDT (Periods 1 & 2 unpaid)
  Total: 8,000 BDT
  Status: unpaid
```

## Monthly Summary Display

The monthly summary shows the **latest invoice** for each customer:

- **May 2025**: 2,000 BDT (May invoice)
- **June 2025**: 2,000 BDT (May invoice carried forward)
- **July 2025**: 2,000 BDT (May invoice carried forward)
- **August 2025**: 4,000 BDT (August invoice - latest)
- **September 2025**: 4,000 BDT (August invoice carried forward)
- **October 2025**: 4,000 BDT (August invoice carried forward)
- **November 2025**: 8,000 BDT (November invoice - latest)
- **December 2025**: 8,000 BDT (November invoice carried forward)

## Key Points

1. **Each billing cycle creates a NEW invoice** with:
   - `subtotal`: Fixed amount for that period (2,000 BDT)
   - `previous_due`: Sum of all unpaid amounts from previous invoices
   - `total_amount`: subtotal + previous_due

2. **All invoices remain active** (not cancelled) so the total debt accumulates

3. **Monthly summary shows the latest invoice** to avoid double-counting

4. **Subtotal comes from the database** (`invoices.subtotal`), not calculated from `monthly_price * billing_cycle`

5. **The assign_date and due_date define the billing period**, not just the billing cycle months

## Implementation

✅ **Correct**: Using `subtotal` from first invoice as reference
✅ **Correct**: Accumulating `previous_due` from all unpaid invoices
✅ **Correct**: Showing latest invoice per customer in monthly summary
✅ **Correct**: All invoices remain active (unpaid status)
✅ **Correct**: Amounts properly accumulate over time

The system is now working as intended!
