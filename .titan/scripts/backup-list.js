#!/usr/bin/env node
/**
 * List available backups
 */

const fs = require('fs');
const path = require('path');

const backupDir = '.backups';

if (!fs.existsSync(backupDir)) {
  console.log('No backups directory found');
  process.exit(0);
}

const files = fs.readdirSync(backupDir)
  .filter(f => f.includes('backup'))
  .sort()
  .reverse();

if (files.length === 0) {
  console.log('No backups found');
  process.exit(0);
}

console.log('\n📋 Available Backups\n');
console.log('Filename                              | Size    | Created');
console.log('----------------------------------------|---------|--------------------');

files.forEach(file => {
  const filePath = path.join(backupDir, file);
  const stats = fs.statSync(filePath);
  const size = (stats.size / 1024 / 1024).toFixed(2);
  const created = new Date(stats.mtime).toLocaleString();

  console.log(`${file.padEnd(40)} | ${size.padEnd(7)} | ${created}`);
});

console.log('\n💡 To restore: npm run backup:restore -- <filename>\n');
