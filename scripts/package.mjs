import { createHash } from 'node:crypto';
import { execFileSync } from 'node:child_process';
import { readFile, readdir, mkdir, writeFile } from 'node:fs/promises';
import { zipSync } from 'fflate';
import { projects, resolveRoot, root } from './paths.mjs';

const hash = content => createHash('sha256').update(content).digest('hex');
const commit = execFileSync('git', ['rev-parse', 'HEAD'], { cwd: root, encoding: 'utf8' }).trim();
const dirty = Boolean(execFileSync('git', ['status', '--porcelain'], { cwd: root, encoding: 'utf8' }).trim());
if (dirty && !process.argv.includes('--allow-dirty')) {
  throw new Error('O pacote de release exige checkout limpo. --allow-dirty serve apenas para testes locais.');
}

const entries = {};
const checksums = {};
async function include(relative) {
  const bytes = await readFile(resolveRoot(relative));
  checksums[relative] = hash(bytes);
  entries[relative] = [bytes, { mtime: new Date(1980, 0, 1) }];
}
async function runtimeFiles(directory, relative = '') {
  const files = await readdir(resolveRoot(directory, relative), { withFileTypes: true });
  for (const file of files.sort((a, b) => a.name.localeCompare(b.name, 'en'))) {
    if (file.isSymbolicLink()) throw new Error(`Symlink não permitido: ${directory}/${relative}/${file.name}`);
    const child = relative ? `${relative}/${file.name}` : file.name;
    if (file.isDirectory()) {
      if (child === 'assets' || child === 'resources' || /^(inc|includes|template-parts|page-templates|languages|resources\/seed|assets\/(?:logo|icons|fonts))(\/|$)/.test(child)) {
        await runtimeFiles(directory, child);
      }
    } else if (/\.php$/.test(child) || child === 'style.css' || child === 'resources/seed/institutional.json' || /^resources\/seed\/media\/[a-z0-9-]+\.(webp|webm|mp4)$/.test(child) || /^languages\/[^/]+\.(mo|json)$/.test(child) || /^assets\/(logo|icons)\/[a-z0-9-]+\.svg$/.test(child) || /^assets\/fonts\/(inter-[a-z-]+\.woff2|OFL\.txt|sources\.json)$/.test(child)) {
      await include(`${directory}/${child}`);
    }
  }
}

for (const project of projects) {
  await runtimeFiles(project.directory);
  const dist = `${project.directory}/assets/dist`;
  const manifest = JSON.parse(await readFile(resolveRoot(dist, 'manifest.json'), 'utf8'));
  const expected = [...project.css.map(name => `${name}.css`), ...project.js.map(name => `${name}.js`)].sort();
  if (JSON.stringify(Object.keys(manifest).sort()) !== JSON.stringify(expected)) throw new Error(`Manifesto incompleto: ${dist}`);
  await include(`${dist}/manifest.json`);
  for (const filename of Object.values(manifest)) {
    if (!/^[a-z0-9-]+\.[a-f0-9]{12}\.(css|js)$/.test(filename)) throw new Error('Asset inválido');
    const bytes = await readFile(resolveRoot(dist, filename));
    if (hash(bytes).slice(0, 12) !== filename.split('.')[1]) throw new Error(`Hash incorreto: ${filename}`);
    await include(`${dist}/${filename}`);
  }
}

// Atestação empacotada: o endpoint informa o código carregado pelo PHP/FPM.
for(const relative of ['wp-content/themes/iotnest/inc/release.php','wp-content/plugins/iotnest-core/includes/release.php']) {
  const bytes=Buffer.from(`<?php\ndefined('ABSPATH') || exit;\nreturn array('commit' => '${commit}', 'schema' => 1);\n`);
  checksums[relative]=hash(bytes);entries[relative]=[bytes,{mtime:new Date(1980,0,1)}];
}
const metadata = { format: 2, commit, dirty, theme: '0.1.0', plugin: '0.1.0', schema: 1, php:'8.2', wordpress:'7.1', files: checksums };
entries['release.json'] = [Buffer.from(`${JSON.stringify(metadata, null, 2)}\n`), { mtime: new Date(1980, 0, 1) }];
const zip = zipSync(entries, { level: 9 });
const filename = `iotnest-${commit}${dirty ? '-dirty' : ''}.zip`;
await mkdir(resolveRoot('artifacts'), { recursive: true });
await writeFile(resolveRoot('artifacts', filename), zip);
await writeFile(resolveRoot('artifacts', `${filename}.sha256`), `${hash(zip)}  ${filename}\n`);
console.log(`Pacote: artifacts/${filename} (${Object.keys(checksums).length} arquivos, sem core, banco, uploads ou tooling)`);
