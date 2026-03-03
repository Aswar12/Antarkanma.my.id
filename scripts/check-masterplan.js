#!/usr/bin/env node

/**
 * Check if MASTERPLAN.md has been updated when code files are modified
 * Run this before commit to ensure documentation is up-to-date
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

console.log('🔍 Checking MASTERPLAN.md update...\n');

try {
  // Get staged files
  const stagedFiles = execSync('git diff --cached --name-only', { encoding: 'utf8' })
    .split('\n')
    .filter(file => file.trim() !== '');

  // Filter out non-code files
  const codeFiles = stagedFiles.filter(file => {
    return !file.includes('MASTERPLAN.md') &&
           !file.includes('.agents/workflows/update-masterplan.md') &&
           !file.includes('.gitignore') &&
           !file.includes('package.json');
  });

  if (codeFiles.length === 0) {
    console.log('✅ No code changes detected. Skipping MASTERPLAN.md check.\n');
    process.exit(0);
  }

  // Check if MASTERPLAN.md is staged
  const masterplanUpdated = stagedFiles.some(file => file.includes('MASTERPLAN.md'));

  if (!masterplanUpdated) {
    console.error('❌ ERROR: Code changes detected but MASTERPLAN.md not updated!\n');
    console.log('Modified files:');
    codeFiles.forEach(file => console.log(`  - ${file}`));
    console.log('\n📝 Please update MASTERPLAN.md with:');
    console.log('  1. Add new tasks to "## ✅ SELESAI" section');
    console.log('  2. Update project status percentage');
    console.log('  3. Update "Last Updated" date');
    console.log('\n💡 Then run: git add MASTERPLAN.md && git commit\n');
    console.log('⚠️  To bypass (not recommended): git commit --no-verify\n');
    process.exit(1);
  }

  console.log('✅ MASTERPLAN.md is updated. Good job!\n');
  console.log('Modified files:');
  codeFiles.forEach(file => console.log(`  - ${file}`));
  console.log('\n✅ Ready to commit!\n');
  process.exit(0);

} catch (error) {
  console.error('❌ Error checking MASTERPLAN.md:', error.message);
  process.exit(1);
}
