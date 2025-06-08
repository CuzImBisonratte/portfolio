// Module dependencies
const favicons = require('favicons');
const express = require('express');
const WebSocket = require('ws');
const sharp = require('sharp');
const ico = require('sharp-ico');
const path = require('path');
const open = require('open');
const fs = require('fs');

// Config
const CONFIG = require('./config.json');

// Init
fs.existsSync(path.join(__dirname, 'build')) || fs.mkdirSync(path.join(__dirname, 'build'));
fs.existsSync(path.join(__dirname, 'images')) || fs.mkdirSync(path.join(__dirname, 'images'));

// Dev server
const app = express();
app.use(express.static(path.join(__dirname, 'build')));
app.listen(CONFIG.PORT, () => {
    log(`Preview server is running on http://localhost:${CONFIG.PORT}`);
    if (!process.argv.includes('--noopen')) {
        open.default(`http://localhost:${CONFIG.PORT}`);
    }
});
const wss = new WebSocket.Server({ port: 3001 });
function broadcastReload() {
    wss.clients.forEach((client) => {
        if (client.readyState === WebSocket.OPEN) {
            client.send('reload');
        }
    });
}

function log(message, type = 0) {
    const types = [
        '[i]', // info
        '[✓]', // success
        '[⚠]', // warning
        '[⏹]'  // error
    ];
    const colors = [
        '\x1b[34m', // blue
        '\x1b[32m', // green
        '\x1b[33m', // yellow
        '\x1b[31m'  // red
    ];
    const reset = '\x1b[0m';
    const timestamp = new Date().toISOString().split('T')[1].split('.')[0]; // HH:MM:SS format
    console.log(`${colors[type]}${types[type]} ${timestamp} ${message}${reset}`);
}

