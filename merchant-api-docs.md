# Merchant API Documentation

## Overview
This API allows merchants to manage their information, including creating, updating, retrieving, and deleting merchant records.

## Authentication
Authentication details (if applicable) should be provided here.

## Summary of Merchant API Endpoints
1. **GET /api/merchants**
   - Retrieves all merchants.

2. **POST /api/merchants**
   - Creates a new merchant.

3. **GET /api/merchants/{id}**
   - Retrieves a specific merchant by ID.

4. **PUT /api/merchants/{id}**
   - Updates an existing merchant.

5. **DELETE /api/merchants/{id}**
   - Deletes a specific merchant by ID.

6. **GET /api/merchants/owner/{id}**
   - Retrieves merchants by owner ID.


### 1. Retrieve All Merchants
- **Method**: GET
- **URL**: `/api/merchants`
- **Response**:
  - **200 OK**: 
    ```json
    {
      "status": "success",
      "data": [
        {
          "id": 1,
          "name": "Merchant Name",
          "address": "Merchant Address",
          ...
        }
      ],
      "message": "Merchants retrieved successfully"
    }
    ```

### 2. Create a New Merchant
- **Method**: POST
- **URL**: `/api/merchants`
- **Request Body**:
  ```json
  {
    "owner_id": 1,
    "name": "Merchant Name",
    "address": "Merchant Address",
    "phone_number": "1234567890",
    "status": "active",
    "description": "Merchant Description",
    "logo": "image_file",
    "opening_time": "09:00",
    "closing_time": "17:00",
    "operating_days": ["Monday", "Tuesday"]
  }
  ```
- **Response**:
  - **201 Created**: 
    ```json
    {
      "status": "success",
      "data": {
        "id": 1,
        "name": "Merchant Name",
        ...
      },
      "message": "Merchant created successfully"
    }
    ```
  - **400 Bad Request**: 
    ```json
    {
      "status": "error",
      "data": null,
      "message": "Failed to create merchant: Validation error message",
      "code": 500
    }
    ```

### 3. Retrieve a Specific Merchant
- **Method**: GET
- **URL**: `/api/merchants/{id}`
- **Response**:
  - **200 OK**: 
    ```json
    {
      "status": "success",
      "data": {
        "id": 1,
        "name": "Merchant Name",
        ...
      },
      "message": "Merchant details retrieved successfully"
    }
    ```
  - **404 Not Found**: 
    ```json
    {
      "status": "error",
      "data": null,
      "message": "Merchant not found",
      "code": 404
    }
    ```

### 4. Update an Existing Merchant
- **Method**: PUT
- **URL**: `/api/merchants/{id}`
- **Request Body**: (same structure as Create)
- **Response**:
  - **200 OK**: 
    ```json
    {
      "status": "success",
      "data": {
        "id": 1,
        "name": "Updated Merchant Name",
        ...
      },
      "message": "Merchant updated successfully"
    }
    ```

### 5. Delete a Specific Merchant
- **Method**: DELETE
- **URL**: `/api/merchants/{id}`
- **Response**:
  - **200 OK**: 
    ```json
    {
      "status": "success",
      "data": null,
      "message": "Merchant deleted successfully"
    }
    ```

### 6. Retrieve Merchants by Owner ID
- **Method**: GET
- **URL**: `/api/merchants/owner/{id}`
- **Response**:
  - **200 OK**: 
    ```json
    {
      "status": "success",
      "data": [
        {
          "id": 1,
          "name": "Merchant Name",
          ...
        }
      ],
      "message": "Merchant list by owner retrieved successfully"
    }
    ```

## Error Handling
Common error responses include:
- **400 Bad Request**: Validation errors.
- **404 Not Found**: Resource not found.
- **500 Internal Server Error**: General server error.
