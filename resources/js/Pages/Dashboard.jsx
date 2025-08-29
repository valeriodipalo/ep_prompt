import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Sparkles, Upload, History, Crown, Star, Zap } from 'lucide-react';

export default function Dashboard({ auth }) {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Dashboard - StyleAI" />

            <div className="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    {/* Welcome Section */}
                    <div className="text-center mb-12">
                        <h1 className="text-4xl md:text-5xl font-bold mb-4">
                            Welcome back, <span className="bg-gradient-to-r from-purple-600 via-pink-600 to-indigo-600 bg-clip-text text-transparent">
                                {auth.user.name}
                            </span>!
                        </h1>
                        <p className="text-xl text-gray-600 max-w-2xl mx-auto">
                            Ready to discover your perfect hairstyle? Let's create some magic together!
                        </p>
                    </div>

                    {/* Quick Actions */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                        {/* Transform Photo */}
                        <Link
                            href={route('transform')}
                            className="group bg-white rounded-2xl shadow-xl p-8 hover:shadow-2xl transform hover:scale-105 transition-all duration-300"
                        >
                            <div className="bg-gradient-to-br from-purple-100 to-pink-100 w-16 h-16 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                <Sparkles className="h-8 w-8 text-purple-600" />
                            </div>
                            <h3 className="text-2xl font-bold text-gray-900 mb-4">Transform Photo</h3>
                            <p className="text-gray-600 mb-6">
                                Upload your photo and try different hairstyles with AI magic
                            </p>
                            <div className="flex items-center text-purple-600 font-semibold">
                                <span>Start Transformation</span>
                                <Zap className="h-4 w-4 ml-2 group-hover:translate-x-1 transition-transform duration-200" />
                            </div>
                        </Link>

                        {/* Upload Gallery */}
                        <div className="bg-white rounded-2xl shadow-xl p-8 hover:shadow-2xl transform hover:scale-105 transition-all duration-300 cursor-pointer opacity-75">
                            <div className="bg-gradient-to-br from-pink-100 to-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                                <Upload className="h-8 w-8 text-pink-600" />
                            </div>
                            <h3 className="text-2xl font-bold text-gray-900 mb-4">Quick Upload</h3>
                            <p className="text-gray-600 mb-6">
                                Drag and drop photos directly to your gallery
                            </p>
                            <div className="flex items-center text-gray-400 font-semibold">
                                <span>Coming Soon</span>
                            </div>
                        </div>

                        {/* History */}
                        <div className="bg-white rounded-2xl shadow-xl p-8 hover:shadow-2xl transform hover:scale-105 transition-all duration-300 cursor-pointer opacity-75">
                            <div className="bg-gradient-to-br from-indigo-100 to-purple-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                                <History className="h-8 w-8 text-indigo-600" />
                            </div>
                            <h3 className="text-2xl font-bold text-gray-900 mb-4">My Transformations</h3>
                            <p className="text-gray-600 mb-6">
                                View and download your previous transformations
                            </p>
                            <div className="flex items-center text-gray-400 font-semibold">
                                <span>Coming Soon</span>
                            </div>
                        </div>
                    </div>

                    {/* Stats Cards */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                        <div className="bg-white rounded-xl shadow-lg p-6 text-center">
                            <div className="bg-purple-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4">
                                <Star className="h-6 w-6 text-purple-600" />
                            </div>
                            <div className="text-2xl font-bold text-gray-900">0</div>
                            <div className="text-gray-600">Transformations</div>
                        </div>
                        
                        <div className="bg-white rounded-xl shadow-lg p-6 text-center">
                            <div className="bg-pink-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4">
                                <Crown className="h-6 w-6 text-pink-600" />
                            </div>
                            <div className="text-2xl font-bold text-gray-900">Free</div>
                            <div className="text-gray-600">Current Plan</div>
                        </div>
                        
                        <div className="bg-white rounded-xl shadow-lg p-6 text-center">
                            <div className="bg-indigo-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4">
                                <Zap className="h-6 w-6 text-indigo-600" />
                            </div>
                            <div className="text-2xl font-bold text-gray-900">5</div>
                            <div className="text-gray-600">Credits Left</div>
                        </div>
                    </div>

                    {/* Recent Activity */}
                    <div className="bg-white rounded-2xl shadow-xl p-8">
                        <h2 className="text-2xl font-bold text-gray-900 mb-6">Recent Activity</h2>
                        <div className="text-center py-12">
                            <div className="bg-gray-100 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-4">
                                <History className="h-12 w-12 text-gray-400" />
                            </div>
                            <h3 className="text-xl font-semibold text-gray-900 mb-2">No transformations yet</h3>
                            <p className="text-gray-600 mb-6">
                                Start your first transformation to see your activity here
                            </p>
                            <Link
                                href={route('transform')}
                                className="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-3 rounded-full font-semibold hover:shadow-lg transform hover:scale-105 transition-all duration-200 inline-flex items-center space-x-2"
                            >
                                <Sparkles className="h-4 w-4" />
                                <span>Create First Transformation</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
