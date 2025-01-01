# Merchant API Documentation

## Authentication

All API requests require authentication using a Bearer token in the Authorization header:

```
Authorization: Bearer <your_token>
```

## Products

### Update Product
```http
PUT /api/products/{id}
```

Request Body:
```json
{
    "name": "string",
    "description": "string",
    "price": "numeric",
    "category_id": "numeric",
    "status": "ACTIVE|INACTIVE|OUT_OF_STOCK",
    "variants": [
        {
            "name": "string",
            "price": "numeric",
            "stock": "numeric"
        }
    ]
}
```

Notes:
- All fields are optional - only include fields you want to update
- `variants` array is optional. If provided, all existing variants will be replaced with the new ones
- When updating variants, you must provide all variants as the existing ones will be deleted

Response (200 - Success):
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Product updated successfully"
    },
    "data": {
        "id": 1,
        "name": "Updated Product Name",
        "description": "Updated product description",
        "price": 150000,
        "category_id": 1,
        "merchant_id": 1,
        "status": "ACTIVE",
        "variants": [
            {
                "id": 1,
                "name": "Variant 1",
                "price": 160000,
                "stock": 10
            }
        ],
        "created_at": "2024-02-15T12:00:00.000000Z",
        "updated_at": "2024-02-15T13:00:00.000000Z"
    }
}
```

## Product Galleries

### Add Gallery Images
```http
POST /api/products/{id}/gallery
```

Request Body (multipart/form-data):
```
gallery[]: file (image)
gallery[]: file (image)
...
```

Response (200 - Success):
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Gallery images uploaded successfully"
    },
    "data": [
        {
            "id": 1,
            "url": "http://your-domain.com/storage/product_galleries/image1.jpg",
            "product_id": 1
        },
        {
            "id": 2,
            "url": "http://your-domain.com/storage/product_galleries/image2.jpg",
            "product_id": 1
        }
    ]
}
```

### Update Gallery Image
```http
PUT /api/products/{productId}/gallery/{galleryId}
```

Request Body (multipart/form-data):
```
gallery: file (image)
```

Response (200 - Success):
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Gallery image updated successfully"
    },
    "data": {
        "id": 1,
        "url": "storage/product_galleries/updated-image.jpg",
        "product_id": 1,
        "created_at": "2024-02-15T12:00:00.000000Z",
        "updated_at": "2024-02-15T13:00:00.000000Z"
    }
}
```

### Delete Gallery Image
```http
DELETE /api/products/{productId}/gallery/{galleryId}
```

Response (200 - Success):
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Gallery image deleted successfully"
    }
}
```

Error Responses:

404 - Not Found:
```json
{
    "meta": {
        "code": 404,
        "status": "error",
        "message": "Product or gallery not found"
    },
    "data": null
}
```

422 - Validation Error:
```json
{
    "meta": {
        "code": 422,
        "status": "error",
        "message": "Validation Error"
    },
    "data": {
        "gallery": [
            "Please select an image file",
            "Image must be jpeg, png, jpg, or gif",
            "Image size must not exceed 2MB"
        ]
    }
}
```

403 - Unauthorized:
```json
{
    "meta": {
        "code": 403,
        "status": "error",
        "message": "Unauthorized to perform this action"
    },
    "data": null
}
```

500 - Server Error:
```json
{
    "meta": {
        "code": 500,
        "status": "error",
        "message": "Failed to process request"
    },
    "data": null
}
```

## Important Notes

1. Image Upload Requirements:
   - Supported formats: JPEG, PNG, JPG, GIF
   - Maximum file size: 2MB per image
   - Multiple images can be uploaded at once using the gallery[] parameter

2. Performance Considerations:
   - Using get_all=true will return ALL products at once, which might be slow for large datasets
   - For better performance with large datasets:
     * Use pagination (default behavior) instead of get_all
     * Use appropriate limit values (default: 10)
     * Consider implementing frontend caching
   - Products are always ordered by created_at in descending order (newest first)

3. Authentication:
   - All endpoints require a valid Bearer token
   - Product updates and gallery operations require merchant ownership
   - Unauthorized attempts will return a 403 error

4. Product Variants:
   - When updating a product with variants, all existing variants will be replaced
   - The operation is wrapped in a transaction - if any part fails, all changes are rolled back
   - Variants are optional - if not provided, existing variants will remain unchanged
