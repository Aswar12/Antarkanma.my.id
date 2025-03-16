# Merchant API Documentation

## Authentication
All API requests require authentication using a Bearer token in the Authorization header:
```
Authorization: Bearer <your_token>
```

## Merchant Logo Upload

### Update Merchant Logo
```http
POST /api/merchant/{id}/logo
```

#### Request
- Method: `POST`
- Endpoint: `/api/merchant/{id}/logo`
- Content-Type: `multipart/form-data`
- Authentication: Required

#### Parameters
| Name | Type | Required | Description |
|------|------|----------|-------------|
| logo | File | Yes | Image file (jpeg, png, jpg, gif, webp, heic). Max size: 20MB |

#### Example Request using cURL
```bash
curl -X POST \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "logo=@/path/to/logo.png" \
  https://dev.antarkanmaa.my.id/api/merchant/{merchant_id}/logo
```




#### Example Request using Axios
```javascript
const formData = new FormData();
formData.append('logo', logoFile);

const response = await axios.post(`/api/merchant/${merchantId}/logo`, formData, {
  headers: {
    'Content-Type': 'multipart/form-data',
    'Authorization': `Bearer ${token}`
  }
});
```

#### Successful Response
```json
{
  "meta": {
    "code": 200,
    "status": "success",
    "message": "Merchant logo updated successfully"
  },
  "data": {
    "id": 1,
    "name": "Merchant Name",
    "logo": "merchants/logos/merchant-1-12345.jpg",
    "logo_url": "https://is3.cloudhost.id/antarkanma/merchants/logos/merchant-1-12345.jpg",
    ...
  }
}
```

#### Error Responses

Invalid File Type
```json
{
  "meta": {
    "code": 422,
    "status": "error",
    "message": "The logo must be an image."
  },
  "data": null
}
```

File Too Large
```json
{
  "meta": {
    "code": 422,
    "status": "error",
    "message": "The logo must not be greater than 20480 kilobytes."
  },
  "data": null
}
```

Authentication Error
```json
{
  "meta": {
    "code": 401,
    "status": "error",
    "message": "Unauthenticated."
  },
  "data": null
}
```

### Important Notes
1. The image will be automatically:
   - Compressed to optimize file size
   - Resized if dimensions exceed 1920x1080
   - Converted to appropriate format if needed
2. Old logo will be automatically deleted when updating
3. The returned URL is a public S3 URL that can be used directly
4. Supported image formats: JPEG, PNG, JPG, GIF, WEBP, HEIC
5. Maximum file size: 20MB (will be compressed)
