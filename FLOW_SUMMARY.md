# Job Seeker Platform - Flow Summary

## 🎯 Complete User Flows

### **1. USER (Job Seeker) Flow**

#### Step 1: Register
```http
POST /api/register/user
Content-Type: application/json

{
  "email": "jobseeker@example.com",
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
    "user": { "id": "uuid", "email": "...", "role": "user", "is_approved": true },
    "access_token": "token_here",
    "token_type": "Bearer",
    "next_step": "complete_profile"
  }
}
```

#### Step 2: Complete Profile (WAJIB)
```http
POST /api/user/profile
Authorization: Bearer {token}
Content-Type: application/json

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
    "profile": { ... }
  }
}
```

#### Step 3: Browse & Apply Jobs
- Browse companies: `GET /api/companies`
- View job details: `GET /api/job-postings/{id}` (belum implementasi)
- Apply for job: `POST /api/applications` (belum implementasi)

**Note:** 
- ✅ User langsung approved setelah register
- ✅ Harus isi profile dulu sebelum bisa apply job
- ✅ Middleware `user.profile` akan memblokir jika profile belum diisi

---

### **2. COMPANY Flow**

#### Step 1: Register + Submit Company Details (Sekaligus)
```http
POST /api/register/company
Content-Type: application/json

{
  "email": "company@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "full_name": "Jane Doe",
  "company_name": "Tech Corp Indonesia",
  "description": "Leading technology company...",
  "address": "Jl. Sudirman No. 123, Jakarta",
  "photo_url": "https://example.com/company-logo.jpg",
  "avatar_url": "https://example.com/jane-avatar.jpg"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Company registered successfully. Your account is pending admin approval. You will be able to login once approved.",
  "data": {
    "user": { "id": "uuid", "email": "...", "role": "company", "is_approved": false },
    "profile": { "id": "uuid", "full_name": "Jane Doe", ... },
    "company": { "id": "uuid", "name": "Tech Corp Indonesia", "is_approved": false, ... },
    "status": "pending_approval"
  }
}
```

**Note:** 
- ⚠️ **TIDAK ADA TOKEN** yang diberikan saat register
- ⚠️ Company **TIDAK BISA LOGIN** sampai di-approve oleh admin
- ✅ Semua data (user, profile, company) dibuat sekaligus saat register

#### Step 2: Wait for Admin Approval
Company tidak bisa melakukan apa-apa sampai admin approve.

Jika mencoba login sebelum approved:
```http
POST /api/login
{
  "email": "company@example.com",
  "password": "password123"
}
```

**Response (403 Forbidden):**
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

#### Step 3: After Approved - Login
Setelah admin approve, company bisa login:

```http
POST /api/login
{
  "email": "company@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": "uuid",
      "email": "company@example.com",
      "role": "company",
      "is_approved": true,
      "profile": {
        "company": { "name": "Tech Corp Indonesia", ... }
      }
    },
    "access_token": "token_here",
    "token_type": "Bearer",
    "role": "company"
  }
}
```

#### Step 4: Create Job Postings
```http
POST /api/company/job-postings
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Senior Software Engineer",
  "description": "We are looking for...",
  "requirements": "5+ years experience...",
  "salary_range": "Rp 15.000.000 - Rp 25.000.000",
  ...
}
```

**Note:**
- ✅ Hanya company yang **approved** bisa akses endpoint ini
- ✅ Middleware `company.approved` akan memblokir company yang belum approved

---

### **3. ADMIN Flow**

#### Step 1: Login
```http
POST /api/login
{
  "email": "admin@jobseeker.com",
  "password": "admin123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { "id": "uuid", "email": "admin@jobseeker.com", "role": "admin" },
    "access_token": "token_here",
    "token_type": "Bearer",
    "role": "admin"
  }
}
```

#### Step 2: View Dashboard
```http
GET /api/admin/dashboard
Authorization: Bearer {token}
```

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

