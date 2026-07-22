import { createHash } from 'node:crypto';
import { access, mkdir, readFile, rm, stat, writeFile } from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const checkOnly = process.argv.includes('--check');

const generatedAssets = [
  {
    source: 'resources/css/contentpilot.css',
    output: 'public/assets/css/contentpilot.css',
    type: 'project-owned-css',
  },
  {
    source: 'resources/js/contentpilot.js',
    output: 'public/assets/js/contentpilot.js',
    type: 'project-owned-js',
  },
];

const requiredProjectAssets = [
  'public/assets/images/contentpilot-icon.svg',
  'public/assets/images/contentpilot-logo.svg',
  'public/assets/images/contentpilot-logo-light.svg',
  'public/assets/images/avatar-placeholder.svg',
];

const forbiddenPaths = [
  'public/frontend',
  'public/backend',
  'public/assets/css/contentpilot-responsive.css',
  'public/assets/js/contentpilot-responsive.js',
];

const manifestPath = path.join(root, 'public/assets/contentpilot-build-manifest.json');

function digest(contents) {
  return createHash('sha256').update(contents).digest('hex');
}

async function exists(relativePath) {
  try {
    await access(path.join(root, relativePath));
    return true;
  } catch {
    return false;
  }
}

async function readRequired(relativePath) {
  const absolutePath = path.join(root, relativePath);
  const fileStats = await stat(absolutePath);

  if (!fileStats.isFile() || fileStats.size === 0) {
    throw new Error(`Required asset is missing or empty: ${relativePath}`);
  }

  return readFile(absolutePath);
}

async function assertForbiddenPathsAbsent() {
  const present = [];
  for (const relativePath of forbiddenPaths) {
    if (await exists(relativePath)) present.push(relativePath);
  }

  if (present.length > 0) {
    throw new Error(`Commercial or superseded asset paths must be removed: ${present.join(', ')}`);
  }
}

async function expectedManifest() {
  const generated = [];
  for (const asset of generatedAssets) {
    const contents = await readRequired(asset.source);
    generated.push({
      source: asset.source,
      output: asset.output,
      bytes: contents.length,
      sha256: digest(contents),
      ownership: asset.type,
    });
  }

  const staticAssets = [];
  for (const relativePath of requiredProjectAssets) {
    const contents = await readRequired(relativePath);
    staticAssets.push({
      path: relativePath,
      bytes: contents.length,
      sha256: digest(contents),
      ownership: 'project-owned',
    });
  }

  return {
    schema: 2,
    generated,
    staticAssets,
    commercialThemeAssets: false,
    externalFrontendDependencies: [],
  };
}

async function build() {
  for (const legacyPath of forbiddenPaths.slice(2)) {
    await rm(path.join(root, legacyPath), { force: true });
  }

  await assertForbiddenPathsAbsent();

  for (const asset of generatedAssets) {
    const contents = await readRequired(asset.source);
    const outputPath = path.join(root, asset.output);
    await mkdir(path.dirname(outputPath), { recursive: true });
    await writeFile(outputPath, contents);
    console.log(`Built ${asset.output}`);
  }

  const manifest = await expectedManifest();
  await mkdir(path.dirname(manifestPath), { recursive: true });
  await writeFile(manifestPath, `${JSON.stringify(manifest, null, 2)}\n`);
  console.log('Wrote public/assets/contentpilot-build-manifest.json');
}

async function check() {
  const problems = [];

  try {
    await assertForbiddenPathsAbsent();
  } catch (error) {
    problems.push(error.message);
  }

  for (const asset of generatedAssets) {
    const source = await readRequired(asset.source);
    const output = await readRequired(asset.output);
    if (!source.equals(output)) problems.push(`${asset.output} does not match ${asset.source}`);
  }

  const expected = `${JSON.stringify(await expectedManifest(), null, 2)}\n`;
  const actual = await readRequired('public/assets/contentpilot-build-manifest.json');
  if (actual.toString('utf8') !== expected) {
    problems.push('public/assets/contentpilot-build-manifest.json is out of date');
  }

  if (problems.length > 0) {
    for (const problem of problems) console.error(`ERROR: ${problem}`);
    process.exitCode = 1;
    return;
  }

  console.log('Project-owned frontend assets are reproducible and up to date.');
}

try {
  if (checkOnly) await check();
  else await build();
} catch (error) {
  console.error(`Frontend build failed: ${error.message}`);
  process.exitCode = 1;
}
