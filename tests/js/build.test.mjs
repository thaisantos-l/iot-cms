import assert from 'node:assert/strict';
import test from 'node:test';
import { execFileSync } from 'node:child_process';
import { cp, mkdir, mkdtemp, readFile, rm, stat, symlink } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { pathToFileURL } from 'node:url';
import { projects, resolveRoot } from '../../scripts/paths.mjs';

test('build permite leitura pelo servidor web mesmo com umask restritiva', async () => {
  const fixture = await mkdtemp(path.join(tmpdir(), 'iotnest-build-permissions-'));
  try {
    await mkdir(path.join(fixture, 'scripts'));
    for (const file of ['build.mjs', 'paths.mjs']) {
      await cp(resolveRoot('scripts', file), path.join(fixture, 'scripts', file));
    }
    await symlink(resolveRoot('node_modules'), path.join(fixture, 'node_modules'), 'dir');
    for (const project of projects) {
      const assets = path.join(fixture, project.directory, 'assets');
      await mkdir(assets, { recursive: true });
      await cp(resolveRoot(project.directory, 'assets/src'), path.join(assets, 'src'), { recursive: true });
    }
    const script = pathToFileURL(path.join(fixture, 'scripts/build.mjs')).href;
    execFileSync(process.execPath, ['--input-type=module', '-e', `process.umask(0o077); await import(${JSON.stringify(script)});`], { stdio: 'pipe' });
    for (const project of projects) {
      const dist = path.join(fixture, project.directory, 'assets/dist');
      assert.equal((await stat(dist)).mode & 0o777, 0o755, `${project.directory}: diretório acessível ao Apache`);
      const manifest = JSON.parse(await readFile(path.join(dist, 'manifest.json'), 'utf8'));
      for (const file of ['manifest.json', ...Object.values(manifest)]) {
        assert.equal((await stat(path.join(dist, file))).mode & 0o777, 0o644, `${project.directory}/${file}: leitura pública sem escrita pública`);
      }
    }
  } finally {
    await rm(fixture, { recursive: true, force: true });
  }
});
