import { spawn, execFileSync } from 'node:child_process';
import { randomBytes } from 'node:crypto';
import { mkdtemp, mkdir, cp, readFile, readdir, writeFile, rm } from 'node:fs/promises';
import { createServer } from 'node:net';
import { createServer as createHttpServer } from 'node:http';
import os from 'node:os';
import path from 'node:path';
import { unzipSync } from 'fflate';
import { root, resolveRoot } from './paths.mjs';

const artifact = process.argv[2];
if (!artifact) throw new Error('Uso: npm run test:integration -- artifacts/iotnest-COMMIT.zip');
const php = process.env.PHP_BIN || 'php';
const temporary = await mkdtemp(path.join(os.tmpdir(), 'iotnest-qa-'));
const wp = path.join(temporary, 'wordpress');
const container = `iotnest-qa-${randomBytes(5).toString('hex')}`;
const password = randomBytes(24).toString('hex');
const phpString = value => `'${value.replaceAll('\\', '\\\\').replaceAll("'", "\\'")}'`;
const execute = (command, args, options = {}) => execFileSync(command, args, { cwd: root, stdio: 'pipe', ...options });
const wait = milliseconds => new Promise(resolve => setTimeout(resolve, milliseconds));
let server;
let referenceServer;
let serverLog = '';
let started = false;