// Build function
async function build() {
    // Clear build directory
    const buildPath = path.join(__dirname, 'build');
    fs.rmSync(buildPath, { recursive: true, force: true });
    fs.mkdirSync(buildPath, { recursive: true });
    log('Starting build process...');

    // 
    // Base stuff (Not related to pages)
    // 

    // Copy src
    fs.cpSync(path.join(__dirname, 'src'), buildPath, { recursive: true, force: true });

    // Create favicon
    const faviconSrc = path.join(__dirname, 'res/img/logo.png');
    const faviconDest = path.join(buildPath, 'favicon.ico');
    ico.sharpsToIco(
        [
            sharp(faviconSrc)
        ],
        faviconDest,
        {
            sizes: [64, 32, 24],
            // sizes: "default", // equal to [256, 128, 64, 48, 32, 24, 16]
            resizeOptions: {}, // sharp resize optinos
        }
    );
    log('Favicon created successfully', 1);

    // Create logo as png 256x256
    sharp(faviconSrc)
        .resize(256, 256)
        .toFile(path.join(buildPath, 'logo.webp'), (err, info) => {
            if (err) {
                log(`Error creating logo.webp: ${err.message}`, 2);
            } else {
                log(`logo.webp created successfully: ${info.width}x${info.height}`, 1);
            }
        });

    const pages = JSON.parse(fs.readFileSync(path.join(__dirname, 'pages.json'), 'utf8'));
    images = [];
    pages.forEach(page => {
        if (!page.path) return;
        const pagePath = path.join(buildPath, page.path);
        fs.mkdirSync(pagePath, { recursive: true });
        fs.mkdirSync(path.join(pagePath, 'images'), { recursive: true });
        // Create HTML file
        pageFile = fs.readFileSync(path.join(__dirname, 'res/templates/page.html'), 'utf8');
        pageFile = pageFile.replace(/{{title}}/g, page.title || 'Untitled Page');
        pageFile = pageFile.replace(/{{subtitle}}/g, page.subtitle || '');
        pageFile = pageFile.replace(/<!--JS-INJECTION-ZONE-->/g, CONFIG.autoReload ? '<script src="/js/preview-connection.js"></script>' : '');
        // Add images to the list
        clusters = [];
        if (page.displays && Array.isArray(page.displays)) {
            page.displays.forEach(display => {
                if (display.images && Array.isArray(display.images)) {
                    const cluster = display.images.map((image, imageIndex) => {
                        // Generate random 8-char A-Z filename as output name
                        const outputName = Array.from({ length: 8 }, () => String.fromCharCode(65 + Math.floor(Math.random() * 26))).join('');
                        images.push({ path: image, type: display.type.split("")[imageIndex], outputName });
                        return { type: display.type.split("")[imageIndex], source: outputName };
                    });
                    clusters.push(cluster);
                }
            });
        }
        clusterHTML = "";
        for (let i = 0; i < clusters.length; i++) {
            clusterHTML += '<div class="image-cluster" style="grid-template-columns: {{GRID-VALUES}};">';
            grid_values = [];
            // Calculate image width factor
            sumInverseAspectRatio = clusters[i].reduce((sum, image) => {
                if (image.type === 'p') return sum + (2 / 3);
                if (image.type === 'l') return sum + (3 / 2);
                if (image.type === 's') return sum + 1;
                return sum;
            }, 0);
            clusters[i].forEach(image => {
                clusterHTML += `<img src="/${page.path}/images/${image.source}.webp" alt="Image ${image.source}" />`;
                aspectRatio = 1; // Default aspect ratio for square images
                if (image.type === 'p') aspectRatio = 2 / 3; // Portrait
                else if (image.type === 'l') aspectRatio = 3 / 2; // Landscape
                grid_values.push((aspectRatio / sumInverseAspectRatio * 1000).toFixed(3));
            });
            clusterHTML = clusterHTML.replace('{{GRID-VALUES}}', grid_values.join('fr ') + 'fr');
            clusterHTML += '</div>';
        }
        pageFile = pageFile.replace(/{{clusters}}/g, clusterHTML);
        // Write HTML file
        fs.writeFileSync(path.join(pagePath, 'index.html'), pageFile);
        // Success message
        log(`Page ${page.name} built successfully`, 1);
    });

    // Image processing
    log('Processing images...');
    images.forEach(image => {
        // Load image
        sharpImage = sharp(path.join(__dirname, 'images', image.path));
        // Resize image based on type (p = portrait 2:3, l = landscape 3:2, s = square 1:1) - image should be as large as possible
        sharpImage.metadata().then(async meta => {
            let resizeOptions = { fit: 'cover', height: meta.height, width: meta.width };
            switch (image.type) {
                case 'p': // portrait 2:3
                    if (meta.width / meta.height > 2 / 3) resizeOptions.width = Math.round(meta.height * 2 / 3);
                    else resizeOptions.height = Math.round(meta.width * 3 / 2);
                    break;
                case 'l': // landscape 3:2
                    if (meta.width / meta.height < 3 / 2) resizeOptions.width = Math.round(meta.height * 3 / 2);
                    else resizeOptions.height = Math.round(meta.width * 2 / 3);
                    break;
                case 's': // square 1:1
                    if (meta.width > meta.height) resizeOptions.width = resizeOptions.height = meta.height;
                    else resizeOptions.width = resizeOptions.height = meta.width;
                    break;
            }

            // Create watermark logo
            const watermarkSize = Math.round(resizeOptions.height * CONFIG.watermark.size);
            watermark = await sharp(faviconSrc)
                .resize(watermarkSize, watermarkSize)
                .toBuffer();

            // Weirdly the image has to be re-loaded here, otherwise only one input image is used
            sharpImage = sharp(path.join(__dirname, 'images', image.path)).resize(resizeOptions);

            // Overlay the logo
            if (CONFIG.watermark.enabled) sharpImage.composite([{
                input: watermark,
                gravity: CONFIG.watermark.position,
                autoOrient: false,
            }]);

            // Save image as WebP
            sharpImage.toFile(
                path.join(__dirname, 'build', path.dirname(image.path), 'images', image.outputName + '.webp'),
                (err, info) => {
                    if (err) {
                        log(`Error processing image ${image.path}: ${err.message}`, 3);
                    } else {
                        log(`Image ${image.path} processed as ${image.outputName}.webp (${info.width}x${info.height})`, 1);
                    }
                }
            );
        }).catch(err => {
            log(`Error reading metadata for ${image.path}: ${err.message}`, 3);
        });
    });

    // Broadcast reload to all clients
    log('Build completed successfully', 1);
    !CONFIG.autoReload || broadcastReload();
}

function validatePages() {
    log('Validating page configuration...');
    const pages = JSON.parse(fs.readFileSync(path.join(__dirname, 'pages.json'), 'utf8'));
    // Check wheter each page has a 'path' property
    pages.forEach(page => {
        if (!page.path) return [`Page configuration error: 'path' is required for page ${page.name}`, 3];
        if (["css", "js"].includes(page.path)) return [`Page configuration error: 'path' cannot be 'css' or 'js' for page ${page.name}`, 3];
        if (page.path.startsWith('/')) return [`Page configuration error: 'path' cannot start with '/' for page ${page.name}`, 3];
    });
    return true;
}

function validateAndBuild() {
    valid = validatePages();
    if (typeof valid == "object") {
        log(valid[0], valid[1] || 2);
        if (valid[1] == 3) return; // Stop if error is critical
    } else if (typeof valid == "boolean" && valid) {
        log('Page configuration is valid, starting build...', 1);
        build();
    } else {
        log('Unknown error during page validation', 3);
        return;
    }
}

// Watch for changes
fs.watchFile(path.join(__dirname, 'config.json'), validateAndBuild);
fs.watchFile(path.join(__dirname, 'pages.json'), validateAndBuild);
// Initial build
setTimeout(validateAndBuild, 1000);