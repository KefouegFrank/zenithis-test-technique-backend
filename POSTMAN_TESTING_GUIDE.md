# Postman Testing Guide for Trip Management API

## Setup Instructions

### 1. Import Environment Variables
Create a new environment in Postman with these variables:
```
base_url: http://localhost:8000/api/v1
token: (leave empty initially)
```

### 2. Start Laravel Server
```bash
php artisan serve
```
The server will run on `http://localhost:8000`

---

## Testing Sequence

### Step 1: Health Check ✅
**Purpose:** Verify API is running

**Request:**
- **Method:** GET
- **URL:** `{{base_url}}/health`
- **Headers:** None required

**Expected Response:**
```json
{
    "success": true,
    "message": "API is running",
    "timestamp": "2024-01-15T10:30:00.000000Z",
    "version": "1.0.0"
}
```

---

### Step 2: API Documentation ✅
**Purpose:** Verify API documentation endpoint

**Request:**
- **Method:** GET
- **URL:** `http://localhost:8000/api/`
- **Headers:** None required

**Expected Response:** JSON with all available endpoints

---

### Step 3: User Registration ✅
**Purpose:** Create a new user account

**Request:**
- **Method:** POST
- **URL:** `{{base_url}}/auth/register`
- **Headers:**
  ```
  Content-Type: application/json
  ```
- **Body (raw JSON):**
```json
{
    "name": "John Doe",
    "email": "john.doe@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+1234567890",
    "address": "123 Main Street, New York"
}
```

**Expected Response (201):**
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john.doe@example.com",
            "phone": "+1234567890",
            "address": "123 Main Street, New York"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

**⚠️ Important:** Copy the `token` value and set it in your Postman environment variable `{{token}}`

---

### Step 4: User Login ✅
**Purpose:** Test login with existing user

**Request:**
- **Method:** POST
- **URL:** `{{base_url}}/auth/login`
- **Headers:**
  ```
  Content-Type: application/json
  ```
- **Body (raw JSON):**
```json
{
    "email": "john.doe@example.com",
    "password": "password123"
}
```

**Expected Response (200):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john.doe@example.com"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

---

### Step 5: Get Current User (Me) ✅
**Purpose:** Test authenticated endpoint

**Request:**
- **Method:** GET
- **URL:** `{{base_url}}/auth/me`
- **Headers:**
  ```
  Authorization: Bearer {{token}}
  ```

**Expected Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john.doe@example.com"
    }
}
```

---

### Step 6: Create a Trip ✅
**Purpose:** Test trip creation

**Request:**
- **Method:** POST
- **URL:** `{{base_url}}/trips`
- **Headers:**
  ```
  Authorization: Bearer {{token}}
  Content-Type: application/json
  ```
- **Body (raw JSON):**
```json
{
    "title": "Weekend Getaway to Denver",
    "description": "A relaxing trip to the mountains for the weekend",
    "departure": "New York",
    "destination": "Denver",
    "departure_date": "2024-02-15",
    "departure_time": "08:00",
    "return_date": "2024-02-17",
    "return_time": "18:00",
    "price": 299.99,
    "available_seats": 4
}
```

**Expected Response (201):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Weekend Getaway to Denver",
        "description": "A relaxing trip to the mountains for the weekend",
        "departure": "New York",
        "destination": "Denver",
        "departure_date": "2024-02-15",
        "departure_time": "08:00:00",
        "return_date": "2024-02-17",
        "return_time": "18:00:00",
        "price": "299.99",
        "available_seats": 4,
        "status": "active",
        "user_id": 1,
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john.doe@example.com"
        }
    },
    "message": "Trip created successfully"
}
```

---

### Step 7: Get All Trips ✅
**Purpose:** Test trip listing with pagination

**Request:**
- **Method:** GET
- **URL:** `{{base_url}}/trips`
- **Headers:**
  ```
  Authorization: Bearer {{token}}
  ```

