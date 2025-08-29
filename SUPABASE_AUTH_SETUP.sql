-- ===================================
-- SUPABASE AUTHENTICATION & TOKEN SYSTEM SETUP
-- Run these commands in your Supabase SQL Editor
-- ===================================

-- 1. Create user_profiles table (extends auth.users)
CREATE TABLE public.user_profiles (
    id UUID REFERENCES auth.users(id) PRIMARY KEY,
    email TEXT NOT NULL,
    name TEXT,
    is_premium BOOLEAN DEFAULT FALSE,
    stripe_customer_id TEXT,
    free_tokens_used INTEGER DEFAULT 0,
    premium_tokens_used INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 2. Create user_tokens table
CREATE TABLE public.user_tokens (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
    free_tokens INTEGER DEFAULT 10, -- 10 free tokens for new users
    premium_tokens INTEGER DEFAULT 0,
    last_reset_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 3. Create transformations table (usage tracking)
CREATE TABLE public.transformations (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
    style_type TEXT NOT NULL,
    color_type TEXT NOT NULL,
    gender TEXT NOT NULL,
    is_premium_style BOOLEAN DEFAULT FALSE,
    tokens_used INTEGER DEFAULT 1,
    result_url TEXT,
    original_image_url TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 4. Create token_transactions table (audit trail)
CREATE TABLE public.token_transactions (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
    transaction_type TEXT NOT NULL, -- 'earned', 'spent', 'purchased', 'reset'
    amount INTEGER NOT NULL,
    description TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 5. Create subscriptions table
CREATE TABLE public.subscriptions (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
    stripe_subscription_id TEXT UNIQUE,
    plan_type TEXT NOT NULL, -- 'basic', 'premium', 'pro'
    is_active BOOLEAN DEFAULT TRUE,
    current_period_start TIMESTAMP WITH TIME ZONE,
    current_period_end TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ===================================
-- ROW LEVEL SECURITY POLICIES
-- ===================================

-- Enable RLS on all tables
ALTER TABLE public.user_profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.user_tokens ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.transformations ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.token_transactions ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.subscriptions ENABLE ROW LEVEL SECURITY;

-- User profiles policies
CREATE POLICY "Users can view own profile" ON public.user_profiles
    FOR SELECT USING (auth.uid() = id);

CREATE POLICY "Users can update own profile" ON public.user_profiles
    FOR UPDATE USING (auth.uid() = id);

-- User tokens policies
CREATE POLICY "Users can view own tokens" ON public.user_tokens
    FOR SELECT USING (auth.uid() = user_id);

CREATE POLICY "Users can update own tokens" ON public.user_tokens
    FOR UPDATE USING (auth.uid() = user_id);

-- Transformations policies
CREATE POLICY "Users can view own transformations" ON public.transformations
    FOR SELECT USING (auth.uid() = user_id);

CREATE POLICY "Users can insert own transformations" ON public.transformations
    FOR INSERT WITH CHECK (auth.uid() = user_id);

-- Token transactions policies (read-only for users)
CREATE POLICY "Users can view own transactions" ON public.token_transactions
    FOR SELECT USING (auth.uid() = user_id);

-- Subscriptions policies
CREATE POLICY "Users can view own subscriptions" ON public.subscriptions
    FOR SELECT USING (auth.uid() = user_id);

-- ===================================
-- TRIGGERS FOR AUTOMATION
-- ===================================

-- Function to create user profile and tokens on signup
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS TRIGGER AS $$
BEGIN
    -- Create user profile
    INSERT INTO public.user_profiles (id, email, name)
    VALUES (NEW.id, NEW.email, COALESCE(NEW.raw_user_meta_data->>'name', ''));
    
    -- Create initial token allocation
    INSERT INTO public.user_tokens (user_id, free_tokens, premium_tokens)
    VALUES (NEW.id, 10, 0);
    
    -- Log the free tokens earned
    INSERT INTO public.token_transactions (user_id, transaction_type, amount, description)
    VALUES (NEW.id, 'earned', 10, 'Welcome bonus - 10 free transformations');
    
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

CREATE TRIGGER update_user_tokens_updated_at
    BEFORE UPDATE ON public.user_tokens
    FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();

CREATE TRIGGER update_subscriptions_updated_at
    BEFORE UPDATE ON public.subscriptions
    FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();

-- ===================================
-- HELPER FUNCTIONS
-- ===================================

-- Function to check if user has enough tokens
CREATE OR REPLACE FUNCTION public.can_user_transform(user_id UUID, is_premium_style BOOLEAN DEFAULT FALSE)
RETURNS BOOLEAN AS $$
DECLARE
    user_tokens RECORD;
BEGIN
    SELECT * INTO user_tokens FROM public.user_tokens WHERE user_tokens.user_id = $1;
    
    IF user_tokens IS NULL THEN
        RETURN FALSE;
    END IF;
    
    -- For premium styles, check premium tokens first, then free tokens
    IF is_premium_style THEN
        RETURN (user_tokens.premium_tokens > 0 OR user_tokens.free_tokens > 0);
    ELSE
        -- For regular styles, free tokens only
        RETURN (user_tokens.free_tokens > 0);
    END IF;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Function to consume tokens
CREATE OR REPLACE FUNCTION public.consume_token(user_id UUID, is_premium_style BOOLEAN DEFAULT FALSE)
RETURNS BOOLEAN AS $$
DECLARE
    user_tokens RECORD;
    tokens_consumed INTEGER := 1;
    transaction_desc TEXT;
BEGIN
    SELECT * INTO user_tokens FROM public.user_tokens WHERE user_tokens.user_id = $1 FOR UPDATE;
    
    IF user_tokens IS NULL THEN
        RETURN FALSE;
    END IF;
    
    -- Determine token consumption strategy
    IF is_premium_style THEN
        IF user_tokens.premium_tokens > 0 THEN
            -- Use premium tokens first
            UPDATE public.user_tokens 
            SET premium_tokens = premium_tokens - tokens_consumed,
                updated_at = NOW()
            WHERE user_tokens.user_id = $1;
            transaction_desc := 'Premium style transformation';
        ELSIF user_tokens.free_tokens > 0 THEN
            -- Fall back to free tokens
            UPDATE public.user_tokens 
            SET free_tokens = free_tokens - tokens_consumed,
                updated_at = NOW()
            WHERE user_tokens.user_id = $1;
            transaction_desc := 'Premium style transformation (using free token)';
        ELSE
            RETURN FALSE; -- No tokens available
        END IF;
    ELSE
        -- Regular style - use free tokens only
        IF user_tokens.free_tokens > 0 THEN
            UPDATE public.user_tokens 
            SET free_tokens = free_tokens - tokens_consumed,
                updated_at = NOW()
            WHERE user_tokens.user_id = $1;
            transaction_desc := 'Regular style transformation';
        ELSE
            RETURN FALSE; -- No free tokens available
        END IF;
    END IF;
    
    -- Log the transaction
    INSERT INTO public.token_transactions (user_id, transaction_type, amount, description)
    VALUES ($1, 'spent', -tokens_consumed, transaction_desc);
    
    RETURN TRUE;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- ===================================
-- INDEXES FOR PERFORMANCE
-- ===================================

CREATE INDEX idx_user_tokens_user_id ON public.user_tokens(user_id);
CREATE INDEX idx_transformations_user_id ON public.transformations(user_id);
CREATE INDEX idx_transformations_created_at ON public.transformations(created_at);
CREATE INDEX idx_token_transactions_user_id ON public.token_transactions(user_id);
CREATE INDEX idx_subscriptions_user_id ON public.subscriptions(user_id);
CREATE INDEX idx_subscriptions_stripe_id ON public.subscriptions(stripe_subscription_id);

-- ===================================
-- INITIAL DATA
-- ===================================

-- Create subscription plans (you can modify these)
CREATE TABLE public.subscription_plans (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    name TEXT NOT NULL,
    stripe_price_id TEXT NOT NULL,
    monthly_tokens INTEGER NOT NULL,
    price_cents INTEGER NOT NULL,
    features JSONB,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Insert sample plans
INSERT INTO public.subscription_plans (name, stripe_price_id, monthly_tokens, price_cents, features) VALUES
('Basic', 'price_basic_monthly', 50, 999, '{"premium_styles": false, "exclusive_colors": false}'),
('Premium', 'price_premium_monthly', 200, 1999, '{"premium_styles": true, "exclusive_colors": true, "priority_processing": true}'),
('Pro', 'price_pro_monthly', 500, 4999, '{"premium_styles": true, "exclusive_colors": true, "priority_processing": true, "api_access": true}');
