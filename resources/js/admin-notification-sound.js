let notificationAudio = null;
let isUnlocked = false;

function getOrderSoundUrl() {
    return window.__notificationRoutes?.orderSoundUrl ?? '/sounds/order_notification.mp3';
}

function getNotificationAudio() {
    if (!notificationAudio) {
        notificationAudio = new Audio(getOrderSoundUrl());
        notificationAudio.preload = 'auto';
    }

    return notificationAudio;
}

export async function playNewOrderSound() {
    try {
        const audio = getNotificationAudio().cloneNode();
        audio.volume = 1;
        audio.currentTime = 0;
        await audio.play();
    } catch (error) {
        console.warn('Notification sound failed', error);
    }
}

export function unlockNotificationSound() {
    if (isUnlocked) {
        return;
    }

    const audio = getNotificationAudio();
    const previousVolume = audio.volume;
    audio.volume = 0;
    audio.currentTime = 0;

    void audio.play().then(() => {
        audio.pause();
        audio.currentTime = 0;
        audio.volume = previousVolume;
        isUnlocked = true;
    }).catch(() => {
        audio.volume = previousVolume;
    });
}
