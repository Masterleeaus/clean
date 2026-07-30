#!/usr/bin/env node
/**
 * Titan Zero Report Generator
 * Generates comprehensive reports from registry data
 */

import * as fs from 'fs';
import * as path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

interface BranchInfo {
  name: string;
  status: string;
  ahead: number;
  behind: number;
  conflict_risk: string;
}

interface ReportOptions {
  registryPath?: string;
  outputPath?: string;
}

function loadRegistry(registryPath: string): any {
  try {
    const data = fs.readFileSync(registryPath, 'utf-8');
    return JSON.parse(data);
  } catch (err) {
    return null;
  }
}

function generateBranchHealth(branches: BranchInfo[]): string {
  let md = '# Branch Health Report\n\n';
  md += `Generated: ${new Date().toISOString()}\n\n`;

  md += '## Summary\n';
  md += `Total branches: ${branches.length}\n`;
  md += `Ready to merge: ${branches.filter(b => b.status === 'fast_forward').length}\n`;
  md += `Need recovery: ${branches.filter(b => b.status === 'cherry_pick_candidate').length}\n`;
  md += `Already merged: ${branches.filter(b => b.status === 'already_merged').length}\n\n`;

  md += '## Branches by Risk Level\n\n';

  md += '### High Risk (High conflict)\n';
  const highRisk = branches.filter(b => b.conflict_risk === 'high');
  if (highRisk.length === 0) {
    md += 'None\n';
  } else {
    highRisk.forEach(b => {
      md += `- **${b.name}** [${b.status}] - Ahead: ${b.ahead}, Behind: ${b.behind}\n`;
    });
  }

  md += '\n### Medium Risk\n';
  const mediumRisk = branches.filter(b => b.conflict_risk === 'medium');
  if (mediumRisk.length === 0) {
    md += 'None\n';
  } else {
    mediumRisk.forEach(b => {
      md += `- **${b.name}** [${b.status}] - Ahead: ${b.ahead}, Behind: ${b.behind}\n`;
    });
  }

  md += '\n### Low Risk\n';
  const lowRisk = branches.filter(b => b.conflict_risk === 'low');
  if (lowRisk.length === 0) {
    md += 'None\n';
  } else {
    lowRisk.forEach(b => {
      md += `- **${b.name}** [${b.status}] - Ahead: ${b.ahead}, Behind: ${b.behind}\n`;
    });
  }

  return md;
}

function generateSummary(registry: any): string {
  let md = '# Titan Zero Recovery System - Summary Report\n\n';
  md += `Generated: ${new Date().toISOString()}\n\n`;

  if (!registry) {
    md += '⚠️ No registry data available. Run `titan:scan` first.\n';
    return md;
  }

  md += '## Branch Scan Results\n\n';
  md += `- Total branches: ${registry.total_branches}\n`;
  md += `- Scan date: ${registry.scan_date}\n\n`;

  md += '## Categories\n\n';
  md += `| Status | Count |\n`;
  md += `|--------|-------|\n`;
  for (const [status, count] of Object.entries(registry.categories)) {
    md += `| ${status} | ${count} |\n`;
  }

  md += '\n## Next Steps\n\n';
  md += '1. Review branches by risk level\n';
  md += '2. Identify duplicate implementations\n';
  md += '3. Create recovery plans for candidates\n';
  md += '4. Execute recovery and validation\n';
  md += '5. Merge into integration branch\n';

  return md;
}

function writeReport(content: string, fileName: string, outputPath?: string): void {
  const finalPath = outputPath || '.titan/reports';
  if (!fs.existsSync(finalPath)) {
    fs.mkdirSync(finalPath, { recursive: true });
  }

  const filePath = path.join(finalPath, fileName);
  fs.writeFileSync(filePath, content);
  console.log(`✅ Report saved: ${filePath}`);
}

async function main(): Promise<void> {
  const registryPath = '.titan/registry/branches.json';
  console.log('📝 Generating reports...\n');

  // Load registry
  const registry = loadRegistry(registryPath);

  // Generate reports
  const summary = generateSummary(registry);
  writeReport(summary, 'summary.md');

  if (registry) {
    const health = generateBranchHealth(registry.branches);
    writeReport(health, 'branch-health.md');
  }

  console.log('\n✅ All reports generated');
}

main().catch(err => {
  console.error('❌ Report generation failed:', err);
  process.exit(1);
});
