# Railway Environment Variables Template

Copy these environment variables to your Railway dashboard:

```
APP_NAME=StyleAI Professional
APP_ENV=production
APP_KEY=base64:Y0Rgc2PX3vssQxw/JcgWYZhtnYuW9JE016fec0TjXh8=
APP_DEBUG=false
APP_URL=https://your-app-name.up.railway.app

LOG_LEVEL=error
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

# fal.ai Configuration
FAL_KEY=4d4f3d66-f99b-48b9-9b3b-af0ca1668c2f:34f7573ed94f9d2123d653f962c8bc42

# Supabase Configuration
SUPABASE_URL=https://nxxznqnrxomzudghktrz.supabase.co
SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im54eHpucW5yeG9tenVkZ2hrdHJ6Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTYzOTY0MzgsImV4cCI6MjA3MTk3MjQzOH0.XoSFOvFZoNiR8Se1fJh-wOQ04yDEGm4bfMyEURiBW-s
SUPABASE_SERVICE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im54eHpucW5yeG9tenVkZ2hrdHJ6Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1NjM5NjQzOCwiZXhwIjoyMDcxOTcyNDM4fQ.adaC2x2Crh3NjfV7EHjpuQP3tUfSTcBzZbz37sonCGg

# Stripe (add your production keys)
STRIPE_KEY=pk_live_your_stripe_publishable_key
STRIPE_SECRET=sk_live_your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret

# CORS
FRONTEND_URL=https://your-vercel-domain.vercel.app
```

## Important Notes:

1. **APP_URL**: Replace with your actual Railway app URL after deployment
2. **STRIPE_KEY & STRIPE_SECRET**: Use your production Stripe keys
3. **FRONTEND_URL**: Replace with your actual Vercel domain
4. **DATABASE_URL**: Railway will provide this automatically when you add a database
