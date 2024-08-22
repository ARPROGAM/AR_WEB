// Gesture Handler for Moving, Rotating, and Scaling
AFRAME.registerComponent('gesture-handler', {
    schema: {
        enabled: { default: true }
    },
    init: function () {
        this.initialScale = this.el.getAttribute('scale');
        this.initialRotation = this.el.getAttribute('rotation');

        this.handleGesture = this.handleGesture.bind(this);
        this.el.sceneEl.addEventListener('touchmove', this.handleGesture);
    },
    remove: function () {
        this.el.sceneEl.removeEventListener('touchmove', this.handleGesture);
    },
    handleGesture: function (event) {
        if (event.touches.length === 2) {
            // Scaling Gesture
            let scale = this.el.getAttribute('scale');
            let touch1 = event.touches[0];
            let touch2 = event.touches[1];
            let dist = Math.sqrt(
                Math.pow(touch2.clientX - touch1.clientX, 2) +
                Math.pow(touch2.clientY - touch1.clientY, 2)
            );
            let newScale = {
                x: this.initialScale.x * (dist / 100),
                y: this.initialScale.y * (dist / 100),
                z: this.initialScale.z * (dist / 100)
            };
            this.el.setAttribute('scale', newScale);
        } else if (event.touches.length === 1) {
            // Rotating Gesture
            let rotation = this.el.getAttribute('rotation');
            rotation.y += event.touches[0].movementX * 0.1;
            this.el.setAttribute('rotation', rotation);
        }
    }
});

// Simple AI Interaction
document.getElementById('ai-text').addEventListener('click', () => {
    // Example interaction with AI
    let aiText = document.getElementById('ai-text');
    aiText.innerText = "Ask me anything!";
    
    // AI logic (replace with real AI service)
    setTimeout(() => {
        aiText.innerText = "Just kidding! I'm still learning.";
    }, 2000);
});
