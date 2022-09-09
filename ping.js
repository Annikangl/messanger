const W3CWebSocket = require('websocket').w3cwebsocket;

const client = new W3CWebSocket('ws://78.140.242.71:6001/');

client.onopen = function() {
    console.log('WebSocket Client Connected');

    function sendData() {
        if (client.readyState === client.OPEN) {
            let data = {
                type: 'ping',
            };

            client.send(JSON.stringify(data));
        }
    }
    sendData();
    client.close();
};

client.onerror = function() {
    console.log('Connection Error');
};

client.onclose = function() {
    console.log('Socket Closed');
};
