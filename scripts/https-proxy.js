/**
 * Lightweight HTTPS reverse proxy for local development.
 *
 * Terminates TLS with the self-signed certificate generated in
 * storage/certs/ and forwards every request to the PHP built-in
 * server running on HTTP.
 *
 * Environment variables (set by start-full-stack.ps1):
 *   HTTPS_PROXY_PORT  – listening port (default 8443)
 *   HTTPS_TARGET_PORT – PHP server port (default 8001)
 *   HTTPS_CERT        – path to server.crt
 *   HTTPS_KEY         – path to server.key
 */

const https = require('https');
const http  = require('http');
const fs    = require('fs');
const path  = require('path');

const proxyPort  = parseInt(process.env.HTTPS_PROXY_PORT  || '8443', 10);
const targetPort = parseInt(process.env.HTTPS_TARGET_PORT || '8001', 10);

const certPath = process.env.HTTPS_CERT || path.join(__dirname, '..', 'storage', 'certs', 'server.crt');
const keyPath  = process.env.HTTPS_KEY  || path.join(__dirname, '..', 'storage', 'certs', 'server.key');

if (!fs.existsSync(certPath) || !fs.existsSync(keyPath)) {
    console.error('[https-proxy] Certificate files not found. Run the cert generation first.');
    process.exit(1);
}

const server = https.createServer(
    {
        cert: fs.readFileSync(certPath),
        key:  fs.readFileSync(keyPath),
    },
    (clientReq, clientRes) => {
        const options = {
            hostname: '127.0.0.1',
            port:     targetPort,
            path:     clientReq.url,
            method:   clientReq.method,
            headers:  {
                ...clientReq.headers,
                'x-forwarded-proto': 'https',
                'x-forwarded-for':  clientReq.socket.remoteAddress,
            },
        };

        // Remove the host header so the PHP server sees the correct one
        delete options.headers['host'];

        const proxy = http.request(options, (proxyRes) => {
            clientRes.writeHead(proxyRes.statusCode, proxyRes.headers);
            proxyRes.pipe(clientRes, { end: true });
        });

        proxy.on('error', (err) => {
            console.error('[https-proxy] Upstream error:', err.message);
            if (!clientRes.headersSent) {
                clientRes.writeHead(502, { 'Content-Type': 'text/plain' });
            }
            clientRes.end('Bad Gateway – PHP server unreachable');
        });

        clientReq.pipe(proxy, { end: true });
    }
);

server.listen(proxyPort, '0.0.0.0', () => {
    console.log(`[https-proxy] HTTPS listening on https://0.0.0.0:${proxyPort} → http://127.0.0.1:${targetPort}`);
});
