const { minify } = require('terser');
const fs = require('fs');
const path = require('path');
const archiver = require('archiver');

// Configure paths
const sourceDir = './';
const distDir = './dist';
const filesToMinify = [
  'background.js',
  'lib/content.js',
  'lib/popup.js',
  'lib/i18n.js',
  'lib/storage-safety.js',
  'lib/onboarding.js'
];

// Files to copy without minification
const filesToCopy = [
  'lib/tex-mml-chtml.js',
  'lib/content.css',
  'lib/popup.css',
  'popup.html',
  'onboarding.html',
  'manifest.json',
  'assets/logo.png',
  'assets/logo-line.png',
  'assets/settings.svg',
  'assets/website.svg',
  'assets/icons/icon-16.png',
  'assets/icons/icon-48.png',
  'assets/icons/icon-128.png',
  '_locales/en/messages.json',
  '_locales/fr/messages.json',
  '_locales/es/messages.json',
  '_locales/it/messages.json',
  '_locales/ar/messages.json',
  '_locales/ru/messages.json'
];

// Ensure directory exists
function ensureDirectoryExists(dirPath) {
  if (!fs.existsSync(dirPath)) {
    fs.mkdirSync(dirPath, { recursive: true });
  }
}

// Copy a file, creating directories as needed
function copyFile(source, destination) {
  const destDir = path.dirname(destination);
  ensureDirectoryExists(destDir);
  fs.copyFileSync(source, destination);
  console.log(`Copied: ${source} -> ${destination}`);
}

// Process JavaScript files with Terser
async function minifyFiles() {
  for (const file of filesToMinify) {
    const sourcePath = path.join(sourceDir, file);
    const destPath = path.join(distDir, file);
    const destDir = path.dirname(destPath);
    
    console.log(`Processing ${file}...`);
    
    try {
      // Create destination directory if it doesn't exist
      ensureDirectoryExists(destDir);
      
      // Read the source file
      const code = fs.readFileSync(sourcePath, 'utf8');
      
      // Minify the code
      const result = await minify(code, {
        compress: true,
        mangle: true
      });
      
      // Write the minified code
      fs.writeFileSync(destPath, result.code);
      console.log(`✓ Minified ${file} -> ${destPath}`);
    } catch (err) {
      console.error(`Error processing ${file}:`, err);
    }
  }
}

// Copy non-JS files to dist folder
function copyFiles() {
  for (const file of filesToCopy) {
    const sourcePath = path.join(sourceDir, file);
    const destPath = path.join(distDir, file);
    copyFile(sourcePath, destPath);
  }
}

// Clean dist directory
function cleanDist() {
  if (fs.existsSync(distDir)) {
    fs.rmSync(distDir, { recursive: true, force: true });
    console.log(`Cleaned ${distDir} directory`);
  }
  
  ensureDirectoryExists(distDir);
}

// Get version from manifest.json
function getVersionFromManifest() {
  try {
    const manifestPath = path.join(sourceDir, 'manifest.json');
    const manifestContent = fs.readFileSync(manifestPath, 'utf8');
    const manifest = JSON.parse(manifestContent);
    return manifest.version;
  } catch (error) {
    console.error('Error reading manifest.json:', error);
    return 'unknown';
  }
}

// Create zip file with extension version
function createZipFile(version) {
  return new Promise((resolve, reject) => {
    const releasesDir = './releases';
    ensureDirectoryExists(releasesDir);
    
    const zipFileName = `${version}.zip`;
    const zipFilePath = path.join(releasesDir, zipFileName);
    
    // Create a file to stream archive data to
    const output = fs.createWriteStream(zipFilePath);
    const archive = archiver('zip', {
      zlib: { level: 9 } // Compression level
    });
    
    output.on('close', () => {
      console.log(`✓ Created ${zipFileName} (${archive.pointer()} total bytes)`);
      resolve(zipFilePath);
    });
    
    output.on('end', () => {
      console.log('Data has been drained');
    });
    
    archive.on('warning', (err) => {
      if (err.code === 'ENOENT') {
        console.warn('Archive warning:', err);
      } else {
        reject(err);
      }
    });
    
    archive.on('error', (err) => {
      reject(err);
    });
    
    // Pipe archive data to the file
    archive.pipe(output);
    
    // Append files from dist directory
    archive.directory(distDir, false);
    
    // Finalize the archive
    archive.finalize();
  });
}

// Run the build process
async function build() {
  console.log('Starting build process...');
  
  // Get version from manifest
  const version = getVersionFromManifest();
  console.log(`Building extension version: ${version}`);
  
  // Build process
  cleanDist();
  await minifyFiles();
  copyFiles();
  console.log('Build completed successfully!');
  
  // Create zip file
  try {
    console.log('Creating zip file...');
    await createZipFile(version);
    console.log(`✓ Release package created: releases/${version}.zip`);
  } catch (error) {
    console.error('Error creating zip file:', error);
  }
}

build();