# 🔧 StyleAI Professional - Quick Debug Checklist

## 🚨 **IMMEDIATE ISSUE RESOLUTION**

### **App Not Loading**
```bash
# Check these URLs in order:
✅ https://laraver-final-ai-headshot-o6n3.vercel.app/styleai-widget.html (Frontend)
✅ https://web-production-5e40.up.railway.app/api/health (Backend)
✅ https://web-production-5e40.up.railway.app/api/payments/webhook-test (Stripe)
```

### **Generation Errors**
```javascript
// Browser Console Debug Commands:
console.log('Selected Style:', selectedStyle);
console.log('Color Choice:', colorChoice);
console.log('Uploaded Image:', uploadedImage);
console.log('API Base URL:', API_BASE_URL);

// Test API Connection:
testConnection();
```

### **Payment Issues**
```bash
# Check Stripe Configuration:
1. Verify test/live keys in Railway Variables
2. Check webhook secret matches Stripe dashboard
3. Test webhook: https://web-production-5e40.up.railway.app/api/payments/webhook-test
4. Check Railway logs for webhook events
```

### **Authentication Problems**
```bash
# Supabase Debug:
1. Test connection: https://web-production-5e40.up.railway.app/api/auth/test-supabase
2. Check Railway environment variables: SUPABASE_URL, SUPABASE_ANON_KEY, SUPABASE_SERVICE_KEY
3. Verify user can register/login in app
```

---

## 🎯 **5-MINUTE HEALTH CHECK**

**Run these tests to verify everything is working:**

1. **Frontend Loading**: Visit Vercel URL, should load StyleAI widget
2. **Backend Health**: Visit `/api/health`, should return JSON with status "ok"
3. **Image Upload**: Upload test image, should show preview
4. **Style Selection**: Select hairstyle, button should enable
5. **Developer Mode**: Toggle test mode, should bypass authentication
6. **API Connection**: Check browser console for successful API calls

---

## 🔑 **CRITICAL ENVIRONMENT VARIABLES**

**If anything breaks, verify these are set in Railway:**

```
APP_KEY=base64:Y0Rgc2PX3vssQxw/JcgWYZhtnYuW9JE016fec0TjXh8=
APP_URL=https://web-production-5e40.up.railway.app
FRONTEND_URL=https://laraver-final-ai-headshot-o6n3.vercel.app
FAL_KEY=4d4f3d66-f99b-48b9-9b3b-af0ca1668c2f:34f7573ed94f9d2123d653f962c8bc42
SUPABASE_URL=https://nxxznqnrxomzudghktrz.supabase.co
STRIPE_SECRET=sk_test_your_key (or sk_live_)
STRIPE_WEBHOOK_SECRET=whsec_your_secret
```

---

## 🆘 **EMERGENCY PROCEDURES**

### **Service Down**
1. Check Railway deployment status
2. Check Railway logs for errors
3. Verify environment variables
4. Redeploy if necessary: `git push origin 10.x`

### **Payment Failures**
1. Check Stripe dashboard for webhook delivery
2. Verify webhook secret in Railway
3. Test webhook endpoint manually
4. Check Railway logs for webhook processing

### **Generation Not Working**
1. Enable Developer Mode for testing
2. Check fal.ai API key validity
3. Verify image upload is working
4. Check browser console for JavaScript errors

---

**🎯 Keep this checklist handy for quick issue resolution!**
