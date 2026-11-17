import { execFileSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { readFile, writeFile, mkdir } from 'node:fs/promises';
import path from 'node:path';
import assert from 'node:assert/strict';
import { resolveRoot } from './paths.mjs';

const reference = path.resolve(process.argv[3] || resolveRoot('../iotnest-site'));
const destination = resolveRoot('docs/evidencias/referencia-front.json');
const git = (...args) => execFileSync('git', args, { cwd: reference, encoding: 'utf8' });
const files = git('ls-files', '-z').split('\0').filter(Boolean).sort();
const hashes = {};
for (const file of files) {
  hashes[file] = createHash('sha256').update(await readFile(path.join(reference, file))).digest('hex');
}
const snapshot = { commit: git('rev-parse', 'HEAD').trim(), status: git('status', '--porcelain=v1'), files: hashes };
if (process.argv[2] === 'record') {
  await mkdir(path.dirname(destination), { recursive: true });
  await writeFile(destination, `${JSON.stringify(snapshot, null, 2)}\n`, { flag: 'wx' });
} else if (process.argv[2] === 'check') {
  assert.deepEqual(snapshot, JSON.parse(await readFile(destination, 'utf8')), 'A referência mudou; investigar sem restaurar arquivos do usuário.');
} else {
  throw new Error('Uso: node scripts/reference.mjs record|check [diretório de referência]');
}
console.log(`Referência: ${files.length} arquivos e estado Git conferidos; nenhum arquivo do front escrito.`);
