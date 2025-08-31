-- ===================================
-- CRITICAL FIX: User Profile Creation with Correct Token Structure
-- 
-- PROJECT: StyleAI Professional
-- ISSUE: New users getting 0 tokens instead of 10 tokens
-- ROOT CAUSE: Supabase trigger creating profiles with old structure
-- 
-- RUN THIS IN SUPABASE SQL EDITOR:
-- https://supabase.com/dashboard/project/nxxznqnrxomzudghktrz/sql
-- ===================================

-- 1. Update the trigger function to create profiles with new token structure
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS TRIGGER AS $$
BEGIN
    -- Create user profile with correct initial token allocation
    INSERT INTO public.user_profiles (
        id, 
        email, 
        name, 
        is_premium,
        current_package,
        tokens_remaining,
        generations_remaining,
        free_transformations_used,
        package_purchased_at,
        created_at,
        updated_at
    )
    VALUES (
        NEW.id, 
        NEW.email, 
        COALESCE(NEW.raw_user_meta_data->>'name', ''),
        FALSE,                    -- is_premium
        'free',                   -- current_package
        10,                       -- tokens_remaining (initial free tokens)
        2,                        -- generations_remaining (initial free generations)
        0,                        -- free_transformations_used (legacy compatibility)
        NULL,                     -- package_purchased_at
        NOW(),                    -- created_at
        NOW()                     -- updated_at
    );
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- 2. Add missing RLS policy for service key to insert profiles
-- (In case backend needs to create profiles manually)
CREATE POLICY "Service can insert user profiles" ON public.user_profiles
    FOR INSERT WITH CHECK (true);

-- 3. Update existing users who might have 0 tokens
UPDATE public.user_profiles 
SET 
    tokens_remaining = COALESCE(tokens_remaining, 10),
    generations_remaining = COALESCE(generations_remaining, 2),
    current_package = COALESCE(current_package, 'free')
WHERE 
    tokens_remaining IS NULL 
    OR tokens_remaining = 0 
    OR generations_remaining IS NULL 
    OR generations_remaining = 0;

-- 4. Verify the fix worked
SELECT 
    id,
    email,
    is_premium,
    tokens_remaining,
    generations_remaining,
    current_package,
    created_at
FROM public.user_profiles 
ORDER BY created_at DESC 
LIMIT 5;

-- ===================================
-- EXPECTED RESULT FOR NEW USERS:
-- tokens_remaining: 10
-- generations_remaining: 2
-- current_package: 'free'
-- is_premium: false
-- ===================================
