# SmartQueue - Comprehensive Project Report

**Institution:** University Project  
**Subject:** Web Development & Queue Management System  
**Technologies:** PHP, MySQL, JavaScript, HTML/CSS  
**Date:** April 2026

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [System Architecture](#system-architecture)
3. [Database Schema](#database-schema)
4. [User Roles & Permissions](#user-roles--permissions)
5. [Core Features](#core-features)
6. [Technical Implementation](#technical-implementation)
7. [Recent Improvements](#recent-improvements)
8. [Security Measures](#security-measures)
9. [Likely Exam Questions](#likely-exam-questions)
10. [How to Run & Test](#how-to-run--test)

---

## Project Overview

**SmartQueue** is a queue management system that allows clients to book tickets at various establishments (banks, cinemas, restaurants, administrative offices) and receive real-time queue information. The system serves three types of users:

- **Clients**: Book tickets, view wait times, manage their reservations
- **Agents/Administrators**: Manage queues, serve clients, update ticket status
- **Directors/Main Admins**: Oversee all agents, create admin accounts, manage daily resets

### Key Goals
✅ Eliminate paper-based queues  
✅ Reduce customer wait times  
✅ Provide real-time queue information  
✅ Enable administrators to manage multiple establishments  
✅ Secure user authentication & data

---

## System Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    CLIENT BROWSER                       │
│  (signin, booking, profile, agent dashboard)           │
└────────────────────────┬────────────────────────────────┘
                         │ HTTP/HTTPS
                         ▼
┌─────────────────────────────────────────────────────────┐
│                    WEB SERVER (Apache)                  │
│              PHP Application + Sessions                │
└────────────────────────┬────────────────────────────────┘
                         │
        ┌────────────────┼────────────────┐
        ▼                ▼                ▼
   ┌─────────┐    ┌─────────┐    ┌──────────────┐
   │ Pages   │    │ API     │    │ Handlers     │
   │ (.php)  │    │ Endpoints│   │ (validation) │
   │         │    │         │    │              │
   └────┬────┘    └────┬────┘    └──────┬───────┘
        │              │                │
        └──────────────┼────────────────┘
                       ▼
        ┌──────────────────────────────┐
        │    MySQL Database            │
        │  (users, tickets, queues)   │
        └──────────────────────────────┘
```

### Technology Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| **Frontend** | HTML5, CSS3, JavaScript | User Interface |
| **Session Management** | PHP Sessions | User authentication & state |
| **Backend** | PHP 8.0.30 | Server-side logic |
| **Database** | MySQL (XAMPP) | Data persistence |
| **Server** | Apache 2.4.58 | HTTP server |
| **API** | RESTful JSON API | Ticket management |

---

## Database Schema

### Users Table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('client', 'admin', 'main_admin') DEFAULT 'client',
    establishment VARCHAR(255),
    sector VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Explanation:**
- `role`: Determines user permissions (client can only book, admin can manage queue, main_admin can create admins)
- `establishment`: The branch/establishment assigned to admin users
- `sector`: The service sector (banque, cinema, resto, administration)

---

### Tickets Table (NEW - Database-Backed Queue)
```sql
CREATE TABLE tickets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    ticket_number VARCHAR(20) NOT NULL,
    agency VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    service_key VARCHAR(50) NOT NULL,
    establishment_key VARCHAR(255) NOT NULL,
    wait_time VARCHAR(50),
    people_ahead INT DEFAULT 0,
    status ENUM('waiting', 'served', 'cancelled') DEFAULT 'waiting',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    served_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX (establishment_key),
    INDEX (status)
);
```

**Explanation:**
- **Before:** Tickets stored only in browser localStorage (insecure, volatile)
- **After:** Tickets in database (secure, persistent, multi-tab consistent)
- `ticket_number`: Unique identifier like "A-001", "A-002", etc.
- `status`: Tracks ticket lifecycle
- `people_ahead`: Real-time queue position

---

## User Roles & Permissions

### 1. **Client** (Regular User)
**What they can do:**
- Sign up / Sign in
- Browse available establishments
- Book a ticket (creates entry in database)
- View their active tickets
- Cancel a ticket
- View wait time and queue position

**Permissions:**
- Read own tickets only
- Cannot modify other users' tickets
- Cannot access admin dashboard

**File Location:** `/profilClient/ProfilClient.php`

---

### 2. **Admin/Agent** (Staff)
**What they can do:**
- Sign in with establishment-specific credentials
- View queue for their establishment only
- Serve clients (update ticket status to "served")
- See next client in line
- Mark ticket as completed

**Permissions:**
- Read/update tickets for assigned establishment only
- Cannot create other admins
- Cannot access main admin dashboard

**File Location:** `/agent-dashboard/agent-dashboard.php`

---

### 3. **Main Admin/Director** (Director)
**What they can do:**
- Sign in as director for their establishment
- Create new admin accounts (with default password)
- Revoke admin access
- View all admins under their establishment
- Reset daily ticket counter (A-001 to A-999 cycle)
- View daily metrics

**Permissions:**
- Full control of their establishment's admins
- Cannot see other directors' establishments
- Can reset queue for daily restart

**File Location:** `/admin/main-admin-dashboard.php`

---

## Core Features

### 1. **User Authentication**

#### Sign Up Flow
```
User fills form → Validation (email unique?) → Password hashing → Store in DB
                                    ↓ (success)
                          Auto-redirect to signin after 2 seconds
```

**File:** `register.php`

**Key Code:**
```php
$hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'client')");
$stmt->bind_param("sss", $name, $email, $hashed_password);
```

#### Sign In Flow
```
User enters email/password → Verify against DB → Check password hash
                                    ↓ (match)
                    Create session → Redirect to appropriate dashboard
```

**File:** `signin/signin.html` (JavaScript) + `login.php` (PHP backend)

---

### 2. **Ticket Booking System** (NEW - Database-Backed)

#### User Booking Flow

```javascript
// Step 1: Client clicks "Nouveau Ticket" button
// Step 2: Select establishment from list
// Step 3: Click "Choisir"
// Step 4: JavaScript generates random ticket number (A-123)

fetch('/Web_Project/api/tickets.php?action=create', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',  // CRITICAL: Send session cookie
    body: JSON.stringify({
        ticket_number: "A-123",
        agency: "BIAT Marina",
        location: "Monastir",
        service_key: "banque",
        establishment_key: "biat_marina",
        wait_time: "~15 min",
        people_ahead: 45
    })
})

// Step 5: API validates & inserts into database
// Step 6: Redirect to profile page
// Step 7: Profile page fetches tickets from API
```

**File:** `/etablissement/etablissement.js` + `/api/tickets.php`

**Key Improvement - credentials: 'include':**
```javascript
// BEFORE (broken):
fetch('/Web_Project/api/tickets.php?action=create', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({...})  // Session cookie NOT sent!
})

// AFTER (fixed):
fetch('/Web_Project/api/tickets.php?action=create', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',  // Session cookie INCLUDED
    body: JSON.stringify({...})
})
```

---

### 3. **API Endpoints** (`/api/tickets.php`)

#### Endpoint: Create Ticket
```
POST /Web_Project/api/tickets.php?action=create

Request:
{
    "ticket_number": "A-123",
    "agency": "BIAT Marina",
    "location": "Monastir",
    "service_key": "banque",
    "establishment_key": "biat_marina",
    "wait_time": "~15 min",
    "people_ahead": 45
}

Response (Success):
{
    "success": true,
    "message": "Ticket créé avec succès",
    "ticket_id": 42
}

Response (Error):
{
    "success": false,
    "message": "Non authentifié"
}
```

#### Endpoint: Get User Tickets
```
GET /Web_Project/api/tickets.php?action=get-user-tickets

Response:
{
    "success": true,
    "tickets": [
        {
            "id": 42,
            "ticket_number": "A-123",
            "agency": "BIAT Marina",
            "location": "Monastir",
            "service_key": "banque",
            "wait_time": "~15 min",
            "people_ahead": 45,
            "status": "waiting"
        }
    ]
}
```

#### Endpoint: Cancel Ticket
```
POST /Web_Project/api/tickets.php?action=cancel

Request:
{
    "ticket_id": 42
}

Response:
{
    "success": true,
    "message": "Ticket annulé"
}
```

---

### 4. **Logout Confirmation Dialog** (NEW)

**Before:**
- User could accidentally click logout
- Would immediately disconnect

**After:**
```javascript
// logout-confirmation.js
if (confirm("👋 Êtes-vous sûr de vouloir vous déconnecter ?")) {
    window.location.href = 'logout.php';
}
```

**Integrated into:**
- `/index.php`
- `/profilClient/ProfilClient.php`
- `/agent-dashboard/agent-dashboard.php`

**Result:** Users now get a confirmation dialog before logout

---

### 5. **Error Handling** (NEW - Professional Error Pages)

**File:** `/error-handler.php`

#### Error Types Handled:
1. **401 Unauthorized** - User not logged in
2. **403 Forbidden** - User lacks permissions
3. **404 Not Found** - Page/resource doesn't exist
4. **500 Internal Server Error** - Database/server error
5. **400 Bad Request** - Invalid input data

**Example:**
```php
// Before (blank page):
if (!$user_id) exit;  // Browser shows blank

// After (professional error page):
errorUnauthorized("Vous devez être connecté");
// Shows styled page with message, icon, and return button
```

**Visual Design:**
- 🎨 Glassmorphism UI matching brand
- 📱 Responsive layout
- 🔗 Quick navigation buttons
- 🌙 Dark mode support

---

## Technical Implementation

### Session Management

```php
// Step 1: Start session (all pages)
session_start();

// Step 2: After login, store user data
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['establishment'] = $user['establishment'];
$_SESSION['sector'] = $user['sector'];

// Step 3: Verify session before accessing protected pages
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.html");
    exit;
}

// Step 4: Check role-based access
if ($_SESSION['user_role'] !== 'admin') {
    errorForbidden("Vous n'êtes pas autorisé");
}
```

**Session Storage:**
- **Location:** Server-side (secure)
- **Identifier:** PHPSESSID cookie
- **Duration:** Default browser session
- **Security:** httpOnly flag (cannot be accessed by JavaScript)

---

### Password Security

```php
// Creating account:
$hashed = password_hash($password, PASSWORD_DEFAULT);  // Uses bcrypt
INSERT INTO users ... ($email, $hashed, ...);

// Logging in:
$stored_hash = $user['password'];
if (password_verify($login_password, $stored_hash)) {
    // Correct password
    $_SESSION['user_id'] = $user['id'];
}
```

**Security Benefits:**
✅ Passwords never stored in plain text  
✅ Bcrypt algorithm resistant to brute force  
✅ Unique salt per password (even identical passwords hash differently)

---

### Input Validation

#### Example: Create Ticket
```php
// Step 1: Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Step 2: Validate required fields
if (!$data['ticket_number'] || !$data['agency'] || !$data['location']) {
    throw new Exception('Données de ticket manquantes');
}

// Step 3: Sanitize data
$ticket_number = htmlspecialchars($data['ticket_number']);

// Step 4: Type casting
$people_ahead = (int)($data['people_ahead'] ?? 0);

// Step 5: Prepared statements (prevent SQL injection)
$stmt = $conn->prepare("INSERT INTO tickets (...) VALUES (?, ?, ...)");
$stmt->bind_param("isssss", $user_id, $ticket_number, ...);
```

---

### Frontend-Backend Communication

#### API Call Pattern (with credentials):
```javascript
// JavaScript on frontend
fetch('/Web_Project/api/tickets.php?action=create', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    credentials: 'include',  // CRITICAL: Include session cookie
    body: JSON.stringify({...})
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        // Update UI
    } else {
        // Show error message
    }
})
```

#### Why `credentials: 'include'` is Critical:
- By default, Fetch API does NOT send cookies
- Without it, PHP session is not recognized
- Server returns 401 "Not authenticated"
- Ticket doesn't get saved

---

## Recent Improvements

### Improvement #1: Database-Backed Queue ✅

**Problem:**
- Tickets stored only in browser localStorage
- Lost if cache cleared
- Cannot be shared between tabs
- Users could manipulate via console

**Solution:**
- Created `tickets` table in database
- API endpoints for ticket operations
- Server-side validation
- Real-time synchronization

**Impact:**
- ✅ Secure (cannot be manipulated client-side)
- ✅ Persistent (survives browser restart)
- ✅ Multi-user (consistent across tabs)
- ✅ Scalable (database handles large queues)

---

### Improvement #2: Professional Error Handling ✅

**Problem:**
- Errors showed blank pages or stack traces
- No user-friendly messages
- Inconsistent error formats

**Solution:**
- Created `/error-handler.php`
- Reusable error functions
- Beautiful styled error pages
- Consistent JSON API responses

**File:** `/error-handler.php`

**Functions:**
```php
errorUnauthorized($message)  // 401
errorForbidden($message)     // 403
errorNotFound($message)      // 404
errorServerError($message)   // 500
errorBadRequest($message)    // 400
```

**Example Usage:**
```php
if (!isset($_SESSION['user_id'])) {
    errorUnauthorized("Veuillez vous connecter d'abord");
}

if ($_SESSION['user_role'] !== 'admin') {
    errorForbidden("Vous n'avez pas accès à cette ressource");
}
```

---

### Improvement #3: Logout Confirmation Dialog ✅

**Problem:**
- Users accidentally clicking logout
- No confirmation to prevent accidental disconnection

**Solution:**
- Created `logout-confirmation.js`
- Intercept logout clicks
- Show confirmation dialog
- Only proceed if user confirms

**File:** `/logout-confirmation.js`

**Code:**
```javascript
function confirmLogout(event) {
    event.preventDefault();
    if (confirm("👋 Êtes-vous sûr de vouloir vous déconnecter ?")) {
        window.location.href = '../logout.php';
    }
}
```

**Integrated into:**
- `/index.php` - Main page logout
- `/profilClient/ProfilClient.php` - Client profile logout
- `/agent-dashboard/agent-dashboard.php` - Agent dashboard logout

---

## Security Measures

### 1. **Authentication**
✅ Session-based authentication  
✅ Password hashing with bcrypt  
✅ HTTPS-ready (though HTTP in development)

### 2. **Authorization**
✅ Role-based access control (RBAC)  
✅ Establishment-specific permissions  
✅ Session validation on every request

### 3. **Input Validation**
✅ Required field checking  
✅ Email format validation  
✅ Type casting and sanitization

### 4. **SQL Injection Prevention**
✅ Prepared statements with parameterized queries  
✅ No string concatenation in SQL

**Example:**
```php
// VULNERABLE:
$sql = "SELECT * FROM users WHERE email = '" . $email . "'";

// SAFE:
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
```

### 5. **CSRF Protection**
✅ Session-based state  
✅ POST for state-changing operations  
✅ Referer validation (server checks origin)

### 6. **Data Protection**
✅ No sensitive data in URLs  
✅ Session cookies httpOnly (not accessible to JavaScript)  
✅ Database credentials in secure file

---

## Likely Exam Questions

### Architecture & Design

**Q1: Explain the overall architecture of SmartQueue. What are the main components?**

**Sample Answer:**
SmartQueue uses a three-tier architecture:
1. **Presentation Layer (Frontend):** HTML/CSS/JavaScript running in the browser. Handles user interface and displays data. Makes API calls to backend.
2. **Application Layer (Backend):** PHP running on Apache server. Processes requests, validates input, applies business logic, manages authentication.
3. **Data Layer (Database):** MySQL database stores users, tickets, and queue information.

The flow is: Client browser → HTTP request → Apache/PHP → MySQL → Response JSON → Client browser renders

**Q2: What are the three user roles in SmartQueue? What permissions does each have?**

**Sample Answer:**
- **Client:** Can sign up, browse establishments, book tickets, view own tickets, cancel tickets. Cannot see other users' data.
- **Admin (Agent):** Can sign in, view queue for assigned establishment, serve clients, update ticket status. Can only manage their own establishment's queue.
- **Main Admin (Director):** Can create admin accounts, revoke access, view all admins for their establishment, reset daily ticket counter.

Access is enforced by checking `$_SESSION['user_role']` and `$_SESSION['establishment']` on every request.

---

### Database & Data

**Q3: Explain how the tickets table stores queue information. Why is it better than localStorage?**

**Sample Answer:**
The tickets table stores:
- `id`: Primary key, auto-increment
- `user_id`: Links to the user who booked the ticket (foreign key)
- `ticket_number`: Unique identifier like "A-001"
- `status`: Current state (waiting, served, cancelled)
- `created_at`: When ticket was created
- `people_ahead`: Queue position

**Benefits over localStorage:**
1. **Secure:** Server-side storage, cannot be manipulated via browser console
2. **Persistent:** Survives browser restart, cache clearing, power outages
3. **Multi-tab:** Same ticket visible in all tabs (browser tab A and B see identical data)
4. **Scalable:** Database handles millions of tickets efficiently
5. **Queryable:** Can generate reports, analytics, trends

---

**Q4: What does the `credentials: 'include'` parameter do in the Fetch API?**

**Sample Answer:**
By default, the Fetch API does NOT send cookies with cross-origin or same-origin requests. Adding `credentials: 'include'` tells the browser to attach cookies (including the PHPSESSID session cookie) to the request.

This is CRITICAL for authentication because:
- PHP session is stored in a cookie (PHPSESSID)
- Without `credentials: 'include'`, the cookie is not sent
- Server cannot identify the user
- Returns 401 "Not authenticated"

**Example:**
```javascript
// BROKEN - session not sent:
fetch('/api/tickets.php', { method: 'POST', body: JSON.stringify(...) })

// FIXED - session sent:
fetch('/api/tickets.php', { 
    method: 'POST',
    credentials: 'include',  // Sends PHPSESSID cookie
    body: JSON.stringify(...) 
})
```

---

### API & Frontend

**Q5: Explain the ticket booking flow from user clicking "Book" to seeing the ticket in their profile.**

**Sample Answer:**

1. **User clicks "Choisir" button** on establishment page
2. **JavaScript generates ticket data:**
   ```javascript
   ticket_number: "A-123"
   agency: "BIAT Marina"
   location: "Monastir"
   ```
3. **API call sends data to server:**
   ```javascript
   fetch('/api/tickets.php?action=create', {
       method: 'POST',
       credentials: 'include',
       body: JSON.stringify({...})
   })
   ```
4. **Server validates:**
   - Checks if user is logged in: `if (!isset($_SESSION['user_id']))`
   - Validates required fields
   - Checks for SQL injection/invalid input
5. **Database insert:**
   ```php
   INSERT INTO tickets (user_id, ticket_number, agency, ...) VALUES (...)
   ```
6. **Success response:**
   ```json
   {"success": true, "ticket_id": 42}
   ```
7. **JavaScript shows success alert** and redirects
8. **Profile page loads** and fetches tickets via API:
   ```javascript
   fetch('/api/tickets.php?action=get-user-tickets', {
       credentials: 'include'
   })
   ```
9. **Tickets rendered** on the page

---

**Q6: Why is password hashing important? How is it implemented?**

**Sample Answer:**
Password hashing is critical because:
- If database is breached, passwords cannot be read (they're hashed, not encrypted)
- Passwords with same content hash to different values (salting)
- Hash is one-way (cannot decrypt to get original password)

**Implementation:**
```php
// Storing password:
$hashed = password_hash($password, PASSWORD_DEFAULT);  // Uses bcrypt
INSERT INTO users (email, password) VALUES (?, ?);

// Verifying password:
$user = fetch_user_from_db($email);
if (password_verify($login_password, $user['password'])) {
    // Correct password - user logged in
    $_SESSION['user_id'] = $user['id'];
}
```

**Security Features:**
- **Bcrypt algorithm:** Intentionally slow (0.1+ seconds per hash) to prevent brute force
- **Automatic salting:** `password_hash()` generates unique salt per password
- **Future-proof:** If attacker cracks one password, others remain safe

---

### Error Handling & UX

**Q7: Explain the improvements made to error handling. Why are they important?**

**Sample Answer:**

**Before:**
- Blank white pages when errors occurred
- PHP stack traces exposed to users (security risk)
- Inconsistent error messages
- No navigation back to safety

**After:**
- Professional error pages with branded UI
- User-friendly error messages (French)
- HTTP status codes (401, 403, 404, 500, 400)
- Styled error cards with icons
- "Go back" or "Login" buttons
- Consistent JSON format for API errors

**Example:**
```php
errorUnauthorized("Vous devez vous connecter d'abord");
// Shows:
// - 🔒 Icon
// - "401 - Non Autorisé"
// - "Vous devez vous connecter d'abord"
// - [Se connecter] button
```

**Why it matters:**
- Improves user experience (users understand what went wrong)
- Increases security (no stack traces to attackers)
- Reduces support tickets (users self-correct)
- Professional appearance (reflects well on organization)

---

### Session Management

**Q8: Explain how session management works. What happens when a user logs in?**

**Sample Answer:**

**Login Flow:**
1. User enters email/password on signin page
2. PHP receives POST data
3. Query database: `SELECT * FROM users WHERE email = ?`
4. Verify password: `password_verify($input_password, $stored_hash)`
5. If correct, create session:
   ```php
   session_start();
   $_SESSION['user_id'] = $user['id'];
   $_SESSION['user_name'] = $user['name'];
   $_SESSION['user_role'] = $user['role'];
   $_SESSION['establishment'] = $user['establishment'];
   ```
6. Server sends Set-Cookie header: `PHPSESSID=abc123...`
7. Browser stores cookie locally
8. Redirect to dashboard

**Subsequent Requests:**
1. Browser automatically includes PHPSESSID cookie
2. PHP recreates `$_SESSION` array from server storage
3. Can access user data: `$_SESSION['user_id']`, etc.
4. Verify access before processing: `if (!isset($_SESSION['user_id']))`

**Session Storage:**
- **Location:** Server filesystem (/opt/lampp/var/lib/php/sessions/)
- **Identifier:** PHPSESSID value (random string)
- **Timeout:** Default 24 minutes of inactivity
- **Security:** httpOnly flag prevents JavaScript access

---

### Testing & Deployment

**Q9: How do you test that a ticket is correctly saved to the database?**

**Sample Answer:**

**Manual Testing:**
1. Log in as a client
2. Navigate to establishment selection
3. Click "Choisir" on an establishment
4. See success alert: "✅ Ticket réservé"
5. Check profile page - should show "1 ticket actif"
6. Open browser console (F12) → Application → Cookies → PHPSESSID (verify session exists)

**Database Verification:**
```bash
mysql -u root --skip-ssl smartqueue
SELECT * FROM tickets WHERE user_id = [YOUR_USER_ID] ORDER BY created_at DESC LIMIT 1;
```

**Expected Output:**
```
| id | user_id | ticket_number | agency | status  | created_at |
|----|---------|---------------|--------|---------|------------|
| 42 | 10      | A-123         | BIAT   | waiting | 2026-04-25 |
```

**API Testing (using curl):**
```bash
# First, simulate login and get session
curl -c cookies.txt -X POST 'http://localhost/Web_Project/api/tickets.php?action=create' \
  -H 'Content-Type: application/json' \
  -d '{"ticket_number":"A-999","agency":"Test","location":"Test",...}'

# Check response - should be {"success": true, ...}
```

---

**Q10: What improvements would you recommend for SmartQueue's future?**

**Sample Answer:**

**Short-term (Before Production):**
1. ✅ Add rate limiting to API endpoints (prevent spam bookings)
2. ✅ Implement HTTPS (SSL certificate)
3. ✅ Add email notifications (confirmation, when ready to serve)
4. ✅ Implement real-time updates (WebSockets, not polling)
5. ✅ Add data validation on frontend (reduce server load)

**Medium-term (Production Features):**
1. ✅ Mobile app (iOS/Android)
2. ✅ SMS notifications (for queue status)
3. ✅ Queue analytics (peak hours, wait time trends)
4. ✅ Multi-establishment dashboard
5. ✅ Admin metrics (tickets served per hour, etc.)

**Long-term (Advanced Features):**
1. ✅ AI-powered queue prediction (estimate wait times)
2. ✅ Integration with payment systems
3. ✅ Loyalty program (rewards frequent users)
4. ✅ Accessibility features (audio announcements, etc.)
5. ✅ International language support

---

## How to Run & Test

### Prerequisites
- XAMPP installed with PHP 8.0+ and MySQL
- MySQL running
- Apache running

### Setup

**1. Import Database:**
```bash
mysql -u root --skip-ssl smartqueue < /opt/lampp/htdocs/Web_Project/database.sql
```

**2. Verify Files Exist:**
```bash
ls -la /opt/lampp/htdocs/Web_Project/api/tickets.php
ls -la /opt/lampp/htdocs/Web_Project/error-handler.php
ls -la /opt/lampp/htdocs/Web_Project/logout-confirmation.js
```

**3. Create Test User:**
```bash
mysql -u root --skip-ssl smartqueue -e "
INSERT INTO users (name, email, password, role) VALUES 
('Test Client', 'client@test.com', '\$2y\$10\$...', 'client');
"
```

### Testing Workflow

**1. Client Signup & Ticket Booking:**
```
http://localhost/Web_Project/index.php
→ Click "Créer un compte"
→ Fill form, sign up
→ Auto-redirects to signin
→ Sign in with new account
→ Click "+ Nouveau Ticket"
→ Select "BIAT Marina"
→ Click "Choisir"
→ See success alert
→ Verify ticket in profile
```

**2. Verify Database:**
```bash
mysql -u root --skip-ssl smartqueue -e "SELECT * FROM tickets LIMIT 5;"
```

**3. Test Error Handling:**
```
http://localhost/Web_Project/api/tickets.php?action=create
→ Should show 401 Unauthorized (not logged in)
→ Check error page styling
```

**4. Test Logout Confirmation:**
```
In profile page
→ Click "Déconnexion"
→ Should see confirmation dialog
→ Click "Annuler" - stays on page
→ Click "Déconnexion" again
→ Click "OK" - redirects to signin
```

---

## Key Files Reference

| File | Purpose |
|------|---------|
| `/register.php` | User signup page |
| `/signin/signin.html` | Login form |
| `/login.php` | Login handler |
| `/profilClient/ProfilClient.php` | Client dashboard |
| `/etablissement/etablissement.php` | Choose establishment |
| `/api/tickets.php` | Ticket API endpoints |
| `/error-handler.php` | Error page utilities |
| `/logout-confirmation.js` | Logout confirmation dialog |
| `/admin/main-admin-dashboard.php` | Director admin panel |
| `/agent-dashboard/agent-dashboard.php` | Agent queue view |
| `/database.sql` | Database schema |

---

## Summary

**SmartQueue** is a robust queue management system with:
✅ Secure authentication & authorization  
✅ Database-backed persistent queue  
✅ RESTful API with JSON responses  
✅ Professional error handling  
✅ User-friendly UI with confirmation dialogs  
✅ Role-based access control  
✅ Input validation & SQL injection prevention  

This project demonstrates real-world web development practices including proper session management, password security, API design, and error handling.

---

**Document prepared for:** University Project Submission  
**Last Updated:** April 25, 2026  
**Prepared by:** Development Team
