# API Pembatalan Transaksi

## 1. Pembatalan oleh User

### Endpoint
```
PUT /api/transactions/{id}/cancel
```

### Deskripsi
API ini memungkinkan user untuk membatalkan transaksi mereka. Pembatalan hanya dapat dilakukan jika status transaksi masih PENDING.

### Parameter URL
- `id` (required): ID dari transaksi yang akan dibatalkan

### Headers
```
Accept: application/json
Authorization: Bearer {token}
```

### Response

#### Success Response (200 OK)
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Transaksi berhasil dibatalkan"
    },
    "data": {
        "id": 1,
        "user_id": 1,
        "total_price": 50000,
        "shipping_price": 10000,
        "status": "CANCELED",
        "payment_method": "MANUAL",
        "payment_status": "PENDING",
        "created_at": "2024-02-20T10:00:00.000000Z",
        "updated_at": "2024-02-20T10:30:00.000000Z",
        "order": {
            "id": 1,
            "order_status": "CANCELED",
            "notes": null,
            "order_items": [...]
        }
    }
}
```

#### Error Responses

##### Validation Error (422)
```json
{
    "meta": {
        "code": 422,
        "status": "error",
        "message": "Validation error"
    },
    "data": {
        "status": ["Transaksi tidak dapat dibatalkan"]
    }
}
```

##### Transaction Not Found (404)
```json
{
    "meta": {
        "code": 404,
        "status": "error",
        "message": "Transaksi tidak ditemukan"
    },
    "data": null
}
```

##### Unauthorized (401)
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

### Aturan Bisnis untuk User
1. User hanya dapat membatalkan transaksi miliknya sendiri
2. Pembatalan hanya dapat dilakukan jika status transaksi masih PENDING
3. Setelah dibatalkan:
   - Status transaksi akan berubah menjadi "CANCELED"
   - Status order akan berubah menjadi "CANCELED"
4. Transaksi yang sudah dibatalkan tidak dapat diubah kembali

### Contoh Penggunaan untuk User

#### cURL
```bash
curl -X PUT \
  'https://your-domain.com/api/transactions/1/cancel' \
  -H 'Authorization: Bearer your_token' \
  -H 'Accept: application/json'
```

#### PHP
```php
$client = new \GuzzleHttp\Client();
$response = $client->put(
    'https://your-domain.com/api/transactions/1/cancel',
    [
        'headers' => [
            'Authorization' => 'Bearer your_token',
            'Accept' => 'application/json'
        ]
    ]
);
```

#### JavaScript/Fetch
```javascript
const response = await fetch('https://your-domain.com/api/transactions/1/cancel', {
    method: 'PUT',
    headers: {
        'Authorization': 'Bearer your_token',
        'Accept': 'application/json'
    }
});
const data = await response.json();
```

## 2. Pembatalan oleh Merchant

### Endpoint
```
PUT /api/transactions/{orderId}/merchant/{merchantId}/status
```

### Deskripsi
API ini memungkinkan merchant untuk membatalkan transaksi. Pembatalan hanya dapat dilakukan jika status transaksi masih PENDING.

### Parameter URL
- `orderId` (required): ID dari order yang akan dibatalkan
- `merchantId` (required): ID dari merchant yang melakukan pembatalan

### Headers
```
Accept: application/json
Authorization: Bearer {token}
```

### Request Body
```json
{
    "status": "CANCELED",
    "notes": "Alasan pembatalan (opsional)"
}
```

### Parameter Request Body
- `status` (required): Harus bernilai "CANCELED"
- `notes` (optional): Catatan atau alasan pembatalan

### Response

#### Success Response (200 OK)
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Order status updated successfully"
    },
    "data": {
        "id": 1,
        "user_id": 1,
        "total_amount": 50000,
        "order_status": "CANCELED",
        "notes": "Stok habis",
        "created_at": "2024-02-20T10:00:00.000000Z",
        "updated_at": "2024-02-20T10:30:00.000000Z",
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "081234567890"
        },
        "order_items": [...],
        "transaction": {
            "id": 1,
            "status": "CANCELED",
            "user_location": {...}
        }
    }
}
```

#### Error Responses

##### Validation Error (422)
```json
{
    "meta": {
        "code": 422,
        "status": "error",
        "message": "Validation error"
    },
    "data": {
        "status": ["The status field is required"]
    }
}
```

##### Order Not Found (404)
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

##### Server Error (500)
```json
{
    "meta": {
        "code": 500,
        "status": "error",
        "message": "Failed to update order status: {error_message}"
    },
    "data": null
}
```

### Aturan Bisnis untuk Merchant
1. Hanya merchant yang memiliki order tersebut yang dapat melakukan pembatalan
2. Pembatalan akan mengubah:
   - Status order menjadi "CANCELED"
   - Status transaksi menjadi "CANCELED"
3. Setelah dibatalkan, status tidak dapat diubah kembali
4. Notes (alasan pembatalan) bersifat opsional

### Contoh Penggunaan untuk Merchant

#### cURL
```bash
curl -X PUT \
  'https://your-domain.com/api/transactions/1/merchant/2/status' \
  -H 'Authorization: Bearer your_token' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{
    "status": "CANCELED",
    "notes": "Stok produk habis"
}'
```

#### PHP
```php
$client = new \GuzzleHttp\Client();
$response = $client->put(
    'https://your-domain.com/api/transactions/1/merchant/2/status',
    [
        'headers' => [
            'Authorization' => 'Bearer your_token',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ],
        'json' => [
            'status' => 'CANCELED',
            'notes' => 'Stok produk habis'
        ]
    ]
);
```

#### JavaScript/Fetch
```javascript
const response = await fetch('https://your-domain.com/api/transactions/1/merchant/2/status', {
    method: 'PUT',
    headers: {
        'Authorization': 'Bearer your_token',
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        status: 'CANCELED',
        notes: 'Stok produk habis'
    })
});
const data = await response.json();
```

## 3. Status Transaksi

### Status yang Tersedia
Berdasarkan migration file, transaksi memiliki beberapa status:
- `PENDING`: Status awal transaksi
- `COMPLETED`: Transaksi telah selesai
- `CANCELED`: Transaksi dibatalkan

### Status Pembayaran
- `PENDING`: Menunggu pembayaran
- `COMPLETED`: Pembayaran berhasil
- `FAILED`: Pembayaran gagal

### Metode Pembayaran
- `MANUAL`: Pembayaran manual
- `ONLINE`: Pembayaran online

## 4. Catatan Penting

1. Transaksi hanya dapat dibatalkan jika statusnya masih `PENDING`
2. Setelah transaksi dibatalkan:
   - Status tidak dapat diubah kembali
   - Pembayaran yang sudah dilakukan akan diproses untuk refund (jika ada)
3. Pembatalan akan mempengaruhi:
   - Status transaksi
   - Status order
   - Catatan penjualan merchant
4. Pastikan untuk selalu menyertakan token autentikasi yang valid dalam header request
5. Semua response akan menggunakan format yang konsisten dengan struktur meta dan data