**Expected Response (200):**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "title": "Weekend Getaway to Denver",
                "departure": "New York",
                "destination": "Denver",
                "departure_date": "2024-02-15",
                "price": "299.99",
                "status": "active",
                "user": {
                    "id": 1,
                    "name": "John Doe",
                    "email": "john.doe@example.com"
                }
            }
        ],
        "first_page_url": "http://localhost:8000/api/v1/trips?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http://localhost:8000/api/v1/trips?page=1",
        "links": [...],
        "next_page_url": null,
        "path": "http://localhost:8000/api/v1/trips",
        "per_page": 15,
        "prev_page_url": null,
        "to": 1,
        "total": 1
    },
    "message": "Trips retrieved successfully"
}
```

---

### Step 8: Search Trips ✅
**Purpose:** Test search functionality

**Request:**
- **Method:** GET
- **URL:** `{{base_url}}/trips?search=Denver&per_page=10`
- **Headers:**
  ```
  Authorization: Bearer {{token}}
  ```

**Expected Response (200):** Similar to Step 7, but filtered results

---

### Step 9: Get My Trips ✅
**Purpose:** Test user's own trips

**Request:**
- **Method:** GET
- **URL:** `{{base_url}}/trips/my-trips`
- **Headers:**
  ```
  Authorization: Bearer {{token}}
  ```

**Expected Response (200):** Similar to Step 7, but only user's trips

---

### Step 10: Get Single Trip ✅
**Purpose:** Test trip detail view

**Request:**
- **Method:** GET
- **URL:** `{{base_url}}/trips/1`
- **Headers:**
  ```
  Authorization: Bearer {{token}}
  ```

**Expected Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Weekend Getaway to Denver",
        "description": "A relaxing trip to the mountains for the weekend",
        "departure": "New York",
        "destination": "Denver",
        "departure_date": "2024-02-15",
        "departure_time": "08:00:00",
        "return_date": "2024-02-17",
        "return_time": "18:00:00",
        "price": "299.99",
        "available_seats": 4,
        "status": "active",
        "user_id": 1,
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john.doe@example.com",
            "phone": "+1234567890"
        }
    },
    "message": "Trip retrieved successfully"
}
```

---

### Step 11: Update Trip ✅
**Purpose:** Test trip update (ownership required)

**Request:**
- **Method:** PUT
- **URL:** `{{base_url}}/trips/1`
- **Headers:**
  ```
  Authorization: Bearer {{token}}
  Content-Type: application/json
  ```
- **Body (raw JSON):**
```json
{
    "title": "Updated Weekend Getaway to Denver",
    "description": "Updated description for the trip",
    "price": 349.99,
    "available_seats": 3
}
```

**Expected Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Updated Weekend Getaway to Denver",
        "description": "Updated description for the trip",
        "price": "349.99",
        "available_seats": 3,
        "status": "active"
    },
    "message": "Trip updated successfully"
}
```

---

### Step 12: Cancel Trip ✅
**Purpose:** Test trip cancellation

**Request:**
- **Method:** PATCH
- **URL:** `{{base_url}}/trips/1/cancel`
- **Headers:**
  ```
  Authorization: Bearer {{token}}
  ```

**Expected Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "status": "cancelled"
    },
    "message": "Trip cancelled successfully"
}
```

---

### Step 13: Complete Trip ✅
**Purpose:** Test trip completion

**Request:**
- **Method:** PATCH
- **URL:** `{{base_url}}/trips/1/complete`
- **Headers:**
  ```
  Authorization: Bearer {{token}}
  ```

**Expected Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "status": "completed"
    },
    "message": "Trip marked as completed successfully"
}
```

---

### Step 14: Get User Statistics ✅
**Purpose:** Test user statistics

**Request:**
- **Method:** GET
- **URL:** `{{base_url}}/users/stats`
- **Headers:**
  ```
  Authorization: Bearer {{token}}
  ```

**Expected Response (200):**
```json
{
    "success": true,
    "data": {
        "total_trips": 1,
        "active_trips": 0,
        "completed_trips": 1,
        "cancelled_trips": 0,
        "upcoming_trips": 0
    },
    "message": "User statistics retrieved successfully"
}
```

---

### Step 15: Update User Profile ✅
**Purpose:** Test profile update

**Request:**
- **Method:** PUT
- **URL:** `{{base_url}}/users/profile`
- **Headers:**
  ```
  Authorization: Bearer {{token}}
  Content-Type: application/json
  ```
- **Body (raw JSON):**
```json
{
    "name": "John Smith",
    "phone": "+1987654321",
    "address": "456 Updated Street, New York"
}
```

**Expected Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Smith",
        "email": "john.doe@example.com",
        "phone": "+1987654321",
        "address": "456 Updated Street, New York"
    },
    "message": "Profile updated successfully"
}
```

---

### Step 16: Get All Users ✅
**Purpose:** Test user listing

**Request:**
- **Method:** GET
- **URL:** `{{base_url}}/users`
- **Headers:**
  ```
  Authorization: Bearer {{token}}
  ```

