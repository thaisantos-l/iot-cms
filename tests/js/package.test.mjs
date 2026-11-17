import assert from 'node:assert/strict';
import test from 'node:test';
import { execFileSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { readFileSync, writeFileSync } from 'node:fs';
import { unzipSync } from 'fflate';
import { root, resolveRoot } from '../../scripts/paths.mjs';

const runPackage = () => execFileSync(process.execPath, ['scripts/package.mjs', '--allow-dirty'], { cwd: root, encoding: 'utf8', stdio: 'pipe' });

test('pacote determinístico contém somente código próprio e assets verificáveis', () => {
  const output = runPackage();
  const filename = output.match(/Pacote: (artifacts\/[^ ]+)/)[1];
  const first = readFileSync(resolveRoot(filename));
  runPackage();
  assert.deepEqual(readFileSync(resolveRoot(filename)), first);
  const files = unzipSync(first);
  const release = JSON.parse(Buffer.from(files['release.json']).toString());
  for (const [name, data] of Object.entries(files)) {
    if (name === 'release.json') continue;
    assert.match(name, /^wp-content\/(themes\/iotnest|plugins\/iotnest-core)\//);
    assert.doesNotMatch(name, /\/src\/|node_modules|vendor\/|wp-config|\.env|uploads\/|\.sql$/);
    assert.equal(createHash('sha256').update(data).digest('hex'), release.files[name]);
  }
  assert.ok(files['wp-content/themes/iotnest/style.css']);
  assert.ok(files['wp-content/plugins/iotnest-core/iotnest-core.php']);
});

test('empacotamento rejeita manifesto que aponta para fora do build', () => {
  const location = resolveRoot('wp-content/themes/iotnest/assets/dist/manifest.json');
  const original = readFileSync(location);
  try {
    const manifest = JSON.parse(original);
    manifest['app.css'] = '../../wp-config.php';
    writeFileSync(location, JSON.stringify(manifest));
    assert.throws(runPackage, /Asset inválido/);
  } finally {
    writeFileSync(location, original);
  }
});

test('git continua ignorando core, credenciais, banco, uploads e dependências', () => {
  const paths = ['wp-config.php', 'wp-settings.php', 'wp-admin/index.php', 'wp-includes/version.php',
    'wp-content/uploads/example.jpg', 'wp-content/plugins/iotnest-core/.env', '.env',
    'wp-content/themes/iotnest/database.sql', 'node_modules/x', 'vendor/x', 'artifacts/x',
    'wp-content/themes/iotnest/assets/dist/manifest.json'];
  const ignored = execFileSync('git', ['check-ignore', '--stdin'], { cwd: root, input: paths.join('\n'), encoding: 'utf8' }).trim().split('\n');
  assert.deepEqual(ignored, paths);
});
