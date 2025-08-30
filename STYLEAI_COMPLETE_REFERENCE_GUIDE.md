# 🎨 StyleAI Professional - Complete Reference & Debug Guide

## 📋 **APPLICATION OVERVIEW**

**StyleAI Professional** is a freemium salon widget that transforms hairstyles using AI. It features a sophisticated color palette system, premium hairstyle options, and integrated payment processing.

### **Architecture Summary**
- **Frontend**: Static HTML widget deployed on Vercel
- **Backend**: Laravel PHP API deployed on Railway  
- **Database**: Supabase (PostgreSQL)
- **AI Service**: fal.ai for hairstyle transformations
- **Payments**: Stripe (test/live modes)
- **Authentication**: Supabase Auth

---

## 🌐 **DEPLOYMENT ARCHITECTURE**

### **Production URLs**
```
Frontend (Vercel): https://laraver-final-ai-headshot-o6n3.vercel.app/styleai-widget.html
Backend (Railway): https://web-production-5e40.up.railway.app
Health Check: https://web-production-5e40.up.railway.app/api/health
Webhook Test: https://web-production-5e40.up.railway.app/api/payments/webhook-test
```

### **Local Development URLs**
```
Frontend: http://localhost:8080/styleai-widget.html
Backend: http://localhost:8001
Vite Dev Server: http://localhost:5173
```

---

## 🎯 **CORE FEATURES & IMPLEMENTATION**

### **1. Freemium Model**
- **Basic Hairstyles**: Free access (Classic Bob, Beach Waves, Straight Hair)
- **Premium Hairstyles**: Locked for paying customers (Curly Afro, Vintage Hollywood, etc.)
- **Single-Tone Colors**: Free access
- **Accented Colors**: Premium feature with advanced controls

### **2. Color System Architecture**

#### **Single-Tone Colors (Free)**
```javascript
professionalColorPalette = [
    { name: 'No Color Change', value: 'none', isPremium: false },
    { name: 'Jet Black', value: '#1C1C1C', isPremium: false },
    { name: 'Chocolate Brown', value: '#3C2415', isPremium: false },
    // ... more free colors
]
```

#### **Accented Colors (Premium)**
```javascript
accentedColors = [
    {
        name: 'Golden Blonde',
        baseColor: '#8B4513',
        accentColor: '#FFD700',
        technique: 'balayage',
        // ... premium parameters
    }
]
```

### **3. User Interface Design**
Based on Color Style Demo professional design:
- **Layout**: `max-w-4xl mx-auto` (professional salon scale)
- **Background**: `bg-gradient-to-br from-slate-50 via-gray-50 to-purple-50`
- **Color Swatches**: Square (`w-12 h-12`) with `rounded-lg` borders
- **Interactive Elements**: Professional hover states and animations

---

## 🔧 **TECHNICAL IMPLEMENTATION**

### **Frontend Stack**
- **Framework**: React (via Babel standalone)
- **Styling**: Tailwind CSS
- **Build Tool**: Vite
- **Deployment**: Vercel (static site)

### **Backend Stack**
- **Framework**: Laravel 10.x
- **Database**: Supabase PostgreSQL
- **Deployment**: Railway
- **Web Server**: PHP built-in server

### **External Services**
- **AI Generation**: fal.ai (`fal-ai/flux-pro` model)
- **Authentication**: Supabase Auth
- **Payments**: Stripe Checkout
- **File Storage**: Base64 encoding (no file storage needed)

---

## 🔑 **ENVIRONMENT CONFIGURATION**

### **Railway Environment Variables**
```bash
# Core Laravel
APP_NAME=StyleAI Professional
APP_ENV=production
APP_KEY=base64:Y0Rgc2PX3vssQxw/JcgWYZhtnYuW9JE016fec0TjXh8=
APP_DEBUG=false
APP_URL=https://web-production-5e40.up.railway.app

# Database
DB_CONNECTION=sqlite
DB_DATABASE=/app/database/database.sqlite

# Session & Cache
SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync

# External Services
FAL_KEY=4d4f3d66-f99b-48b9-9b3b-af0ca1668c2f:34f7573ed94f9d2123d653f962c8bc42
SUPABASE_URL=https://nxxznqnrxomzudghktrz.supabase.co
SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
SUPABASE_SERVICE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...

# Stripe (Test Keys)
STRIPE_KEY=pk_test_your_publishable_key
STRIPE_SECRET=sk_test_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret

# CORS
FRONTEND_URL=https://laraver-final-ai-headshot-o6n3.vercel.app
```

