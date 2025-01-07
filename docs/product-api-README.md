# Product API Documentation Guide

This directory contains comprehensive documentation for the Antarkanma Product API. The documentation is split into two main files for better organization and readability:

## 1. [Product API Documentation](product-api.md)
Contains the complete API reference including:
- All available endpoints
- Request/response formats
- Authentication requirements
- Error handling
- Parameter descriptions

## 2. [Product API Examples](product-api-examples.md)
Contains detailed examples including:
- Sample requests and responses
- Real-world use cases
- Error response examples
- Complete JSON payloads

## Quick Start

### Authentication
Most endpoints require authentication using a Bearer token. Include the token in your request headers:
```http
Authorization: Bearer your_token_here
```

### Common Operations

1. **List Products**
   ```http
   GET /api/products
   ```

2. **Create Product**
   ```http
   POST /api/products
   Content-Type: application/json

   {
       "name": "Product Name",
       "description": "Product Description",
       "price": 100000,
       "category_id": 1,
       "merchant_id": 1,
       "status": "active"
   }
   ```

3. **Add Product Images**
   ```http
   POST /api/products/{id}/gallery
   Content-Type: multipart/form-data

   images[]: file1
   images[]: file2
   ```

4. **Search Products**
   ```http
   GET /api/products/search?q=keyword&category=1
   ```

## Response Format
All API responses follow a consistent format:
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Operation description"
    },
    "data": {
        // Response data here
    }
}
```

## Error Handling
The API uses standard HTTP status codes and includes detailed error messages:
```json
{
    "meta": {
        "code": 400,
        "status": "error",
        "message": "Error description"
    },
    "errors": {
        // Detailed error information
    }
}
```

## Best Practices
1. Always check the response status code and message
2. Handle pagination for list endpoints
3. Implement proper error handling
4. Cache responses when appropriate
5. Use proper content types for file uploads

## Need Help?
- Check the example responses in [product-api-examples.md](product-api-examples.md)
- Ensure you're using the correct authentication
- Verify all required parameters are included
- Check error responses for detailed information

## API Versioning
The current version of the API is v1. All endpoints are prefixed with `/api/`.
