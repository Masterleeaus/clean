#!/usr/bin/env node
/**
 * Titan Backup Restore
 * Restores .titan system from backup
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

function listBackups() {
  const backupDir = '.backups';
  if (!fs.existsSync(backupDir)) {
    console.log('No backups found');
    return [];
  }

  const files = fs.readdirSync(backupDir)
    .filter(f => f.includes('titan-backup'))
    .sort()
    .reverse();

  return files;
}

function restoreBackup(backupFile) {
  if (!backupFile) {
    const backups = listBackups();
    if (backups.length === 0) {
      console.error('❌ No backups available');
      process.exit(1);
    }
    backupFile = backups[0];
    console.log(`Using latest backup: ${backupFile}`);
  }

  const backupPath = path.join('.backups', backupFile);

  if (!fs.existsSync(backupPath)) {
    console.error(`❌ Backup not found: ${backupPath}`);
    process.exit(1);
  }

  console.log(`⚠️  Restoring from: ${backupFile}`);
  console.log(`   This will overwrite current .titan/`);
  console.log(`   Current version will be backed up first\n`);

  try {
    // Backup current state
    console.log(`📦 Backing up current state...`);
    const currentBackup = `.backups/titan-before-restore-${Date.now()}.tar.gz`;
    execSync(`tar -czf "${currentBackup}" .titan/`, { stdio: 'inherit' });
    console.log(`✅ Current state saved to: ${currentBackup}\n`);

    // Remove current .titan
    console.log(`🗑️  Removing current .titan/...`);
    execSync('rm -rf .titan', { stdio: 'inherit' });

    // Restore from backup
    console.log(`📥 Restoring from backup...`);
    execSync(`tar -xzf "${backupPath}"`, { stdio: 'inherit' });
    console.log(`✅ Restore complete\n`);

    // Update .titan-backup
    console.log(`📋 Updating .titan-backup...`);
    execSync('rm -rf .titan-backup && cp -r .titan .titan-backup', { stdio: 'inherit' });

    console.log(`\n✅ Restoration successful!`);
    console.log(`   From: ${backupFile}`);
    console.log(`   Previous backed up to: ${currentBackup}`);

  } catch (error) {
    console.error(`❌ Restore failed:`, error.message);
    process.exit(1);
  }
}

// Main
const backupFile = process.argv[2];
if (!backupFile && process.argv[2] === '--list') {
  console.log('📋 Available backups:');
  listBackups().forEach(f => console.log(`   ${f}`));
  process.exit(0);
}

restoreBackup(backupFile);
