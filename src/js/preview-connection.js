// preview-connection.js

const socket = new WebSocket('ws://localhost:3001');

socket.onopen = () => {
    // Send keep-alive ping every 5 seconds
    setInterval(() => {
        socket.send('ping');
    }, 5000);
};

socket.onmessage = (event) => {
    if (event.data === 'reload') {
        window.location.reload();
    }
};

socket.onclose = () => {
    console.warn('WebSocket connection closed');
};
