# POSTMAN TEST - Job Seeker Platform API

## Base URL
```
http://localhost:8000/api
```

---

## 1. REGISTER USER (Job Seeker)  

**Endpoint:** `POST /api/register/user`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "full_name" : "Job Seeker Ni Bos",
  "email": "jobseeker@example.com",
  "password": "password123"
}
```

**Expected Response (201):**
```json
{
  "success": true,
  "message": "User registered successfully. Please complete your profile.",
  "data": {
    "user": {
      "id": "uuid",
      "email": "jobseeker@example.com",
      "role": "user",
      "is_approved": true
    },
    "access_token": "1|xxxxxxxxxxxxx",
    "token_type": "Bearer",
    "next_step": "complete_profile"
  }
}
```

---

## 2. REGISTER COMPANY

**Endpoint:** `POST /api/register/company`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "email": "company@techcorp.com",
  "password": "password123",
  "password_confirmation": "password123",
  "company_name": "Tech Corp Indonesia",
  "description": "We are a leading technology company specializing in software development and IT solutions.",
  "address": "Jl. Sudirman No. 123, Jakarta Pusat, DKI Jakarta 10220",
  "photo_url": "https://example.com/company-logo.jpg",
  "avatar_url": "https://example.com/jane-avatar.jpg"
}
```

**Expected Response (201):**
```json
{
  "success": true,
  "message": "Company registered successfully. Your account is pending admin approval. You will be able to login once approved.",
  "data": {
    "user": {
      "id": "uuid",
      "email": "company@techcorp.com",
      "role": "company",
      "is_approved": false
    },
    "profile": {
      "id": "uuid",
      "user_id": "uuid",
      "full_name": "Jane Doe",
      "avatar_url": "https://example.com/jane-avatar.jpg"
    },
    "company": {
      "id": "uuid",
      "profile_id": "uuid",
      "name": "Tech Corp Indonesia",
      "description": "We are a leading technology company...",
      "address": "Jl. Sudirman No. 123...",
      "photo_url": "https://example.com/company-logo.jpg",
      "is_approved": false
    },
    "status": "pending_approval"
  }
}
```

---

## 3. LOGIN

**Endpoint:** `POST /api/login`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON) - User:**
```json
{
  "email": "jobseeker@example.com",
  "password": "password123"
}
```

**Body (JSON) - Admin:**
```json
{
  "email": "admin@jobseeker.com",
  "password": "admin123"
}
```

**Expected Response (200) - Success:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": "uuid",
      "email": "admin@jobseeker.com",
      "role": "admin",
      "is_approved": true,
      "profile": null
    },
    "access_token": "2|xxxxxxxxxxxxx",
    "token_type": "Bearer",
    "role": "admin"
  }
}
```

**Expected Response (403) - Company Not Approved:**
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

**Expected Response (422) - Validation Error:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["Email wajib diisi."],
    "password": ["Password wajib diisi."]
  }
}
```

---

## 4. GET CURRENT USER INFO

**Endpoint:** `GET /api/me`

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {your_token_here}
```

**No Body Required**

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "uuid",
      "email": "admin@jobseeker.com",
      "role": "admin",
      "is_approved": true
    }
  }
}
```

---

## 5. COMPLETE PROFILE (User Only)

**Endpoint:** `POST /api/user/profile`

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {your_user_token}
```

**Body (JSON):**
```json
{
  "full_name": "John Doe",
  "role": "Senior Software Engineer",
  "bio": "Experienced full-stack developer with 5+ years in web development. Proficient in Laravel, React, and Node.js.",
  "cv_url": "https://example.com/john-doe-cv.pdf",
  "avatar_url": "https://example.com/john-avatar.jpg"
}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Profile saved successfully",
  "data": {
    "profile": {
      "id": "uuid",
      "user_id": "uuid",
      "full_name": "John Doe",
      "role": "Senior Software Engineer",
      "bio": "Experienced full-stack developer...",
      "cv_url": "https://example.com/john-doe-cv.pdf",
      "avatar_url": "https://example.com/john-avatar.jpg"
    }
  }
}
```

---

## 6. ADMIN: GET DASHBOARD

**Endpoint:** `GET /api/admin/dashboard`

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {your_admin_token}
```

**No Body Required**

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "total_users": 10,
    "total_companies": 5,
    "total_approved_companies": 3,
    "total_pending_companies": 2,
    "total_job_postings": 15,
    "total_active_job_postings": 12,
    "total_applications": 50
  }
}
```

---

## 7. ADMIN: GET PENDING COMPANIES

**Endpoint:** `GET /api/admin/companies/pending`

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {your_admin_token}
```

