/**
 * Dynamic Lightweight QR Code Generator for Mobile AR Handoff
 * AR Spatial Furniture Catalog & Visualization System
 */
class SpatialQRCode {
    /**
     * Renders a QR code image inside the specified container element.
     * 
     * @param {string} targetUrl The URL to encode into the QR code
     * @param {string} containerId The DOM container element ID
     */
    static render(targetUrl, containerId = 'qr-container') {
        const container = document.getElementById(containerId);
        if (!container) return;

        const encodedUrl = encodeURIComponent(targetUrl);
        // High-resolution crisp QR code endpoint
        const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=180x180&margin=4&data=${encodedUrl}`;

        const img = document.createElement('img');
        img.src = qrApiUrl;
        img.alt = 'Scan with Mobile Camera to launch WebXR AR';
        img.id = 'qr-code-canvas';
        img.className = 'img-fluid shadow-sm border rounded bg-white p-1';
        img.loading = 'lazy';
        img.style.maxWidth = '140px';
        img.style.height = 'auto';

        img.onerror = () => {
            // Offline or network error fallback
            container.innerHTML = `
                <div class="p-2 border rounded bg-light small text-muted">
                    <i class="fa-solid fa-link me-1"></i> Open on mobile: <br>
                    <code class="user-select-all">${window.location.href}</code>
                </div>
            `;
        };

        container.innerHTML = '';
        container.appendChild(img);
    }
}

// Auto-initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    const qrContainer = document.getElementById('qr-container');
    if (qrContainer) {
        SpatialQRCode.render(window.location.href, 'qr-container');
    }
});