**Expected Response (200):**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "name": "John Smith",
                "email": "john.doe@example.com",
                "phone": "+1987654321",
                "created_at": "2024-01-15T10:30:00.000000Z"
            }
        ],
        "per_page": 15,
        "total": 1
    },
    "message": "Users retrieved successfully"
}
```

---

### Step 17: Refresh Token ✅
**Purpose:** Test token refresh

**Request:**
- **Method:** POST
- **URL:** `{{base_url}}/auth/refresh`
- **Headers:**
  ```
  Authorization: Bearer {{token}}
  ```

**Expected Response (200):**
```json
{
    "success": true,
    "message": "Token refreshed successfully",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

**⚠️ Important:** Update your `{{token}}` variable with the new token

---

### Step 18: Logout ✅
**Purpose:** Test logout functionality

**Request:**
- **Method:** POST
- **URL:** `{{base_url}}/auth/logout`
- **Headers:**
  ```
  Authorization: Bearer {{token}}
  ```

**Expected Response (200):**
```json
{
    "success": true,
    "message": "Successfully logged out"
}
```

---

## Error Testing

### Test 1: Unauthorized Access ❌
**Purpose:** Verify authentication is required

**Request:**
- **Method:** GET
- **URL:** `{{base_url}}/trips`
- **Headers:** None (no authorization)

**Expected Response (401):**
```json
{
    "success": false,
    "message": "Token absent"
}
```

### Test 2: Invalid Token ❌
**Purpose:** Verify token validation

**Request:**
- **Method:** GET
- **URL:** `{{base_url}}/trips`
- **Headers:**
  ```
  Authorization: Bearer invalid_token_here
  ```

**Expected Response (401):**
```json
{
    "success": false,
    "message": "Token invalid"
}
```

### Test 3: Validation Error ❌
**Purpose:** Verify input validation

**Request:**
- **Method:** POST
- **URL:** `{{base_url}}/trips`
- **Headers:**
  ```
  Authorization: Bearer {{token}}
  Content-Type: application/json
  ```
- **Body (raw JSON):**
```json
{
    "title": "",
    "departure_date": "2020-01-01"
}
```

**Expected Response (422):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "title": ["The title field is required."],
        "departure_date": ["The departure date must be today or later."]
    }
}
```

---

## Postman Collection Setup

### 1. Create Collection
1. Click "New" → "Collection"
2. Name it "Trip Management API"
3. Add the environment variables mentioned above

### 2. Organize Requests
Create folders:
- **Authentication** (register, login, logout, refresh, me)
- **Users** (index, show, update, delete, stats)
- **Trips** (index, store, show, update, delete, cancel, complete, my-trips)
- **Utility** (health, documentation)
- **Error Testing** (unauthorized, validation errors)

### 3. Add Tests (Optional)
Add these tests to your requests:

**For successful requests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has success field", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('success');
    pm.expect(jsonData.success).to.be.true;
});
```

**For authentication requests:**
```javascript
pm.test("Token is present", function () {
    var jsonData = pm.response.json();
    if (jsonData.data && jsonData.data.token) {
        pm.environment.set("token", jsonData.data.token);
    }
});
```

---

## Quick Test Checklist

- [ ] Health check works
- [ ] User registration works
- [ ] User login works
- [ ] JWT token is generated and stored
- [ ] Authenticated endpoints work with token
- [ ] Trip creation works
- [ ] Trip listing with pagination works
- [ ] Search functionality works
- [ ] Trip update works (ownership check)
- [ ] Trip cancellation works
- [ ] Trip completion works
- [ ] User statistics work
- [ ] Profile update works
- [ ] Token refresh works
- [ ] Logout works
- [ ] Unauthorized access is blocked
- [ ] Validation errors are returned properly

---

## Troubleshooting

### Common Issues:

1. **"Token absent" error:**
   - Make sure you're using `Bearer {{token}}` in Authorization header
   - Verify the token variable is set in your environment

2. **"Connection refused" error:**
   - Make sure Laravel server is running (`php artisan serve`)
   - Check if port 8000 is available

3. **"Validation failed" error:**
   - Check the request body format (must be valid JSON)
   - Verify all required fields are provided
   - Check date formats (YYYY-MM-DD)

4. **"Unauthorized" error:**
   - Make sure you're logged in and have a valid token
   - Check if the token has expired (try refreshing)

### Success Indicators:
- All requests return appropriate HTTP status codes
- JWT tokens are generated and work for authenticated requests
- Pagination works correctly
- Search and filtering work as expected
- User ownership validation works (can't modify others' trips)
- Error handling works properly

This comprehensive testing will ensure your API is ready for deployment! 🚀
