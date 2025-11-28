# Product Assignment Data Flow

## When you assign a product to a customer, here's what happens:

### 1. PRIMARY TABLE: `customer_to_products`
**This is the main table where product assignments are stored.**

#### Fields stored:
- `cp_id` (Primary Key) - Auto-generated unique ID
- `c_id` - Customer ID (links to `customers` table)
- `p_id` - Product ID (links to `products` table)
- `customer_product_id` - Unique identifier (format: C-25-0066-P10)
- `assign_date` - Date when product was assigned
- `billing_cycle_months` - Billing cycle (1, 2, 3, 6, or 12 months)
- `due_date` - Next payment due date
- `status` - Status (active, pending, expired)
- `is_active` - Boolean (1 or 0)
- `invoice_id` - First invoice ID (nullable)
- `created_at` - Timestamp
- `updated_at` - Timestamp

### 2. SECONDARY TABLE: `invoices`
**Invoices are automatically generated for each product assignment.**

#### Fields stored:
- `invoice_id` (Primary Key) - Auto-generated unique ID
- `invoice_number` - Unique invoice number (format: INV-2025-0001)
- `cp_id` - Links to `customer_to_products` table
- `issue_date` - Invoice issue date
- `previous_due` - Previous unpaid amount
- `service_charge` - Service charge (default: ৳50)
- `vat_percentage` - VAT percentage (default: 5%)
- `vat_amount` - Calculated VAT amount
- `subtotal` - Product amount + service charge
- `total_amount` - Subtotal + VAT + previous due
- `received_amount` - Amount paid
- `next_due` - Remaining amount to pay
- `status` - Payment status (paid, unpaid, partial)
- `notes` - Invoice notes
- `created_by` - User who created the invoice
- `created_at` - Timestamp
- `updated_at` - Timestamp

## Data Flow Example:

```
Customer: John Doe (c_id: 66)
Product: Internet Package (p_id: 10, monthly_price: ৳1000)
Billing Cycle: 3 months
Assign Date: 2025-11-23
Due Day: 4th of month

↓

STEP 1: Insert into `customer_to_products`
----------------------------------------
cp_id: 123 (auto-generated)
c_id: 66
p_id: 10
customer_product_id: "C-25-0066-P10"
assign_date: "2025-11-23"
billing_cycle_months: 3
due_date: "2026-02-04" (3 months from assign date, on 4th)
status: "active"
is_active: 1

↓

STEP 2: Auto-generate invoices in `invoices` table
-------------------------------------------------
Multiple invoices are created for the next 6 months:

Invoice 1:
- invoice_number: "INV-2025-0123"
- cp_id: 123
- issue_date: "2025-11-23"
- subtotal: ৳3050 (৳1000 × 3 months + ৳50 service charge)
- vat_amount: ৳152.50 (5% of subtotal)
- total_amount: ৳3202.50
- status: "unpaid"

Invoice 2 (for next billing cycle):
- invoice_number: "INV-2025-0124"
- cp_id: 123
- issue_date: "2026-02-04"
- subtotal: ৳3050
- vat_amount: ৳152.50
- total_amount: ৳3202.50
- status: "unpaid"

... and so on for up to 6 months
```

## Database Relationships:

```
customers (c_id)
    ↓
    └─→ customer_to_products (c_id, p_id)
            ↓
            ├─→ invoices (cp_id) [One-to-Many]
            └─→ products (p_id)
```

## Summary:

1. **Main storage**: `customer_to_products` table
2. **Related data**: `invoices` table (automatically created)
3. **Links to**: `customers` table and `products` table
4. **Auto-generated**: 
   - `cp_id` (primary key)
   - `customer_product_id` (unique identifier)
   - Multiple invoices for future billing periods
