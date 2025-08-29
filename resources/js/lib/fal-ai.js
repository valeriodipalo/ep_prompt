// fal.ai API integration for hairstyle transformations via Laravel backend
const API_BASE_URL = window.location.origin;

export class FalAI {
    constructor() {
        this.baseUrl = `${API_BASE_URL}/api/fal`;
    }

    async transformHairstyle(imageFile, styleOptions) {
        try {
            // Step 1: Upload image to fal.ai storage via our backend
            const imageUrl = await this.uploadImage(imageFile);
            
            // Step 2: Submit transformation request
            const response = await fetch(`${this.baseUrl}/transform-hairstyle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    image_url: imageUrl,
                    style: styleOptions.style,
                    color: styleOptions.color,
                    gender: styleOptions.gender
                })
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `API request failed: ${response.status}`);
            }

            const result = await response.json();

            // Handle async processing
            if (result.status === 'processing' && result.request_id) {
                return await this.pollForResult(result.request_id);
            }

            // Handle direct result
            if (result.status === 'completed' && result.images?.length > 0) {
                return {
                    success: true,
                    image_url: result.images[0].url,
                    description: result.description,
                    processing_time: result.processing_time || null,
                    style_applied: styleOptions.style,
                    color_applied: styleOptions.color
                };
            }

            throw new Error('No images returned from transformation');

        } catch (error) {
            console.error('Error transforming hairstyle:', error);
            throw error;
        }
    }

    async uploadImage(imageFile) {
        try {
            const formData = new FormData();
            formData.append('image', imageFile);

            const response = await fetch(`${this.baseUrl}/upload-image`, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to upload image');
            }

            const result = await response.json();
            return result.url;

        } catch (error) {
            console.error('Error uploading image:', error);
            throw error;
        }
    }

    async pollForResult(requestId, maxAttempts = 30, interval = 2000) {
        for (let attempt = 0; attempt < maxAttempts; attempt++) {
            try {
                // Check status
                const statusResponse = await fetch(`${this.baseUrl}/check-status?request_id=${requestId}`);
                if (!statusResponse.ok) {
                    throw new Error('Failed to check status');
                }

                const status = await statusResponse.json();

                if (status.status === 'COMPLETED') {
                    // Get result
                    const resultResponse = await fetch(`${this.baseUrl}/get-result?request_id=${requestId}`);
                    if (!resultResponse.ok) {
                        throw new Error('Failed to get result');
                    }

                    const result = await resultResponse.json();
                    
                    if (result.images?.length > 0) {
                        return {
                            success: true,
                            image_url: result.images[0].url,
                            description: result.description,
                            request_id: requestId
                        };
                    }
                }

                if (status.status === 'FAILED') {
                    throw new Error('Transformation failed');
                }

                // Wait before next attempt
                if (attempt < maxAttempts - 1) {
                    await new Promise(resolve => setTimeout(resolve, interval));
                }

            } catch (error) {
                if (attempt === maxAttempts - 1) {
                    throw error;
                }
                await new Promise(resolve => setTimeout(resolve, interval));
            }
        }

        throw new Error('Transformation timed out');
    }

    // Mock transformation for development (when backend is not available)
    async mockTransformation(imageFile, styleOptions) {
        // Simulate API delay
        await new Promise(resolve => setTimeout(resolve, 3000));
        
        // Return mock result
        return {
            success: true,
            image_url: 'https://via.placeholder.com/512x512/6366f1/ffffff?text=Transformed',
            processing_time: 2.5,
            style_applied: styleOptions.style,
            color_applied: styleOptions.color
        };
    }
}

export const falAI = new FalAI();
