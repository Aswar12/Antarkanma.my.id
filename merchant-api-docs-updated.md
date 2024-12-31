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
    "merchant_id": "numeric",
    "status": "ACTIVE|INACTIVE|OUT_OF_STOCK"
}
```

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
