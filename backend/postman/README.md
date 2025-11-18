# Maawa API - Postman Collection

Complete API testing collection for the Maawa property rental marketplace backend.

## 📦 Files

- `Maawa_API.postman_collection.json` - Complete API collection with all endpoints
- `Maawa_API.postman_environment.json` - Environment variables (base_url, tokens, IDs)

## 🚀 Quick Start

### 1. Import into Postman

1. Open Postman
2. Click **Import** button
3. Select both files:
   - `Maawa_API.postman_collection.json`
   - `Maawa_API.postman_environment.json`
4. Select the **"Maawa API - Local"** environment from the dropdown

### 2. Update Environment Variables

Click the **Environment quick look** (eye icon) and edit:

```
base_url: http://maawa_project.test/v1
```

Or for production:
```
base_url: http://your-server-ip/v1
```

### 3. Authentication Flow

The collection uses **JWT authentication**. Follow this flow:

#### **Step 1: Register or Login**

Run either:
- **Register** (creates new user + auto-saves tokens)
- **Login** (gets tokens for existing user)

Both automatically save `access_token` and `refresh_token` to environment variables.

#### **Step 2: Use Authenticated Endpoints**

All requests in folders Auth, Properties, Bookings, etc. automatically use:
```
Authorization: Bearer {{access_token}}
```

#### **Step 3: Refresh Token (when expired)**

Run **Refresh Token** endpoint when access token expires (usually after 1 hour).

---

## 📂 Collection Structure

### 🔐 Auth
- **Register** - Create new user (tenant/owner)
- **Login** - Authenticate existing user
- **Logout** - Revoke current refresh token
- **Refresh Token** - Get new access token
- **Get Current User** - Get profile (with phone_number, region)
- **Update Profile** - Update name, phone, region
- **Update Profile (Change Password)** - Change password with current_password verification

### 🏠 Properties
- **List Properties** - Browse properties (role-based access)
  - **Tenant:** See all properties (explore mode)
  - **Owner:** See only their own properties
  - **Admin:** See all properties with full access
- **Get Property by ID** - View single property details
- **Create Property** - Owner creates new listing
- **Update Property** - Owner edits listing
- **Delete Property** - Owner removes listing
- **Upload Property Photos** - Add property images

### 📅 Bookings
- **List Bookings** - View bookings (role-based access, general endpoint)
  - **Tenant:** See only their own bookings
  - **Owner:** See all bookings on their properties
  - **Admin:** See all bookings with complete information
- **Get Owner Bookings** - Owner-specific endpoint (`/v1/owner/bookings`)
  - Returns all bookings on properties owned by the authenticated owner
  - Only owners can access (403 for tenants/admins)
- **Get Owner Bookings by Status** - Status-specific endpoints for owners
  - `/v1/owner/bookings/pending` - PENDING bookings
  - `/v1/owner/bookings/accepted` - ACCEPTED bookings
  - `/v1/owner/bookings/confirmed` - CONFIRMED bookings
  - `/v1/owner/bookings/rejected` - REJECTED bookings
  - `/v1/owner/bookings/canceled` - CANCELED bookings
  - `/v1/owner/bookings/expired` - EXPIRED bookings
  - `/v1/owner/bookings/completed` - COMPLETED bookings
  - `/v1/owner/bookings/failed` - FAILED bookings
- **Get Tenant Bookings** - Tenant-specific endpoint (`/v1/tenant/bookings`)
  - Returns all bookings created by the authenticated tenant
  - Only tenants can access (403 for owners/admins)
- **Get Tenant Bookings by Status** - Status-specific endpoints for tenants
  - `/v1/tenant/bookings/pending` - PENDING bookings
  - `/v1/tenant/bookings/accepted` - ACCEPTED bookings
  - `/v1/tenant/bookings/confirmed` - CONFIRMED bookings
  - `/v1/tenant/bookings/rejected` - REJECTED bookings
  - `/v1/tenant/bookings/canceled` - CANCELED bookings
  - `/v1/tenant/bookings/expired` - EXPIRED bookings
  - `/v1/tenant/bookings/completed` - COMPLETED bookings
  - `/v1/tenant/bookings/failed` - FAILED bookings
- **Create Booking** - Tenant books property (with conflict prevention)
- **Owner Decision (Accept/Reject)** - Owner responds to booking

### 💰 Payments
- **Register FCM Token** - For push notifications
- **Confirm Payment** - Submit payment proof

### 📝 Proposals
- **Submit Proposal** - Owner submits property for approval
- **My Proposals** - View own proposals

### ⭐ Reviews
- **Create Review** - Tenant reviews property after stay
- **Property Reviews** - View reviews for a property

---

## 🔑 Important Notes

### Role-Based Access Control
The API enforces role-based filtering on properties and bookings:

**Properties (`/v1/properties`):**
- **Tenant:** Can explore all properties (full browse mode)
- **Owner:** Can only see their own properties (management mode)
- **Admin:** Can see all properties with full management access

**Bookings:**
- **`/v1/bookings`** (General endpoint - role-based):
  - **Tenant:** Returns only bookings created by the tenant
  - **Owner:** Returns all bookings made on properties owned by the owner
  - **Admin:** Returns all bookings with complete information (email, region, full property details)
