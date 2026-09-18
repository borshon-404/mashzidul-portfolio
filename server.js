#!/usr/bin/env node
/**
 * MTB Portfolio — zero-dependency static server.
 * Serves the built site in ./site with clean-URL support and a 404 fallback.
 * Usage: node server.js   (or: npm start)   Port: process.env.PORT || 8080
 */
'use strict';
const http = require('http');
const fs = require('fs');
const path = require('path');

const ROOT = __dirname; // repo root = web root
const PORT = process.env.PORT || 8080;

const TYPES = {
  '.html': 'text/html; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8',
  '.json': 'application/json; charset=utf-8',
  '.svg': 'image/svg+xml',
  '.png': 'image/png',
  '.webp': 'image/webp',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.ico': 'image/x-icon',
  '.woff': 'font/woff',
  '.woff2': 'font/woff2',
  '.xml': 'application/xml; charset=utf-8',
  '.txt': 'text/plain; charset=utf-8',
  '.map': 'application/json'
};

function send(res, code, file, headers) {
  const ext = path.extname(file).toLowerCase();
  const h = Object.assign({
    'Content-Type': TYPES[ext] || 'application/octet-stream',
    'Cache-Control': ext === '.html' ? 'no-cache' : 'public, max-age=86400'
  }, headers || {});
  res.writeHead(code, h);
  fs.createReadStream(file).pipe(res);
}

const server = http.createServer((req, res) => {
  let urlPath;
  try {
    urlPath = decodeURIComponent(new URL(req.url, 'http://localhost').pathname);
  } catch (e) {
    res.writeHead(400); res.end('Bad request'); return;
  }
  let fp = path.normalize(path.join(ROOT, urlPath));
  if (!fp.startsWith(ROOT)) { res.writeHead(403); res.end('Forbidden'); return; }

  fs.stat(fp, (err, st) => {
    if (!err && st.isDirectory()) fp = path.join(fp, 'index.html');
    fs.stat(fp, (err2, st2) => {
      if (!err2 && st2.isFile()) return send(res, 200, fp);
      // clean-URL: /about -> /about/index.html
      const withIndex = path.join(fp, 'index.html');
      fs.stat(withIndex, (err3, st3) => {
        if (!err3 && st3.isFile()) return send(res, 200, withIndex);
        const notFound = path.join(ROOT, '404.html');
        fs.stat(notFound, (err4) => {
          if (err4) { res.writeHead(404); res.end('Not found'); return; }
          send(res, 404, notFound);
        });
      });
    });
  });
});

server.listen(PORT, '0.0.0.0', () => {
  console.log(`MTB Portfolio serving ./site at http://localhost:${PORT}`);
});
