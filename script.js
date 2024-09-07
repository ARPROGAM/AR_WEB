document.getElementById('enter-ar').addEventListener('click', () => {
    if ('xr' in navigator) {
        navigator.xr.requestSession('immersive-ar').then((session) => {
            // Handle AR session
            console.log('AR session started');
        }).catch((err) => {
            console.error('Failed to start AR session', err);
        });
    } else {
        alert('WebXR not supported in this browser.');
    }
});
