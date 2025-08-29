# StyleAI - AI Hairstyle Transformation App

A beautiful web application that allows users to try different hairstyles using AI technology, built with Laravel, React, and modern design principles.

## ✨ Features

- 🎨 **Beautiful Modern UI**: Clean, responsive design with gradient themes
- 🤖 **AI-Powered Transformations**: Integration with fal.ai for hairstyle generation
- 🔐 **Secure Authentication**: User registration and login with modern design
- 💳 **Payment Integration**: Stripe payment processing for premium features
- 📱 **Mobile-First Design**: Fully responsive across all devices
- ⚡ **Lightning Fast**: Optimized performance with modern tech stack
- 🎯 **Drag & Drop Upload**: Intuitive photo upload interface
- 🎨 **Style Gallery**: Browse hundreds of hairstyles and colors
- 📊 **User Dashboard**: Track transformations and manage account

## 🛠 Tech Stack

- **Backend**: Laravel 10, PHP 8.1+
- **Frontend**: React 18, Inertia.js, Tailwind CSS
- **Icons**: Lucide React
- **Animations**: Framer Motion, React Hot Toast
- **AI Integration**: fal.ai API
- **Authentication**: Laravel Breeze + Supabase (optional)
- **Payments**: Stripe
- **Database**: MySQL/PostgreSQL
- **Admin Panel**: Laravel Nova

## 🚀 Quick Start

### Prerequisites

- PHP 8.1 or higher
- Node.js 16+ and npm
- Composer
- MySQL or PostgreSQL database

### Installation

1. **Clone and install dependencies**:
   ```bash
   git clone https://github.com/valeriodipalo/laraver_final_ai_headshot.git
   cd laraver_final_ai_headshot
   composer install
   npm install
   ```

2. **Environment Setup**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configure your `.env` file**:
   ```env
   APP_NAME="StyleAI"
   APP_URL=http://localhost:8000

   # Database
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=styleai
   DB_USERNAME=your_username
   DB_PASSWORD=your_password

   # Supabase (optional - for enhanced auth)
   VITE_SUPABASE_URL=your_supabase_project_url
   VITE_SUPABASE_ANON_KEY=your_supabase_anon_key

   # fal.ai API
   VITE_FAL_API_KEY=your_fal_ai_api_key

   # Stripe
   VITE_STRIPE_PUBLISHABLE_KEY=pk_test_...
   STRIPE_SECRET_KEY=sk_test_...
   ```

4. **Database Setup**:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build and Run**:
   ```bash
   npm run build
   php artisan serve
   ```

   Or for development:
   ```bash
   npm run dev
   php artisan serve
   ```

## 🔑 API Configuration

### fal.ai Setup

1. Sign up at [fal.ai](https://fal.ai)
2. Get your API key from the dashboard
3. Add it to your `.env` file as `VITE_FAL_API_KEY`

### Stripe Setup

1. Create a Stripe account at [stripe.com](https://stripe.com)
2. Get your publishable and secret keys from the dashboard
3. Add them to your `.env` file
4. Configure webhook endpoints for subscription management

### Supabase Setup (Optional)

1. Create a project at [supabase.com](https://supabase.com)
2. Get your project URL and anon key
3. Add them to your `.env` file

## 📱 Usage

### For Users

1. **Sign Up**: Create an account with email and password
2. **Upload Photo**: Drag and drop or select a clear photo
3. **Select Gender**: Choose male or female for appropriate styles
4. **Choose Style**: Browse through hundreds of hairstyles
5. **Pick Color**: Select from natural and vibrant color options
6. **Generate**: Watch AI transform your photo in seconds
7. **Download**: Save your transformed image in high resolution

### For Administrators

Access the admin panel at `/nova` to:
- Manage users and their transformations
- Monitor system usage and performance
- Handle payments and subscriptions
- View analytics and reports

## 🎨 Design System

The app uses a cohesive design system with:

- **Colors**: Purple to pink gradients (#6366f1 to #ec4899)
- **Typography**: Modern, readable fonts with proper hierarchy
- **Spacing**: Consistent 8px grid system
- **Components**: Reusable, accessible UI components
- **Animations**: Smooth transitions and micro-interactions

## 🔧 Development

### Project Structure

```
├── app/                    # Laravel backend
├── resources/
│   ├── js/
│   │   ├── Components/     # Reusable React components
│   │   ├── Layouts/        # Page layouts
│   │   ├── Pages/          # Application pages
│   │   └── lib/           # Utility libraries
│   └── css/               # Stylesheets
├── routes/                # Application routes
└── database/              # Migrations and seeders
```

### Key Components

- **Welcome.jsx**: Beautiful landing page with features
- **Transform.jsx**: Multi-step transformation flow
- **Dashboard.jsx**: User dashboard with stats and quick actions
- **Auth/**: Modern login and registration pages

### Development Commands

```bash
# Start development servers
npm run dev
php artisan serve

# Build for production
npm run build

# Run tests
php artisan test
npm run test

# Code formatting
./vendor/bin/pint
npm run format
```

## 🚀 Deployment

### Production Setup

1. **Server Requirements**:
   - PHP 8.1+, Composer
   - Node.js 16+, npm
   - Web server (Apache/Nginx)
   - Database (MySQL/PostgreSQL)

2. **Environment**:
   ```bash
   APP_ENV=production
   APP_DEBUG=false
   ```

3. **Optimization**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   npm run build
   ```

### Recommended Hosting

- **Laravel Forge**: Automated server management
- **DigitalOcean**: Reliable cloud hosting
- **AWS**: Scalable cloud infrastructure
- **Vercel**: For frontend-only deployments

## 📊 Features Roadmap

### Current Features ✅
- Beautiful landing page
- User authentication
- Photo upload with drag & drop
- Style and color selection
- AI transformation (mock)
- Results display and download
- Responsive design
- Loading states and animations

### Coming Soon 🚧
- Real fal.ai API integration
- Payment processing
- User subscription management
- Transformation history
- Social sharing
- Mobile app
- Advanced editing tools
- API for developers

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Documentation**: Check this README and code comments
- **Issues**: Report bugs on GitHub Issues
- **Email**: Contact the development team
- **Discord**: Join our community server

---

Built with ❤️ by the StyleAI team using Laravel, React, and modern web technologies.
