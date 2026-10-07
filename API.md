# CariKostKita — API Specification

Base URL:
`https://api.example.com`

## Authentication
Gunakan Bearer Token.

Header:
```text
Authorization: Bearer {token}
Accept: application/json
```

## Public Endpoints
```text
POST /api/register
POST /api/login
GET  /api/kosts
GET  /api/kosts/{id}
```

## Authenticated Endpoints
```text
POST /api/logout
GET  /api/user
PUT  /api/user/profile
GET  /api/favorites
POST /api/favorites/{kost}
DELETE /api/favorites/{kost}
GET  /api/chats
POST /api/chats
GET  /api/chats/{chat}/messages
POST /api/chats/{chat}/messages
```

## Owner Endpoints
```text
POST   /api/owner/kosts
PUT    /api/owner/kosts/{id}
DELETE /api/owner/kosts/{id}

POST   /api/owner/kosts/{kost}/rooms
PUT    /api/owner/rooms/{room}
DELETE /api/owner/rooms/{room}
```

## Admin Endpoints
```text
GET    /api/admin/users
GET    /api/admin/kosts
GET    /api/admin/kosts/pending
PUT    /api/admin/kosts/{id}/approve
PUT    /api/admin/kosts/{id}/reject
DELETE /api/admin/kosts/{id}
```

## Search
Contoh:
```text
GET /api/kosts?search=mawar
GET /api/kosts?district=bukit-raya
GET /api/kosts?min_price=500000&max_price=1000000
GET /api/kosts?gender=putra
```

## Response
Success:
```json
{
  "success": true,
  "message": "Success",
  "data": {}
}
```

Validation error:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {}
}
```
