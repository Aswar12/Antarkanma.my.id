# Antarkanma API Documentation

[Previous content remains the same until Merchant Management section]

## Merchant Management

### Get All Merchants
```http
GET /api/merchants
```
Get list of all merchants.

**Response Data:**
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Merchants retrieved successfully"
    },
    "data": [
        {
            "id": "integer",
            "owner_id": "integer",
            "name": "string",
            "address": "string",
            "phone_number": "string",
            "status": "string",
            "description": "string",
            "logo": "string",
            "opening_time": "time",
            "closing_time": "time",
            "operating_days": "string",
            "created_at": "timestamp",
            "updated_at": "timestamp"
        }
    ]
}
```

### Create Merchant
```http
POST /api/merchants
```
Create a new merchant.

**Required:** Authentication token

**Request Body:**
```json
{
    "owner_id": "integer",
    "name": "string",
    "address": "string",
    "phone_number": "string",
    "status": "string",
    "description": "string (optional)",
    "logo": "file (optional)",
    "opening_time": "time (optional)",
    "closing_time": "time (optional)",
    "operating_days": "array (optional)"
}
```

**Validation Rules:**
- owner_id: required, must exist in users table
- name: required, string, max 255 characters
- address: required, string, max 255 characters
- phone_number: required, string, max 15 characters
- status: required, string
- logo: optional, image file (jpeg, png, jpg, gif), max 2MB
- opening_time: optional, format H:i
- closing_time: optional, format H:i
- operating_days: optional, array of strings

### Get Merchant Detail
```http
GET /api/merchants/{id}
```
Get detailed information about a specific merchant.

**Response Data:**
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Merchant details retrieved successfully"
    },
    "data": {
        "id": "integer",
        "owner_id": "integer",
        "name": "string",
        "address": "string",
        "phone_number": "string",
        "status": "string",
        "description": "string",
        "logo": "string",
        "opening_time": "time",
        "closing_time": "time",
        "operating_days": "string",
        "created_at": "timestamp",
        "updated_at": "timestamp"
    }
}
```

### Update Merchant
```http
PUT /api/merchants/{id}
```
Update an existing merchant.

**Required:** Authentication token

**Request Body:**
```json
{
    "owner_id": "integer (optional)",
    "name": "string (optional)",
    "address": "string (optional)",
    "phone_number": "string (optional)",
    "status": "string (optional)",
    "description": "string (optional)",
    "logo": "file (optional)",
    "opening_time": "time (optional)",
    "closing_time": "time (optional)",
    "operating_days": "array (optional)"
}
```

### Delete Merchant
```http
DELETE /api/merchants/{id}
```
Delete a merchant.

**Required:** Authentication token

### Get Merchant by Owner
```http
GET /api/merchants/owner/{id}
```
Get all merchants owned by a specific user.

**Response Data:**
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Merchant list by owner retrieved successfully"
    },
    "data": [
        {
            "id": "integer",
            "owner_id": "integer",
            "name": "string",
            "address": "string",
            "phone_number": "string",
            "status": "string",
            "description": "string",
            "logo": "string",
            "opening_time": "time",
            "closing_time": "time",
            "operating_days": "string",
            "created_at": "timestamp",
            "updated_at": "timestamp",
            "product_count": "integer",
            "order_count": "integer",
            "products_sold": "integer",
            "total_sales": "float",
            "monthly_revenue": "float"
        }
    ]
}
```

[Rest of the documentation remains the same]
