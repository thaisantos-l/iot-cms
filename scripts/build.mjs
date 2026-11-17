import { createHash } from 'node:crypto';
import { mkdir, mkdtemp, rename, rm, writeFile } from 'node:fs/promises';
import { watch } from 'node:fs';
import { build } from 'esbuild';
import * as sass from 'sass';
import { projects, resolveRoot } from './paths.mjs';

async function compile() {
  for (const project of projects) {
    const assets = resolveRoot(project.directory, 'assets');
    await mkdir(assets, { recursive: true });
    const temporary = await mkdtemp(`${assets}/.build-`);
    try {
      const manifest = {};
      for (const extension of ['css', 'js']) {
        for (const entry of project[extension]) {
          let content;
          if (extension === 'css') {
            content = sass.compile(`${assets}/src/scss/${entry}.scss`, { style: 'compressed', charset: false }).css;
          } else {
            const result = await build({
              entryPoints: [`${assets}/src/js/${entry}.js`],
              bundle: true, minify: true, write: false, format: 'iife',
              target: ['es2020'], legalComments: 'inline', sourcemap: false,
            });
            content = result.outputFiles[0].text;
          }
          const hash = createHash('sha256').update(content).digest('hex').slice(0, 12);
          const filename = `${entry}.${hash}.${extension}`;
          await writeFile(`${temporary}/${filename}`, content);
          manifest[`${entry}.${extension}`] = filename;
        }
      }
      await writeFile(`${temporary}/manifest.json`, `${JSON.stringify(manifest, null, 2)}\n`);
      await rm(`${assets}/dist`, { recursive: true, force: true });
      await rename(temporary, `${assets}/dist`);
      console.log(`Build: ${project.directory}`);
    } finally {
      await rm(temporary, { recursive: true, force: true });
    }
  }
}

await compile();
if (process.argv.includes('--watch')) {
  let timer;
  let pending = Promise.resolve();
  for (const project of projects) {
    watch(resolveRoot(project.directory, 'assets/src'), { recursive: true }, () => {
      clearTimeout(timer);
      timer = setTimeout(() => {
        pending = pending.then(compile).catch(error => console.error(error.message));
      }, 100);
    });
  }
  console.log('Observando apenas fontes do tema/plugin no CMS.');
}
