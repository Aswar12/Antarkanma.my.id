# Product API Example Responses

## Get All Products
```http
GET /api/products
```

**Success Response:**
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Products retrieved successfully"
    },
    "data": [
        {
            "id": 1,
            "name": "Kipas Angin 16 inch",
            "description": "Kipas angin berkualitas dengan 3 kecepatan",
            "price": 250000,
            "category_id": 1,
            "merchant_id": 1,
            "status": "active",
            "created_at": "2024-01-06T22:13:50.000000Z",
            "updated_at": "2024-01-06T22:13:50.000000Z",
            "category": {
                "id": 1,
                "name": "Elektronik"
            },
            "merchant": {
                "id": 1,
                "name": "Toko Elektronik Segeri",
                "address": "Jl. Poros Segeri No. 123"
            },
            "galleries": [
                {
                    "id": 1,
                    "url": "product-galleries/image1.jpg"
                }
            ]
        }
    ]
}
```

## Get Product with Reviews
```http
GET /api/products/1/with-reviews
```

**Success Response:**
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Product details retrieved successfully"
    },
    "data": {
        "id": 1,
        "name": "Kipas Angin 16 inch",
        "description": "Kipas angin berkualitas dengan 3 kecepatan",
        "price": 250000,
        "category_id": 1,
        "merchant_id": 1,
        "status": "active",
        "reviews": [
            {
                "id": 1,
                "user_id": 5,
                "rating": 5,
                "comment": "Produk sangat bagus dan berkualitas!",
                "created_at": "2024-01-06T22:13:50.000000Z",
                "user": {
                    "id": 5,
                    "name": "John Doe"
                }
            }
        ],
        "average_rating": 4.5,
        "review_count": 10
    }
}
```

## Create Product
```http
POST /api/products
```

**Request Body:**
```json
{
    "name": "Rice Cooker 1.8L",
    "description": "Rice cooker dengan teknologi terbaru",
    "price": 350000,
    "category_id": 1,
    "merchant_id": 1,
    "status": "active"
}
```

**Success Response:**
```json
{
    "meta": {
        "code": 201,
        "status": "success",
        "message": "Product created successfully"
    },
    "data": {
        "id": 2,
        "name": "Rice Cooker 1.8L",
        "description": "Rice cooker dengan teknologi terbaru",
        "price": 350000,
        "category_id": 1,
        "merchant_id": 1,
        "status": "active",
        "created_at": "2024-01-06T22:15:30.000000Z",
        "updated_at": "2024-01-06T22:15:30.000000Z"
    }
}
```

## Add Gallery Images
```http
POST /api/products/1/gallery
```

**Request:**
- Multipart form data with image files

**Success Response:**
```json
{
    "meta": {
        "code": 201,
        "status": "success",
        "message": "Product gallery images added successfully"
    },
    "data": [
        {
            "id": 1,
            "product_id": 1,
            "url": "product-galleries/image1.jpg",
            "created_at": "2024-01-06T22:16:00.000000Z"
        }
    ]
}
```

## Search Products
```http
GET /api/products/search?q=kipas&category=1&min_price=200000&max_price=300000
```

**Success Response:**
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Products retrieved successfully"
    },
    "data": [
        {
            "id": 1,
            "name": "Kipas Angin 16 inch",
            "description": "Kipas angin berkualitas dengan 3 kecepatan",
            "price": 250000,
            "category_id": 1,
            "merchant_id": 1,
            "status": "active",
            "merchant": {
                "id": 1,
                "name": "Toko Elektronik Segeri"
            },
            "category": {
                "id": 1,
                "name": "Elektronik"
            }
        }
    ],
    "meta_data": {
        "total": 1,
        "per_page": 10,
        "current_page": 1,
        "last_page": 1
    }
}
```

## Create Product Review
```http
POST /api/reviews
```

**Request Body:**
```json
{
    "product_id": 1,
    "rating": 5,
    "comment": "Produk sangat bagus dan berkualitas!"
}
```

**Success Response:**
```json
{
    "meta": {
        "code": 201,
        "status": "success",
        "message": "Review created successfully"
    },
    "data": {
        "id": 1,
        "product_id": 1,
        "user_id": 5,
        "rating": 5,
        "comment": "Produk sangat bagus dan berkualitas!",
        "created_at": "2024-01-06T22:17:30.000000Z",
        "updated_at": "2024-01-06T22:17:30.000000Z"
    }
}
```

## Error Response Examples

### Validation Error
```json
{
    "meta": {
        "code": 422,
        "status": "error",
        "message": "The given data was invalid"
    },
    "errors": {
        "name": [
            "The name field is required"
        ],
        "price": [
            "The price must be at least 0"
        ]
    }
}
```

### Not Found Error
```json
{
    "meta": {
        "code": 404,
        "status": "error",
        "message": "Product not found"
    }
}
```

### Unauthorized Error
```json
{
    "meta": {
        "code": 401,
        "status": "error",
        "message": "Unauthenticated"
    }
}
