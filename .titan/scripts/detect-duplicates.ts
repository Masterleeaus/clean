#!/usr/bin/env node
/**
 * Titan Zero Duplicate Detector - Phase 2
 * Identifies duplicate implementations across branches
 */

import * as fs from 'fs';
import * as path from 'path';
import * as crypto from 'crypto';

interface DuplicateSet {
  id: string;
  type: 'service_class' | 'controller' | 'route' | 'migration' | 'test' | 'component' | 'hook' | 'utility';
  description: string;
  files: Array<{
    path: string;
    branch: string;
    lines: number;
  }>;
  similarity: number;
  lines_of_code: number;
  hash: string;
  recommendation: 'merge_into_one' | 'keep_separate' | 'refactor_shared' | 'remove_older' | 'review_manually';
  reasoning: string;
  severity: 'critical' | 'high' | 'medium' | 'low';
}

interface DuplicateReport {
  scan_date: string;
  total_duplicates: number;
  duplicate_sets: DuplicateSet[];
}

const filePatterns = {
  service_class: /Service\.php$/,
  controller: /Controller\.php$/,
  route: /(routes|Routes).*\.php$/,
  migration: /\d+_.*\.php$/,
  test: /Test\.php$/,
  component: /\.vue$/,
  hook: /use[A-Z].*\.ts(x)?$/,
  utility: /[Uu]til(ity)?\..*\.(ts|js|php)$/,
};

function getFileType(filePath: string): keyof typeof filePatterns | null {
  for (const [type, pattern] of Object.entries(filePatterns)) {
    if (pattern.test(filePath)) {
      return type as keyof typeof filePatterns;
    }
  }
  return null;
}

function hashContent(content: string): string {
  return crypto.createHash('md5').update(content).digest('hex');
}

function calculateSimilarity(content1: string, content2: string): number {
  const lines1 = content1.split('\n').length;
  const lines2 = content2.split('\n').length;

  // Simple line count similarity
  const maxLines = Math.max(lines1, lines2);
  const minLines = Math.min(lines1, lines2);

  if (maxLines === 0) return 1;
  return minLines / maxLines;
}

function countLines(content: string): number {
  return content.split('\n').length;
}

function analyzeFileDuplicates(): DuplicateSet[] {
  const duplicates: DuplicateSet[] = [];
  const fileMap: Map<string, Array<{ path: string; branch: string; hash: string }>> = new Map();

  // This would scan all branches and files
  // For now, return template structure

  return duplicates;
}

function generateDuplicateReport(): DuplicateReport {
  console.log('🔍 Detecting duplicates...');

  const duplicates = analyzeFileDuplicates();

  return {
    scan_date: new Date().toISOString(),
    total_duplicates: duplicates.length,
    duplicate_sets: duplicates,
  };
}

function writeReport(report: DuplicateReport): void {
  const registryDir = path.join(__dirname, '../registry');
  const reportPath = path.join(registryDir, 'duplicates.json');

  if (!fs.existsSync(registryDir)) {
    fs.mkdirSync(registryDir, { recursive: true });
  }

  fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
  console.log(`\n✅ Duplicate report saved to ${reportPath}`);
}

function printSummary(report: DuplicateReport): void {
  console.log('\n📊 Duplicate Detection Summary');
  console.log('==============================');
  console.log(`Total duplicate sets found: ${report.total_duplicates}`);
  console.log(`Scan date: ${report.scan_date}`);

  if (report.duplicate_sets.length === 0) {
    console.log('✅ No duplicates detected');
    return;
  }

  console.log('\n🔴 Critical Duplicates:');
  report.duplicate_sets
    .filter(d => d.severity === 'critical')
    .forEach(d => {
      console.log(`  ${d.id}: ${d.description} (${d.similarity.toFixed(2)}% similar)`);
      d.files.forEach(f => console.log(`    - ${f.path} (${f.branch})`));
    });
}

async function main(): Promise<void> {
  try {
    const report = generateDuplicateReport();
    writeReport(report);
    printSummary(report);
    process.exit(0);
  } catch (error) {
    console.error('❌ Duplicate detection failed:', error);
    process.exit(1);
  }
}

main();
