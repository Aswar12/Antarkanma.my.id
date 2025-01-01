# Merchant Transaction API Documentation

## Overview
This documentation covers the API endpoints for merchants to manage transactions, including accepting transactions, viewing transaction summaries, and managing transaction statuses.

## Authentication
All endpoints require authentication using a Bearer token in the Authorization header:
```
Authorization: Bearer <your_token>
```

## Endpoints

### 1. Accept Transaction
Accept a pending transaction for processing.

```http
PUT /api/merchant/transactions/{id}/accept
```

#### Path Parameters
- `id` (integer, required) - The ID of the transaction to accept

#### Response
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Transaction successfully accepted"
    },
    "data": {
        "id": 1,
        "status": "PROCESSING",
        "total_amount": 150000,
        "payment_status": "PAID",
        "order_items": [
            {
                "id": 1,
                "product_name": "Product A",
                "quantity": 2,
                "price": 75000,
                "subtotal": 150000
            }
        ],
        "updated_at": "2024-02-15T08:30:00Z",
        "created_at": "2024-02-15T08:00:00Z"
    }
}
```

### 2. Get Transaction Summary
Retrieve a summary of merchant's transactions.

```http
GET /api/merchant/transactions/summary
```

#### Query Parameters
- `start_date` (string, optional) - Start date for filtering (format: YYYY-MM-DD)
- `end_date` (string, optional) - End date for filtering (format: YYYY-MM-DD)
- `status` (string, optional) - Filter by transaction status (PENDING, PROCESSING, COMPLETED, CANCELLED)

#### Response
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Transaction summary retrieved successfully"
    },
    "data": {
        "total_transactions": 150,
        "total_amount": 7500000,
        "status_breakdown": {
            "PENDING": 20,
            "PROCESSING": 30,
            "COMPLETED": 90,
            "CANCELLED": 10
        },
        "daily_summary": [
            {
                "date": "2024-02-15",
                "total_transactions": 25,
                "total_amount": 1250000,
                "completed_transactions": 20,
                "cancelled_transactions": 5
            }
        ],
        "payment_method_breakdown": {
            "BANK_TRANSFER": 80,
            "E_WALLET": 50,
            "CREDIT_CARD": 20
        }
    }
}
```

### 3. Get Transaction List
Retrieve a list of transactions for the merchant.

```http
GET /api/merchant/transactions
```

#### Query Parameters
- `page` (integer, optional) - Page number for pagination (default: 1)
- `limit` (integer, optional) - Number of items per page (default: 10)
- `status` (string, optional) - Filter by transaction status
- `start_date` (string, optional) - Start date filter (YYYY-MM-DD)
- `end_date` (string, optional) - End date filter (YYYY-MM-DD)
- `sort` (string, optional) - Sort field (created_at, total_amount)
- `order` (string, optional) - Sort order (asc, desc)

#### Response
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Transactions retrieved successfully"
    },
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "order_id": "ORD001",
                "total_amount": 150000,
                "status": "COMPLETED",
                "payment_status": "PAID",
                "payment_method": "BANK_TRANSFER",
                "customer": {
                    "name": "John Doe",
                    "phone": "081234567890",
                    "email": "john@example.com"
                },
                "shipping_address": {
                    "address": "Jl. Example No. 123",
                    "city": "Jakarta",
                    "postal_code": "12345"
                },
                "items": [
                    {
                        "product_name": "Product A",
                        "quantity": 2,
                        "price": 75000,
                        "subtotal": 150000
                    }
                ],
                "created_at": "2024-02-15T08:00:00Z",
                "updated_at": "2024-02-15T08:30:00Z"
            }
        ],
        "total": 150,
        "per_page": 10,
        "last_page": 15
    }
}
```

### 4. Update Transaction Status
Update the status of a transaction.

```http
PUT /api/merchant/transactions/{id}/status
```

#### Path Parameters
- `id` (integer, required) - The ID of the transaction

#### Request Body
```json
{
    "status": "COMPLETED",
    "notes": "Order has been delivered successfully"
}
```

#### Response
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Transaction status updated successfully"
    },
    "data": {
        "id": 1,
        "status": "COMPLETED",
        "notes": "Order has been delivered successfully",
        "updated_at": "2024-02-15T09:00:00Z"
    }
}
```

## Error Responses

### 401 Unauthorized
```json
{
    "meta": {
        "code": 401,
        "status": "error",
        "message": "Unauthorized access"
    }
}
```

### 404 Not Found
```json
{
    "meta": {
        "code": 404,
        "status": "error",
        "message": "Transaction not found"
    }
}
```

### 422 Validation Error
```json
{
    "meta": {
        "code": 422,
        "status": "error",
        "message": "Validation failed"
    },
    "errors": {
        "status": ["The selected status is invalid"]
    }
}
```

## Transaction Status Flow
1. PENDING -> Initial state when order is created
2. PROCESSING -> After merchant accepts the transaction
3. COMPLETED -> When order is successfully delivered
4. CANCELLED -> If order is cancelled by either merchant or customer

## Notes
- All timestamps are in UTC format
- Amount values are in Indonesian Rupiah (IDR)
- Status changes are logged and can be tracked in the transaction history
- Merchants can only update transactions associated with their account
