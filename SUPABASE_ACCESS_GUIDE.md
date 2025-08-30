# 🗄️ Supabase Database Access Guide

## 📋 **Database Information**

**Project Owner:** vale98.apple@gmail.com  
**Project URL:** https://nxxznqnrxomzudghktrz.supabase.co  
**Project ID:** nxxznqnrxomzudghktrz  

## 🔑 **Access Links**

### **Main Dashboard**
https://supabase.com/dashboard/project/nxxznqnrxomzudghktrz

### **SQL Editor** 
https://supabase.com/dashboard/project/nxxznqnrxomzudghktrz/sql

### **Table Editor**
https://supabase.com/dashboard/project/nxxznqnrxomzudghktrz/editor

### **Authentication**
https://supabase.com/dashboard/project/nxxznqnrxomzudghktrz/auth/users

### **API Settings**
https://supabase.com/dashboard/project/nxxznqnrxomzudghktrz/settings/api

## 🛠️ **Quick Actions**

### **Check User Profiles**
```sql
SELECT id, email, name, is_premium, current_package, 
       generations_remaining, tokens_remaining, package_purchased_at
FROM public.user_profiles 
ORDER BY created_at DESC 
LIMIT 10;
```

### **Check Recent Payments**
```sql
SELECT p.*, up.email, up.name
FROM public.payments p
LEFT JOIN public.user_profiles up ON p.user_id = up.id
ORDER BY p.created_at DESC 
LIMIT 10;
```

### **Manually Add Tokens to User**
```sql
UPDATE public.user_profiles 
SET 
  tokens_remaining = 50,
  generations_remaining = 10,
  is_premium = true,
  current_package = 'creator',
  package_purchased_at = NOW()
WHERE email = 'user@example.com';
```

## 🚨 **Emergency Database Access**

If you need to quickly fix user tokens:

1. **Login**: https://supabase.com/dashboard (use vale98.apple@gmail.com)
2. **Go to SQL Editor**: Click project → SQL Editor
3. **Run manual token update** (see SQL above)
4. **Check Table Editor** to verify changes

## 🔐 **API Keys Location**

**In Supabase Dashboard → Settings → API:**
- **Project URL:** Already configured
- **Anon Key:** Already in Railway environment
- **Service Role Key:** Already in Railway environment (for webhooks)

---

**Keep this guide handy for database management!** 📚
