import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: process.env.VUE_APP_REVERB_APP_KEY,
    wsHost: process.env.VUE_APP_REVERB_HOST,
    wsPort: process.env.VUE_APP_REVERB_PORT,
    wssPort: process.env.VUE_APP_REVERB_PORT,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
    authorizer: (channel) => {
        return {
            authorize: (socketId, callback) => {
                const token = localStorage.getItem('token')
                console.log('Broadcasting auth token:', token)
                fetch('http://localhost:8000/broadcasting/auth', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('token')}`
                    },
                    body: JSON.stringify({
                        socket_id: socketId,
                        channel_name: channel.name
                    })
                })
                .then(res => res.json())
                .then(data => callback(null, data))
                .catch(err => callback(err))
            }
        }
    }
})

export default window.Echo