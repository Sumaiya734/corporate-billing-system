# Fix SQL Error: Column 'is_active' not found in products table

## Tasks
- [x] Remove 'is_active' from the select statement in CustomerProductsController.php product relationship query

## Details
- The error occurs because the controller is trying to select 'is_active' from the 'products' table, but this column only exists in the 'customer_to_products' table.
- Removing 'is_active' from the select will fix the SQL error while maintaining functionality since is_active is already filtered at the CustomerProduct level.
