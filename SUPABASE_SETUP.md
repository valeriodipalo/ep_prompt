# 🗄️ Supabase Setup Guide

## 📋 **Prerequisites**
- A Supabase account (sign up at [supabase.com](https://supabase.com))

## 🚀 **Step 1: Create Supabase Project**

1. **Go to** [supabase.com/dashboard](https://supabase.com/dashboard)
2. **Click** "New Project"
3. **Choose** your organization
4. **Enter** project details:
   - **Name**: `StyleAI Hairstyle App`
   - **Database Password**: Create a strong password
   - **Region**: Choose closest to your location
5. **Click** "Create new project"
6. **Wait** for project setup (2-3 minutes)

## 🔑 **Step 2: Get API Keys**

1. **Go to** Settings → API
2. **Copy** these values:
   - **Project URL**: `https://your-project-ref.supabase.co`
   - **Anon (public) key**: `eyJhbGc...` (starts with eyJ)
   - **Service role key**: `eyJhbGc...` (different from anon key)

## 📁 **Step 3: Create Storage Bucket**

1. **Go to** Storage in the sidebar
2. **Click** "Create a new bucket"
3. **Enter** bucket name: `hairstyle-images`
4. **Set** as **Public bucket** ✅
5. **Click** "Create bucket"

### **Set Bucket Policies**
1. **Click** on the `hairstyle-images` bucket
2. **Go to** "Policies" tab
3. **Click** "Add policy" → "For full customization"
4. **Add** this policy for public uploads:

```sql
CREATE POLICY "Public upload access" ON storage.objects
FOR INSERT TO public
WITH CHECK (bucket_id = 'hairstyle-images');

CREATE POLICY "Public read access" ON storage.objects
FOR SELECT TO public
USING (bucket_id = 'hairstyle-images');
```

## 🗃️ **Step 4: Create Database Table**

1. **Go to** SQL Editor in Supabase
2. **Run** this SQL:

```sql
-- Create uploaded_images table
CREATE TABLE uploaded_images (
    id BIGSERIAL PRIMARY KEY,
    file_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    url VARCHAR(500) NOT NULL,
    size BIGINT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    uploaded_at TIMESTAMP WITH TIME ZONE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create indexes
CREATE INDEX idx_uploaded_images_file_name ON uploaded_images(file_name);
CREATE INDEX idx_uploaded_images_uploaded_at ON uploaded_images(uploaded_at);

-- Enable Row Level Security (optional but recommended)
ALTER TABLE uploaded_images ENABLE ROW LEVEL SECURITY;

-- Create policy for public access (adjust as needed)
CREATE POLICY "Public access to uploaded_images" ON uploaded_images
FOR ALL TO public
USING (true);
```

## ⚙️ **Step 5: Update Environment Variables**

**Edit your `.env` file** and replace the placeholders:

```bash
# Supabase Configuration
SUPABASE_URL=https://your-project-ref.supabase.co
SUPABASE_ANON_KEY=your_anon_key_here
SUPABASE_SERVICE_KEY=your_service_role_key_here
```

## ✅ **Step 6: Test the Setup**

1. **Restart** your Laravel server:
   ```bash
   # Stop current server (Ctrl+C)
   php artisan serve --port=8001
   ```

2. **Test** the upload endpoint:
   ```bash
   curl -X GET "http://localhost:8001/api/fal/test"
   ```

3. **Try** uploading an image through the widget at:
   `http://localhost:8080/styleai-widget.html`

## 🔧 **Troubleshooting**

### **Common Issues:**

1. **"Supabase configuration is missing"**
   - Check your `.env` file has correct values
   - Restart Laravel server after changing `.env`

2. **"Failed to upload image to storage"**
   - Verify bucket name is `hairstyle-images`
   - Check bucket is set to public
   - Verify storage policies are correct

3. **"CORS error"**
   - Make sure your Supabase project allows requests from `localhost:8080`
   - Check API keys are correct

### **Verify Setup:**
- ✅ Project created and running
- ✅ API keys copied to `.env`
- ✅ `hairstyle-images` bucket created (public)
- ✅ Storage policies added
- ✅ `uploaded_images` table created
- ✅ Laravel server restarted

## 🎯 **What This Enables:**

- ✅ **Reliable Image Storage**: No more failed uploads
- ✅ **Database Tracking**: Track all uploaded images
- ✅ **Public URLs**: Direct access to uploaded images
- ✅ **Scalable**: Handles any number of uploads
- ✅ **Secure**: Proper authentication and policies

Once setup is complete, your widget will use Supabase for reliable image storage! 🚀
