document.addEventListener('DOMContentLoaded', () => {
    const modelViewer = document.querySelector('#arModel');
    const aiOutput = document.querySelector('#aiOutput');

    // Setup AI Speech Recognition
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
                speakAI('Hi there!');
            }
        }, {
            probabilityThreshold: 0.75
        });

        // Stop listening after 10 seconds
        setTimeout(() => recognizer.stopListening(), 10000);
    }

    // AI Talking Function
    function speakAI(text) {
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'en-US';
        window.speechSynthesis.speak(utterance);
    }

    setupAI();

    // Handling model gestures
    modelViewer.addEventListener('load', () => {
        modelViewer.scale = '1 1 1'; // Default scale
    });

    modelViewer.addEventListener('scene-graph-ready', () => {
        console.log('Model is ready to be interacted with.');
    });

    modelViewer.addEventListener('ar-status', (event) => {
        if (event.detail.status === 'failed') {
            console.log('AR failed to start.');
        }
    });
});
