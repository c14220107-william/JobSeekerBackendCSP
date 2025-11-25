# Job Seeker Platform - API Documentation

## Overview
Platform ini memiliki 3 role berbeda:
- **User (Job Seeker)**: Mencari dan melamar pekerjaan
- **Company**: Membuat dan mengelola lowongan pekerjaan
- **Admin**: Mengelola approval company dan melihat dashboard

## Authentication Flow

### 1. Register User (Job Seeker)
**Endpoint:** `POST /api/register/user`

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "User registered successfully. Please complete your profile.",
  "data": {
    "user": {
      "id": "uuid",
      "email": "user@example.com",
      "role": "user",
      "is_approved": true
    },
    "access_token": "token",
    "token_type": "Bearer",
    "next_step": "complete_profile"
  }
}
```

**Next Step:** User harus mengisi profile dengan endpoint `POST /api/user/profile`

---

### 2. Register Company
**Endpoint:** `POST /api/register/company`

**Request Body:**
```json
{
  "email": "company@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Company registered successfully. Please complete your company details and wait for admin approval.",
  "data": {
    "user": {
      "id": "uuid",
      "email": "company@example.com",
      "role": "company",
      "is_approved": false
    },
    "access_token": "token",
    "token_type": "Bearer",
    "next_step": "complete_company_details"
  }
}
```

**Next Step:** Company harus mengisi company details dengan endpoint `POST /api/company/details`

---

### 3. Login
**Endpoint:** `POST /api/login`

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": "uuid",
      "email": "user@example.com",
      "role": "user",
      "is_approved": true,
      "profile": { ... }
    },
    "access_token": "token",
    "token_type": "Bearer",
    "role": "user"
  }
}
```

**Response (Company Not Approved):**
```json
{
  "success": false,
  "message": "Your company account is pending approval from admin. Please wait for approval.",
  "data": {
    "is_approved": false,
    "role": "company"
  }
}
```

---

### 4. Logout
**Endpoint:** `POST /api/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

### 5. Get Current User Info
**Endpoint:** `GET /api/me`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "uuid",
      "email": "user@example.com",
      "role": "user",
      "profile": { ... }
    }
  }
}
```

---

## User (Job Seeker) Endpoints

**All endpoints require:**
- Authentication: `Bearer {token}`
- Role: `user`

### 1. Create/Update Profile
**Endpoint:** `POST /api/user/profile`

**Request Body:**
```json
{
  "full_name": "John Doe",
  "role": "Software Engineer",
  "bio": "Experienced developer...",
  "cv_url": "https://example.com/cv.pdf",
  "avatar_url": "https://example.com/avatar.jpg"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Profile saved successfully",
  "data": {
    "profile": {
      "id": "uuid",
      "user_id": "uuid",
      "full_name": "John Doe",
      "role": "Software Engineer",
      "bio": "Experienced developer...",
      "cv_url": "https://example.com/cv.pdf",
      "avatar_url": "https://example.com/avatar.jpg"
    }
  }
}
```

### 2. Get Profile
**Endpoint:** `GET /api/user/profile`

### 3. Delete Profile
**Endpoint:** `DELETE /api/user/profile`

---

## Company Endpoints

**All endpoints require:**
- Authentication: `Bearer {token}`
- Role: `company`

### 1. Create/Update Company Details
**Endpoint:** `POST /api/company/details`

**Request Body:**
```json
{
  "full_name": "Jane Doe",
  "company_name": "Tech Corp",
  "description": "Leading tech company...",
  "address": "123 Main St, City",
  "photo_url": "https://example.com/company-photo.jpg",
  "avatar_url": "https://example.com/avatar.jpg"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Company details submitted successfully. Waiting for admin approval.",
  "data": {
    "profile": {
      "id": "uuid",
      "user_id": "uuid",
      "full_name": "Jane Doe",
      "avatar_url": "https://example.com/avatar.jpg"
    },
    "company": {
      "id": "uuid",
      "profile_id": "uuid",
      "name": "Tech Corp",
      "description": "Leading tech company...",
      "address": "123 Main St, City",
      "photo_url": "https://example.com/company-photo.jpg",
      "is_approved": false
    }
  }
}
```