- **`/v1/owner/bookings`** (Owner-specific - all bookings):
  - Returns all bookings on properties owned by the authenticated owner
  - Only owners can access (403 Forbidden for tenants/admins)
- **`/v1/owner/bookings/{status}`** (Owner-specific - by status):
  - Returns bookings with specific status on owner's properties
  - Status values: `pending`, `accepted`, `confirmed`, `rejected`, `canceled`, `expired`, `completed`, `failed`
  - Only owners can access (403 Forbidden for tenants/admins)
- **`/v1/tenant/bookings`** (Tenant-specific - all bookings):
  - Returns all bookings created by the authenticated tenant
  - Only tenants can access (403 Forbidden for owners/admins)
- **`/v1/tenant/bookings/{status}`** (Tenant-specific - by status):
  - Returns bookings with specific status created by the tenant
  - Status values: `pending`, `accepted`, `confirmed`, `rejected`, `canceled`, `expired`, `completed`, `failed`
  - Only tenants can access (403 Forbidden for owners/admins)

### Phone Number Format
Phone numbers **must** be in Libyan format:
- **Format:** `09XXXXXXXX` (10 digits starting with `09`)
- **Examples:** `0912345678`, `0920206878`
- **Invalid:** `+218912345678`, `912345678`, `0812345678`

### Date Overlap Prevention
The booking system uses **Airbnb-style conflict detection**:
- Overlapping dates return `409 Conflict`
- Only `PENDING`, `ACCEPTED`, `CONFIRMED` bookings block availability
- `REJECTED`, `CANCELED`, `EXPIRED` bookings do not block dates

### Booking Status Flow
```
PENDING → ACCEPTED → CONFIRMED → COMPLETED
        ↘ REJECTED
```

- **PENDING:** Initial status after tenant creates booking
- **ACCEPTED:** Owner approves the booking
- **CONFIRMED:** Payment verified (tenant paid)
- **REJECTED:** Owner rejects the booking
- **COMPLETED:** Booking finished (after checkout)

### Error Responses

#### 401 Unauthorized
```json
{
  "type": "about:blank",
  "title": "Unauthorized",
  "status": 401,
  "detail": "unauthenticated"
}
```

#### 409 Conflict (Date Unavailable)
```json
{
  "type": "about:blank",
  "title": "Conflict",
  "status": 409,
  "detail": "date_range_unavailable"
}
```

#### 422 Validation Error
```json
{
  "message": "The phone number field format is invalid.",
  "errors": {
    "phone_number": [
      "Phone number must be 10 digits and start with 09 (e.g., 0920206878)"
    ]
  }
}
```

---

## 🧪 Testing Workflow

### 1. User Registration & Authentication
```
1. Register → auto-saves access_token
2. Get Current User → verify profile
3. Update Profile → test profile updates
```

### 2. Property Management (Owner)
```
1. Login as owner
2. Create Property → save property_id
3. Upload Property Photos
4. List Properties → verify only YOUR properties appear
   (Other owners' properties are hidden)
```

### 2b. Property Browsing (Tenant)
```
1. Login as tenant
2. List Properties → see ALL properties (explore mode)
3. Get Property by ID → view details
```

### 2c. Property Management (Admin)
```
1. Login as admin
2. List Properties → see ALL properties from all owners
3. Full management access to all properties
```

### 3. Booking Flow (Tenant)
```
1. Login as tenant
2. List Properties → find property_id
3. Create Booking → save booking_id
   - Try overlapping dates → expect 409
4. List Bookings → verify it appears
```

### 4. Owner Decision Flow
```
1. Login as owner
2. List Bookings → see PENDING bookings on YOUR properties
3. Owner Decision (ACCEPT or REJECT)
4. List Bookings → verify status changed
```

### 4b. Admin Booking Management
```
1. Login as admin
2. List Bookings → see ALL bookings with complete information
   - Full tenant details (email, region, phone)
   - Full property details (city, price, thumbnail)
3. Can view all bookings across all properties
```

---

## 🔧 Troubleshooting

### "Unauthenticated" Error
- Run **Login** or **Register** again
- Check if `access_token` is saved in environment
- Try **Refresh Token** if token expired

### "409 Conflict" on Booking
- Dates overlap with existing booking
- Try different dates
- This is **expected behavior** for conflict prevention

### "422 Validation Error"
- Check phone number format: must start with `09`
- Verify all required fields are provided
- Check password confirmation matches

### "403 Forbidden" on Owner Decision
- Only property owner can accept/reject
- Check if logged-in user owns the property
- Verify booking status is `PENDING`

---

## 📖 API Documentation

For full API documentation, see:
- **OpenAPI Spec:** `backend/openapi/maawa.yaml`
- **Import to Swagger UI or Postman** for interactive docs

---

## 🆘 Support

If you encounter issues:
1. Check environment variables are set
2. Verify base_url points to correct server
3. Ensure migrations are run on server
4. Check server logs for detailed errors

---

**Happy Testing! 🚀**