try {
  await mkdir(wp);
  const source = process.env.IOTNEST_WP_SOURCE || root;
  try {
    await readFile(path.join(source, 'wp-includes/version.php'));
    for (const directory of ['wp-admin', 'wp-includes']) await cp(path.join(source, directory), path.join(wp, directory), { recursive: true });
    for (const file of await readdir(source)) {
      if (file.endsWith('.php') && !file.startsWith('wp-config')) await cp(path.join(source, file), path.join(wp, file));
    }
  } catch (error) {
    if (error.code !== 'ENOENT') throw error;
    // Checkout limpo não contém core. A versão de QA é explícita e separada do contrato de produção.
    const download = path.join(temporary, 'wordpress.zip');
    execute('curl', ['--fail', '--location', '--silent', '--show-error', 'https://wordpress.org/wordpress-7.1.4.zip', '--output', download]);
    execute('unzip', ['-q', download, '-d', temporary]);
  }
  const files = unzipSync(await readFile(path.resolve(artifact)));
  for (const [name, data] of Object.entries(files)) {
    if (name === 'release.json') continue;
    if (!/^wp-content\/(themes\/iotnest|plugins\/iotnest-core)\//.test(name) || name.split('/').includes('..') || name.includes('\\')) {
      throw new Error(`Arquivo fora do escopo de entrega: ${name}`);
    }
    await mkdir(path.dirname(path.join(wp, name)), { recursive: true });
    await writeFile(path.join(wp, name), data);
  }

  execute('docker', ['run', '--detach', '--rm', '--name', container, '--publish', '127.0.0.1::3306',
    '--env', 'MYSQL_ROOT_PASSWORD', '--env', 'MYSQL_DATABASE=iotnest_qa',
    'mysql@sha256:7dcddc01f13bab2f15cde676d44d01f61fc9f99fe7785e86196dfc07d358ae2b'],
  { env: { ...process.env, MYSQL_ROOT_PASSWORD: password } });
  started = true;
  const binding = execute('docker', ['port', container, '3306/tcp'], { encoding: 'utf8' }).trim();
  const dbPort = binding.split(':').at(-1);
  const socket = createServer();
  await new Promise(resolve => socket.listen(0, '127.0.0.1', resolve));
  const port = socket.address().port;
  await new Promise(resolve => socket.close(resolve));
  const url = `http://127.0.0.1:${port}`;
  const config = {
    DB_NAME: 'iotnest_qa', DB_USER: 'root', DB_PASSWORD: password, DB_HOST: `127.0.0.1:${dbPort}`,
    DB_CHARSET: 'utf8mb4', WP_HOME: url, WP_SITEURL: url,
  };
  await writeFile(path.join(wp, 'wp-config.php'), `<?php\n${Object.entries(config).map(([key, value]) => `define('${key}', ${phpString(value)});`).join('\n')}
define('IOTNEST_TEST_ENV', true);
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', true);
define('WP_DEBUG_LOG', true);
define('DISABLE_WP_CRON', true);
define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_ENVIRONMENT_TYPE', 'local');
$table_prefix = 'iotnest_qa_';
if (!defined('ABSPATH')) { define('ABSPATH', __DIR__ . '/'); }
require_once ABSPATH . 'wp-settings.php';\n`, { mode: 0o600 });
  await writeFile(path.join(wp, '.iotnest-test'), 'Disposable integration environment.\n');
  await mkdir(path.join(wp, 'wp-content/mu-plugins'), { recursive: true });
  await cp(resolveRoot('tests/php/offline-updates.php'), path.join(wp, 'wp-content/mu-plugins/iotnest-qa-updates.php'));
  const env = { ...process.env, PHP_BIN: php, IOTNEST_TEST_WP_ROOT: wp, IOTNEST_TEST_URL: url,
    IOTNEST_TEST_ADMIN_PASSWORD: randomBytes(24).toString('hex'), IOTNEST_TEST_DB_PASSWORD: password, IOTNEST_TEST_DB_PORT: dbPort };
  // A referência é servida diretamente em modo somente leitura; nunca copiada para o release.
  const referenceRoot = path.resolve(root, '../iotnest-site');
  try {
    await readFile(path.join(referenceRoot, 'index.html'));
    referenceServer = createHttpServer(async (request, response) => {
      try {
        const requested = decodeURIComponent(new URL(request.url, 'http://reference.local').pathname);
        const file = path.resolve(referenceRoot, `.${requested === '/' ? '/index.html' : requested}`);
        if (!file.startsWith(`${referenceRoot}${path.sep}`)) throw new Error('Path inválido');
        const content = await readFile(file);
        const types = { '.html': 'text/html', '.css': 'text/css', '.js': 'text/javascript', '.svg': 'image/svg+xml', '.webp': 'image/webp', '.png': 'image/png', '.webm': 'video/webm', '.mp4': 'video/mp4' };
        const headers = { 'Content-Type': types[path.extname(file)] || 'application/octet-stream', 'Accept-Ranges': 'bytes' };
        const range = request.headers.range;
        if (range) {
          const match = /^bytes=(\d*)-(\d*)$/.exec(range);
          const start = match?.[1] ? Number(match[1]) : Math.max(0, content.length - Number(match?.[2] || 0));
          const end = match?.[1] && match[2] ? Math.min(Number(match[2]), content.length - 1) : content.length - 1;
          if (!match || start > end || start >= content.length) {
            response.writeHead(416, { 'Content-Range': `bytes */${content.length}` }); response.end(); return;
          }
          const slice = content.subarray(start, end + 1);
          response.writeHead(206, { ...headers, 'Content-Length': slice.length, 'Content-Range': `bytes ${start}-${end}/${content.length}` });
          response.end(request.method === 'HEAD' ? undefined : slice);
        } else {
          response.writeHead(200, { ...headers, 'Content-Length': content.length });
          response.end(request.method === 'HEAD' ? undefined : content);
        }
      } catch { response.writeHead(404); response.end(); }
    });
    await new Promise(resolve => referenceServer.listen(0, '127.0.0.1', resolve));
    env.IOTNEST_REFERENCE_URL = `http://127.0.0.1:${referenceServer.address().port}`;
  } catch (error) { if (error.code !== 'ENOENT') throw error; }
  let ready = false;
  for (let attempt = 0; attempt < 120; attempt++) {
    try {
      execute(php, ['-r', 'mysqli_report(MYSQLI_REPORT_OFF); $db = @new mysqli("127.0.0.1", "root", getenv("IOTNEST_TEST_DB_PASSWORD"), "iotnest_qa", (int) getenv("IOTNEST_TEST_DB_PORT")); exit($db->connect_errno ? 1 : 0);'], { env });
      ready = true;
      break;
    } catch { await wait(500); }
  }
  if (!ready) throw new Error('MySQL de QA não ficou disponível.');
  for (const script of ['install', 'foundation', 'content', 'navigation', 'import', 'publication']) {
    console.log(execute(php, [`tests/php/${script}.php`], { env, encoding: 'utf8' }).trim());
  }
  server = spawn(php, ['-S', `127.0.0.1:${port}`, '-t', wp, resolveRoot('tests/php/router.php')], { env, stdio: ['ignore', 'pipe', 'pipe'] });
  server.stdout.on('data', data => { serverLog += data; });
  server.stderr.on('data', data => { serverLog += data; });
  for (let attempt = 0; attempt < 80; attempt++) {
    try { await fetch(url); break; } catch { await wait(100); }
  }
  const code = await new Promise((resolve, reject) => {
    const child = spawn(process.execPath, [resolveRoot('node_modules/@playwright/test/cli.js'), 'test', ...process.argv.slice(3)], { cwd: root, env, stdio: 'inherit' });
    child.once('error', reject);
    child.once('exit', resolve);
  });
  if (code !== 0) throw new Error(`Playwright falhou (${code}).`);
  const diagnostics = serverLog.split('\n').filter(line => /PHP (Warning|Notice|Fatal error|Deprecated|Parse error)/.test(line));
  if (diagnostics.length) throw new Error(`PHP emitiu diagnósticos durante os testes HTTP:\n${diagnostics.join('\n')}`);
  try {
    const debug = await readFile(path.join(wp, 'wp-content/debug.log'), 'utf8');
    if (debug.trim()) throw new Error(`WordPress registrou diagnósticos: ${debug.slice(0, 2000)}`);
  } catch (error) { if (error.code !== 'ENOENT') throw error; }
  console.log('T01/T22: artefato instalado sem fontes/Node no destino; PHP/HTTP/navegador sem diagnósticos.');
} finally {
  if (server) {
    server.kill('SIGTERM');
    await new Promise(resolve => server.once('exit', resolve));
  }
  if (referenceServer) {
    referenceServer.closeAllConnections();
    await new Promise(resolve => referenceServer.close(resolve));
  }
  if (started) execute('docker', ['stop', container]);
  await rm(temporary, { recursive: true, force: true });
}
