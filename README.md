# api-crud
Simple API for CRUD

# usage

## Create an item

### Request
Headers:
- Authorization: Bearer <token>
POST /<entity>/ 
Json Body: {...} 

### Response

json: {..., id: <id>, c_at: <created_at>}

## Get an item

### Request

Headers:
- Authorization: Bearer <token>
GET /<entity>/<id>

### Response

json: {...}

## Update an item

### Request

Headers:
- Authorization: Bearer <token>
PUT /<entity>/<id>
Json Body: {...}

### Response

json: {...}

## Delete an item

### Request

Headers:
- Authorization: Bearer <token>
DELETE /<entity>/<id>

### Response

json: {"result": "success", "details": "Item deleted"}