import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import axios from 'axios';

window.Pusher = Pusher;

export function createAdminEcho(config) {
    if (!config?.enabled || !config?.key) {
        return null;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    try {
        return new Echo({
            broadcaster: 'pusher',
            key: config.key,
            cluster: config.cluster || 'mt1',
            forceTLS: true,
            authorizer: (channel) => ({
                authorize: (socketId, callback) => {
                    axios
                        .post(
                            '/broadcasting/auth',
                            {
                                socket_id: socketId,
                                channel_name: channel.name,
                            },
                            {
                                withCredentials: true,
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    Accept: 'application/json',
                                },
                            }
                        )
                        .then((response) => callback(null, response.data))
                        .catch((error) => {
                            console.warn('Broadcast channel auth failed', error?.response?.status, channel.name);
                            callback(error, null);
                        });
                },
            }),
        });
    } catch (error) {
        console.warn('Echo init failed', error);

        return null;
    }
}