### **Vercel Configuration**
```json
// vercel.json
{
  "framework": "vite",
  "buildCommand": "npm run build",
  "outputDirectory": "dist",
  "installCommand": "npm install",
  "rewrites": [
    { "source": "/", "destination": "/index.html" },
    { "source": "/styleai-widget.html", "destination": "/public/styleai-widget.html" }
  ]
}
```

---

## 🚀 **API ENDPOINTS REFERENCE**

### **Health & Testing**
```
GET  /api/health                    - Laravel health check
GET  /api/payments/webhook-test     - Stripe configuration test
GET  /api/auth/test-supabase       - Supabase connection test
GET  /api/fal/test                 - fal.ai connection test
```

### **Authentication (Supabase)**
```
POST /api/auth/register            - User registration
POST /api/auth/login               - User login
GET  /api/auth/profile             - Get user profile
GET  /api/auth/can-transform       - Check transformation credits
POST /api/auth/consume-transformation - Consume transformation credit
```

### **AI Transformations (fal.ai)**
```
POST /api/fal/direct-base64-transform - Base64 image transformation
POST /api/fal/robust-transform        - Multi-fallback transformation
GET  /api/fal/check-status           - Check transformation status
GET  /api/fal/get-result             - Get transformation result
```

### **Payments (Stripe)**
```
POST /api/payments/create-checkout-session - Create payment session
GET  /api/payments/status                  - Check payment status
POST /api/payments/webhook                 - Stripe webhook handler
```

### **Content Management**
```
GET /api/styles/hairstyles         - Get available hairstyles
GET /api/styles/colors             - Get color palette
GET /api/styles/subscription-plans - Get pricing plans
```

---

## 🎨 **USER INTERFACE COMPONENTS**

### **Main Widget Structure**
```html
<div class="styleai-widget">
  <!-- User Menu & Token Counter -->
  <div class="user-controls">
    <!-- Professional card-style user button -->
    <!-- Token counter with gradient design -->
    <!-- Developer mode toggle -->
  </div>
  
  <!-- Image Upload Section -->
  <div class="image-upload">
    <!-- Drag & drop or click to upload -->
    <!-- Base64 conversion for direct processing -->
  </div>
  
  <!-- Popular Presets (Before Color Selection) -->
  <div class="popular-presets">
    <!-- Honey Balayage (Free) -->
    <!-- Classic Brunette (Premium) -->
    <!-- Golden Goddess (Premium) -->
  </div>
  
  <!-- Hairstyle Selection -->
  <div class="hairstyle-selection">
    <!-- Basic styles (free) -->
    <!-- Premium styles (locked with upgrade prompt) -->
  </div>
  
  <!-- Color Selection -->
  <div class="color-selection">
    <!-- Single-tone colors (free) -->
    <!-- Accented colors (premium with controls) -->
  </div>
  
  <!-- Generation Controls -->
  <div class="generation-controls">
    <!-- Transform button with validation -->
    <!-- Loading states with witty messages -->
  </div>
  
  <!-- Authentication Modal -->
  <div class="auth-modal">
    <!-- Enhanced registration form with: -->
    <!-- - Password confirmation field -->
    <!-- - Password visibility toggles -->
    <!-- - Real-time validation feedback -->
    <!-- - Professional styling -->
  </div>
</div>
```

### **Color Selection Implementation**
```javascript
// Color choice object structure
colorChoice = {
  type: 'single-tone' | 'accented',
  singleTone: { name: 'Color Name', value: '#hex' },
  accented: {
    name: 'Golden Blonde',
    baseColor: '#8B4513',
    accentColor: '#FFD700',
    technique: 'balayage',
    intensity: 75,
    placement: 'highlights',
    blend: 'natural'
  }
}
```

### **Premium Feature Logic**
```javascript
// Check if user can access premium features
const canAccessPremium = userProfile?.is_premium || isTestMode;

// Lock premium hairstyles
if (style.isPremium && !canAccessPremium) {
  // Show upgrade prompt
}

// Lock premium color parameters (except Honey Balayage)
if (isPremiumParameter && !canAccessPremium && !isHoneyBalayage) {
  // Show lock overlay
}
```

