import { access, readFile, readdir } from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';

const root = process.cwd();
const failures = [];
const forbiddenPaths = ['public/frontend', 'public/backend'];
const forbiddenMarkers = [
  'Author: Softnio',
  'ThemeForest',
  'NioBoard',
  'Isotope PACKAGED',
  'Isotope v3',
  'Nioicon',
];
const scanRoots = ['public', 'resources'];
const textExtensions = new Set(['.css', '.js', '.json', '.md', '.php', '.svg', '.txt', '.xml', '.yml', '.yaml']);

async function exists(relativePath) {
  try {
    await access(path.join(root, relativePath));
    return true;
  } catch {
    return false;
  }
}

async function walk(directory) {
  const entries = await readdir(directory, { withFileTypes: true });
  const files = [];
  for (const entry of entries) {
    const fullPath = path.join(directory, entry.name);
    if (entry.isDirectory()) files.push(...await walk(fullPath));
    else if (entry.isFile()) files.push(fullPath);
  }
  return files;
}

for (const forbiddenPath of forbiddenPaths) {
  if (await exists(forbiddenPath)) failures.push(`${forbiddenPath} must not exist in the public distribution`);
}

for (const relativeRoot of scanRoots) {
  const absoluteRoot = path.join(root, relativeRoot);
  for (const file of await walk(absoluteRoot)) {
    if (!textExtensions.has(path.extname(file).toLowerCase())) continue;
    const contents = await readFile(file, 'utf8');
    for (const marker of forbiddenMarkers) {
      if (contents.includes(marker)) {
        failures.push(`${path.relative(root, file)} contains removed commercial dependency marker: ${marker}`);
      }
    }
  }
}

const localNoticesPath = 'THIRD_PARTY_NOTICES.md';
const publicNoticePath = await exists(localNoticesPath) ? localNoticesPath : 'README.md';
const notices = await readFile(path.join(root, publicNoticePath), 'utf8');
for (const requiredText of [
  'No commercial theme bundle is included',
  'public/assets/css/contentpilot.css',
  'public/assets/js/contentpilot.js',
  'Isotope is not included',
]) {
  if (!notices.includes(requiredText)) failures.push(`${publicNoticePath} is missing public asset notice: ${requiredText}`);
}

const layouts = [
  'resources/views/home/home_master.blade.php',
  'resources/views/home/page/main_master.blade.php',
  'resources/views/auth/layout.blade.php',
  'resources/views/admin/dashboard.blade.php',
  'resources/views/client/client_dashboard.blade.php',
];
for (const layout of layouts) {
  const contents = await readFile(path.join(root, layout), 'utf8');
  if (!contents.includes("asset('assets/css/contentpilot.css")) failures.push(`${layout} does not load project-owned CSS`);
  if (!contents.includes("asset('assets/js/contentpilot.js")) failures.push(`${layout} does not load project-owned JavaScript`);
  if (/frontend\/assets|backend\/assets|bundle\.js|scripts\.js/.test(contents)) {
    failures.push(`${layout} still references removed theme assets`);
  }
}

if (failures.length > 0) {
  console.error(failures.join('\n'));
  process.exit(1);
}

console.log('Public asset audit passed: no Softnio, NioBoard, Nioicon, or Isotope distribution remains.');
