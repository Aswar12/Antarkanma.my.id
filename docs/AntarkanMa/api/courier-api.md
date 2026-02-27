# Courier API Usage Documentation

## List of Routes

### 1. List All Couriers
- **HTTP Method:** GET
- **Endpoint:** `/api/couriers`
- **Description:** Retrieves a paginated list of all couriers in the system.
- **Parameters:**
  - `per_page` (optional): Number of items per page (default: 10)
  - `search` (optional): Search couriers by name or phone
- **Usage Example:**
  ```bash
  # List all couriers
  curl -X GET "http://your-api-url/api/couriers" \
  -H "Authorization: Bearer {your_token}"

  # Search couriers with pagination
  curl -X GET "http://your-api-url/api/couriers?search=john&per_page=20" \
  -H "Authorization: Bearer {your_token}"
  ```

### 2. Get Courier Transactions
- **HTTP Method:** GET
- **Endpoint:** `/api/courier/transactions`
- **Description:** Retrieves a list of transactions assigned to the authenticated courier.
- **Parameters:**
  - `per_page` (optional): Number of items per page (default: 10)
  - `status` (optional): Filter by transaction status
- **Usage Example:**
  ```bash
  # Get all transactions
  curl -X GET "http://your-api-url/api/courier/transactions" \
  -H "Authorization: Bearer {your_token}"

  # Get transactions with specific status
  curl -X GET "http://your-api-url/api/courier/transactions?status=pending" \
  -H "Authorization: Bearer {your_token}"
  ```

### 3. Approve Transaction
- **HTTP Method:** POST
- **Endpoint:** `/api/courier/transactions/{id}/approve`
- **Description:** Menyetujui dan mengambil transaksi oleh kurir. Setelah disetujui, notifikasi akan dikirim ke merchant terkait.
- **Response Messages:**
  - Success: "Transaksi berhasil disetujui"
  - Error (Invalid Status): "Transaksi tidak dapat disetujui: Status tidak valid"
  - Error (Already Taken): "Transaksi sudah diambil kurir lain"
- **Usage Example:**
  ```bash
  curl -X POST "http://your-api-url/api/courier/transactions/{transaction_id}/approve" \
  -H "Authorization: Bearer {your_token}"
  ```

### 4. Reject Transaction
- **HTTP Method:** POST
- **Endpoint:** `/api/courier/transactions/{id}/reject`
- **Description:** Rejects a transaction assigned to the courier.
- **Usage Example:**
  ```bash
  curl -X POST "http://your-api-url/api/courier/transactions/{transaction_id}/reject" \
  -H "Authorization: Bearer {your_token}"
  ```

### 5. Update Transaction Status
- **HTTP Method:** POST
- **Endpoint:** `/api/courier/transactions/{id}/status`
- **Description:** Updates the status of a specific transaction.
- **Required Parameters:**
  - `status`: New status for the transaction
- **Usage Example:**
  ```bash
  curl -X POST "http://your-api-url/api/courier/transactions/{transaction_id}/status" \
  -H "Authorization: Bearer {your_token}" \
  -H "Content-Type: application/json" \
  -d '{"status": "picked_up"}'
  ```

### 6. Create New Courier
- **HTTP Method:** POST
- **Endpoint:** `/api/couriers`
- **Description:** Creates a new courier in the system.
- **Required Parameters:**
  - `name`: Courier's full name
  - `phone`: Courier's phone number
  - `email`: Courier's email address
- **Usage Example:**
  ```bash
  curl -X POST "http://your-api-url/api/couriers" \
  -H "Authorization: Bearer {your_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "phone": "1234567890",
    "email": "john.doe@example.com"
  }'
  ```

### 7. Get Courier Details
- **HTTP Method:** GET
- **Endpoint:** `/api/couriers/{id}`
- **Description:** Retrieves details of a specific courier.
- **Usage Example:**
  ```bash
  curl -X GET "http://your-api-url/api/couriers/{courier_id}" \
  -H "Authorization: Bearer {your_token}"
  ```

### 8. Update Courier Information
- **HTTP Method:** PUT
- **Endpoint:** `/api/couriers/{id}`
- **Description:** Updates information for a specific courier.
- **Optional Parameters:**
  - `name`: Courier's full name
  - `phone`: Courier's phone number
  - `email`: Courier's email address
- **Usage Example:**
  ```bash
  curl -X PUT "http://your-api-url/api/couriers/{courier_id}" \
  -H "Authorization: Bearer {your_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Smith",
    "phone": "0987654321",
    "email": "john.smith@example.com"
  }'
  ```

### 9. Delete Courier
- **HTTP Method:** DELETE
- **Endpoint:** `/api/couriers/{id}`
- **Description:** Deletes a specific courier from the system.
- **Usage Example:**
  ```bash
  curl -X DELETE "http://your-api-url/api/couriers/{courier_id}" \
  -H "Authorization: Bearer {your_token}"
  ```

## Notes
- All endpoints require authentication using a Bearer token.
- Replace `{your_token}` with the actual authentication token.
- Replace `{transaction_id}` and `{courier_id}` with the respective IDs.
- All responses will be in JSON format with the following structure:
  ```json
  {
    "meta": {
      "code": 200,
      "status": "success",
      "message": "Operation successful"
    },
    "data": {
      // Response data here
    }
  }
  ```

## Error Handling
- All endpoints include proper error handling and will return appropriate error messages and status codes.
- Common HTTP status codes:
  - 200: Success
  - 400: Bad Request (invalid input)
  - 401: Unauthorized (invalid token)
  - 404: Not Found
  - 500: Server Error

This documentation provides a comprehensive guide for integrating and using the courier-related API endpoints effectively.