---

## 🔄 **DATA FLOW & STATE MANAGEMENT**

### **Application State**
```javascript
// Core state variables
const [selectedStyle, setSelectedStyle] = useState(null);
const [colorChoice, setColorChoice] = useState({ type: 'single-tone' });
const [uploadedImage, setUploadedImage] = useState(null);
const [userProfile, setUserProfile] = useState(null);
const [isTestMode, setIsTestMode] = useState(false);
const [isGenerating, setIsGenerating] = useState(false);
```

### **Generation Flow**
```
1. Image Upload → Base64 conversion
2. Style/Color Selection → Validation
3. Authentication Check → Supabase verification
4. Credit Check → Token validation
5. AI Generation → fal.ai API call
6. Result Processing → Display transformed image
7. Credit Consumption → Update user tokens
```

### **Payment Flow**
```
1. Premium Feature Access → Upgrade prompt
2. Stripe Checkout → Payment session creation
3. Payment Processing → Stripe handles transaction
4. Webhook Notification → Laravel receives event
5. User Upgrade → Supabase profile update
6. Feature Unlock → Premium access granted
```

---

## 🐛 **COMMON DEBUGGING SCENARIOS**

### **1. Frontend-Backend Connection Issues**

**Symptoms:**
- CORS errors in browser console
- API calls failing with 404/500
- "Network Error" messages

**Debug Steps:**
```javascript
// Check API URL configuration
console.log('API_BASE_URL:', API_BASE_URL);

// Test backend health
fetch(API_BASE_URL + '/api/health')
  .then(r => r.json())
  .then(console.log);
```

**Common Fixes:**
- Verify Railway backend is running
- Check CORS configuration in `config/cors.php`
- Ensure environment variables are set in Railway

### **2. Image Generation Failures**

**Symptoms:**
- "Generation failed" errors
- Null reference errors (`Cannot read properties of null`)
- fal.ai API errors

**Debug Steps:**
```javascript
// Check image upload
console.log('Uploaded image:', uploadedImage);

// Check style/color selection
console.log('Selected style:', selectedStyle);
console.log('Color choice:', colorChoice);

// Check API response
console.log('Transform response:', transformResponse);
```

**Common Fixes:**
- Ensure `selectedStyle` and `colorChoice` are properly set
- Verify fal.ai API key is correct
- Check image is properly converted to base64

### **3. Authentication Issues**

**Symptoms:**
- Login/register failures
- Token count not updating
- Premium features not unlocking

**Debug Steps:**
```javascript
// Test Supabase connection
fetch(API_BASE_URL + '/api/auth/test-supabase')
  .then(r => r.json())
  .then(console.log);

// Check user profile
console.log('User profile:', userProfile);
```

**Common Fixes:**
- Verify Supabase keys in Railway environment
- Check Supabase RLS policies
- Ensure user_profiles table exists

### **4. Payment/Webhook Issues**

**Symptoms:**
- Payment succeeds but user not upgraded
- Webhook events not received
- Premium features still locked after payment

**Debug Steps:**
```bash
# Check Railway logs for webhook events
# Look for "Premium upgrade completed" messages

# Test webhook configuration
curl https://web-production-5e40.up.railway.app/api/payments/webhook-test

# Check Stripe dashboard for webhook delivery attempts
```

**Common Fixes:**
- Verify webhook secret in Railway matches Stripe
- Check webhook endpoint is accessible
- Ensure CSRF protection is disabled for webhook route

---

## 🔍 **DEBUGGING TOOLS & ENDPOINTS**

### **Built-in Debug Features**

#### **Developer Mode**
```javascript
// Toggle developer mode
setIsTestMode(true);

// Bypasses:
- Authentication requirements
- Token consumption
- Premium restrictions (partial)
```

#### **Debug Endpoints**
```
GET /api/health                    - Overall system health
GET /api/payments/webhook-test     - Stripe configuration status
GET /api/auth/test-supabase       - Supabase connectivity
GET /api/fal/test                 - fal.ai API status
```

