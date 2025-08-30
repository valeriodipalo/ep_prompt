# 🚀 StyleAI Professional - Vercel Deployment Guide

## 📋 Overview

StyleAI Professional consists of:
- **Frontend**: Static HTML widget (`public/styleai-widget.html`)
- **Backend**: Laravel PHP API
- **External Services**: Supabase (auth/db), fal.ai (AI), Stripe (payments)

## 🎯 Deployment Options

### Option 1: Frontend on Vercel + Backend on PHP Hosting (RECOMMENDED)

#### Frontend Deployment (Vercel)

1. **Push to GitHub**:
   ```bash
   git add .
   git commit -m "Deploy StyleAI Professional"
   git push origin main
   ```

2. **Deploy to Vercel**:
   - Go to [vercel.com](https://vercel.com)
   - Import your GitHub repository
   - Set build settings:
     - Framework Preset: `Other`
     - Build Command: (leave empty)
     - Output Directory: `public`

3. **Environment Variables** (Vercel Dashboard):
   ```
   # Not needed for frontend-only deployment
   ```

#### Backend Deployment (Heroku/DigitalOcean/Railway)

1. **For Heroku**:
   ```bash
   # Install Heroku CLI
   npm install -g heroku
   
   # Create Heroku app
   heroku create your-styleai-api
   
   # Add PHP buildpack
   heroku buildpacks:set heroku/php
   
   # Set environment variables
   heroku config:set APP_KEY=your-app-key
   heroku config:set SUPABASE_URL=https://nxxznqnrxomzudghktrz.supabase.co
   heroku config:set SUPABASE_ANON_KEY=your-supabase-anon-key
   heroku config:set SUPABASE_SERVICE_KEY=your-supabase-service-key
   heroku config:set FAL_KEY=your-fal-ai-key
   heroku config:set STRIPE_KEY=your-stripe-key
   heroku config:set STRIPE_SECRET=your-stripe-secret
   
   # Deploy
   git push heroku main
   ```

2. **Update Frontend API URL**:
   - Replace `https://your-laravel-api.herokuapp.com` in `styleai-widget.html`
   - With your actual Heroku app URL

### Option 2: Full Stack on Vercel (Alternative)

#### Create Vercel Configuration

1. **Create `api/index.php`**:
   ```php
   <?php
   // Vercel PHP Runtime Entry Point
   require_once __DIR__ . '/../public/index.php';
   ```

2. **Update `vercel.json`**:
   ```json
   {
     "version": 2,
     "builds": [
       {
         "src": "api/index.php",
         "use": "vercel-php@0.6.0"
       },
       {
         "src": "public/**/*",
         "use": "@vercel/static"
       }
     ],
     "routes": [
       {
         "src": "/api/(.*)",
         "dest": "/api/index.php"
       },
       {
         "src": "/(.*)",
         "dest": "/public/$1"
       }
     ]
   }
   ```

## 🔧 Production Configuration

### 1. Environment Variables

**Required Environment Variables**:
```bash
# Application
APP_NAME="StyleAI Professional"
APP_ENV=production
APP_KEY=base64:your-app-key-here
APP_DEBUG=false
APP_URL=https://your-domain.vercel.app

# Supabase (Project Owner: vale98.apple@gmail.com)
SUPABASE_URL=https://nxxznqnrxomzudghktrz.supabase.co
SUPABASE_ANON_KEY=your-supabase-anon-key
SUPABASE_SERVICE_KEY=your-supabase-service-key

# AI Service
FAL_KEY=your-fal-ai-key

# Stripe
STRIPE_KEY=your-stripe-publishable-key
STRIPE_SECRET=your-stripe-secret-key
STRIPE_WEBHOOK_SECRET=your-stripe-webhook-secret

# CORS
FRONTEND_URL=https://your-domain.vercel.app
```

### 2. Update API URLs

**In `public/styleai-widget.html`** (already done):
```javascript
const API_BASE_URL = window.location.hostname === 'localhost' 
    ? 'http://localhost:8001' 
    : 'https://your-backend-url.com';
```

### 3. Configure CORS

**In `config/cors.php`**:
```php
'allowed_origins' => [
    'https://your-domain.vercel.app',
    'http://localhost:8080', // For local development
],
```

## 🎯 Step-by-Step Deployment

### Phase 1: Frontend Deployment

1. **Commit Changes**:
   ```bash
   git add .
   git commit -m "Prepare for Vercel deployment"
   git push origin main
   ```

2. **Deploy to Vercel**:
   - Go to [vercel.com](https://vercel.com)
   - Click "Import Project"
   - Connect your GitHub repository
   - Configure:
     - Framework: Other
     - Root Directory: `.`
     - Output Directory: `public`

3. **Test Frontend**:
   - Visit your Vercel URL
   - Test the widget loads
   - Enable Developer Mode to test without backend

### Phase 2: Backend Deployment

1. **Choose Backend Host**:
   - **Heroku** (easiest for Laravel)
   - **Railway** (modern alternative)
   - **DigitalOcean App Platform**
   - **AWS Elastic Beanstalk**

2. **Deploy Backend**:
   - Set all environment variables
   - Configure database connections
   - Test API endpoints

3. **Update Frontend**:
   - Replace backend URL in widget
   - Test full integration

## 🧪 Testing Checklist

### Frontend (Vercel)
- [ ] Widget loads correctly
- [ ] Professional UI displays
- [ ] Developer Mode works
- [ ] Color selection functions
- [ ] Image upload works

### Backend Integration
- [ ] API calls succeed
- [ ] Authentication works
- [ ] Payment processing functions
- [ ] fal.ai integration works
- [ ] Supabase connection stable

### Production Features
- [ ] HTTPS everywhere
- [ ] CORS configured
- [ ] Environment variables secure
- [ ] Error handling robust
- [ ] Performance optimized

## 🎨 Domain Configuration

1. **Custom Domain** (Optional):
   - Add custom domain in Vercel
   - Configure DNS records
   - Update CORS settings

2. **SSL Certificates**:
   - Automatic with Vercel
   - Ensure all API calls use HTTPS

## 🔒 Security Checklist

- [ ] All API keys in environment variables
- [ ] No secrets in frontend code
- [ ] CORS properly configured
- [ ] HTTPS enforced
- [ ] Supabase RLS policies active
- [ ] Stripe webhook validation

## 📞 Need Help?

If you encounter issues:
1. Check Vercel deployment logs
2. Test API endpoints individually
3. Verify environment variables
4. Check CORS configuration
5. Monitor Supabase logs

---

**Ready to deploy your professional StyleAI app to the world!** 🌟