**Note:** Setelah submit, company perlu menunggu approval dari admin. `is_approved` akan menjadi `false` dan user tidak bisa login hingga di-approve.

### 2. Get Company Details
**Endpoint:** `GET /api/company/details`

---

## Public Endpoints (No Authentication Required)

### 1. Get All Approved Companies
**Endpoint:** `GET /api/companies`

**Response:**
```json
{
  "success": true,
  "data": {
    "companies": [
      {
        "id": "uuid",
        "name": "Tech Corp",
        "description": "...",
        "is_approved": true,
        "profile": { ... }
      }
    ]
  }
}
```

### 2. Get Company by ID
**Endpoint:** `GET /api/companies/{id}`

**Response:**
```json
{
  "success": true,
  "data": {
    "company": {
      "id": "uuid",
      "name": "Tech Corp",
      "description": "...",
      "is_approved": true,
      "profile": { ... },
      "jobPostings": [ ... ]
    }
  }
}
```

---

## Admin Endpoints

**All endpoints require:**
- Authentication: `Bearer {token}`
- Role: `admin`

### 1. Dashboard Statistics
**Endpoint:** `GET /api/admin/dashboard`

**Response:**
```json
{
  "success": true,
  "data": {
    "total_users": 100,
    "total_companies": 50,
    "total_approved_companies": 45,
    "total_pending_companies": 5,
    "total_job_postings": 200,
    "total_active_job_postings": 150,
    "total_applications": 500
  }
}
```

### 2. Get Pending Companies
**Endpoint:** `GET /api/admin/companies/pending`

**Response:**
```json
{
  "success": true,
  "data": {
    "companies": [
      {
        "id": "uuid",
        "email": "company@example.com",
        "role": "company",
        "is_approved": false,
        "profile": {
          "company": {
            "name": "Tech Corp",
            "description": "..."
          }
        }
      }
    ]
  }
}
```

### 3. Get Approved Companies
**Endpoint:** `GET /api/admin/companies/approved`

### 4. Approve Company
**Endpoint:** `POST /api/admin/companies/{userId}/approve`

**Response:**
```json
{
  "success": true,
  "message": "Company approved successfully",
  "data": {
    "user": {
      "id": "uuid",
      "email": "company@example.com",
      "is_approved": true,
      "profile": {
        "company": { ... }
      }
    }
  }
}
```

### 5. Reject Company
**Endpoint:** `POST /api/admin/companies/{userId}/reject`

### 6. Get All Users
**Endpoint:** `GET /api/admin/users`

### 7. Get All Job Postings
**Endpoint:** `GET /api/admin/job-postings`

### 8. Get All Applications
**Endpoint:** `GET /api/admin/applications`

---

## Error Responses

### Unauthorized (403)
```json
{
  "success": false,
  "message": "Unauthorized. You do not have permission to access this resource."
}
```

### Unauthenticated (401)
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

---

## Testing Account

**Admin Account:**
- Email: `admin@jobseeker.com`
- Password: `admin123`

---

## Flow Summary

### User (Job Seeker) Flow:
1. Register → `POST /api/register/user`
2. Complete profile → `POST /api/user/profile`
3. Browse companies → `GET /api/companies`
4. Apply for jobs (belum diimplementasikan)

### Company Flow:
1. Register → `POST /api/register/company`
2. Complete company details → `POST /api/company/details`
3. Wait for admin approval
4. After approval, login → `POST /api/login`
5. Create job postings (belum diimplementasikan)

### Admin Flow:
1. Login with admin account → `POST /api/login`
2. View dashboard → `GET /api/admin/dashboard`
3. Review pending companies → `GET /api/admin/companies/pending`
4. Approve/Reject companies → `POST /api/admin/companies/{userId}/approve`
5. Monitor platform activity

---

## Notes

- Semua endpoint protected menggunakan Laravel Sanctum
- Token harus dikirim di header: `Authorization: Bearer {token}`
- Company tidak bisa login sampai di-approve oleh admin
- User langsung bisa login setelah register
- Admin perlu dibuat manual melalui seeder atau database
