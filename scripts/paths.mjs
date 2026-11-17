import { fileURLToPath } from 'node:url';
import path from 'node:path';

export const root = fileURLToPath(new URL('../', import.meta.url));
export const projects = [
  { directory: 'wp-content/themes/iotnest', css: ['app', 'editor'], js: ['app', 'home'] },
  { directory: 'wp-content/plugins/iotnest-core', css: ['admin'], js: ['admin'] },
];
export const resolveRoot = (...parts) => path.join(root, ...parts);
