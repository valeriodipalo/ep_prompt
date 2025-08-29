import { Link, Head } from '@inertiajs/react';
import { Sparkles, Upload, Palette, Download, Star, Users, Zap } from 'lucide-react';

export default function Welcome({ auth, laravelVersion, phpVersion }) {
    return (
        <>
            <Head title="StyleAI - Transform Your Look with AI" />
            
            {/* Navigation */}
            <nav className="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-200">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center h-16">
                        <div className="flex items-center space-x-2">
                            <Sparkles className="h-8 w-8 text-purple-600" />
                            <span className="text-2xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                                StyleAI
                            </span>
                        </div>
                        
                        <div className="flex items-center space-x-4">
                    {auth.user ? (
                        <Link
                            href={route('dashboard')}
                                    className="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-2 rounded-full font-semibold hover:shadow-lg transform hover:scale-105 transition-all duration-200"
                        >
                            Dashboard
                        </Link>
                    ) : (
                        <>
                            <Link
                                href={route('login')}
                                        className="text-gray-700 hover:text-purple-600 font-semibold transition-colors duration-200"
                            >
                                        Sign In
                            </Link>
                            <Link
                                href={route('register')}
                                        className="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-2 rounded-full font-semibold hover:shadow-lg transform hover:scale-105 transition-all duration-200"
                            >
                                        Get Started
                            </Link>
                        </>
                    )}
                </div>
                    </div>
                                    </div>
            </nav>

            {/* Hero Section */}
            <section className="pt-24 pb-16 bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50 min-h-screen flex items-center">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center">
                        <h1 className="text-5xl md:text-7xl font-bold mb-6">
                            <span className="bg-gradient-to-r from-purple-600 via-pink-600 to-indigo-600 bg-clip-text text-transparent">
                                Transform Your Look
                            </span>
                            <br />
                            <span className="text-gray-900">with AI Magic</span>
                        </h1>
                        
                        <p className="text-xl md:text-2xl text-gray-600 mb-12 max-w-4xl mx-auto leading-relaxed">
                            Discover your perfect hairstyle with cutting-edge AI technology. 
                            Upload your photo, choose from hundreds of styles, and see yourself transformed instantly.
                        </p>
                        
                        <div className="flex flex-col sm:flex-row gap-4 justify-center items-center mb-16">
                            {!auth.user && (
                                <Link
                                    href={route('register')}
                                    className="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-8 py-4 rounded-full text-lg font-semibold hover:shadow-xl transform hover:scale-105 transition-all duration-300 flex items-center space-x-2"
                                >
                                    <Sparkles className="h-5 w-5" />
                                    <span>Try It Free</span>
                                </Link>
                            )}
                            
                            <button className="border-2 border-purple-600 text-purple-600 px-8 py-4 rounded-full text-lg font-semibold hover:bg-purple-600 hover:text-white transition-all duration-300">
                                Watch Demo
                            </button>
                                    </div>

                        {/* Stats */}
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
                            <div className="text-center">
                                <div className="text-3xl font-bold text-purple-600 mb-2">50K+</div>
                                <div className="text-gray-600">Happy Users</div>
                            </div>
                            <div className="text-center">
                                <div className="text-3xl font-bold text-pink-600 mb-2">500+</div>
                                <div className="text-gray-600">Hairstyles</div>
                            </div>
                            <div className="text-center">
                                <div className="text-3xl font-bold text-indigo-600 mb-2">1M+</div>
                                <div className="text-gray-600">Transformations</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* How It Works Section */}
            <section className="py-20 bg-white">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-16">
                        <h2 className="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
                            How It Works
                                    </h2>
                        <p className="text-xl text-gray-600 max-w-3xl mx-auto">
                            Transform your look in just three simple steps with our advanced AI technology
                        </p>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-12">
                        <div className="text-center">
                            <div className="bg-gradient-to-br from-purple-100 to-pink-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                                <Upload className="h-10 w-10 text-purple-600" />
                            </div>
                            <h3 className="text-2xl font-semibold text-gray-900 mb-4">Upload Your Photo</h3>
                            <p className="text-gray-600 leading-relaxed">
                                Simply upload a clear photo of yourself. Our AI works best with front-facing photos with good lighting.
                                    </p>
                                </div>

                        <div className="text-center">
                            <div className="bg-gradient-to-br from-pink-100 to-indigo-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                                <Palette className="h-10 w-10 text-pink-600" />
                            </div>
                            <h3 className="text-2xl font-semibold text-gray-900 mb-4">Choose Your Style</h3>
                            <p className="text-gray-600 leading-relaxed">
                                Browse through hundreds of hairstyles and colors. Filter by gender, length, and style preferences.
                            </p>
                                    </div>

                        <div className="text-center">
                            <div className="bg-gradient-to-br from-indigo-100 to-purple-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                                <Zap className="h-10 w-10 text-indigo-600" />
                            </div>
                            <h3 className="text-2xl font-semibold text-gray-900 mb-4">Get Your Result</h3>
                            <p className="text-gray-600 leading-relaxed">
                                Watch as our AI transforms your photo in seconds. Download, share, or try another style!
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {/* Features Section */}
            <section className="py-20 bg-gray-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-16">
                        <h2 className="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
                            Why Choose StyleAI?
                                    </h2>
                        <p className="text-xl text-gray-600 max-w-3xl mx-auto">
                            Experience the future of hairstyle visualization with cutting-edge AI technology
                        </p>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div className="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                            <div className="bg-purple-100 w-14 h-14 rounded-lg flex items-center justify-center mb-6">
                                <Zap className="h-7 w-7 text-purple-600" />
                            </div>
                            <h3 className="text-xl font-semibold text-gray-900 mb-4">Lightning Fast</h3>
                            <p className="text-gray-600">
                                Get your transformed photo in seconds, not minutes. Our optimized AI delivers results at incredible speed.
                                    </p>
                                </div>

                        <div className="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                            <div className="bg-pink-100 w-14 h-14 rounded-lg flex items-center justify-center mb-6">
                                <Star className="h-7 w-7 text-pink-600" />
                            </div>
                            <h3 className="text-xl font-semibold text-gray-900 mb-4">Premium Quality</h3>
                            <p className="text-gray-600">
                                High-resolution results with realistic hair textures and natural-looking transformations.
                            </p>
                        </div>
                        
                        <div className="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                            <div className="bg-indigo-100 w-14 h-14 rounded-lg flex items-center justify-center mb-6">
                                <Users className="h-7 w-7 text-indigo-600" />
                            </div>
                            <h3 className="text-xl font-semibold text-gray-900 mb-4">Trusted by Thousands</h3>
                            <p className="text-gray-600">
                                Join over 50,000 happy users who have discovered their perfect hairstyle with StyleAI.
                            </p>
                                    </div>

                        <div className="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                            <div className="bg-green-100 w-14 h-14 rounded-lg flex items-center justify-center mb-6">
                                <Palette className="h-7 w-7 text-green-600" />
                            </div>
                            <h3 className="text-xl font-semibold text-gray-900 mb-4">500+ Styles</h3>
                            <p className="text-gray-600">
                                Explore hundreds of hairstyles from classic cuts to trendy colors and everything in between.
                            </p>
                        </div>
                        
                        <div className="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                            <div className="bg-blue-100 w-14 h-14 rounded-lg flex items-center justify-center mb-6">
                                <Download className="h-7 w-7 text-blue-600" />
                            </div>
                            <h3 className="text-xl font-semibold text-gray-900 mb-4">HD Downloads</h3>
                            <p className="text-gray-600">
                                Download your transformed photos in high resolution, perfect for sharing or printing.
                                    </p>
                                </div>
                        
                        <div className="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                            <div className="bg-yellow-100 w-14 h-14 rounded-lg flex items-center justify-center mb-6">
                                <Sparkles className="h-7 w-7 text-yellow-600" />
                            </div>
                            <h3 className="text-xl font-semibold text-gray-900 mb-4">AI Powered</h3>
                            <p className="text-gray-600">
                                Advanced machine learning algorithms ensure realistic and personalized hairstyle transformations.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {/* CTA Section */}
            <section className="py-20 bg-gradient-to-br from-purple-600 via-pink-600 to-indigo-600">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h2 className="text-4xl md:text-5xl font-bold text-white mb-6">
                        Ready to Transform Your Look?
                    </h2>
                    <p className="text-xl text-purple-100 mb-12 max-w-2xl mx-auto">
                        Join thousands of users who have discovered their perfect hairstyle. 
                        Start your transformation journey today!
                    </p>
                    
                    <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
                        {!auth.user && (
                            <Link
                                href={route('register')}
                                className="bg-white text-purple-600 px-8 py-4 rounded-full text-lg font-semibold hover:shadow-xl transform hover:scale-105 transition-all duration-300 flex items-center space-x-2"
                            >
                                <Sparkles className="h-5 w-5" />
                                <span>Start Free Trial</span>
                            </Link>
                        )}
                        
                        <button className="border-2 border-white text-white px-8 py-4 rounded-full text-lg font-semibold hover:bg-white hover:text-purple-600 transition-all duration-300">
                            View Pricing
                        </button>
                    </div>
                </div>
            </section>

            {/* Footer */}
            <footer className="bg-gray-900 text-white py-12">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div>
                            <div className="flex items-center space-x-2 mb-4">
                                <Sparkles className="h-8 w-8 text-purple-400" />
                                <span className="text-2xl font-bold">StyleAI</span>
                            </div>
                            <p className="text-gray-400">
                                Transform your look with AI-powered hairstyle visualization technology.
                            </p>
                        </div>
                        
                        <div>
                            <h3 className="text-lg font-semibold mb-4">Product</h3>
                            <ul className="space-y-2 text-gray-400">
                                <li><a href="#" className="hover:text-white transition-colors">Features</a></li>
                                <li><a href="#" className="hover:text-white transition-colors">Pricing</a></li>
                                <li><a href="#" className="hover:text-white transition-colors">API</a></li>
                            </ul>
                    </div>

                        <div>
                            <h3 className="text-lg font-semibold mb-4">Support</h3>
                            <ul className="space-y-2 text-gray-400">
                                <li><a href="#" className="hover:text-white transition-colors">Help Center</a></li>
                                <li><a href="#" className="hover:text-white transition-colors">Contact Us</a></li>
                                <li><a href="#" className="hover:text-white transition-colors">Status</a></li>
                            </ul>
                        </div>
                        
                        <div>
                            <h3 className="text-lg font-semibold mb-4">Legal</h3>
                            <ul className="space-y-2 text-gray-400">
                                <li><a href="#" className="hover:text-white transition-colors">Privacy Policy</a></li>
                                <li><a href="#" className="hover:text-white transition-colors">Terms of Service</a></li>
                                <li><a href="#" className="hover:text-white transition-colors">Cookie Policy</a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div className="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
                        <p>&copy; 2024 StyleAI. All rights reserved.</p>
                    </div>
                </div>
            </footer>
        </>
    );
}
