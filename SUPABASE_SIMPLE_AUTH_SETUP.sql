-- ===================================
-- SIMPLIFIED SUPABASE AUTHENTICATION SETUP
-- One-time payment model (no subscriptions)
-- Run these commands in your Supabase SQL Editor
-- ===================================

-- 1. Create user_profiles table (extends auth.users)
CREATE TABLE public.user_profiles (
    id UUID REFERENCES auth.users(id) PRIMARY KEY,
    email TEXT NOT NULL,
    name TEXT,
    is_premium BOOLEAN DEFAULT FALSE,
    stripe_customer_id TEXT,
    free_transformations_used INTEGER DEFAULT 0,
    premium_purchase_date TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 2. Create transformations table (usage tracking)
CREATE TABLE public.transformations (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
    style_type TEXT NOT NULL,
    color_type TEXT NOT NULL,
    gender TEXT NOT NULL,
    is_premium_style BOOLEAN DEFAULT FALSE,
    result_url TEXT,
    original_image_url TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 3. Create payments table (track one-time purchases)
CREATE TABLE public.payments (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
    stripe_payment_intent_id TEXT UNIQUE,
    amount_cents INTEGER NOT NULL,
    status TEXT NOT NULL, -- 'pending', 'succeeded', 'failed'
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ===================================
-- ROW LEVEL SECURITY POLICIES
-- ===================================

-- Enable RLS on all tables
ALTER TABLE public.user_profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.transformations ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.payments ENABLE ROW LEVEL SECURITY;

-- User profiles policies
CREATE POLICY "Users can view own profile" ON public.user_profiles
    FOR SELECT USING (auth.uid() = id);

CREATE POLICY "Users can update own profile" ON public.user_profiles
    FOR UPDATE USING (auth.uid() = id);

-- Transformations policies
CREATE POLICY "Users can view own transformations" ON public.transformations
    FOR SELECT USING (auth.uid() = user_id);

CREATE POLICY "Users can insert own transformations" ON public.transformations
    FOR INSERT WITH CHECK (auth.uid() = user_id);

-- Payments policies (read-only for users)
CREATE POLICY "Users can view own payments" ON public.payments
    FOR SELECT USING (auth.uid() = user_id);

-- ===================================
-- TRIGGERS FOR AUTOMATION
-- ===================================

-- Function to create user profile on signup
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS TRIGGER AS $$
BEGIN
    -- Create user profile with 10 free transformations
    INSERT INTO public.user_profiles (id, email, name, free_transformations_used)
    VALUES (NEW.id, NEW.email, COALESCE(NEW.raw_user_meta_data->>'name', ''), 0);
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Trigger to run on user signup
CREATE TRIGGER on_auth_user_created
    AFTER INSERT ON auth.users
    FOR EACH ROW EXECUTE FUNCTION public.handle_new_user();

-- Function to update updated_at timestamps
CREATE OR REPLACE FUNCTION public.update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Apply updated_at triggers
CREATE TRIGGER update_user_profiles_updated_at
    BEFORE UPDATE ON public.user_profiles
    FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();

CREATE TRIGGER update_payments_updated_at
    BEFORE UPDATE ON public.payments
    FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();

-- ===================================
-- HELPER FUNCTIONS
-- ===================================

-- Function to check if user can transform
CREATE OR REPLACE FUNCTION public.can_user_transform(user_id UUID, is_premium_style BOOLEAN DEFAULT FALSE)
RETURNS BOOLEAN AS $$
DECLARE
    user_profile RECORD;
BEGIN
    SELECT * INTO user_profile FROM public.user_profiles WHERE user_profiles.id = $1;
    
    IF user_profile IS NULL THEN
        RETURN FALSE;
    END IF;
    
    -- If user is premium (paid), they can do anything
    IF user_profile.is_premium THEN
        RETURN TRUE;
    END IF;
    
    -- For free users, check transformation limit and style restrictions
    IF is_premium_style THEN
        RETURN FALSE; -- Premium styles require payment
    ELSE
        -- Free users get 10 transformations
        RETURN (user_profile.free_transformations_used < 10);
    END IF;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Function to consume a transformation
CREATE OR REPLACE FUNCTION public.consume_transformation(user_id UUID)
RETURNS BOOLEAN AS $$
DECLARE
    user_profile RECORD;
BEGIN
    SELECT * INTO user_profile FROM public.user_profiles WHERE user_profiles.id = $1 FOR UPDATE;
    
    IF user_profile IS NULL THEN
        RETURN FALSE;
    END IF;
    
    -- If user is premium, no limit
    IF user_profile.is_premium THEN
        RETURN TRUE;
    END IF;
    
    -- For free users, increment counter if under limit
    IF user_profile.free_transformations_used < 10 THEN
        UPDATE public.user_profiles 
        SET free_transformations_used = free_transformations_used + 1,
            updated_at = NOW()
        WHERE id = $1;
        RETURN TRUE;
    END IF;
    
    RETURN FALSE; -- No more free transformations
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Function to upgrade user to premium
CREATE OR REPLACE FUNCTION public.upgrade_to_premium(user_id UUID)
RETURNS BOOLEAN AS $$
BEGIN
    UPDATE public.user_profiles 
    SET is_premium = TRUE,
        premium_purchase_date = NOW(),
        updated_at = NOW()
    WHERE id = $1;
    
    RETURN TRUE;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- ===================================
-- INDEXES FOR PERFORMANCE
-- ===================================

CREATE INDEX idx_user_profiles_id ON public.user_profiles(id);
CREATE INDEX idx_transformations_user_id ON public.transformations(user_id);
CREATE INDEX idx_transformations_created_at ON public.transformations(created_at);
CREATE INDEX idx_payments_user_id ON public.payments(user_id);
CREATE INDEX idx_payments_stripe_id ON public.payments(stripe_payment_intent_id);