**No Body Required**

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "companies": [
      {
        "id": "uuid",
        "email": "company@techcorp.com",
        "role": "company",
        "is_approved": false,
        "created_at": "2025-11-18T08:00:00.000000Z",
        "profile": {
          "id": "uuid",
          "full_name": "Jane Doe",
          "company": {
            "id": "uuid",
            "name": "Tech Corp Indonesia",
            "description": "We are a leading technology company...",
            "address": "Jl. Sudirman No. 123...",
            "is_approved": false
          }
        }
      }
    ]
  }
}
```

---

## 8. ADMIN: APPROVE COMPANY

**Endpoint:** `POST /api/admin/companies/{userId}/approve`

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {your_admin_token}
```

**No Body Required**

**Example:**
```
POST /api/admin/companies/a06285a3-08c7-4f66-b1b8-94b1578843f8/approve
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Company approved successfully",
  "data": {
    "user": {
      "id": "uuid",
      "email": "company@techcorp.com",
      "role": "company",
      "is_approved": true,
      "profile": {
        "company": {
          "name": "Tech Corp Indonesia",
          "is_approved": true
        }
      }
    }
  }
}
```

---

## 9. ADMIN: REJECT COMPANY

**Endpoint:** `POST /api/admin/companies/{userId}/reject`

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {your_admin_token}
```

**No Body Required**

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Company rejected",
  "data": {
    "user": {
      "id": "uuid",
      "email": "company@techcorp.com",
      "is_approved": false
    }
  }
}
```

---

## 10. LOGOUT

**Endpoint:** `POST /api/logout`

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {your_token}
```

**No Body Required**

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

## 11. GET ALL COMPANIES (Public)

**Endpoint:** `GET /api/companies`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**No Body Required**
**No Authentication Required**

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "companies": [
      {
        "id": "uuid",
        "name": "Tech Corp Indonesia",
        "description": "We are a leading technology company...",
        "address": "Jl. Sudirman No. 123...",
        "is_approved": true,
        "photo_url": "https://example.com/company-logo.jpg",
        "profile": {
          "full_name": "Jane Doe"
        }
      }
    ]
  }
}
```

---

## 12. GET ALL QUALIFICATIONS (Public)

**Endpoint:** `GET /api/qualifications`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**No Body Required**
**No Authentication Required**

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "qualifications": [
      {
        "id": "uuid-1",
        "skill": "PHP",
        "created_at": "2025-11-18T08:00:00.000000Z",
        "updated_at": "2025-11-18T08:00:00.000000Z"
      },
      {
        "id": "uuid-2",
        "skill": "Laravel",
        "created_at": "2025-11-18T08:00:00.000000Z",
        "updated_at": "2025-11-18T08:00:00.000000Z"
      }
    ]
  }
}
```

---

## 13. CREATE JOB POSTING (Company Only)

**Endpoint:** `POST /api/company/job-postings`

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {your_company_token}
```

**Body (JSON):**
```json
{
  "title": "Senior Backend Developer",
  "location": "Jakarta Selatan",
  "salary": "Rp 15.000.000 - Rp 25.000.000",
  "description": "We are looking for an experienced backend developer to join our team. You will be responsible for developing and maintaining our server-side logic, ensuring high performance and responsiveness.",
  "tenure": "Full Time",
  "type": "Permanent",
  "status": "open",
  "qualification_ids": [
    "uuid-qualification-1",
    "uuid-qualification-2",
    "uuid-qualification-3"
  ]
}
```

**Expected Response (201):**
```json
{
  "success": true,
  "message": "Job posting created successfully",
  "data": {
    "job_posting": {
      "id": "uuid",
      "company_id": "uuid",
      "title": "Senior Backend Developer",
      "location": "Jakarta Selatan",
      "salary": "Rp 15.000.000 - Rp 25.000.000",
      "description": "We are looking for...",
      "tenure": "Full Time",
      "type": "Permanent",
      "status": "open",
      "created_at": "2025-11-18T08:00:00.000000Z",
      "updated_at": "2025-11-18T08:00:00.000000Z",
      "qualifications": [
        {
          "id": "uuid-1",
          "skill": "PHP",
          "pivot": {
            "job_id": "uuid",
            "qualification_id": "uuid-1"
          }
        },
        {
          "id": "uuid-2",
          "skill": "Laravel",
          "pivot": {
            "job_id": "uuid",
            "qualification_id": "uuid-2"
          }
        }
      ],
      "company": {
        "id": "uuid",
        "name": "Tech Corp Indonesia",
        "profile": {
          "full_name": "Jane Doe"
        }
      }
    }
  }
}
```

---

## 14. UPDATE JOB POSTING (Company Only)

**Endpoint:** `PUT /api/company/job-postings/{id}`

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {your_company_token}
```

**Body (JSON):**
```json
{
  "title": "Senior Backend Developer (Updated)",
  "location": "Jakarta Pusat",
  "salary": "Rp 18.000.000 - Rp 30.000.000",
  "description": "Updated description...",
  "tenure": "Full Time",
  "type": "Permanent",
  "status": "open",
  "qualification_ids": [
    "uuid-qualification-1",
    "uuid-qualification-4"
  ]
}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Job posting updated successfully",
  "data": {
    "job_posting": { ... }
  }
}
```

---

## 15. DELETE JOB POSTING (Company Only)

**Endpoint:** `DELETE /api/company/job-postings/{id}`

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {your_company_token}
```

