# API Documentation untuk Merchant

## Autentikasi
Semua endpoint memerlukan token autentikasi yang dikirim melalui header `Authorization: Bearer {token}`.

## Endpoint Merchant

### Manajemen Profil Merchant
1. **Membuat Merchant Baru**
   - `POST /api/merchant`
   - Membuat profil merchant baru

2. **Mengambil Data Merchant**
   - `GET /api/merchant/{id}`
   - Mendapatkan detail merchant berdasarkan ID

3. **Memperbarui Data Merchant**
   - `PUT /api/merchant/{id}`
   - Memperbarui informasi merchant

## Manajemen Produk

### Produk
#### Request/Response Examples

1. **Membuat Produk Baru**
```http
POST /api/products
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Produk Baru",
    "description": "Deskripsi produk",
    "price": 100000,
    "category_id": 1,
    "merchant_id": 1,
    "status": "ACTIVE"
}
```

Response:
```json
{
    "meta": {
        "code": 201,
        "status": "success",
        "message": "Produk berhasil ditambahkan"
    },
    "data": {
        "id": 1,
        "name": "Produk Baru",
        "description": "Deskripsi produk",
        "price": 100000,
        "category_id": 1,
        "merchant_id": 1,
        "status": "ACTIVE",
        "created_at": "2024-01-20T10:00:00.000000Z",
        "updated_at": "2024-01-20T10:00:00.000000Z"
    }
}
```

2. **Mendapatkan Produk Merchant**
```http
GET /api/merchants/{merchantId}/products
Authorization: Bearer {token}
```

Response:
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
                "name": "Produk Baru",
                "description": "Deskripsi produk",
                "price": 100000,
                "status": "ACTIVE",
                "rating_info": {
                    "average_rating": 4.5,
                    "total_reviews": 10
                },
                "galleries": [
                    {
                        "id": 1,
                        "url": "https://example.com/image1.jpg"
                    }
                ]
            }
        ],
        "per_page": 10,
        "total": 1
    }
}
```

1. **Membuat Produk Baru**
   - `POST /api/products`
   - Menambahkan produk baru ke toko

2. **Memperbarui Produk**
   - `PUT /api/products/{id}`
   - Mengubah informasi produk yang ada

3. **Menghapus Produk**
   - `DELETE /api/products/{id}`
   - Menghapus produk dari toko

4. **Melihat Produk Merchant**
   - `GET /api/merchants/{merchantId}/products`
   - Mendapatkan daftar semua produk merchant

### Galeri Produk
#### Request/Response Examples

1. **Menambah Foto Produk**
```http
POST /api/products/{id}/gallery
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
    "photo": [file]
}
```

Response:
```json
{
    "meta": {
        "code": 201,
        "status": "success",
        "message": "Foto berhasil ditambahkan"
    },
    "data": {
        "id": 1,
        "product_id": 1,
        "url": "https://example.com/image1.jpg",
        "created_at": "2024-01-20T10:00:00.000000Z"
    }
}
```

1. **Menambah Foto Produk**
   - `POST /api/products/{id}/gallery`
   - Menambahkan foto ke galeri produk

2. **Menghapus Foto Produk**
   - `DELETE /api/galleries/{id}`
   - Menghapus foto dari galeri produk

### Varian Produk
#### Request/Response Examples

1. **Menambah Varian Produk**
```http
POST /api/products/{productId}/variants
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Ukuran",
    "value": "XL",
    "price_adjustment": 10000,
    "status": "ACTIVE"
}
```

Response:
```json
{
    "meta": {
        "code": 201,
        "status": "success",
        "message": "Varian berhasil ditambahkan"
    },
    "data": {
        "id": 1,
        "product_id": 1,
        "name": "Ukuran",
        "value": "XL",
        "price_adjustment": 10000,
        "status": "ACTIVE",
        "created_at": "2024-01-20T10:00:00.000000Z"
    }
}
```

1. **Menambah Varian**
   - `POST /api/products/{productId}/variants`
   - Menambahkan varian baru ke produk

2. **Memperbarui Varian**
   - `PUT /api/variants/{variantId}`
   - Mengubah informasi varian produk

3. **Menghapus Varian**
   - `DELETE /api/variants/{variantId}`
   - Menghapus varian dari produk

4. **Melihat Varian Produk**
   - `GET /api/products/{productId}/variants`
   - Mendapatkan daftar varian produk

## Manajemen Pesanan

### Pesanan
#### Request/Response Examples

1. **Melihat Daftar Pesanan Merchant**
```http
GET /api/merchant/orders
Authorization: Bearer {token}
```

Response:
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Data pesanan berhasil diambil"
    },
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "user_id": 1,
                "total_amount": 110000,
                "order_status": "PENDING",
                "created_at": "2024-01-20T10:00:00.000000Z",
                "items": [
                    {
                        "id": 1,
                        "product_id": 1,
                        "quantity": 1,
                        "price": 110000,
                        "product": {
                            "name": "Produk Baru",
                            "variant": {
                                "name": "Ukuran",
                                "value": "XL"
                            }
                        }
                    }
                ]
            }
        ],
        "per_page": 10,
        "total": 1
    }
}
```

2. **Memperbarui Status Pesanan**
```http
PUT /api/orders/{id}/status
Authorization: Bearer {token}
Content-Type: application/json

{
    "status": "PROCESSING"
}
```

Response:
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Status pesanan berhasil diperbarui"
    },
    "data": {
        "id": 1,
        "order_status": "PROCESSING",
        "updated_at": "2024-01-20T10:30:00.000000Z"
    }
}
```

1. **Melihat Daftar Pesanan**
   - `GET /api/merchant/orders`
   - Mendapatkan daftar pesanan untuk merchant

2. **Memperbarui Status Pesanan**
   - `PUT /api/orders/{id}/status`
   - Mengubah status pesanan

3. **Statistik Pesanan**
   - `GET /api/orders/statistics`
   - Mendapatkan statistik pesanan merchant

### Transaksi
1. **Melihat Transaksi Merchant**
   - `GET /api/merchants/{merchantId}/transactions`
   - Mendapatkan daftar transaksi merchant

## Ulasan Produk

1. **Melihat Ulasan Produk**
   - `GET /api/products/{productId}/reviews`
   - Mendapatkan daftar ulasan untuk produk tertentu

## Format Response

Semua API mengembalikan response dalam format JSON dengan struktur:

```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Data berhasil diambil"
    },
    "data": {
        // Data response sesuai endpoint
    }
}
```

## Status Codes
- 200: Sukses
- 201: Created (Berhasil membuat data baru)
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 500: Internal Server Error
