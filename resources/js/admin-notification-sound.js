let audioContext = null;

function getAudioContext() {
    if (!audioContext) {
        const Ctx = window.AudioContext || window.webkitAudioContext;
        if (!Ctx) {
            return null;
        }
        audioContext = new Ctx();
    }

    if (audioContext.state === 'suspended') {
        void audioContext.resume();
    }

    return audioContext;
}

function playTone(ctx, frequency, start, duration, volume = 0.12) {
    const oscillator = ctx.createOscillator();
    const gain = ctx.createGain();

    oscillator.type = 'sine';
    oscillator.frequency.value = frequency;
    gain.gain.value = volume;

    oscillator.connect(gain);
    gain.connect(ctx.destination);

    const end = start + duration;
    gain.gain.setValueAtTime(volume, start);
    gain.gain.exponentialRampToValueAtTime(0.001, end);

    oscillator.start(start);
    oscillator.stop(end + 0.02);
}

export function playNewOrderSound() {
    try {
        const ctx = getAudioContext();
        if (!ctx) {
            return;
        }

        const now = ctx.currentTime + 0.02;
        playTone(ctx, 880, now, 0.12);
        playTone(ctx, 1174.66, now + 0.14, 0.16, 0.14);
    } catch (error) {
        console.warn('Notification sound failed', error);
    }
}

export function unlockNotificationSound() {
    const ctx = getAudioContext();
    if (ctx?.state === 'suspended') {
        void ctx.resume();
    }
}
