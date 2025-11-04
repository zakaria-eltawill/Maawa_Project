# Maawa API Postman Collection

This directory contains Postman collections and environments for testing the Maawa API.

## Files

- `Maawa_API.postman_collection.json` - Complete API collection with all endpoints
- `Maawa_API.postman_environment.json` - Environment variables for local development

## Setup

### 1. Import Collection

1. Open Postman
2. Click **Import** button
3. Select `Maawa_API.postman_collection.json`
4. The collection will be imported with all endpoints

### 2. Import Environment

1. In Postman, click **Environments** (left sidebar)
2. Click **Import**
3. Select `Maawa_API.postman_environment.json`
4. Select the imported environment from the dropdown (top right)

### 3. Configure Base URL

The default base URL is set to `http://localhost:8000/v1`. If your Laravel server runs on a different port or domain, update the `base_url` variable in the environment.

## Usage

### Quick Start

1. **Register or Login**: Start with the "Auth > Register" or "Auth > Login" request
   - These will automatically save `access_token` and `refresh_token` to environment variables
   
2. **Use Protected Endpoints**: All other endpoints will automatically use the stored `access_token`

3. **Test Flow**:
   - Register/Login → Get tokens
   - Create a proposal (if owner) or browse properties
   - Create a booking (if tenant)
   - Accept/reject booking (if owner)
   - Make payment
   - Complete stay and leave review

## Collection Structure

### Auth
- Register - Create new user account
- Login - Authenticate and get tokens
- Refresh Token - Get new access token
- Logout - Revoke tokens
- Get Current User - Get authenticated user profile

### Properties
- List Properties - Browse with filters
- Get Property by ID - View property details
- List Property Reviews - View reviews for a property

### Bookings
- List Bookings - View bookings (tenant or owner view)
- Create Booking - Request a booking (requires X-Idempotency-Key)
- Owner Decision - Accept or reject booking

### Payments
- Mock Payment - Confirm a booking payment (requires X-Idempotency-Key)

### Moderation (Proposals)
- Create Proposal (ADD) - Submit new property for approval
- Create Proposal (EDIT) - Request property changes
- Create Proposal (DELETE) - Request property deletion
- List Owner Proposals - View your proposals

### Admin
- Admin Moderation Queue - View pending proposals (admin only)
- Review Proposal (APPROVED) - Approve a proposal (admin only, requires X-Idempotency-Key)
- Review Proposal (REJECTED) - Reject a proposal (admin only, requires X-Idempotency-Key)

### Reviews
- Create Review - Leave a review after completed stay

### Notifications (FCM)
- Store FCM Token - Register device for push notifications
- Delete FCM Token - Remove device token

## Features

### Auto-Save Tokens
The collection includes test scripts that automatically save tokens:
- `access_token` - Saved after Register/Login/Refresh
- `refresh_token` - Saved after Register/Login/Refresh
- `booking_id` - Saved after creating a booking
- `proposal_id` - Saved after creating a proposal

### Idempotency
Requests that require idempotency keys (`X-Idempotency-Key` header) automatically generate a random UUID. You can also set a fixed value if needed for testing.

### Environment Variables
The collection uses these variables:
- `base_url` - API base URL (default: http://localhost:8000/v1)
- `access_token` - JWT access token (auto-set)
- `refresh_token` - Refresh token (auto-set)
- `property_id` - Property UUID (set manually or from responses)
- `booking_id` - Booking UUID (auto-set)
- `proposal_id` - Proposal UUID (auto-set)

## Testing Workflows

### Tenant Workflow
1. Register/Login as tenant
2. List properties
3. View property details
4. Create booking
5. Wait for owner acceptance
6. Make payment (when accepted)
7. Complete stay
8. Leave review

### Owner Workflow
1. Register/Login as owner
2. Create proposal (ADD) for new property
3. Wait for admin approval
4. List properties
5. View bookings
6. Accept/reject booking requests
7. Receive payment

### Admin Workflow
1. Login as admin
2. View moderation queue
3. Review proposals (APPROVED/REJECTED)
4. Monitor system

## Notes

- All timestamps use ISO 8601 format
- UUIDs are required for resource IDs
- Status enums are case-sensitive (PENDING, ACCEPTED, etc.)
- Idempotency keys prevent duplicate operations
- Refresh tokens rotate on each refresh request

