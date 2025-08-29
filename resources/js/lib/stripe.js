import { loadStripe } from '@stripe/stripe-js';

const stripePromise = loadStripe(import.meta.env.VITE_STRIPE_PUBLISHABLE_KEY);

export const getStripe = () => stripePromise;

export const createCheckoutSession = async (priceId, userId) => {
    try {
        const response = await fetch('/api/create-checkout-session', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                price_id: priceId,
                user_id: userId
            })
        });

        const session = await response.json();
        
        if (session.error) {
            throw new Error(session.error);
        }

        const stripe = await getStripe();
        const { error } = await stripe.redirectToCheckout({
            sessionId: session.id
        });

        if (error) {
            throw new Error(error.message);
        }
    } catch (error) {
        console.error('Error creating checkout session:', error);
        throw error;
    }
};

export const pricingPlans = [
    {
        id: 'free',
        name: 'Free',
        price: 0,
        credits: 5,
        features: [
            '5 AI transformations',
            'Basic hairstyles',
            'Standard quality',
            'Basic support'
        ]
    },
    {
        id: 'pro',
        name: 'Pro',
        price: 9.99,
        credits: 50,
        features: [
            '50 AI transformations',
            'Premium hairstyles',
            'HD quality downloads',
            'Priority support',
            'Custom color options'
        ],
        stripe_price_id: 'price_pro_monthly'
    },
    {
        id: 'unlimited',
        name: 'Unlimited',
        price: 19.99,
        credits: -1, // Unlimited
        features: [
            'Unlimited transformations',
            'All premium features',
            '4K quality downloads',
            'Priority support',
            'API access',
            'Commercial usage rights'
        ],
        stripe_price_id: 'price_unlimited_monthly'
    }
];
