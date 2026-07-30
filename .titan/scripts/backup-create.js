#!/usr/bin/env node
/**
 * Titan Backup Creator
 * Creates versioned backups of .titan system
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

function createBackup() {
  const timestamp = new Date().toISOString().replace(/[:.]/g, '-').split('T')[0] + '_' +
                   new Date().getHours().toString().padStart(2, '0') +
                   new Date().getMinutes().toString().padStart(2, '0');

  const backupDir = '.backups';
  const backupFile = path.join(backupDir, `titan-backup-${timestamp}.tar.gz`);

  // Create backup directory
  if (!fs.existsSync(backupDir)) {
    fs.mkdirSync(backupDir, { recursive: true });
  }

  console.log(`📦 Creating backup: ${backupFile}`);

  try {
    execSync(`tar -czf "${backupFile}" .titan/`, { stdio: 'inherit' });
    console.log(`✅ Backup created successfully`);

    // Also update .titan-backup copy
    console.log(`📋 Updating .titan-backup copy...`);
    execSync('rm -rf .titan-backup && cp -r .titan .titan-backup', { stdio: 'inherit' });
    console.log(`✅ .titan-backup updated`);

    // Show backup info
    const stats = fs.statSync(backupFile);
    const size = (stats.size / 1024 / 1024).toFixed(2);
    console.log(`\n📊 Backup Statistics:`);
    console.log(`   File: ${backupFile}`);
    console.log(`   Size: ${size} MB`);
    console.log(`   Time: ${new Date().toISOString()}`);

    // List recent backups
    console.log(`\n📋 Recent backups:`);
    execSync(`ls -lh .backups/ | grep titan | tail -5`, { stdio: 'inherit' });

  } catch (error) {
    console.error(`❌ Backup failed:`, error.message);
    process.exit(1);
  }
}

createBackup();
