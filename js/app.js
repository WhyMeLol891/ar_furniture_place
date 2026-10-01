/**
 * 3D Model Viewer and AR Spatial Interaction Controller
 * AR Spatial Furniture Catalog & Visualization System
 */
document.addEventListener('DOMContentLoaded', () => {
    const viewer = document.getElementById('furnitureViewer') || document.querySelector('model-viewer');
    const arNotice = document.getElementById('ar-notice');
    const btnResetCamera = document.getElementById('btnResetCamera');
    const btnToggleRotate = document.getElementById('btnToggleRotate');
    const btnFullscreen = document.getElementById('btnFullscreen');

    if (viewer) {
        // Model Loading Events
        viewer.addEventListener('load', () => {
            console.log('✅ 3D GLB Model successfully loaded and parsed into WebXR scene.');
        });

        viewer.addEventListener('error', (event) => {
            console.error('❌ Error loading 3D model asset:', event);
            if (arNotice) {
                arNotice.className = 'alert alert-danger mt-3 d-flex align-items-center shadow-sm';
                arNotice.innerHTML = `
                    <i class="fa-solid fa-circle-xmark fa-lg me-2"></i>
                    <div><strong>Model Load Error:</strong> Unable to stream the 3D model file. Please check server MIME types or network connection.</div>
                `;
            }
        });

        // AR Session Status Tracking
        viewer.addEventListener('ar-status', (event) => {
            const status = event.detail.status;
            console.log('AR Status change:', status);

            if (status === 'session-started') {
                console.log('AR session initiated in physical room.');
            } else if (status === 'failed') {
                if (arNotice) {
                    arNotice.className = 'alert alert-warning mt-3 d-flex align-items-center shadow-sm';
                    arNotice.innerHTML = `
                        <i class="fa-solid fa-triangle-exclamation fa-lg me-2"></i>
                        <div><strong>AR Session Notice:</strong> AR placement could not be started. Ensure you are on a compatible device (Android with Google Play Services for AR / iOS 12+ Safari).</div>
                    `;
                }
            }
        });

        // 3D Controls: Reset Camera
        if (btnResetCamera) {
            btnResetCamera.addEventListener('click', () => {
                viewer.cameraOrbit = '0deg 75deg 105%';
                viewer.cameraTarget = 'auto auto auto';
                viewer.fieldOfView = 'auto';
            });
        }

        // 3D Controls: Toggle Auto-Rotation
        if (btnToggleRotate) {
            btnToggleRotate.addEventListener('click', () => {
                viewer.autoRotate = !viewer.autoRotate;
                btnToggleRotate.classList.toggle('bg-primary', viewer.autoRotate);
                btnToggleRotate.classList.toggle('text-white', viewer.autoRotate);
            });
        }

        // 3D Controls: Toggle Fullscreen
        if (btnFullscreen) {
            btnFullscreen.addEventListener('click', () => {
                const wrapper = viewer.closest('.model-viewer-wrapper') || viewer;
                if (!document.fullscreenElement) {
                    wrapper.requestFullscreen().catch(err => {
                        console.warn('Fullscreen error:', err);
                    });
                } else {
                    document.exitFullscreen();
                }
            });
        }

        // Auto recover on tab focus
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible' && viewer.dismissPoster) {
                viewer.dismissPoster();
            }
        });
    }
});