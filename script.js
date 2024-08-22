document.addEventListener('DOMContentLoaded', () => {
    const modelViewer = document.querySelector('#arModel');
    const stayBtn = document.querySelector('#stayBtn');
    const scaleBtn = document.querySelector('#scaleBtn');
    const aiOutput = document.querySelector('#aiOutput');

    // Stay 3D Model Button
    stayBtn.addEventListener('click', () => {
        modelViewer.cameraOrbit = '0deg 90deg auto';
    });

    // Scale/Resize 3D Model Button
    scaleBtn.addEventListener('click', () => {
        if (modelViewer.scale === '1 1 1') {
            modelViewer.scale = '2 2 2';
        } else {
            modelViewer.scale = '1 1 1';
        }
    });

    // Speech recognition and AI response
    async function setupAI() {
        const recognizer = speechCommands.create('BROWSER_FFT');
        await recognizer.ensureModelLoaded();
        
        const labels = recognizer.wordLabels(); // Get the word labels
        recognizer.listen(result => {
            const scores = result.scores; // Probability of each word
            const highestScore = Math.max(...scores);
            const word = labels[scores.indexOf(highestScore)];
            aiOutput.innerText = `You said: ${word}`;

            // Add AI response logic here
            if (word === 'hello') {
                aiOutput.innerText += '\nAI: Hi there!';
            }
        }, {
            probabilityThreshold: 0.75
        });

        // Stop listening after 10 seconds
        setTimeout(() => recognizer.stopListening(), 10000);
    }

    setupAI();
});
