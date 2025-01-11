# Dokumentasi API Order untuk Merchant

## Gambaran Umum
Dokumentasi ini menjelaskan endpoint API yang tersedia bagi merchant untuk mengambil data orderan yang mereka miliki.

## Autentikasi
Semua endpoint memerlukan token autentikasi yang dikirim melalui header:
```http
Authorization: Bearer {token}
```

## Endpoint

### 1. Mengambil Daftar Order

Mengambil daftar order yang dimiliki oleh merchant yang terautentikasi.

```http
GET /api/merchant/orders
```

#### Parameter Query

| Parameter | Tipe    | Deskripsi                                           | Wajib |
|-----------|---------|-----------------------------------------------------|-------|
| page      | integer | Nomor halaman untuk paginasi (default: 1)           | Tidak |
| status    | string  | Filter berdasarkan status order (PENDING/PROCESSING/COMPLETED/CANCELED) | Tidak |

#### Response Sukses (200)

```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Merchant orders retrieved successfully"
    },
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "order_status": "PENDING",
                "total_amount": 100000,
                "created_at": "2024-02-15T10:00:00.000000Z",
                "updated_at": "2024-02-15T10:00:00.000000Z",
                "user": {
                    "id": 1,
                    "name": "Nama Pelanggan",
                    "email": "customer@example.com",
                    "phone_number": "081234567890"
                },
                "items": [
                    {
                        "id": 1,
                        "product": {
                            "id": 1,
                            "name": "Nama Produk",
                            "price": 50000,
                            "description": "Deskripsi produk",
                            "galleries": [
                                {
                                    "id": 1,
                                    "url": "https://example.com/image.jpg"
                                }
                            ],
                            "variants": [
                                {
                                    "id": 1,
                                    "name": "Ukuran",
                                    "value": "XL",
                                    "price_adjustment": 5000
                                }
                            ]
                        },
                        "variant": {
                            "id": 1,
                            "name": "Ukuran",
                            "value": "XL",
                            "price_adjustment": 5000
                        },
                        "quantity": 2,
                        "price": 55000,
                        "subtotal": 110000
                    }
                ],
                "transaction": {
                    "id": 1,
                    "status": "PENDING",
                    "payment_status": "PENDING",
                    "payment_method": "MANUAL"
                }
            }
        ],
        "first_page_url": "http://antarkanma.my.id/api/merchant/orders?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http://antarkanma.my.id/api/merchant/orders?page=1",
        "next_page_url": null,
        "path": "http://antarkanma.my.id/api/merchant/orders",
        "per_page": 10,
        "prev_page_url": null,
        "to": 1,
        "total": 1
    }
}
```

### 2. Mengubah Status Order

Mengubah status dari sebuah order spesifik.

```http
PUT /api/orders/{order_id}/status
```

#### Parameter URL

| Parameter | Tipe    | Deskripsi      | Wajib |
|-----------|---------|----------------|-------|
| order_id  | integer | ID dari order  | Ya    |

#### Parameter Body

| Parameter | Tipe   | Deskripsi                                           | Wajib |
|-----------|--------|-----------------------------------------------------|-------|
| status    | string | Status baru (PENDING/PROCESSING/COMPLETED/CANCELED)  | Ya    |

#### Response Sukses (200)

```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Order status updated successfully"
    },
    "data": {
        "id": 1,
        "order_status": "PROCESSING",
        "total_amount": 100000,
        "created_at": "2024-02-15T10:00:00.000000Z",
        "updated_at": "2024-02-15T10:00:00.000000Z"
    }
}
```

### 3. Mengambil Ringkasan Order

Mengambil ringkasan statistik order untuk merchant yang terautentikasi.

```http
GET /api/merchant/orders/summary
```

#### Response Sukses (200)

```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Merchant order summary retrieved successfully"
    },
    "data": {
        "statistics": {
            "total_orders": 100,
            "pending_orders": 25,
            "processing_orders": 15,
            "completed_orders": 55,
            "canceled_orders": 5,
            "total_revenue": 5000000
        },
        "orders": {
            "pending": [
                {
                    "id": 1,
                    "status": "PENDING",
                    "total_amount": 110000,
                    "created_at": "2024-02-15T10:00:00.000000Z",
                    "customer": {
                        "id": 1,
                        "name": "Nama Pelanggan",
                        "phone_number": "081234567890"
                    },
                    "items": [
                        {
                            "id": 1,
                            "product": {
                                "id": 1,
                                "name": "Nama Produk",
                                "price": 50000,
                                "description": "Deskripsi produk",
                                "galleries": [
                                    {
                                        "id": 1,
                                        "url": "https://example.com/image.jpg"
                                    }
                                ],
                                "variants": [
                                    {
                                        "id": 1,
                                        "name": "Ukuran",
                                        "value": "XL",
                                        "price_adjustment": 5000
                                    }
                                ]
                            },
                            "variant": {
                                "id": 1,
                                "name": "Ukuran",
                                "value": "XL",
                                "price_adjustment": 5000
                            },
                            "quantity": 2,
                            "price": 55000,
                            "subtotal": 110000
                        }
                    ]
                }
            ],
            "processing": [],
            "completed": [],
            "canceled": []
        }
    }
}
```

### Response Error

#### Unauthorized (401)
```json
{
    "meta": {
        "code": 401,
        "status": "error",
        "message": "Unauthorized"
    },
    "data": null
}
```

#### Order Tidak Ditemukan (404)
```json
{
    "meta": {
        "code": 404,
        "status": "error",
        "message": "Order not found"
    },
    "data": null
}
```

## Status Order

Status order yang tersedia:
- PENDING: Order baru masuk dan menunggu diproses
- PROCESSING: Order sedang diproses oleh merchant
- COMPLETED: Order telah selesai
- CANCELED: Order dibatalkan

## Catatan Penggunaan

1. Pastikan selalu menyertakan token autentikasi di header request
2. Gunakan parameter query `status` untuk memfilter order berdasarkan statusnya
3. Data order diurutkan dari yang terbaru (descending berdasarkan created_at)
4. Setiap halaman menampilkan maksimal 10 order
5. Perubahan status order juga akan mengubah status transaksi terkait
6. Total revenue pada ringkasan order dihitung hanya dari order yang berstatus COMPLETED
7. Harga produk dalam order sudah termasuk penyesuaian harga dari varian yang dipilih