**No Body Required**

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Job posting deleted successfully"
}
```

---

## 16. GET MY JOB POSTINGS (Company Only)

**Endpoint:** `GET /api/company/job-postings`

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {your_company_token}
```

**No Body Required**

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "job_postings": [
      {
        "id": "uuid",
        "title": "Senior Backend Developer",
        "location": "Jakarta Selatan",
        "salary": "Rp 15.000.000 - Rp 25.000.000",
        "status": "open",
        "qualifications": [ ... ],
        "applications": [
          {
            "id": "uuid",
            "seeker_id": "uuid",
            "status": "pending"
          }
        ]
      }
    ]
  }
}
```

---

## 17. GET SINGLE JOB POSTING (Company Only)

**Endpoint:** `GET /api/company/job-postings/{id}`

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {your_company_token}
```

**No Body Required**

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "job_posting": {
      "id": "uuid",
      "title": "Senior Backend Developer",
      "qualifications": [ ... ],
      "applications": [
        {
          "id": "uuid",
          "seeker": {
            "full_name": "John Doe",
            "cv_url": "..."
          }
        }
      ]
    }
  }
}
```

---

## 18. GET ALL JOB POSTINGS (Public)

**Endpoint:** `GET /api/job-postings`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**No Body Required**
**No Authentication Required**

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "job_postings": [
      {
        "id": "uuid",
        "title": "Senior Backend Developer",
        "location": "Jakarta Selatan",
        "salary": "Rp 15.000.000 - Rp 25.000.000",
        "description": "...",
        "tenure": "Full Time",
        "type": "Permanent",
        "status": "open",
        "company": {
          "id": "uuid",
          "name": "Tech Corp Indonesia",
          "profile": {
            "full_name": "Jane Doe"
          }
        },
        "qualifications": [
          {
            "id": "uuid",
            "skill": "PHP"
          }
        ]
      }
    ]
  }
}
```

---

## 19. GET JOB POSTING BY ID (Public)

**Endpoint:** `GET /api/job-postings/{id}`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**No Body Required**
**No Authentication Required**

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "job_posting": {
      "id": "uuid",
      "title": "Senior Backend Developer",
      "location": "Jakarta Selatan",
      "salary": "Rp 15.000.000 - Rp 25.000.000",
      "description": "...",
      "company": {
        "name": "Tech Corp Indonesia",
        "description": "...",
        "address": "..."
      },
      "qualifications": [ ... ]
    }
  }
}
```

---

## Testing Flow

### 1. Test User Registration & Login:
```
1. POST /api/register/user
   → Copy access_token
2. POST /api/user/profile (with token)
3. POST /api/logout
4. POST /api/login (user credentials)
```

### 2. Test Company Registration & Approval:
```
1. POST /api/register/company
2. POST /api/login (company credentials) → Should get 403
3. POST /api/login (admin credentials) → Get admin token
4. GET /api/admin/companies/pending (with admin token)
5. POST /api/admin/companies/{userId}/approve (with admin token)
6. POST /api/login (company credentials) → Should succeed now
```

### 3. Test Admin:
```
1. POST /api/login (admin credentials)
2. GET /api/admin/dashboard
3. GET /api/admin/companies/pending
4. POST /api/admin/companies/{userId}/approve
```

### 4. Test Job Posting (Company):
```
1. POST /api/login (approved company credentials)
2. GET /api/qualifications → Get qualification IDs
3. POST /api/company/job-postings (with qualification_ids)
4. GET /api/company/job-postings → See your job postings
5. PUT /api/company/job-postings/{id} → Update job posting
6. DELETE /api/company/job-postings/{id} → Delete job posting
```

### 5. Test Job Browsing (Public):
```
1. GET /api/job-postings → Browse all open jobs
2. GET /api/job-postings/{id} → View job details
3. GET /api/companies → Browse companies
4. GET /api/qualifications → See all skills
```

---

## Error Responses

### 401 Unauthorized:
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

### 403 Forbidden:
```json
{
  "success": false,
  "message": "Unauthorized. You do not have permission to access this resource."
}
```

### 422 Validation Error:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["Email wajib diisi."],
    "password": ["Password minimal 8 karakter."]
  }
}
```

---

## Notes:
- Ganti `{your_token_here}` dengan token yang didapat dari login/register
- Ganti `{userId}` dengan ID user yang ingin di-approve/reject
- Default admin: `admin@jobseeker.com` / `admin123`
- Pastikan server Laravel sudah running: `php artisan serve`
