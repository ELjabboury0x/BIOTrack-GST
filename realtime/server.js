const http = require('http');
const { WebSocketServer } = require('ws');

const host = process.env.REALTIME_HOST || '0.0.0.0';
const port = Number(process.env.REALTIME_PORT || 6001);
const secret = process.env.REALTIME_SECRET || 'gmao-realtime-secret';

const clients = new Set();

const server = http.createServer((req, res) => {
  if (req.method === 'GET' && req.url === '/health') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ ok: true, clients: clients.size }));
    return;
  }

  if (req.method === 'POST' && req.url === '/broadcast') {
    let body = '';
    req.on('data', chunk => {
      body += chunk.toString();
    });

    req.on('end', () => {
      try {
        const payload = JSON.parse(body || '{}');

        if ((payload.token || '') !== secret) {
          res.writeHead(403, { 'Content-Type': 'application/json' });
          res.end(JSON.stringify({ ok: false, message: 'Invalid token' }));
          return;
        }

        const message = JSON.stringify({
          channel: payload.channel || 'dashboard.metrics',
          payload: payload.payload || {},
        });

        clients.forEach(client => {
          if (client.readyState === 1) {
            client.send(message);
          }
        });

        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ ok: true, delivered: clients.size }));
      } catch (error) {
        res.writeHead(400, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ ok: false, message: 'Invalid JSON payload' }));
      }
    });
    return;
  }

  res.writeHead(404, { 'Content-Type': 'application/json' });
  res.end(JSON.stringify({ ok: false, message: 'Not found' }));
});

const wss = new WebSocketServer({ server, path: '/ws' });

wss.on('connection', ws => {
  clients.add(ws);

  ws.send(JSON.stringify({
    channel: 'system',
    payload: {
      message: 'connected',
      clients: clients.size,
      ts: new Date().toISOString(),
    },
  }));

  ws.on('close', () => {
    clients.delete(ws);
  });

  ws.on('error', () => {
    clients.delete(ws);
  });
});

server.listen(port, host, () => {
  console.log(`Realtime WebSocket server running on ws://${host}:${port}/ws`);
  console.log(`Broadcast endpoint: http://${host}:${port}/broadcast`);
});
