# Trip Management API Documentation

## Overview
A comprehensive REST API for managing users and trips with JWT authentication, built with Laravel 12.

## Features Implemented ✅

### Core Requirements
- ✅ **API REST for Users & Trips** - Complete CRUD operations
- ✅ **JWT Authentication** - Login, register, logout, refresh, me endpoints  
- ✅ **User Permissions** - Users can only modify their own trips
- ✅ **Pagination** - Configurable pagination (max 50 items per page)
- ✅ **Date Search** - Multiple date filtering options

### Bonus Features
- ✅ **Advanced Search** - Search by title, description, departure, destination
- ✅ **Trip Status Management** - Active, cancelled, completed statuses
- ✅ **User Statistics** - Trip counts and analytics
- ✅ **Comprehensive Validation** - Request validation with custom messages
- ✅ **Error Handling** - Proper HTTP status codes and error messages
- ✅ **API Documentation** - Built-in endpoint documentation

## API Endpoints

### Authentication Endpoints
```
POST /api/v1/auth/register     - Register new user
POST /api/v1/auth/login        - Login user
POST /api/v1/auth/logout       - Logout user (requires auth)
POST /api/v1/auth/refresh      - Refresh token (requires auth)
GET  /api/v1/auth/me          - Get current user (requires auth)
```

### User Endpoints
```
GET    /api/v1/users          - Get all users (requires auth)
GET    /api/v1/users/{id}     - Get user by ID (requires auth)
PUT    /api/v1/users/profile  - Update current user profile (requires auth)
DELETE /api/v1/users/account  - Delete current user account (requires auth)
GET    /api/v1/users/stats    - Get user statistics (requires auth)
```

### Trip Endpoints
```
GET    /api/v1/trips                    - Get all trips with pagination & search (requires auth)
POST   /api/v1/trips                    - Create new trip (requires auth)
GET    /api/v1/trips/my-trips           - Get current user trips (requires auth)
GET    /api/v1/trips/{id}               - Get trip by ID (requires auth)
PUT    /api/v1/trips/{id}               - Update trip (requires auth & ownership)
DELETE /api/v1/trips/{id}               - Delete trip (requires auth & ownership)
PATCH  /api/v1/trips/{id}/cancel        - Cancel trip (requires auth & ownership)
PATCH  /api/v1/trips/{id}/complete      - Complete trip (requires auth & ownership)
```

### Utility Endpoints
```
GET /api/v1/health    - Health check
GET /api/             - API documentation
```

## Request/Response Examples

### User Registration
```bash
POST /api/v1/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+1234567890",
    "address": "123 Main Street"
}
```

**Response:**
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "+1234567890",
            "address": "123 Main Street"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

### User Login
```bash
POST /api/v1/auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

### Create Trip
```bash
POST /api/v1/trips
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Weekend Getaway",
    "description": "A relaxing trip to the mountains",
    "departure": "New York",
    "destination": "Denver",
    "departure_date": "2024-01-15",
    "departure_time": "08:00",
    "return_date": "2024-01-17",
    "return_time": "18:00",
    "price": 299.99,
    "available_seats": 4
}
```

### Search Trips with Pagination
```bash
GET /api/v1/trips?search=mountains&date=2024-01-15&per_page=10&page=1
Authorization: Bearer {token}
```

### Get User Statistics
```bash
GET /api/v1/users/stats
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "total_trips": 15,
        "active_trips": 8,
        "completed_trips": 5,
        "cancelled_trips": 2,
        "upcoming_trips": 3
    },
    "message": "User statistics retrieved successfully"
}
```

## Search & Filter Options

### Trip Search Parameters
- `search` - Search in title, description, departure, destination
- `date` - Filter by specific departure date
- `start_date` - Filter by date range start
- `end_date` - Filter by date range end
- `status` - Filter by trip status (active, cancelled, completed)
- `departure` - Filter by departure location
- `destination` - Filter by destination location
- `sort_by` - Sort field (default: departure_date)
- `sort_direction` - Sort direction (asc/desc, default: asc)
- `per_page` - Items per page (max: 50, default: 15)
- `page` - Page number

## Security Features

### JWT Authentication
- Secure token-based authentication
- Token expiration and refresh
- Blacklist support for logout
- Custom JWT middleware for validation

### Authorization
- Users can only modify their own trips
- Ownership validation on update/delete operations
- Proper HTTP status codes (403 for unauthorized)

### Validation
- Comprehensive input validation
- Custom validation messages
- SQL injection protection via Eloquent ORM
- XSS protection via Laravel's built-in features

## Database Schema

### Users Table
```sql
- id (primary key)
- name
- email (unique)
- password (hashed)
- phone
- address
- email_verified_at
- remember_token
- created_at
- updated_at
```

### Trips Table
```sql
- id (primary key)
- title
- description
- departure
- destination
- departure_date
- departure_time
- return_date
- return_time
- price
- available_seats
- status (active/cancelled/completed)
- user_id (foreign key)
- created_at
- updated_at
```

## Installation & Setup

1. **Install Dependencies**
```bash
composer install
```

2. **Environment Setup**
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

3. **Database Setup**
```bash
php artisan migrate
```

4. **Start Server**
```bash
php artisan serve
```

## Testing the API

The API is ready for testing. You can use tools like:
- Postman
- Insomnia
- curl
- Any HTTP client

### Quick Test Commands
```bash
# Health check
curl -X GET http://localhost:8000/api/v1/health

# Register user
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123","password_confirmation":"password123"}'

# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

## Error Handling

The API returns consistent error responses:

```json
{
    "success": false,
    "message": "Error description",
    "error": "Detailed error message (in development)"
}
```

Common HTTP status codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## Performance Features

- Database indexing on frequently queried fields
- Pagination to handle large datasets
- Efficient Eloquent relationships
- Query optimization with proper eager loading

## Conclusion

This API implementation fully meets all the requirements specified in the task:
- ✅ Complete CRUD operations for users and trips
- ✅ JWT authentication with proper security
- ✅ User permission system (ownership-based)
- ✅ Pagination and advanced search capabilities
- ✅ Bonus features for enhanced functionality

The codebase is production-ready with proper error handling, validation, and security measures in place.
