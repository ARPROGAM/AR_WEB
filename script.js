// Scaling Functionality
let model = document.querySelector('a-entity');

document.getElementById('scaleUp').addEventListener('click', () => {
    let scale = model.getAttribute('scale');
    model.setAttribute('scale', {
        x: scale.x + 0.1,
        y: scale.y + 0.1,
        z: scale.z + 0.1
    });
});

document.getElementById('scaleDown').addEventListener('click', () => {
    let scale = model.getAttribute('scale');
    if (scale.x > 0.1 && scale.y > 0.1 && scale.z > 0.1) {
        model.setAttribute('scale', {
            x: scale.x - 0.1,
            y: scale.y - 0.1,
            z: scale.z - 0.1
        });
    }
});

// Gesture Handler for Moving and Rotating
AFRAME.registerComponent('gesture-handler', {
    schema: {
        enabled: { default: true }
    },
    init: function () {
        this.handleScale = this.handleScale.bind(this);
        this.handleRotate = this.handleRotate.bind(this);

        this.el.sceneEl.addEventListener('touchstart', this.handleScale);
        this.el.sceneEl.addEventListener('touchmove', this.handleRotate);
    },
    remove: function () {
        this.el.sceneEl.removeEventListener('touchstart', this.handleScale);
        this.el.sceneEl.removeEventListener('touchmove', this.handleRotate);
    },
    handleScale: function (event) {
        if (event.touches.length === 2) {
            let scale = this.el.getAttribute('scale');
            this.el.setAttribute('scale', {
                x: scale.x * 1.05,
                y: scale.y * 1.05,
                z: scale.z * 1.05
            });
        }
    },
    handleRotate: function (event) {
        if (event.touches.length === 1) {
            let rotation = this.el.getAttribute('rotation');
            rotation.y += event.touches[0].movementX;
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
