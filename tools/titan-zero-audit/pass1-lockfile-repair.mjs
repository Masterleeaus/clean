import fs from 'node:fs';

const lockPath = new URL('../../package-lock.json', import.meta.url);
const lock = JSON.parse(fs.readFileSync(lockPath, 'utf8'));

const rootDependencies = lock.packages?.['']?.dependencies;
if (rootDependencies && Object.prototype.hasOwnProperty.call(rootDependencies, 'rt-client')) {
  delete rootDependencies['rt-client'];
}

if (lock.packages && Object.prototype.hasOwnProperty.call(lock.packages, 'node_modules/rt-client')) {
  delete lock.packages['node_modules/rt-client'];
}

fs.writeFileSync(lockPath, `${JSON.stringify(lock, null, '\t')}\n`);
