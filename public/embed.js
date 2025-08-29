(function() {
    // StyleAI Widget Embed Script
    // Usage: <script src="https://your-domain.com/embed.js"></script>
    
    const WIDGET_URL = 'http://localhost:8080/styleai-widget.html';
    
    function createStyleAIWidget(containerId = 'styleai-widget') {
        const container = document.getElementById(containerId);
        if (!container) {
            console.error('StyleAI: Container element not found. Please add <div id="styleai-widget"></div> to your HTML.');
            return;
        }

        // Create iframe for the widget
        const iframe = document.createElement('iframe');
        iframe.src = WIDGET_URL;
        iframe.style.width = '100%';
        iframe.style.height = '600px';
        iframe.style.border = 'none';
        iframe.style.borderRadius = '12px';
        iframe.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)';
        iframe.setAttribute('allowfullscreen', 'true');
        iframe.setAttribute('loading', 'lazy');

        // Clear container and add iframe
        container.innerHTML = '';
        container.appendChild(iframe);

        // Handle responsive resizing
        function handleResize() {
            const containerWidth = container.offsetWidth;
            if (containerWidth < 400) {
                iframe.style.height = '700px'; // More height for mobile
            } else {
                iframe.style.height = '600px';
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize(); // Initial call
    }

    // Auto-initialize if container exists
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => createStyleAIWidget());
    } else {
        createStyleAIWidget();
    }

    // Expose global function for manual initialization
    window.StyleAI = {
        init: createStyleAIWidget,
        version: '1.0.0'
    };
})();
