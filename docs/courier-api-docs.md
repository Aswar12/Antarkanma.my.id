# Dokumentasi API Kurir

## Autentikasi
Semua endpoint memerlukan token Bearer untuk autentikasi. Token harus disertakan di header:
```
Authorization: Bearer {token}
```

## 1. Daftar Transaksi Kurir

### Endpoint
```http
GET /api/courier/transactions
```

### Query Parameters
- `limit` (optional): Jumlah item per halaman (default: 10)
- `status` (optional): Filter berdasarkan status transaksi (PENDING/COMPLETED/CANCELED)

### Response Success
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Data list transaksi berhasil diambil"
    },
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "user_id": 123,
                "courier_id": 456,
                "total_price": "150000.00",
                "shipping_price": "15000.00",
                "status": "PENDING",
                "payment_method": "MANUAL",
                "payment_status": "PENDING",
                "courier_approval": "APPROVED",
                "created_at": "2024-02-20T10:00:00.000000Z",
                "updated_at": "2024-02-20T10:00:00.000000Z",
                "user": {
                    "id": 123,
                    "name": "John Doe",
                    "phone": "081234567890"
                },
                "user_location": {
                    "id": 789,
                    "address": "Jl. Contoh No. 123",
                    "city": "Jakarta",
                    "postal_code": "12345"
                },
                "orders": [
                    {
                        "id": 1,
                        "merchant_id": 234,
                        "total_amount": "75000.00",
                        "order_status": "PROCESSING",
                        "merchant": {
                            "id": 234,
                            "name": "Merchant A",
                            "address": "Jl. Merchant No. 1"
                        },
                        "order_items": [
                            {
                                "id": 1,
                                "product_id": 567,
                                "quantity": 2,
                                "price": "37500.00",
                                "product": {
                                    "id": 567,
                                    "name": "Product X"
                                }
                            }
                        ]
                    }
                ]
            }
        ],
        "per_page": 10,
        "total": 1
    }
}
```

### Error Response - Akun Kurir Tidak Ditemukan
```json
{
    "meta": {
        "code": 404,
        "status": "error",
        "message": "Akun kurir tidak ditemukan"
    },
    "data": null
}
```

## 2. Detail Transaksi

### Endpoint
```http
GET /api/courier/transactions/{id}
```

### Response Success
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Data transaksi berhasil diambil"
    },
    "data": {
        "id": 1,
        "user_id": 123,
        "courier_id": 456,
        "total_price": "150000.00",
        "shipping_price": "15000.00",
        "status": "PENDING",
        "payment_method": "MANUAL",
        "payment_status": "PENDING",
        "courier_approval": "APPROVED",
        "created_at": "2024-02-20T10:00:00.000000Z",
        "updated_at": "2024-02-20T10:00:00.000000Z",
        "user": {
            "id": 123,
            "name": "John Doe",
            "phone": "081234567890"
        },
        "user_location": {
            "id": 789,
            "address": "Jl. Contoh No. 123",
            "city": "Jakarta",
            "postal_code": "12345"
        },
        "orders": [
            {
                "id": 1,
                "merchant_id": 234,
                "total_amount": "75000.00",
                "order_status": "PROCESSING",
                "merchant": {
                    "id": 234,
                    "name": "Merchant A",
                    "address": "Jl. Merchant No. 1"
                },
                "order_items": [
                    {
                        "id": 1,
                        "product_id": 567,
                        "quantity": 2,
                        "price": "37500.00",
                        "product": {
                            "id": 567,
                            "name": "Product X"
                        }
                    }
                ]
            }
        ]
    }
}
```

### Error Response - Transaksi Tidak Ditemukan
```json
{
    "meta": {
        "code": 404,
        "status": "error",
        "message": "Data transaksi tidak ditemukan"
    },
    "data": null
}
```

## 3. Approve Transaksi

### Endpoint
```http
POST /api/courier/transactions/{id}/approve
```

### Response Success
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Transaksi berhasil diapprove"
    },
    "data": {
        "id": 1,
        "status": "PENDING",
        "courier_approval": "APPROVED",
        "courier_id": 456
    }
}
```

### Error Response - Transaksi Timeout
```json
{
    "meta": {
        "code": 400,
        "status": "error",
        "message": "Transaksi sudah timeout"
    },
    "data": null
}
```

### Error Response - Tidak Dapat Diapprove
```json
{
    "meta": {
        "code": 400,
        "status": "error",
        "message": "Transaksi tidak dapat diapprove"
    },
    "data": null
}
```

## 4. Reject Transaksi

### Endpoint
```http
POST /api/courier/transactions/{id}/reject
```

### Response Success
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Transaksi berhasil direject"
    },
    "data": {
        "id": 1,
        "status": "CANCELED",
        "courier_approval": "REJECTED"
    }
}
```

### Error Response - Tidak Dapat Direject
```json
{
    "meta": {
        "code": 400,
        "status": "error",
        "message": "Transaksi tidak dapat direject"
    },
    "data": null
}
```

## 5. Update Status Order

### Endpoint
```http
POST /api/courier/orders/{id}/status
```

### Request Body
```json
{
    "status": "PICKED_UP"  // atau "COMPLETED"
}
```

### Response Success
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Status order berhasil diperbarui"
    },
    "data": {
        "id": 1,
        "order_status": "PICKED_UP",
        "updated_at": "2024-02-20T10:30:00.000000Z"
    }
}
```

### Error Response - Status Tidak Valid
```json
{
    "meta": {
        "code": 400,
        "status": "error",
        "message": "Status tidak valid"
    },
    "data": null
}
```

### Error Response - Perubahan Status Tidak Valid
```json
{
    "meta": {
        "code": 400,
        "status": "error",
        "message": "Perubahan status tidak valid"
    },
    "data": null
}
```

## Catatan Penting

1. Status Flow Order:
   - READY -> PICKED_UP (Kurir mengambil pesanan)
   - PICKED_UP -> COMPLETED (Kurir menyelesaikan pengantaran)

2. Validasi:
   - Approve/reject transaksi hanya bisa dilakukan dalam 5 menit setelah transaksi dibuat
   - Update status order hanya bisa dilakukan untuk order yang dimiliki oleh kurir tersebut
   - Status order harus mengikuti flow yang valid

3. Kode Status HTTP:
   - 200: Sukses
   - 400: Bad Request (input tidak valid, status tidak valid, dll)
   - 404: Data tidak ditemukan
   - 500: Internal Server Error

4. Format Error Response:
```json
{
    "meta": {
        "code": 400,
        "status": "error",
        "message": "Pesan error spesifik"
    },
    "data": null
}