#### **Browser Console Debugging**
```javascript
// Check application state
console.log('App state:', {
  selectedStyle,
  colorChoice,
  userProfile,
  isTestMode,
  uploadedImage
});

// Test API connectivity
testConnection();
testSupabase();
```

### **Server-side Debugging**

#### **Railway Logs**
```bash
# Access via Railway Dashboard → Your Service → Logs
# Look for:
- Laravel application errors
- Webhook processing logs
- API request/response logs
- Database connection issues
```

#### **Log Patterns to Watch**
```
✅ "Configuration cached successfully"
✅ "Routes cached successfully" 
✅ "Server running on [http://0.0.0.0:8080]"
✅ "Premium upgrade completed"

❌ "No application encryption key"
❌ "Database connection failed"
❌ "Invalid webhook signature"
❌ "Failed to upgrade user to premium"
```

---

## 🎨 **UI/UX DESIGN SPECIFICATIONS**

### **Professional Design System**
Based on Color Style Demo clean professional design:

#### **Layout Foundation**
```css
/* Container */
max-w-4xl mx-auto

/* Background */
bg-gradient-to-br from-slate-50 via-gray-50 to-purple-50

/* Cards */
bg-white rounded-2xl shadow-2xl p-8

/* Spacing */
space-y-8 between sections
mb-8 for headers
```

#### **Color Swatches (Key Design Element)**
```css
/* Shape: Square (NOT circles) */
w-12 h-12 rounded-lg

/* Border States */
border-2 border-gray-300              /* Default */
border-purple-500 shadow-lg scale-110 /* Selected */
hover:scale-105 hover:border-gray-400 /* Hover */

/* Animation */
transition-all duration-200
```

#### **Interactive Elements**
```css
/* Tabs */
bg-gray-100 rounded-lg p-1
bg-white shadow-lg /* Active */

/* Typography */
text-xl font-bold /* Headers */

/* Grid System */
grid-cols-6 md:grid-cols-8 gap-4 /* Responsive color grid */

/* Buttons */
py-3 px-6 /* Minimum touch targets */
```

### **Premium Feature Indicators**
```html
<!-- Premium Lock Overlay -->
<div class="absolute inset-0 bg-black/20 rounded-lg flex items-center justify-center">
  <div class="bg-white rounded-full p-2">
    <svg class="w-4 h-4 text-purple-600"><!-- Lock icon --></svg>
  </div>
</div>

<!-- Premium Badge -->
<div class="absolute -top-1 -right-1 bg-gradient-to-r from-purple-500 to-pink-500 text-white text-xs px-2 py-1 rounded-full">
  PRO
</div>
```

---

## 🔄 **STATE MANAGEMENT PATTERNS**

### **Color Selection Logic**
```javascript
// Helper function for safe color name retrieval
const getSelectedColorName = () => {
  if (colorChoice.type === 'accented' && colorChoice.accented) {
    return colorChoice.accented.name || 'Unknown Accented Color';
  }
  if (colorChoice.type === 'single-tone' && colorChoice.singleTone) {
    return colorChoice.singleTone.name || 'Unknown Color';
  }
  return 'No Color Selected';
};

// Color selection handlers
const handleSingleToneSelect = (color) => {
  setColorChoice({
    type: 'single-tone',
    singleTone: color,
    accented: null
  });
};

const handleAccentedSelect = (accentedColor) => {
  setColorChoice({
    type: 'accented',
    accented: accentedColor,
    singleTone: null
  });
};
```

### **Generation Validation**
```javascript
// Pre-generation validation (simplified)
const canGenerate = selectedStyle && uploadedImage;

// Color selection is optional - users can generate with just hairstyle
// Generation button state
disabled={!canGenerate || isGenerating}

// Button condition in UI
{selectedStyle && (
  <button onClick={checkAuthAndProceed} disabled={isGenerating}>
    Generate Your New Hairstyle
  </button>
)}
```

#### **Validation Rules:**
- ✅ **Hairstyle**: Required (shows validation toast if missing)
- ✅ **Image**: Required (handled by upload flow)
- ✅ **Color**: Optional (users can generate without color changes)
- ✅ **Authentication**: Required for non-test mode

