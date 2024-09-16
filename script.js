// Initialize the joystick
const joystick = nipplejs.create({
  zone: document.getElementById('joystickContainer'),
  mode: 'dynamic',
  position: { left: '50%', top: '50%' },
  color: 'blue',
  size: 100,
  multitouch: true
});

// Get the camera entity for controlling movement
let camera = document.querySelector('a-camera');

// Handle joystick movements
joystick.on('move', (evt, data) => {
  if (data.direction) {
    let direction = data.direction.angle;
    let speed = data.distance / 50; // Adjust movement speed based on joystick distance

    switch (direction) {
      case 'up':
        camera.object3D.position.z -= speed; // Move forward
        break;
      case 'down':
        camera.object3D.position.z += speed; // Move backward
        break;
      case 'left':
        camera.object3D.position.x -= speed; // Move left
        break;
      case 'right':
        camera.object3D.position.x += speed; // Move right
        break;
    }
  }
});

// Handle joystick release (stop movement)
joystick.on('end', () => {
  // Stop movement when joystick is released
});
