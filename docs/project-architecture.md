# Corporate Billing System - Project Architecture

## Overview
This document provides a visual representation of the corporate billing system architecture, showing the relationships between key components.

## Entity Relationship Diagram

```mermaid
erDiagram
    CUSTOMER ||--o{ CUSTOMER_PRODUCT : assigns
    PRODUCT ||--o{ CUSTOMER_PRODUCT : "is assigned to"
    CUSTOMER_PRODUCT ||--o{ INVOICE : generates
    INVOICE ||--o{ PAYMENT : receives
    CUSTOMER_PRODUCT ||--|| PRODUCT_TYPE : categorizes
    CUSTOMER ||--|| USER : authenticates
    
    CUSTOMER {
        int c_id PK
        string customer_id
        string name
        string email
        string phone
        boolean is_active
        string address
    }
    
    PRODUCT {
        int p_id PK
        string name
        string description
        decimal monthly_price
        int product_type_id FK
    }
    
    PRODUCT_TYPE {
        int id PK
        string name
        string description
    }
    
    CUSTOMER_PRODUCT {
        int cp_id PK
        int c_id FK
        int p_id FK
        date assign_date
        int billing_cycle_months
        decimal custom_price
        date due_date
        string status
        boolean is_active
    }
    
    INVOICE {
        int invoice_id PK
        string invoice_number
        int cp_id FK
        date issue_date
        decimal previous_due
        decimal subtotal
        decimal total_amount
        decimal received_amount
        string status
        boolean is_closed
    }
    
    PAYMENT {
        int payment_id PK
        int invoice_id FK
        decimal amount
        date payment_date
        string payment_method
        string transaction_id
    }
    
    USER {
        int id PK
        string name
        string email
        string password
        string role
    }
```

## System Architecture Diagram

```mermaid
graph TD
    A[Web Browser] --> B[Apache/Nginx Server]
    B --> C[Laravel Application]
    
    C --> D[Controllers Layer]
    C --> E[Models Layer]
    C --> F[Views Layer]
    C --> G[Database Layer]
    
    D --> H[Admin Controllers]
    D --> I[Customer Controllers]
    D --> J[API Controllers]
    
    E --> K[Customer Model]
    E --> L[Product Model]
    E --> M[CustomerProduct Model]
    E --> N[Invoice Model]
    E --> O[Payment Model]
    E --> P[User Model]
    
    F --> Q[Admin Views]
    F --> R[Customer Views]
    F --> S[Shared Components]
    
    G --> T[MySQL Database]
    
    H --> K
    H --> L
    H --> M
    H --> N
    H --> O
    H --> P
    
    K --> T
    L --> T
    M --> T
    N --> T
    O --> T
    P --> T
    
    Q --> H
    R --> I
    S --> F
```

## Key Features Module Diagram

```mermaid
graph LR
    A[Billing System] --> B[Customer Management]
    A --> C[Product Management]
    A --> D[Billing & Invoicing]
    A --> E[Payment Processing]
    A --> F[Reporting]
    A --> G[Dashboard]
    
    B --> B1[Customer Registration]
    B --> B2[Profile Management]
    B --> B3[Customer Status]
    
    C --> C1[Product Creation]
    C --> C2[Product Types]
    C --> C3[Pricing Management]
    
    D --> D1[Invoice Generation]
    D --> D2[Monthly Billing]
    D --> D3[Billing Cycles]
    D --> D4[Due Management]
    
    E --> E1[Payment Recording]
    E --> E2[Payment Methods]
    E --> E3[Transaction Tracking]
    
    F --> F1[Revenue Reports]
    F --> F2[Collection Reports]
    F --> F3[Customer Analytics]
    
    G --> G1[Statistics Cards]
    G --> G2[Charts]
    G --> G3[Recent Activity]
```

## Billing Flow Diagram

```mermaid
graph TD
    A[Assign Product to Customer] --> B[Generate Monthly Invoice]
    B --> C[Carry Forward Previous Due]
    C --> D[Calculate New Charges]
    D --> E[Issue Invoice]
    E --> F[Receive Payment]
    F --> G[Update Invoice Status]
    G --> H[Carry Forward Remaining Due]
    H --> I[Close Billing Month]
    I --> J[Generate Next Month Invoice]
    
    subgraph "Billing Cycle"
        B
        C
        D
        E
        F
        G
        H
    end
```

## Directory Structure

```
corporate-billing-system/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/
│   │   │   ├── BillingController.php
│   │   │   ├── CustomerController.php
│   │   │   ├── ProductController.php
│   │   │   ├── CustomerProductController.php
│   │   │   └── DashboardController.php
│   │   └── Customer/
│   ├── Models/
│   │   ├── Customer.php
│   │   ├── Product.php
│   │   ├── CustomerProduct.php
│   │   ├── Invoice.php
│   │   └── Payment.php
│   └── Services/
├── resources/views/
│   ├── admin/
│   │   ├── billing/
│   │   ├── customers/
│   │   ├── products/
│   │   └── dashboard.blade.php
│   └── customer/
├── routes/
│   └── web.php
├── database/
│   └── migrations/
└── public/
```

## Technology Stack

- **Backend**: Laravel PHP Framework
- **Frontend**: Blade Templates, Bootstrap, jQuery
- **Database**: MySQL
- **Build Tool**: Vite
- **Authentication**: Laravel Auth
- **Styling**: Sass/CSS

## Key Relationships

1. **Customer - Product**: Many-to-many through CustomerProduct pivot table
2. **CustomerProduct - Invoice**: One-to-many (one customer product can generate multiple invoices)
3. **Invoice - Payment**: One-to-many (one invoice can receive multiple payments)
4. **Product - ProductType**: Many-to-one (many products belong to one type)
5. **Customer - User**: One-to-one (each customer has one user account for authentication)

This architecture supports flexible billing cycles, automatic due carry-forward, and comprehensive reporting capabilities.