### **Premium Access Control**
```javascript
// Check premium access
const canAccessPremium = userProfile?.is_premium || isTestMode;

// Selective premium locks (Honey Balayage exception)
const isHoneyBalayage = colorChoice.accented?.name === 'Golden Blonde';
const shouldLockParameter = isPremiumParameter && !canAccessPremium && !isHoneyBalayage;
```

---

## 🔐 **AUTHENTICATION & SECURITY**

### **Supabase Production Configuration**

**Critical:** Supabase Dashboard must be configured with production URLs:

#### **Site URL (Supabase Dashboard):**
```
https://laraver-final-ai-headshot-o6n3.vercel.app
```

#### **Redirect URLs (Supabase Dashboard):**
```
https://laraver-final-ai-headshot-o6n3.vercel.app/auth-confirm.html
https://laraver-final-ai-headshot-o6n3.vercel.app/styleai-widget.html
https://laraver-final-ai-headshot-o6n3.vercel.app/
http://localhost:8080/auth-confirm.html
http://localhost:8080/styleai-widget.html
http://localhost:8080/
```

**⚠️ Common Issue:** If email confirmations redirect to localhost, check these URL configurations in Supabase Dashboard → Authentication → URL Configuration.

### **Enhanced Registration Form Features**

#### **Password Confirmation & Visibility Toggle**
```javascript
// Registration form state
const [password, setPassword] = useState('');
const [confirmPassword, setConfirmPassword] = useState('');
const [showPassword, setShowPassword] = useState(false);
const [showConfirmPassword, setShowConfirmPassword] = useState(false);

// Password validation
if (mode === 'register') {
  if (password !== confirmPassword) {
    alert('Passwords do not match. Please try again.');
    return;
  }
  if (password.length < 6) {
    alert('Password must be at least 6 characters long.');
    return;
  }
}
```

#### **UI Features:**
- ✅ **Password Visibility Toggle**: Eye icon to show/hide password
- ✅ **Password Confirmation**: Separate field for password verification
- ✅ **Real-time Validation**: Visual feedback when passwords don't match
- ✅ **Error Highlighting**: Red border and background for mismatched passwords

#### **Seamless Auto-Login After Email Confirmation**
```javascript
// Enhanced auth confirmation flow
async function goToWidget() {
  const tokens = getTokensFromUrl();
  
  if (tokens.access_token) {
    // Fetch complete user profile using access token
    const profileResponse = await fetch(`${API_BASE_URL}/api/auth/profile`, {
      headers: { 'Authorization': `Bearer ${tokens.access_token}` }
    });
    
    if (profileResponse.ok) {
      const profileData = await profileResponse.json();
      
      // Store complete authentication data
      localStorage.setItem('supabase_auth', JSON.stringify({
        access_token: tokens.access_token,
        refresh_token: tokens.refresh_token,
        expires_at: tokens.expires_at,
        user: profileData.user,
        profile: profileData.profile
      }));
    }
    
    // Redirect with auto-login flag
    window.location.href = `${baseUrl}/styleai-widget.html?auth=confirmed&auto_login=true`;
  }
}
```

#### **Auto-Login Features:**
- ✅ **Seamless Experience**: No manual login required after email confirmation
- ✅ **Profile Pre-fetch**: User data loaded during confirmation process
- ✅ **Welcome Message**: Personalized greeting for new users
- ✅ **Token Management**: Secure storage of authentication tokens
- ✅ **Fallback Handling**: Graceful degradation if profile fetch fails

#### **Beautiful Welcome Banner System**
```javascript
// Welcome banner with confetti animation
const showWelcome = (userData, profileData, isNewUser = false) => {
  const tokensRemaining = profileData?.tokens_remaining || 10;
  const userName = userData?.name || profileData?.name || 'Stylist';
  
  setWelcomeData({
    name: userName,
    tokens: tokensRemaining,
    isNewUser: isNewUser,
    isPremium: profileData?.is_premium || false
  });
  
  setShowWelcomeBanner(true);
  setShowConfetti(true);
  
  // Hide confetti after 3 seconds
  setTimeout(() => setShowConfetti(false), 3000);
  
  // Auto-hide banner after 8 seconds
  setTimeout(() => setShowWelcomeBanner(false), 8000);
};
```

