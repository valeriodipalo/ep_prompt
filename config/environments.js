// Environment Configuration for StyleAI Professional
// Automatically detects environment and sets appropriate URLs

const environments = {
  // Local Development
  development: {
    frontend: 'http://localhost:5174',
    backend: 'http://localhost:8001',
    name: 'Development',
    debug: true
  },
  
  // Development/Staging (Vercel Preview)
  staging: {
    frontend: 'https://laraver-final-ai-headshot-dev.vercel.app',
    backend: 'https://web-staging-xyz.up.railway.app', // You'll create this
    name: 'Staging',
    debug: true
  },
  
  // Production
  production: {
    frontend: 'https://laraver-final-ai-headshot-o6n3.vercel.app',
    backend: 'https://web-production-5e40.up.railway.app',
    name: 'Production',
    debug: false
  }
};

// Auto-detect environment
function getEnvironment() {
  const hostname = window.location.hostname;
  
  if (hostname === 'localhost') {
    return environments.development;
  } else if (hostname.includes('-dev.vercel.app') || hostname.includes('preview')) {
    return environments.staging;
  } else {
    return environments.production;
  }
}

// Export for use in widget
window.StyleAI_Environment = getEnvironment();

console.log('🌍 Environment detected:', window.StyleAI_Environment.name);
console.log('🔗 Backend URL:', window.StyleAI_Environment.backend);
