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
PREVIEWMODE = process.argv.includes('--preview') || CONFIG.preview.enabled;

// Init
fs.existsSync(path.join(__dirname, 'build')) || fs.mkdirSync(path.join(__dirname, 'build'));
fs.existsSync(path.join(__dirname, 'images')) || fs.mkdirSync(path.join(__dirname, 'images'));

// Dev server
if (PREVIEWMODE) {
    const app = express();
    app.use(express.static(path.join(__dirname, 'build')));
    app.listen(CONFIG.PORT || 8080, () => {
        log(`Preview server is running on http://localhost:${CONFIG.PORT || '8080'}`);
        if (!process.argv.includes('--noopen') && !CONFIG.noOpen)
            open.default(`http://localhost:${CONFIG.PORT || '8080'}`);
    });
    const wss = new WebSocket.Server({ port: 3001 });
    function broadcastReload() {
        log('Broadcasting reload to all connected clients');
        wss.clients.forEach((client) => {
            if (client.readyState === WebSocket.OPEN) {
                client.send('reload');
            }
        });
    }
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
    if (type === 3) process.exit(1); // Exit on error
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
        pageFile = pageFile.replace(/{{footer}}/g, CONFIG.metadata.footer.replace(/{{year}}/g, new Date().getFullYear()) || '');
        pageFile = pageFile.replace(/<!--JS-INJECTION-ZONE-->/g, PREVIEWMODE ? '<script src="/js/preview-connection.js"></script>' : '');
        pageFile = pageFile.replace(/<!--NAV-LEFT-INJECTION-ZONE-->/g, CONFIG.nav.left.join(" "));
        pageFile = pageFile.replace(/<!--NAV-RIGHT-INJECTION-ZONE-->/g, CONFIG.nav.right.join(" "));
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
                const ratio = CONFIG.imageRatios[image.type];
                if (ratio && Array.isArray(ratio) && ratio.length === 2) {
                    return sum + (ratio[0] / ratio[1]);
                } else log(`ERROR: No image ratio configured for type '${image.type}'`, 3);
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
        log(`Page ${page.path} built successfully`, 1);
    });
    // Broadcast reload to all clients
    log('Build of pages completed');

    // Build home page
    log('Building home page...');
    const homePath = path.join(buildPath, 'index.html');
    homeHtml = fs.readFileSync(path.join(__dirname, 'res/templates/home.html'), 'utf8');
    homeHtml = homeHtml.replace(/{{title}}/g, CONFIG.metadata.title || 'Home');
    homeHtml = homeHtml.replace(/{{footer}}/g, CONFIG.metadata.footer.replace(/{{year}}/g, new Date().getFullYear()) || '');
    homeHtml = homeHtml.replace(/<!--NAV-LEFT-INJECTION-ZONE-->/g, CONFIG.nav.left.join(" "));
    homeHtml = homeHtml.replace(/<!--NAV-RIGHT-INJECTION-ZONE-->/g, CONFIG.nav.right.join(" "));
    homeHtml = homeHtml.replace(/<!--JS-INJECTION-ZONE-->/g, PREVIEWMODE ? '<script src="/js/preview-connection.js"></script>' : '');
    pageElements = "";
    pages.forEach(page => {
        if (!page.path) return;
        pageElement = fs.readFileSync(path.join(__dirname, 'res/templates/home_page.html'), 'utf8');
        pageElement = pageElement.replace(/{{title}}/g, page.title || 'Untitled Page');
        pageElement = pageElement.replace(/{{subtitle}}/g, page.subtitle || '');
        pageElement = pageElement.replace(/{{path}}/g, page.path);
        pageElements += pageElement;
    });
    homeHtml = homeHtml.replace(/{{pages}}/g, pageElements);
    fs.writeFileSync(homePath, homeHtml);
    log('Home page built successfully');

    // Image processing
    log('Processing images...');
    imgProcessingSuccessTracker = [];
    images.forEach(image => {
        // Load image
        sharpImage = sharp(path.join(__dirname, 'images', image.path));
        // Resize image based on type (TYPES FROM CONFIG)
        sharpImage.metadata().then(async meta => {
            let resizeOptions = { fit: 'cover', height: meta.height, width: meta.width };
            // Use configurable image ratios from CONFIG
            const ratio = CONFIG.imageRatios[image.type];
            if (!ratio) log(`ERROR: No image ratio configured for type '${image.type}'`, 3);
            const [wRatio, hRatio] = ratio;
            const imgRatio = meta.width / meta.height;
            const targetRatio = wRatio / hRatio;

            if (imgRatio > targetRatio) {
                // Image is too wide, limit width
                resizeOptions.width = Math.round(meta.height * targetRatio);
            } else {
                // Image is too tall, limit height
                resizeOptions.height = Math.round(meta.width / targetRatio);
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
                        imgProcessingSuccessTracker.push(image);

                        // Check if all images are processed
                        if (imgProcessingSuccessTracker.length === images.length) {
                            log(`All ${imgProcessingSuccessTracker.length} images processed`);
                            if (imgProcessingSuccessTracker.length === images.length) // Check if all images are processed
                                if (thumbnailProcessingSuccessTracker.length === pages.length) // Check if all thumbnails are processed
                                    PREVIEWMODE && setTimeout(broadcastReload, 100); // Delay to ensure all files are written
                        }
                    }
                }
            );
        }).catch(err => {
            log(`Error reading metadata for ${image.path}: ${err.message}`, 3);
        });
    });

    // Thumbnail generation
    log('Generating thumbnails...');
    thumbnailProcessingSuccessTracker = [];
    pages.forEach(async page => {
        if (!page.path) return;
        const pagePath = path.join(buildPath, page.path);
        const thumbnailSrc = path.join(__dirname, 'images', page.thumbnail);
        const thumbnailDest = path.join(pagePath, 'thumb.webp');
        watermark = await sharp(faviconSrc)
            .resize(90, 90)
            .toBuffer();
        sharpImage = sharp(thumbnailSrc)
            .resize({ width: 1200, height: 900, fit: 'cover' });
        if (CONFIG.watermark.thumbnails) sharpImage.composite([{
            input: watermark,
            gravity: CONFIG.watermark.position,
            autoOrient: false,
        }]);
        sharpImage.toFile(thumbnailDest, (err, info) => {
            if (err) log(`Error creating thumbnail for ${page.name}: ${err.message}`, 3);
            else {
                log(`Thumbnail for ${page.path} created successfully: ${info.width}x${info.height}`, 1);

                thumbnailProcessingSuccessTracker.push(page);
                // Check if all thumbnails are processed
                if (thumbnailProcessingSuccessTracker.length === pages.length) {
                    log(`All ${thumbnailProcessingSuccessTracker.length} thumbnails processed`);
                    if (imgProcessingSuccessTracker.length === images.length) // Check if all images are processed
                        if (thumbnailProcessingSuccessTracker.length === pages.length) // Check if all thumbnails are processed
                            PREVIEWMODE && setTimeout(broadcastReload, 100); // Delay to ensure all files are written
                }
            }
        });
    });
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

if (PREVIEWMODE) {
    // Watch for changes
    fs.watchFile(path.join(__dirname, 'pages.json'), validateAndBuild);
    // Initial build
    setTimeout(validateAndBuild, 1000);
} else {
    validateAndBuild();
}