#### **Welcome Banner Features:**
- ✅ **Confetti Animation**: 50 colorful particles falling with CSS animations
- ✅ **Personalized Greeting**: Uses actual user name and token count
- ✅ **Premium Upsell**: "Unlock All Premium Features" button for free users
- ✅ **Professional Design**: Gradient backgrounds and smooth animations
- ✅ **Auto-dismiss**: Banner auto-hides after 8 seconds
- ✅ **Celebration Icon**: Beautiful gradient icon with professional styling
- ✅ **Selective Display**: Only shows for first-time users (email confirmation), not regular logins

#### **Improved Error Notifications**
```javascript
// Subtle toast notifications instead of alerts
const showNotification = (message, type = 'error') => {
  const notification = document.createElement('div');
  notification.className = `fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 ${
    type === 'error' ? 'bg-red-100 border border-red-300 text-red-700' :
    type === 'warning' ? 'bg-orange-100 border border-orange-300 text-orange-700' :
    'bg-green-100 border border-green-300 text-green-700'
  }`;
  notification.innerHTML = message;
  document.body.appendChild(notification);
  
  setTimeout(() => {
    if (notification.parentNode) {
      notification.parentNode.removeChild(notification);
    }
  }, 3000);
};
```

#### **Error Notification Features:**
- ✅ **Non-intrusive**: Toast notifications instead of blocking alerts
- ✅ **Auto-dismiss**: Disappear after 3 seconds
- ✅ **Color-coded**: Red for errors, orange for warnings
- ✅ **Professional Styling**: Consistent with app design
- ✅ **Better UX**: Users can continue using the app while seeing the message

### **Supabase Authentication Flow**
```javascript
// Registration
const handleRegister = async (email, password, name) => {
  const response = await fetch(`${API_BASE_URL}/api/auth/register`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password, name })
  });
};

// Login
const handleLogin = async (email, password) => {
  const response = await fetch(`${API_BASE_URL}/api/auth/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
};
```

### **Token Management**
```javascript
// Credit checking
const checkAuthAndProceed = async () => {
  if (isTestMode) {
    handleGenerate(); // Bypass auth in test mode
    return;
  }
  
  // Check user credits
  const canTransform = await fetch(`${API_BASE_URL}/api/auth/can-transform`);
  // ... handle credit validation
};
```

### **Security Measures**
- **CSRF Protection**: Disabled for webhook endpoints only
- **CORS**: Restricted to specific domains
- **API Keys**: Stored in environment variables
- **Webhook Verification**: Stripe signature validation

---

## 💳 **STRIPE PAYMENT INTEGRATION**

### **Payment Session Creation**
```javascript
const handleUpgrade = async () => {
  const response = await fetch(`${API_BASE_URL}/api/payments/create-checkout-session`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      user_id: userProfile.id,
      user_email: userProfile.email,
      success_url: window.location.href + '?payment=success',
      cancel_url: window.location.href + '?payment=cancelled'
    })
  });
};
```

### **Webhook Processing**
```php
// Laravel webhook handler
public function handleWebhook(Request $request): JsonResponse
{
    // 1. Verify signature
    // 2. Parse event
    // 3. Handle checkout.session.completed
    // 4. Update user to premium in Supabase
    // 5. Log success/failure
}
```

### **Test Cards for Sandbox**
```
Success: 4242 4242 4242 4242
Decline: 4000 0000 0000 0002
3D Secure: 4000 0000 0000 3220
Insufficient Funds: 4000 0000 0000 9995
```

---

## 🤖 **AI GENERATION SYSTEM**

### **fal.ai Integration**
```javascript
// Base64 transformation request
const transformData = {
  image: uploadedImage.base64,
  hairstyle: selectedStyle?.name?.toLowerCase(),
  color: getSelectedColorName().toLowerCase(),
  // Additional parameters for accented colors
  technique: colorChoice.accented?.technique,
  intensity: colorChoice.accented?.intensity,
  placement: colorChoice.accented?.placement
};
```

### **Prompt Engineering**
```javascript
// Dynamic prompt generation
const generatePrompt = () => {
  let prompt = `Professional hairstyle transformation: ${selectedStyle?.name}`;
  
  if (colorChoice.type === 'accented' && colorChoice.accented) {
    prompt += ` with ${colorChoice.accented.name} ${colorChoice.accented.technique}`;
    prompt += ` technique, ${colorChoice.accented.intensity}% intensity`;
  } else if (colorChoice.type === 'single-tone' && colorChoice.singleTone) {
    prompt += ` in ${colorChoice.singleTone.name} color`;
  }
  
  return prompt;
};
```

### **Error Handling & Fallbacks**
```javascript
// Multiple transformation endpoints for reliability
const transformationEndpoints = [
  '/api/fal/direct-base64-transform',  // Primary
  '/api/fal/robust-transform',         // Fallback 1
  '/api/fal/base64-transform'          // Fallback 2
];
```

---

## 📊 **DATABASE SCHEMA (Supabase)**

### **user_profiles Table**
```sql
CREATE TABLE user_profiles (
  id UUID PRIMARY KEY,
  email VARCHAR UNIQUE NOT NULL,
  name VARCHAR,
  is_premium BOOLEAN DEFAULT FALSE,
  tokens_remaining INTEGER DEFAULT 3,
  upgraded_at TIMESTAMP,
  created_at TIMESTAMP DEFAULT NOW()
);
```

### **payments Table**
```sql
CREATE TABLE payments (
  id UUID PRIMARY KEY,
  user_id UUID REFERENCES user_profiles(id),
  stripe_session_id VARCHAR UNIQUE,
  amount INTEGER,
  currency VARCHAR DEFAULT 'usd',
  status VARCHAR DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT NOW()
);
```

---

## 🚨 **TROUBLESHOOTING GUIDE**

### **Common Error Patterns**

#### **Email Confirmation Redirecting to Localhost**
**Symptoms:**
- Email confirmation links redirect to `http://localhost:3000/#access_token=...` instead of production URL
- Users can't complete email verification in production
- New account registration fails to complete

