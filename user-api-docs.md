# User API Documentation

## Authentication

All API requests require authentication using a Bearer token in the Authorization header:

```
Authorization: Bearer <your_token>
```

## Products

### Get All Products
```http
GET /api/products
```

Query Parameters:
- `get_all` (boolean, optional): Set to true to retrieve all products without pagination. Default: false
- `limit` (integer, optional): Number of items per page when paginated. Default: 6
- `page` (integer, optional): Page number when paginated. Default: 1
- `id` (integer, optional): Filter by product ID
- `name` (string, optional): Filter products by name (partial match)
- `description` (string, optional): Filter products by description (partial match)
- `tags` (string, optional): Filter products by tags
- `categories` (integer, optional): Filter products by category ID
- `price_from` (numeric, optional): Filter products with price greater than or equal to this value
- `price_to` (numeric, optional): Filter products with price less than or equal to this value

Example Requests:
```http
# Get all products without pagination
GET /api/products?get_all=true

# Get products with pagination (6 per page)
GET /api/products?page=1

# Get filtered products
GET /api/products?price_from=10000&price_to=100000&categories=1

# Search products by name
GET /api/products?name=laptop
```

Response (200 - Success) with get_all=true:
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Data produk berhasil diambil"
    },
    "data": [
        {
            "id": 1,
            "name": "Product Name",
            "description": "Product description",
            "price": 50000,
            "status": "ACTIVE",
            "merchant": {
                "id": 1,
                "name": "Merchant Name",
                "address": "Merchant Address"
            },
            "category": {
                "id": 1,
                "name": "Category Name"
            },
            "galleries": [
                {
                    "id": 1,
                    "url": "storage/product_galleries/image1.jpg"
                }
            ],
            "rating_info": {
                "average_rating": 4.5,
                "total_reviews": 10
            },
            "created_at": "2024-02-15T12:00:00.000000Z",
            "updated_at": "2024-02-15T13:00:00.000000Z"
        }
        // ... more products
    ]
}
```

Response (200 - Success) with pagination:
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Data produk berhasil diambil"
    },
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "name": "Product Name",
                "description": "Product description",
                "price": 50000,
                "status": "ACTIVE",
                "merchant": {
                    "id": 1,
                    "name": "Merchant Name",
                    "address": "Merchant Address"
                },
                "category": {
                    "id": 1,
                    "name": "Category Name"
                },
                "galleries": [
                    {
                        "id": 1,
                        "url": "storage/product_galleries/image1.jpg"
                    }
                ],
                "rating_info": {
                    "average_rating": 4.5,
                    "total_reviews": 10
                },
                "created_at": "2024-02-15T12:00:00.000000Z",
                "updated_at": "2024-02-15T13:00:00.000000Z"
            }
            // ... more products
        ],
        "first_page_url": "http://your-domain.com/api/products?page=1",
        "from": 1,
        "last_page": 5,
        "last_page_url": "http://your-domain.com/api/products?page=5",
        "next_page_url": "http://your-domain.com/api/products?page=2",
        "path": "http://your-domain.com/api/products",
        "per_page": 6,
        "prev_page_url": null,
        "to": 6,
        "total": 30
    }
}
```

### Get Products by Category
```http
GET /api/products/category/{categoryId}
```

Query Parameters:
- `limit` (integer, optional): Number of items per page. Default: 10
- `page` (integer, optional): Page number. Default: 1
- `price_from` (numeric, optional): Filter products with price greater than or equal to this value
- `price_to` (numeric, optional): Filter products with price less than or equal to this value

Example Request:
```http
GET /api/products/category/1?price_from=10000&price_to=100000
```

### Get Popular Products
```http
GET /api/products/popular
```

Query Parameters:
- `limit` (integer, optional): Number of items per page. Default: 12
- `category_id` (integer, optional): Filter by category
- `min_rating` (numeric, optional): Minimum average rating. Default: 4.0
- `min_reviews` (integer, optional): Minimum number of reviews. Default: 5

Example Request:
```http
GET /api/products/popular?category_id=1&min_rating=4.5
```

### Get Product with Reviews
```http
GET /api/products/{id}/with-reviews
```

Response includes:
- Product details
- Average rating
- Total reviews
- Rating distribution (1-5 stars)
- List of reviews with user details

Example Request:
```http
GET /api/products/1/with-reviews
```

Response (200 - Success):
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Data produk dan review berhasil diambil"
    },
    "data": {
        "id": 1,
        "name": "Product Name",
        "description": "Product description",
        "price": 50000,
        "status": "ACTIVE",
        "merchant": {
            "id": 1,
            "name": "Merchant Name"
        },
        "category": {
            "id": 1,
            "name": "Category Name"
        },
        "galleries": [
            {
                "id": 1,
                "url": "storage/product_galleries/image1.jpg"
            }
        ],
        "rating_info": {
            "average": 4.5,
            "total": 10,
            "distribution": {
                "5": 6,
                "4": 3,
                "3": 1,
                "2": 0,
                "1": 0
            }
        },
        "reviews": [
            {
                "id": 1,
                "user": {
                    "id": 1,
                    "name": "User Name"
                },
                "rating": 5,
                "comment": "Great product!",
                "created_at": "2024-02-15T12:00:00.000000Z"
            }
            // ... more reviews
        ]
    }
}
```

### Get Top Products by Category
```http
GET /api/products/top-by-category
```

Query Parameters:
- `limit` (integer, optional): Number of products per category. Default: 5
- `min_rating` (numeric, optional): Minimum average rating. Default: 4.0
- `min_reviews` (integer, optional): Minimum number of reviews. Default: 3

Example Request:
```http
GET /api/products/top-by-category?limit=3&min_rating=4.5
```

## Product Reviews

### Get Product Reviews
```http
GET /api/products/{productId}/reviews
```

Response includes:
- List of reviews with user details
- Rating and comment for each review

Example Request:
```http
GET /api/products/1/reviews
```

### Add Product Review
```http
POST /api/reviews
```

Request Body:
```json
{
    "product_id": "numeric|required",
    "rating": "numeric|required|min:1|max:5",
    "comment": "string|required"
}
```

### Update Product Review
```http
PUT /api/reviews/{id}
```

Request Body:
```json
{
    "rating": "numeric|min:1|max:5",
    "comment": "string"
}
```

### Delete Product Review
```http
DELETE /api/reviews/{id}
```

### Get User Reviews
```http
GET /api/user/reviews
```

Gets all reviews made by the authenticated user.

## Error Responses

404 - Not Found:
```json
{
    "meta": {
        "code": 404,
        "status": "error",
        "message": "Product not found"
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
        "rating": [
            "The rating field is required",
            "The rating must be between 1 and 5"
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
```

## Important Notes

1. Performance Considerations:
   - Using get_all=true will return ALL products at once, which might be slow for large datasets
   - For better performance with large datasets:
     * Use pagination (default behavior) instead of get_all
     * Use appropriate limit values
     * Consider implementing frontend caching
   - Products are ordered by created_at in descending order (newest first)

2. Authentication:
   - All review operations (add/update/delete) require authentication
   - Users can only modify their own reviews
   - Product listing endpoints are public and don't require authentication

3. Rate Limiting:
   - API requests are subject to rate limiting
   - Exceeded limits will return a 429 Too Many Requests response
