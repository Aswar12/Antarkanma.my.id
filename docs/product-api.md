# Product API Documentation

## Public Endpoints

### Get All Products
```http
GET /api/products
```
Get list of all products.

### Get Products by Category
```http
GET /api/products/category/{categoryId}
```
Get products filtered by category.

### Get Popular Products
```http
GET /api/products/popular
```
Get list of popular products.

### Get Top Products by Category
```http
GET /api/products/top-by-category
```
Get top-performing products grouped by category.

### Get Product with Reviews
```http
GET /api/products/{id}/with-reviews
```
Get detailed product information including reviews.

### Get Product Reviews
```http
GET /api/products/{productId}/reviews
```
Get all reviews for a specific product.

## Protected Endpoints (Requires Authentication)

### Create Product
```http
POST /api/products
```
Create a new product.

**Request Body:**
```json
{
    "name": "string",
    "description": "string",
    "price": "integer",
    "category_id": "integer",
    "merchant_id": "integer",
    "status": "string"
}
```

### Update Product
```http
PUT /api/products/{id}
```
Update an existing product.

### Delete Product
```http
DELETE /api/products/{id}
```
Delete a product.

## Product Gallery Management

### Add Gallery Images
```http
POST /api/products/{id}/gallery
```
Add images to product gallery.

### Edit Gallery Image
```http
PUT /api/products/{productId}/gallery/{galleryId}
```
Update a specific gallery image.

### Delete Gallery Image
```http
DELETE /api/products/{productId}/gallery/{galleryId}
```
Remove an image from product gallery.

## Product Variants

### Add Product Variant
```http
POST /api/products/{productId}/variants
```
Add a variant to a product.

### Update Variant
```http
PUT /api/variants/{variantId}
```
Update a product variant.

### Delete Variant
```http
DELETE /api/variants/{variantId}
```
Delete a product variant.

### Get Product Variants
```http
GET /api/products/{productId}/variants
```
Get all variants of a product.

### Get Specific Variant
```http
GET /api/variants/{variantId}
```
Get details of a specific variant.

## Product Search and Filtering

### Search Products
```http
GET /api/products/search
```
Search products with filters.

**Query Parameters:**
- `q`: Search query string
- `category`: Filter by category
- `min_price`: Minimum price
- `max_price`: Maximum price
- `sort`: Sort order (e.g., price_asc, price_desc)

### Get Merchant Products
```http
GET /api/merchants/{merchantId}/products
```
Get all products from a specific merchant.

## Product Reviews

### Create Review
```http
POST /api/reviews
```
Create a product review.

**Request Body:**
```json
{
    "product_id": "integer",
    "rating": "integer",
    "comment": "string"
}
```

### Update Review
```http
PUT /api/reviews/{id}
```
Update an existing review.

### Delete Review
```http
DELETE /api/reviews/{id}
```
Delete a review.

### Get User Reviews
```http
GET /api/user/reviews
```
Get all reviews by the authenticated user.

## Response Format

All API endpoints return responses in the following format:

```json
{
    "meta": {
        "code": "integer",
        "status": "string",
        "message": "string"
    },
    "data": {
        // Response data here
    }
}
```

## Error Responses

In case of errors, the API will return:

```json
{
    "meta": {
        "code": "integer",
        "status": "error",
        "message": "Error description"
    },
    "errors": {
        // Detailed error information
    }
}
```

Common error codes:
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Validation Error
- 500: Server Error