#### Step 3: Review Pending Companies
```http
GET /api/admin/companies/pending
Authorization: Bearer {token}
```

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
          "full_name": "Jane Doe",
          "company": {
            "name": "Tech Corp Indonesia",
            "description": "...",
            "address": "..."
          }
        }
      }
    ]
  }
}
```

#### Step 4: Approve Company
```http
POST /api/admin/companies/{userId}/approve
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Company approved successfully",
  "data": {
    "user": {
      "id": "uuid",
      "is_approved": true,
      "profile": {
        "company": {
          "is_approved": true,
          ...
        }
      }
    }
  }
}
```

Setelah approved, company bisa login dan mulai buat job posting.

#### Step 5: Reject Company (Optional)
```http
POST /api/admin/companies/{userId}/reject
Authorization: Bearer {token}
```

---

## 📊 Validation Rules

### **User Registration:**
- ✅ Email required & unique
- ✅ Password min 8 characters
- ✅ Password confirmation required

### **Company Registration:**
- ✅ Email required & unique
- ✅ Password min 8 characters
- ✅ Password confirmation required
- ✅ Full name required (PIC/representative)
- ✅ Company name required
- ✅ Description optional
- ✅ Address optional
- ✅ Photo URL optional
- ✅ Avatar URL optional

### **Profile Completion (User):**
- ✅ Full name required
- ✅ Role/position optional
- ✅ Bio optional
- ✅ CV URL optional
- ✅ Avatar URL optional

---

## 🔒 Middleware Protection

### **User Routes** (`/api/user/*`)
- ✅ `auth:sanctum` - Must be logged in
- ✅ `role:user` - Must have user role
- ✅ `user.profile` - Must have completed profile (for job applications)

### **Company Routes** (`/api/company/*`)
- ✅ `auth:sanctum` - Must be logged in
- ✅ `role:company` - Must have company role
- ✅ `company.approved` - Must be approved by admin

### **Admin Routes** (`/api/admin/*`)
- ✅ `auth:sanctum` - Must be logged in
- ✅ `role:admin` - Must have admin role

---

## 🎭 Role-Based Access Summary

| Endpoint | User | Company (Not Approved) | Company (Approved) | Admin |
|----------|------|------------------------|-------------------|-------|
| POST /api/register/user | ✅ | ✅ | ✅ | ✅ |
| POST /api/register/company | ✅ | ✅ | ✅ | ✅ |
| POST /api/login | ✅ | ❌ | ✅ | ✅ |
| POST /api/user/profile | ✅ | ❌ | ❌ | ❌ |
| GET /api/companies | ✅ | ✅ | ✅ | ✅ |
| POST /api/applications | ✅* | ❌ | ❌ | ❌ |
| POST /api/company/job-postings | ❌ | ❌ | ✅ | ❌ |
| GET /api/admin/dashboard | ❌ | ❌ | ❌ | ✅ |
| POST /api/admin/companies/{id}/approve | ❌ | ❌ | ❌ | ✅ |

*Hanya jika sudah complete profile

---

## ✅ Current Implementation Status

| Feature | Status |
|---------|--------|
| User Registration | ✅ Implemented |
| Company Registration (with details) | ✅ Implemented |
| User Profile Management | ✅ Implemented |
| Company Details (auto-created on register) | ✅ Implemented |
| Login with Role Check | ✅ Implemented |
| Company Approval System | ✅ Implemented |
| Admin Dashboard | ✅ Implemented |
| Admin Approve/Reject Company | ✅ Implemented |
| Middleware Role-Based Protection | ✅ Implemented |
| Middleware Profile Check (User) | ✅ Implemented |
| Middleware Approval Check (Company) | ✅ Implemented |
| Job Posting CRUD | ❌ Not Yet |
| Job Application System | ❌ Not Yet |

---

## 🚀 Next Steps

1. **Job Posting CRUD** (Company)
   - Create job posting
   - Update job posting
   - Delete job posting
   - List job postings (public + company's own)

2. **Job Application System** (User)
   - Apply for job
   - View application status
   - Withdraw application

3. **Application Management** (Company)
   - View applications for their jobs
   - Accept/reject applications
   - Contact applicants

4. **Notifications**
   - Email notification for company approval
   - Notification for application status updates
