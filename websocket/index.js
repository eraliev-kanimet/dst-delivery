import express from 'express';
import http from 'http';
import { Server } from 'socket.io';
import cors from 'cors';
import dotenv from 'dotenv';

dotenv.config();

const app = express();

app.use(cors());
app.use(express.json());

const server = http.createServer(app);

const io = new Server(server, {
    path: '/connect',
    cors: { origin: '*' },
});

io.on('connection', (socket) => {
    const customer_id = socket.handshake.query.customer_id;

    if (customer_id) {
        const roomName = `customer-${customer_id}`;

        socket.join(roomName);

        socket.on('disconnect', () => {
            socket.leave(roomName);
        });
    }
});

app.post('/', (req, res) => {
    const { command, customer_id, message } = req.body;

    if (command === 'customer') {
        io.emit(`customer-${customer_id}`, message);
    }

    res.end();
});

const HOST = process.env.WEBSOCKET_HOST;
const PORT = process.env.WEBSOCKET_PORT;

server.listen(PORT, HOST, () => {
    console.log('listening on *:' + PORT);
});
