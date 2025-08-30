# 🏗️ StyleAI Professional - Development Environment Setup

## 🎯 **Environment Strategy**

### **Development Environment**
- **Frontend**: `http://localhost:5174/public/styleai-widget-dev.html`
- **Backend**: Railway Production (for now) or local Laravel
- **Database**: Supabase Production (shared)
- **Purpose**: Testing, debugging, feature development

### **Staging Environment**  
- **Frontend**: Vercel Preview deployments
- **Backend**: Railway Production (shared)
- **Database**: Supabase Production (shared)
- **Purpose**: Pre-production testing

### **Production Environment**
- **Frontend**: `https://laraver-final-ai-headshot-o6n3.vercel.app/styleai-widget.html`
- **Backend**: `https://web-production-5e40.up.railway.app`
- **Database**: Supabase Production
- **Purpose**: Live customer usage

## 🚀 **Development Workflow**

### **Daily Development**
```bash
# Start local development
npm run dev

# Access development widget
http://localhost:5174/public/styleai-widget-dev.html

# Test features locally without hitting Vercel limits
```

### **Testing Payment Flow**
1. **Use localhost for frontend testing**
2. **Connect to Railway backend** (shared)
3. **Test with Stripe sandbox** 
4. **Use developer mode tools** for debugging

### **Production Deployment**
```bash
# Only when ready for production
npm run build
git add .
git commit -m "Production ready changes"
git push origin 10.x

# Deploy to production Vercel (when limit allows)
```

## 🔧 **Environment URLs**

### **Development**
- **Widget**: `http://localhost:5174/public/styleai-widget-dev.html`
- **Backend**: `https://web-production-5e40.up.railway.app` (shared)
- **Features**: All debugging tools enabled

### **Production** 
- **Widget**: `https://laraver-final-ai-headshot-o6n3.vercel.app/styleai-widget.html`
- **Backend**: `https://web-production-5e40.up.railway.app`
- **Features**: Clean user experience, minimal debugging

## 🧪 **Testing Strategy**

### **Development Testing**
1. **Use localhost** for rapid iteration
2. **Test payment buttons** in developer mode
3. **Debug with console logs** enabled
4. **No Vercel deployment limits**

### **Production Testing**
1. **Deploy only when features are complete**
2. **Test critical user flows**
3. **Verify production performance**

## 💡 **Benefits**

- ✅ **Unlimited local testing**
- ✅ **No Vercel deployment limits during development**
- ✅ **Professional separation of concerns**
- ✅ **Easier debugging and iteration**
- ✅ **Safer production deployments**
