# 🔧 FIX SUPABASE REDIRECT URLs

## The Problem
Supabase is redirecting email confirmations to `localhost:3000` instead of your widget.

## Step 1: Configure Redirect URLs in Supabase

1. **Go to your Supabase Dashboard**
2. **Authentication** → **URL Configuration**
3. **Add these URLs:**

### Site URL:
```
http://localhost:8080
```

### Redirect URLs (add all of these):
```
http://localhost:8080/styleai-widget.html
http://localhost:8080/
http://localhost:8001/
https://your-production-domain.com/
```

## Step 2: Update Email Templates (Optional)

1. **Go to Authentication** → **Email Templates**
2. **Find "Confirm signup" template**
3. **Update the redirect URL** in the template to point to your widget

## Step 3: Test the Flow

After making these changes:
1. **Register a new account** with a different email
2. **Check the confirmation email**
3. **Click the link** - should redirect to your widget
4. **Try logging in** - should work immediately

## Alternative: Extract Token from URL

If the redirect still goes to the wrong place, you can handle it manually by:
1. **Copy the access_token** from the URL
2. **Use it directly** in your widget for authentication

The token from your URL is:
```
eyJhbGciOiJIUzI1NiIsImtpZCI6Im9MU1lOaEJyN2lQU1hmRUIiLCJ0eXAiOiJKV1QifQ.eyJpc3MiOiJodHRwczovL254eHpucW5yeG9tenVkZ2hrdHJ6LnN1cGFiYXNlLmNvL2F1dGgvdjEiLCJzdWIiOiIwOWFhNWNhZC1kMTYwLTRjMDQtOTdjZS1iMjY5NGRmZGM5NGMiLCJhdWQiOiJhdXRoZW50aWNhdGVkIiwiZXhwIjoxNzU2NDI5MTg5LCJpYXQiOjE3NTY0MjU1ODksImVtYWlsIjoidmVzb2IzOTM5M0Bza2F0ZXJ1LmNvbSIsInBob25lIjoiIiwiYXBwX21ldGFkYXRhIjp7InByb3ZpZGVyIjoiZW1haWwiLCJwcm92aWRlcnMiOlsiZW1haWwiXX0sInVzZXJfbWV0YWRhdGEiOnsiZW1haWwiOiJ2ZXNvYjM5MzkzQHNrYXRlcnUuY29tIiwiZW1haWxfdmVyaWZpZWQiOnRydWUsIm5hbWUiOiJ2YWxlcmlvIiwicGhvbmVfdmVyaWZpZWQiOmZhbHNlLCJzdWIiOiIwOWFhNWNhZC1kMTYwLTRjMDQtOTdjZS1iMjY5NGRmZGM5NGMifSwicm9sZSI6ImF1dGhlbnRpY2F0ZWQiLCJhYWwiOiJhYWwxIiwiYW1yIjpbeyJtZXRob2QiOiJvdHAiLCJ0aW1lc3RhbXAiOjE3NTY0MjU1ODl9XSwic2Vzc2lvbl9pZCI6ImEwOGZmMWI3LTU1YzUtNDA5Zi1hNmU3LTQzZmQ2MDhlYjY0ZiIsImlzX2Fub255bW91cyI6ZmFsc2V9.qexsBPTEFH_ifCYR-wS4YKtpaKHkm7ec5F2ugcuivoE
```

This token contains:
- ✅ **User ID**: `09aa5cad-d160-4c04-97ce-b2694dfdc94c`
- ✅ **Email**: `vesob39393@skateru.com`  
- ✅ **Name**: `valerio`
- ✅ **Email Verified**: `true`

## Recommendation

**Disable email confirmation** for smoother widget experience:
- **Authentication** → **Settings** → **Turn OFF "Enable email confirmations"**
