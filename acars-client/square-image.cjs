const Jimp = require('jimp');
const path = require('path');
const fs = require('fs');

async function makeSquare(inputPath, outputPath) {
    try {
        const image = await Jimp.read(inputPath);
        const width = image.bitmap.width;
        const height = image.bitmap.height;
        const size = Math.max(width, height);
        
        console.log(`Original size: ${width}x${height}`);
        console.log(`New size: ${size}x${size}`);

        const square = new Jimp(size, size, 0x00000000); // transparent
        const x = (size - width) / 2;
        const y = (size - height) / 2;

        square.composite(image, x, y);
        await square.writeAsync(outputPath);
        console.log(`Saved square image to ${outputPath}`);
    } catch (err) {
        console.error('Error processing image:', err);
        process.exit(1);
    }
}

const input = 'C:\\Users\\noxxr\\Desktop\\FlyAway-VAM\\logo_ico.png';
const output = 'C:\\Users\\noxxr\\Desktop\\FlyAway-VAM\\logo_ico_square.png';

makeSquare(input, output);
