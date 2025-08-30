-- ===================================
-- CRITICAL SUPABASE SCHEMA UPDATE FOR PAYMENT SYSTEM
-- Run these commands in your Supabase SQL Editor
-- ===================================

-- Add missing columns to user_profiles table
ALTER TABLE public.user_profiles 
ADD COLUMN IF NOT EXISTS current_package TEXT,
ADD COLUMN IF NOT EXISTS generations_remaining INTEGER DEFAULT 0,
ADD COLUMN IF NOT EXISTS tokens_remaining INTEGER DEFAULT 0,
ADD COLUMN IF NOT EXISTS package_purchased_at TIMESTAMP WITH TIME ZONE;

-- Update payments table to match our current implementation
ALTER TABLE public.payments 
ADD COLUMN IF NOT EXISTS package_type TEXT,
ADD COLUMN IF NOT EXISTS generations_purchased INTEGER DEFAULT 0,
ADD COLUMN IF NOT EXISTS stripe_session_id TEXT;

-- Update existing users to have 2 free generations (new free tier)
UPDATE public.user_profiles 
SET generations_remaining = 2, tokens_remaining = 10
WHERE generations_remaining IS NULL OR generations_remaining = 0;

-- Add index for better performance
CREATE INDEX IF NOT EXISTS idx_user_profiles_email ON public.user_profiles(email);
CREATE INDEX IF NOT EXISTS idx_payments_stripe_session ON public.payments(stripe_session_id);

-- Verify the schema update
SELECT column_name, data_type, is_nullable 
FROM information_schema.columns 
WHERE table_name = 'user_profiles' 
AND table_schema = 'public'
ORDER BY ordinal_position;