**Root Cause:** Supabase Dashboard URL configuration still pointing to development URLs

**Fix Steps:**
1. **Go to [Supabase Dashboard](https://supabase.com/dashboard/project/nxxznqnrxomzudghktrz)**
2. **Authentication → URL Configuration**
3. **Set Site URL:** `https://laraver-final-ai-headshot-o6n3.vercel.app`
4. **Add Redirect URLs:**
   ```
   https://laraver-final-ai-headshot-o6n3.vercel.app/auth-confirm.html
   https://laraver-final-ai-headshot-o6n3.vercel.app/styleai-widget.html
   https://laraver-final-ai-headshot-o6n3.vercel.app/
   http://localhost:8080/auth-confirm.html
   http://localhost:8080/styleai-widget.html
   http://localhost:8080/
   ```
5. **Save configuration and test with new email**

#### **"Cannot read properties of null"**
```javascript
// ❌ Problematic code
selectedStyle.name

// ✅ Safe access patterns
selectedStyle?.name
getSelectedColorName() // Helper function with fallbacks
```

#### **CORS Errors**
```
Access to fetch at 'https://web-production-5e40.up.railway.app/api/...' 
from origin 'https://laraver-final-ai-headshot-o6n3.vercel.app' 
has been blocked by CORS policy
```

**Fix:** Update `config/cors.php` with correct frontend URL

#### **Stripe Webhook Signature Failures**
```
Invalid webhook signature
```

**Fix:** Verify webhook secret matches between Stripe dashboard and Railway environment

#### **fal.ai Generation Timeouts**
```
Request timeout after 30s
```

**Fix:** Check fal.ai API key and model availability

### **Performance Optimization**

#### **Image Handling**
```javascript
// Optimize image before upload
const optimizeImage = (file) => {
  const canvas = document.createElement('canvas');
  const ctx = canvas.getContext('2d');
  
  // Resize to max 1024px while maintaining aspect ratio
  const maxSize = 1024;
  let { width, height } = image;
  
  if (width > height && width > maxSize) {
    height = (height * maxSize) / width;
    width = maxSize;
  } else if (height > maxSize) {
    width = (width * maxSize) / height;
    height = maxSize;
  }
  
  canvas.width = width;
  canvas.height = height;
  ctx.drawImage(image, 0, 0, width, height);
  
  return canvas.toDataURL('image/jpeg', 0.8);
};
```

#### **Loading States**
```javascript
// Witty loading messages
const loadingMessages = [
  "🎨 Mixing the perfect color blend...",
  "✨ Consulting with AI hair stylists...",
  "💫 Creating your stunning new look...",
  "🌟 Adding the finishing touches..."
];
```

---

## 📱 **RESPONSIVE DESIGN BREAKPOINTS**

### **Grid System**
```css
/* Mobile First */
grid-cols-6 gap-4           /* Color swatches on mobile */

/* Desktop */
md:grid-cols-8 gap-4        /* More columns on larger screens */

/* Touch Targets */
min-w-12 min-h-12           /* Accessibility compliance */
```

### **Professional UI Elements**
```css
/* User Button */
bg-white rounded-xl shadow-lg border border-gray-200 p-4

/* Token Counter */
bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg px-4 py-2

/* Popular Presets */
bg-white rounded-xl shadow-md border border-gray-100 p-6
```

---

## 🔧 **DEPLOYMENT CONFIGURATION**

### **Railway Setup**
```json
// railway.json
{
  "build": { "builder": "NIXPACKS" },
  "deploy": {
    "startCommand": "php artisan config:cache && php artisan route:cache && php artisan serve --host=0.0.0.0 --port=$PORT",
    "healthcheckPath": "/api/health",
    "healthcheckTimeout": 300
  }
}
```

### **Vite Build Configuration**
```javascript
// vite.config.js
export default defineConfig({
  build: {
    rollupOptions: {
      input: {
        main: 'index.html',
        widget: 'public/styleai-widget.html',
        demo: 'public/color-style-demo.html',
        auth: 'public/auth-confirm.html'
      }
    },
    outDir: 'dist',
    emptyOutDir: true
  }
});
```

---

## 🚀 **PRODUCTION DEPLOYMENT CHECKLIST**

### **Pre-Launch Verification**
- [ ] All environment variables configured in Railway
- [ ] CORS properly configured for production domains
- [ ] Stripe webhook created and tested
- [ ] fal.ai API key working in production
- [ ] Supabase authentication functioning
- [ ] Premium features properly locked/unlocked
- [ ] Payment flow tested end-to-end
- [ ] Error handling robust across all features

### **Go-Live Steps**
1. **Switch Stripe to Live Mode**
   - Replace test keys with live keys in Railway
   - Create live webhook in Stripe dashboard
   - Update webhook secret in Railway

2. **Monitor Initial Usage**
   - Watch Railway logs for errors
   - Monitor Stripe dashboard for payments
   - Check Supabase for user activity

3. **Performance Monitoring**
   - Track API response times
   - Monitor image generation success rates
   - Watch for CORS or authentication issues

---

## 📞 **SUPPORT & MAINTENANCE**

### **Key Monitoring Points**
- **Railway Service Health**: Check deployment status
- **Stripe Webhook Delivery**: Monitor success rates
- **fal.ai Usage**: Track API quota and performance
- **Supabase Database**: Monitor connection and queries

### **Regular Maintenance Tasks**
- **Update Dependencies**: Laravel, Node packages
- **Rotate API Keys**: Especially for production
- **Monitor Costs**: Railway, Stripe fees, fal.ai usage
- **Backup Database**: Supabase data export

### **Emergency Procedures**
- **Service Down**: Check Railway deployment logs
- **Payment Issues**: Verify Stripe webhook delivery
- **Generation Failures**: Check fal.ai service status
- **Auth Problems**: Verify Supabase configuration

---

## 🎯 **FEATURE ROADMAP & EXTENSIBILITY**

### **Potential Enhancements**
- **Custom Domain**: Add professional salon branding
- **Subscription Model**: Monthly/yearly premium plans
- **Advanced AI Models**: Additional fal.ai models
- **Mobile App**: React Native version
- **Salon Dashboard**: Multi-user management
- **Analytics**: Usage tracking and insights

### **Code Extension Points**
- **New Hairstyles**: Add to styles array with `isPremium` flag
- **Additional Colors**: Extend color palettes
- **Payment Plans**: Modify Stripe session creation
- **AI Models**: Add new fal.ai model endpoints
- **Authentication**: Extend Supabase schema

---

**📚 This guide serves as your complete reference for maintaining, debugging, and extending StyleAI Professional. Keep it updated as you add new features!**

---

*Last Updated: August 29, 2025*  
*Version: Production 1.0*
