import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from "@tailwindcss/vite";
import fs from 'fs';
import path from 'path';

function viteLoggerPlugin() {
  const logDir = path.resolve(__dirname, 'logs');
  const logFile = path.join(logDir, `vite-${new Date().toISOString().split('T')[0]}.log`);

  if (!fs.existsSync(logDir)) {
    fs.mkdirSync(logDir, { recursive: true });
  }

  const logStream = fs.createWriteStream(logFile, { flags: 'a' });

  const writeLog = (level: string, message: string) => {
    const timestamp = new Date().toISOString();
    logStream.write(`[${timestamp}] [${level}] ${message}\n`);
  };

  return {
    name: 'vite-logger',
    configResolved(config: any) {
      writeLog('INFO', `Vite server starting in ${config.mode} mode`);
    },
    configureServer(server: any) {
      writeLog('INFO', 'Dev server configured');

      server.middlewares.use((req: any, res: any, next: any) => {
        const start = Date.now();
        res.on('finish', () => {
          const duration = Date.now() - start;
          writeLog('HTTP', `${req.method} ${req.url} - ${res.statusCode} (${duration}ms)`);
        });
        next();
      });
    },
    handleHotUpdate({ file }: any) {
      writeLog('HMR', `Hot module update: ${file}`);
    },
    buildStart() {
      writeLog('BUILD', 'Build started');
    },
    buildEnd() {
      writeLog('BUILD', 'Build completed');
    },
    closeBundle() {
      logStream.end();
    }
  };
}

// https://vite.dev/config/
export default defineConfig({
  plugins: [react(), tailwindcss(), viteLoggerPlugin()],
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8080',
        changeOrigin: true,
        secure: false,
      },
    },
  },
})
