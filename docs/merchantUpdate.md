# Merchant Update API

## Endpoint
`PUT /api/merchants/{merchantId}`

## Description
Updates the details of an existing merchant.

## Request Body
The request body must be a JSON object with the following properties:

- `owner_id` (string, optional): The unique identifier for the owner.
- `name` (string, required): The name of the merchant.
- `address` (string, optional): The address of the merchant.
- `phone_number` (string, optional): The phone number of the merchant.
- `status` (string, optional): The status of the merchant.
- `description` (string, optional): The description of the merchant.
- `logo` (string, optional): The logo of the merchant.
- `opening_time` (string, optional): The opening time of the merchant.
- `closing_time` (string, optional): The closing time of the merchant.
- `operating_days` (array of strings, optional): The operating days of the merchant.

### Example Request
```json
{
  "owner_id": "123",
  "name": "Merchant Name",
  "address": "123 Merchant St.",
  "phone_number": "555-555-5555",
  "status": "active",
  "description": "A description of the merchant",
  "logo": "logo.png",
  "opening_time": "08:00",
  "closing_time": "18:00",
  "operating_days": ["Monday", "Tuesday", "Wednesday"]
}
```

## Responses

### 200 OK
The merchant was successfully updated.

### 400 Bad Request
The request was invalid. This can happen if required fields are missing or if the data types are incorrect.

### 404 Not Found
The merchant with the specified ID was not found.

### 500 Internal Server Error
An error occurred on the server.

## Example Response
```json
{
  "status": "success",
  "message": "Merchant updated successfully"
}
```
