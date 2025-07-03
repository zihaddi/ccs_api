# Media Content API Endpoints

The Media Content API is already implemented and ready to use. Here are the available endpoints:

## Base URL
- Admin endpoints: `/api/admin/media-contents`
- Public endpoints: `/api/media-contents` (CMS)

## Admin Endpoints (Requires Authentication)

### 1. Create Media Content
**POST** `/api/admin/media-contents`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "title": "Breaking News Report",
  "slug": "breaking-news-report", // Optional - auto-generated from title if not provided
  "description": "Latest breaking news coverage",
  "content_type": "news",
  "video_url": "https://example.com/video.mp4",
  "featured_image": "data:image/jpeg;base64,/9j/4AAQ...", // Base64 or URL
  "tags": ["news", "breaking", "report"],
  "is_featured": true,
  "status": true,
  "view_count": 1
}
```

**Response:**
```json
{
    "status": true,
    "message": "Created successfully.",
    "data": {
        "id": 3,
        "title": "Breaking News Report",
        "slug": "breaking-news-report",
        "description": "Latest breaking news coverage",
        "featured_image": "http://127.0.0.1:8000/storage/media-contents/unique-filename.jpeg",
        "video_url": "https://example.com/video.mp4",
        "content_type": "news",
        "published_at": null,
        "tags": ["news", "breaking", "report"],
        "view_count": 1,
        "is_featured": true,
        "status": true,
        "created_at": "2025-07-01T09:18:44.000000Z",
        "updated_at": "2025-07-01T09:18:44.000000Z",
        "deleted_at": null
    }
}
```

### 2. Update Media Content
**PUT/PATCH** `/api/admin/media-contents/{id}`

**Request Body:**
```json
{
  "title": "Updated Media Title",
  "description": "Updated description",
  "is_featured": false,
  "status": true
}
```

**Response:**
```json
{
    "status": true,
    "message": "Updated successfully.",
    "data": {
        "id": 3,
        "title": "Updated Media Title",
        "slug": "updated-media-title", // Auto-generated if title changed
        "description": "Updated description",
        "featured_image": "http://127.0.0.1:8000/storage/media-contents/unique-filename.jpeg",
        "video_url": "https://example.com/video.mp4",
        "content_type": "news",
        "published_at": null,
        "tags": ["news", "breaking", "report"],
        "view_count": 1,
        "is_featured": false,
        "status": true,
        "created_at": "2025-07-01T09:18:44.000000Z",
        "updated_at": "2025-07-01T09:18:44.000000Z",
        "deleted_at": null
    }
}
```

### 3. Get All Media Contents (Admin)
**GET** `/api/admin/media-contents`

**Query Parameters:**
- `length`: Number of items per page (default: 10)
- `search`: Search in title and description
- `content_type`: Filter by content type
- `is_featured`: Filter by featured status
- `status`: Filter by status
- `trashed`: Include trashed items ('with', 'only')

### 4. Get Single Media Content
**GET** `/api/admin/media-contents/{id}`

### 5. Delete Media Content (Soft Delete)
**DELETE** `/api/admin/media-contents/{id}`

### 6. Restore Media Content
**POST** `/api/admin/media-contents/restore/{id}`

### 7. Toggle Featured Status
**POST** `/api/admin/media-contents/toggle-featured/{id}`

### 8. Update Status
**POST** `/api/admin/media-contents/update-status/{id}`

**Request Body:**
```json
{
  "status": true
}
```

### 9. Get Featured Content
**POST** `/api/admin/media-contents/featured`

### 10. Get Popular Content
**POST** `/api/admin/media-contents/popular`

### 11. Get Content by Type
**POST** `/api/admin/media-contents/type/{contentType}`

## Public CMS Endpoints (Requires CMS Token)

### 1. Get All Media Contents (Public)
**POST** `/api/media-contents`

### 2. Get Media Content by ID
**POST** `/api/media-contents/{id}`

### 3. Get Media Content by Slug
**POST** `/api/media-contents/{slug}`

### 4. Get Featured Content
**POST** `/api/media-contents/featured`

### 5. Get Content by Type
**POST** `/api/media-contents/type/{contentType}`

### 6. Get Popular Content
**POST** `/api/media-contents/popular`

### 7. Get Recent Content
**POST** `/api/media-contents/recent`

### 8. Search Content
**POST** `/api/media-contents/search/{searchTerm}`

## Content Types Available:
- `news`
- `entertainment`
- `sports`
- `educational`
- `other`

## Notes:
1. **Slug Auto-generation**: If you don't provide a slug, it will be automatically generated from the title with proper formatting (lowercase, spaces replaced with hyphens)
2. **Base64 Images**: You can upload images as base64 strings. They will be automatically stored and the path will be returned
3. **View Counts**: View counts are automatically incremented when content is viewed via the show methods
4. **Soft Deletes**: Content is soft deleted, meaning it can be restored
5. **Permissions**: Admin endpoints require proper permissions based on the action (view, add, edit, delete)

## Authentication:
- Admin endpoints require Bearer token authentication with appropriate permissions
- CMS endpoints require CMS authentication token

The API is fully functional and ready to use with the specifications you provided!
