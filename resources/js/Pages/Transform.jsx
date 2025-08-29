import { useState, useCallback } from 'react';
import { Head, Link } from '@inertiajs/react';
import { useDropzone } from 'react-dropzone';
import { Upload, Image as ImageIcon, X, Sparkles, ArrowLeft, ArrowRight, Download, Loader2 } from 'lucide-react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { falAI } from '@/lib/fal-ai';
import toast, { Toaster } from 'react-hot-toast';

export default function Transform({ auth }) {
    const [uploadedImage, setUploadedImage] = useState(null);
    const [selectedGender, setSelectedGender] = useState('');
    const [selectedStyle, setSelectedStyle] = useState(null);
    const [selectedColor, setSelectedColor] = useState(null);
    const [step, setStep] = useState(1);
    const [isGenerating, setIsGenerating] = useState(false);
    const [transformedImage, setTransformedImage] = useState(null);

    const onDrop = useCallback((acceptedFiles) => {
        const file = acceptedFiles[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = () => {
                setUploadedImage({
                    file,
                    preview: reader.result
                });
                setStep(2);
            };
            reader.readAsDataURL(file);
        }
    }, []);

    const { getRootProps, getInputProps, isDragActive } = useDropzone({
        onDrop,
        accept: {
            'image/*': ['.jpeg', '.jpg', '.png', '.webp']
        },
        multiple: false,
        maxSize: 10485760 // 10MB
    });

    const hairstyles = [
        { id: 1, name: 'Short Bob', category: 'short', gender: 'female', image: '/images/styles/bob.jpg' },
        { id: 2, name: 'Long Waves', category: 'long', gender: 'female', image: '/images/styles/waves.jpg' },
        { id: 3, name: 'Pixie Cut', category: 'short', gender: 'female', image: '/images/styles/pixie.jpg' },
        { id: 4, name: 'Beach Curls', category: 'medium', gender: 'female', image: '/images/styles/curls.jpg' },
        { id: 5, name: 'Classic Fade', category: 'short', gender: 'male', image: '/images/styles/fade.jpg' },
        { id: 6, name: 'Modern Quiff', category: 'medium', gender: 'male', image: '/images/styles/quiff.jpg' },
        { id: 7, name: 'Long Layers', category: 'long', gender: 'male', image: '/images/styles/layers.jpg' },
        { id: 8, name: 'Buzz Cut', category: 'short', gender: 'male', image: '/images/styles/buzz.jpg' },
    ];

    const hairColors = [
        { id: 1, name: 'Natural Black', hex: '#1a1a1a' },
        { id: 2, name: 'Dark Brown', hex: '#3d2914' },
        { id: 3, name: 'Medium Brown', hex: '#5c4033' },
        { id: 4, name: 'Light Brown', hex: '#8b4513' },
        { id: 5, name: 'Blonde', hex: '#faf0be' },
        { id: 6, name: 'Platinum', hex: '#e5e4e2' },
        { id: 7, name: 'Auburn', hex: '#a52a2a' },
        { id: 8, name: 'Red', hex: '#ff0000' },
        { id: 9, name: 'Purple', hex: '#800080' },
        { id: 10, name: 'Blue', hex: '#0000ff' },
        { id: 11, name: 'Pink', hex: '#ffc0cb' },
        { id: 12, name: 'Green', hex: '#008000' },
    ];

    const filteredStyles = selectedGender 
        ? hairstyles.filter(style => style.gender === selectedGender)
        : hairstyles;

    const handleGenerate = async () => {
        if (!uploadedImage || !selectedStyle || !selectedColor) {
            toast.error('Please complete all steps before generating');
            return;
        }

        setIsGenerating(true);
        toast.loading('Generating your transformation...', { id: 'generating' });

        try {
            const styleOptions = {
                style: selectedStyle.name,
                color: selectedColor.name,
                gender: selectedGender
            };

            // Use mock transformation for development
            const result = await falAI.mockTransformation(uploadedImage.file, styleOptions);
            
            if (result.success) {
                setTransformedImage(result);
                setStep(5);
                toast.success('Transformation complete!', { id: 'generating' });
            } else {
                throw new Error('Transformation failed');
            }
        } catch (error) {
            console.error('Transformation error:', error);
            toast.error('Failed to generate transformation. Please try again.', { id: 'generating' });
        } finally {
            setIsGenerating(false);
        }
    };

    const handleDownload = () => {
        if (transformedImage?.image_url) {
            const link = document.createElement('a');
            link.href = transformedImage.image_url;
            link.download = `styleai-transformation-${Date.now()}.png`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            toast.success('Image downloaded!');
        }
    };

    const handleReset = () => {
        setUploadedImage(null);
        setSelectedGender('');
        setSelectedStyle(null);
        setSelectedColor(null);
        setTransformedImage(null);
        setStep(1);
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Transform Your Look - StyleAI" />
            <Toaster position="top-center" />

            <div className="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50">
                <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    {/* Header */}
                    <div className="text-center mb-8">
                        <Link
                            href={route('dashboard')}
                            className="inline-flex items-center text-purple-600 hover:text-purple-800 mb-4"
                        >
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Dashboard
                        </Link>
                        <h1 className="text-4xl md:text-5xl font-bold mb-4">
                            <span className="bg-gradient-to-r from-purple-600 via-pink-600 to-indigo-600 bg-clip-text text-transparent">
                                Transform Your Look
                            </span>
                        </h1>
                        <p className="text-xl text-gray-600 max-w-3xl mx-auto">
                            Upload your photo and discover your perfect hairstyle with AI magic
                        </p>
                    </div>

                    {/* Progress Steps */}
                    <div className="flex justify-center mb-12">
                        <div className="flex items-center space-x-4">
                            <div className={`flex items-center justify-center w-10 h-10 rounded-full ${
                                step >= 1 ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-500'
                            }`}>
                                1
                            </div>
                            <div className={`h-1 w-12 ${step >= 2 ? 'bg-purple-600' : 'bg-gray-200'}`}></div>
                            <div className={`flex items-center justify-center w-10 h-10 rounded-full ${
                                step >= 2 ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-500'
                            }`}>
                                2
                            </div>
                            <div className={`h-1 w-12 ${step >= 3 ? 'bg-purple-600' : 'bg-gray-200'}`}></div>
                            <div className={`flex items-center justify-center w-10 h-10 rounded-full ${
                                step >= 3 ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-500'
                            }`}>
                                3
                            </div>
                            <div className={`h-1 w-12 ${step >= 4 ? 'bg-purple-600' : 'bg-gray-200'}`}></div>
                            <div className={`flex items-center justify-center w-10 h-10 rounded-full ${
                                step >= 4 ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-500'
                            }`}>
                                4
                            </div>
                            <div className={`h-1 w-12 ${step >= 5 ? 'bg-purple-600' : 'bg-gray-200'}`}></div>
                            <div className={`flex items-center justify-center w-10 h-10 rounded-full ${
                                step >= 5 ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-500'
                            }`}>
                                5
                            </div>
                        </div>
                    </div>

                    {/* Step 1: Upload Photo */}
                    {step === 1 && (
                        <div className="max-w-2xl mx-auto">
                            <div className="bg-white rounded-2xl shadow-xl p-8">
                                <div className="text-center mb-6">
                                    <h2 className="text-2xl font-bold text-gray-900 mb-2">Upload Your Photo</h2>
                                    <p className="text-gray-600">Upload a clear, front-facing photo for best results</p>
                                </div>

                                <div
                                    {...getRootProps()}
                                    className={`border-2 border-dashed rounded-xl p-12 text-center cursor-pointer transition-all duration-300 ${
                                        isDragActive
                                            ? 'border-purple-500 bg-purple-50'
                                            : 'border-gray-300 hover:border-purple-400 hover:bg-gray-50'
                                    }`}
                                >
                                    <input {...getInputProps()} />
                                    <Upload className="h-16 w-16 text-gray-400 mx-auto mb-4" />
                                    {isDragActive ? (
                                        <p className="text-lg text-purple-600">Drop your photo here...</p>
                                    ) : (
                                        <>
                                            <p className="text-lg text-gray-600 mb-2">
                                                Drag & drop your photo here, or click to select
                                            </p>
                                            <p className="text-sm text-gray-400">
                                                Supports JPG, PNG, WebP up to 10MB
                                            </p>
                                        </>
                                    )}
                                </div>

                                <div className="mt-6 text-center">
                                    <p className="text-sm text-gray-500">
                                        💡 <strong>Tip:</strong> Use a clear, well-lit photo facing forward for the best AI transformation results
                                    </p>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Step 2: Select Gender & Style */}
                    {step === 2 && (
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            {/* Uploaded Photo Preview */}
                            <div className="bg-white rounded-2xl shadow-xl p-6">
                                <div className="flex justify-between items-center mb-4">
                                    <h3 className="text-xl font-semibold text-gray-900">Your Photo</h3>
                                    <button
                                        onClick={() => {
                                            setUploadedImage(null);
                                            setStep(1);
                                        }}
                                        className="text-gray-500 hover:text-red-500"
                                    >
                                        <X className="h-5 w-5" />
                                    </button>
                                </div>
                                {uploadedImage && (
                                    <div className="aspect-square rounded-xl overflow-hidden bg-gray-100">
                                        <img
                                            src={uploadedImage.preview}
                                            alt="Uploaded photo"
                                            className="w-full h-full object-cover"
                                        />
                                    </div>
                                )}
                            </div>

                            {/* Gender & Style Selection */}
                            <div className="space-y-6">
                                {/* Gender Selection */}
                                <div className="bg-white rounded-2xl shadow-xl p-6">
                                    <h3 className="text-xl font-semibold text-gray-900 mb-4">Select Gender</h3>
                                    <div className="grid grid-cols-2 gap-4">
                                        <button
                                            onClick={() => setSelectedGender('female')}
                                            className={`p-4 rounded-xl border-2 transition-all duration-200 ${
                                                selectedGender === 'female'
                                                    ? 'border-purple-500 bg-purple-50 text-purple-700'
                                                    : 'border-gray-200 hover:border-purple-300'
                                            }`}
                                        >
                                            <div className="text-center">
                                                <div className="text-2xl mb-2">👩</div>
                                                <div className="font-semibold">Female</div>
                                            </div>
                                        </button>
                                        <button
                                            onClick={() => setSelectedGender('male')}
                                            className={`p-4 rounded-xl border-2 transition-all duration-200 ${
                                                selectedGender === 'male'
                                                    ? 'border-purple-500 bg-purple-50 text-purple-700'
                                                    : 'border-gray-200 hover:border-purple-300'
                                            }`}
                                        >
                                            <div className="text-center">
                                                <div className="text-2xl mb-2">👨</div>
                                                <div className="font-semibold">Male</div>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                {/* Style Selection */}
                                {selectedGender && (
                                    <div className="bg-white rounded-2xl shadow-xl p-6">
                                        <h3 className="text-xl font-semibold text-gray-900 mb-4">Choose Hairstyle</h3>
                                        <div className="grid grid-cols-2 gap-4 max-h-64 overflow-y-auto">
                                            {filteredStyles.map((style) => (
                                                <button
                                                    key={style.id}
                                                    onClick={() => {
                                                        setSelectedStyle(style);
                                                        setStep(3);
                                                    }}
                                                    className={`p-3 rounded-xl border-2 transition-all duration-200 ${
                                                        selectedStyle?.id === style.id
                                                            ? 'border-purple-500 bg-purple-50'
                                                            : 'border-gray-200 hover:border-purple-300'
                                                    }`}
                                                >
                                                    <div className="aspect-square bg-gray-100 rounded-lg mb-2 flex items-center justify-center">
                                                        <ImageIcon className="h-8 w-8 text-gray-400" />
                                                    </div>
                                                    <div className="text-sm font-medium text-center">{style.name}</div>
                                                </button>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}

                    {/* Step 3: Select Color */}
                    {step === 3 && (
                        <div className="max-w-4xl mx-auto">
                            <div className="bg-white rounded-2xl shadow-xl p-8">
                                <div className="text-center mb-8">
                                    <h2 className="text-2xl font-bold text-gray-900 mb-2">Choose Hair Color</h2>
                                    <p className="text-gray-600">Select the perfect color for your new hairstyle</p>
                                </div>

                                <div className="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4">
                                    {hairColors.map((color) => (
                                        <button
                                            key={color.id}
                                            onClick={() => {
                                                setSelectedColor(color);
                                                setStep(4);
                                            }}
                                            className={`group relative p-4 rounded-xl border-2 transition-all duration-200 ${
                                                selectedColor?.id === color.id
                                                    ? 'border-purple-500 bg-purple-50'
                                                    : 'border-gray-200 hover:border-purple-300'
                                            }`}
                                        >
                                            <div
                                                className="w-12 h-12 rounded-full mx-auto mb-2 border-2 border-white shadow-md"
                                                style={{ backgroundColor: color.hex }}
                                            ></div>
                                            <div className="text-xs font-medium text-center text-gray-700">
                                                {color.name}
                                            </div>
                                        </button>
                                    ))}
                                </div>

                                <div className="flex justify-center mt-8">
                                    <button
                                        onClick={() => setStep(2)}
                                        className="flex items-center px-6 py-3 text-purple-600 hover:text-purple-800"
                                    >
                                        <ArrowLeft className="h-4 w-4 mr-2" />
                                        Back to Style Selection
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Step 4: Review & Generate */}
                    {step === 4 && (
                        <div className="max-w-4xl mx-auto">
                            <div className="bg-white rounded-2xl shadow-xl p-8">
                                <div className="text-center mb-8">
                                    <h2 className="text-2xl font-bold text-gray-900 mb-2">Review Your Selection</h2>
                                    <p className="text-gray-600">Everything looks good? Let's create your transformation!</p>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                                    {/* Original Photo */}
                                    <div className="text-center">
                                        <h3 className="text-lg font-semibold text-gray-900 mb-4">Your Photo</h3>
                                        {uploadedImage && (
                                            <div className="aspect-square rounded-xl overflow-hidden bg-gray-100">
                                                <img
                                                    src={uploadedImage.preview}
                                                    alt="Original photo"
                                                    className="w-full h-full object-cover"
                                                />
                                            </div>
                                        )}
                                    </div>

                                    {/* Selected Style */}
                                    <div className="text-center">
                                        <h3 className="text-lg font-semibold text-gray-900 mb-4">Selected Style</h3>
                                        <div className="aspect-square rounded-xl bg-gray-100 flex items-center justify-center mb-2">
                                            <ImageIcon className="h-16 w-16 text-gray-400" />
                                        </div>
                                        <p className="font-medium">{selectedStyle?.name}</p>
                                    </div>

                                    {/* Selected Color */}
                                    <div className="text-center">
                                        <h3 className="text-lg font-semibold text-gray-900 mb-4">Selected Color</h3>
                                        <div className="flex justify-center mb-2">
                                            <div
                                                className="w-24 h-24 rounded-xl border-4 border-white shadow-lg"
                                                style={{ backgroundColor: selectedColor?.hex }}
                                            ></div>
                                        </div>
                                        <p className="font-medium">{selectedColor?.name}</p>
                                    </div>
                                </div>

                                <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
                                    <button
                                        onClick={() => setStep(3)}
                                        className="flex items-center px-6 py-3 text-purple-600 hover:text-purple-800"
                                    >
                                        <ArrowLeft className="h-4 w-4 mr-2" />
                                        Back to Color Selection
                                    </button>
                                    
                                    <button
                                        onClick={handleGenerate}
                                        disabled={isGenerating}
                                        className="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-8 py-4 rounded-full text-lg font-semibold hover:shadow-xl transform hover:scale-105 transition-all duration-300 flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                                    >
                                        {isGenerating ? (
                                            <>
                                                <Loader2 className="h-5 w-5 animate-spin" />
                                                <span>Generating...</span>
                                            </>
                                        ) : (
                                            <>
                                                <Sparkles className="h-5 w-5" />
                                                <span>Generate Transformation</span>
                                            </>
                                        )}
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Step 5: Results */}
                    {step === 5 && transformedImage && (
                        <div className="max-w-6xl mx-auto">
                            <div className="bg-white rounded-2xl shadow-xl p-8">
                                <div className="text-center mb-8">
                                    <h2 className="text-3xl font-bold text-gray-900 mb-2">Your Transformation is Ready! ✨</h2>
                                    <p className="text-gray-600">Here's your new look with AI magic</p>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                                    {/* Before */}
                                    <div className="text-center">
                                        <h3 className="text-xl font-semibold text-gray-900 mb-4">Before</h3>
                                        {uploadedImage && (
                                            <div className="aspect-square rounded-xl overflow-hidden bg-gray-100 shadow-lg">
                                                <img
                                                    src={uploadedImage.preview}
                                                    alt="Original photo"
                                                    className="w-full h-full object-cover"
                                                />
                                            </div>
                                        )}
                                    </div>

                                    {/* After */}
                                    <div className="text-center">
                                        <h3 className="text-xl font-semibold text-gray-900 mb-4">After</h3>
                                        <div className="aspect-square rounded-xl overflow-hidden bg-gray-100 shadow-lg">
                                            <img
                                                src={transformedImage.image_url}
                                                alt="Transformed photo"
                                                className="w-full h-full object-cover"
                                            />
                                        </div>
                                    </div>
                                </div>

                                {/* Transformation Details */}
                                <div className="bg-gray-50 rounded-xl p-6 mb-8">
                                    <h3 className="text-lg font-semibold text-gray-900 mb-4">Transformation Details</h3>
                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <span className="font-medium text-gray-700">Style:</span>
                                            <span className="ml-2 text-gray-600">{selectedStyle?.name}</span>
                                        </div>
                                        <div>
                                            <span className="font-medium text-gray-700">Color:</span>
                                            <span className="ml-2 text-gray-600">{selectedColor?.name}</span>
                                        </div>
                                        <div>
                                            <span className="font-medium text-gray-700">Processing Time:</span>
                                            <span className="ml-2 text-gray-600">{transformedImage.processing_time}s</span>
                                        </div>
                                    </div>
                                </div>

                                {/* Action Buttons */}
                                <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
                                    <button
                                        onClick={handleDownload}
                                        className="bg-gradient-to-r from-green-600 to-emerald-600 text-white px-8 py-4 rounded-full text-lg font-semibold hover:shadow-xl transform hover:scale-105 transition-all duration-300 flex items-center space-x-2"
                                    >
                                        <Download className="h-5 w-5" />
                                        <span>Download Image</span>
                                    </button>
                                    
                                    <button
                                        onClick={handleReset}
                                        className="border-2 border-purple-600 text-purple-600 px-8 py-4 rounded-full text-lg font-semibold hover:bg-purple-600 hover:text-white transition-all duration-300"
                                    >
                                        Try Another Style
                                    </button>

                                    <Link
                                        href={route('dashboard')}
                                        className="text-gray-600 hover:text-gray-800 px-4 py-2 rounded-lg transition-colors duration-200"
                                    >
                                        Back to Dashboard
                                    </Link>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
