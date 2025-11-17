# AI Image Generator

Simple AI-powered image transformation application.

## Stack

- **Frontend:** React (standalone HTML) + Tailwind CSS
- **Backend:** Laravel 10
- **Database:** Supabase (PostgreSQL)
- **AI:** fal.ai
- **Payments:** Stripe

## Local Setup

1. Install dependencies:
```bash
composer install
```

2. Configure environment:
```bash
cp example.env .env
# Edit .env with your API keys
```

3. Start backend:
```bash
php artisan serve --port=8001
```

4. Start frontend:
```bash
python3 -m http.server 8080 --directory public
```

5. Open: `http://localhost:8080/simple-widget.html`

## Environment Variables

Required in `.env`:
- `SUPABASE_URL` - Your Supabase project URL
- `SUPABASE_ANON_KEY` - Supabase anon key
- `SUPABASE_SERVICE_KEY` - Supabase service key
- `FAL_KEY` - fal.ai API key (used for all AI models)
- `STRIPE_KEY` - Stripe publishable key (test mode)
- `STRIPE_SECRET` - Stripe secret key (test mode)

## AI Models

This application uses fal.ai for AI-powered image processing:

1. **nano-banana/edit** - General image transformation with prompts
2. **NAFNet Deblur** - Image deblurring and noise reduction

Both models use the same `FAL_KEY` credential and deduct 1 generation credit per use.

For detailed NAFNet Deblur API documentation, see [NAFNET_DEBLUR_API.md](NAFNET_DEBLUR_API.md).

## Deployment

### Frontend (Vercel)
Push to GitHub, Vercel will auto-deploy.

### Backend (Railway)
Set environment variables in Railway dashboard, push to deploy.

## API Endpoints

### Authentication
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `GET /api/auth/profile` - Get user profile
- `POST /api/auth/consume-transformation` - Deduct generations

### AI Transformations
- `POST /api/fal/direct-base64-transform` - AI image transformation (nano-banana)
- `POST /api/fal/deblur` - Image deblur & denoise (NAFNet)

### Payments
- `GET /api/payments/packages` - Get pricing
- `POST /api/payments/create-checkout-session` - Stripe checkout
- `POST /api/payments/webhook` - Stripe webhook

## License

MIT
