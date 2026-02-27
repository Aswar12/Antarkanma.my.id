# Prompt Penggunaan API FCM Token

## 1. Menyimpan/Memperbarui Token FCM
```
Endpoint: POST /api/fcm/token
Headers: 
- Authorization: Bearer {token_auth}
- Content-Type: application/json

Request Body:
{
    "token": "{token_fcm}",
    "device_type": "android"
}

Catatan:
- token_auth: Token autentikasi setelah login
- token_fcm: Token yang didapat dari Firebase
- device_type: Pilihan - "android", "ios", atau "web"

Response Sukses:
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "FCM token berhasil disimpan"
    },
    "data": {
        "token": "...",
        "device_type": "android",
        "is_active": true,
        "user_id": 1
    }
}
```

## 2. Menghapus Token FCM
```
Endpoint: DELETE /api/fcm/token
Headers:
- Authorization: Bearer {token_auth}
- Content-Type: application/json

Request Body:
{
    "token": "{token_fcm}"
}

Response Sukses:
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "FCM token berhasil dihapus"
    },
    "data": null
}
```

## 3. Berlangganan ke Topic
```
Endpoint: POST /api/fcm/topic/subscribe
Headers:
- Authorization: Bearer {token_auth}
- Content-Type: application/json

Request Body:
{
    "topic": "{nama_topic}"
}

Response Sukses:
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Berhasil subscribe ke topic"
    },
    "data": {
        "topic": "nama_topic"
    }
}
```

## Contoh Response Error
```json
{
    "meta": {
        "code": 500,
        "status": "error",
        "message": "Pesan error sesuai kesalahan"
    },
    "data": null
}
```

## Catatan Penting:
1. Semua endpoint memerlukan autentikasi (Bearer token)
2. Token FCM harus valid dari Firebase
3. Device type harus salah satu dari: android, ios, atau web
4. Setiap user bisa memiliki multiple token (untuk multiple device)
5. Token yang sama tidak bisa digunakan untuk user yang berbeda

## Kode Error:
- 400: Input tidak valid
- 401: Token autentikasi tidak valid/tidak ada
- 500: Error server
