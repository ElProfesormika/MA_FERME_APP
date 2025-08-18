<?php
// Dynamic PNG icon generator for PWA icons
// Usage: /icons/icon.php?size=144

$size = isset($_GET['size']) ? (int)$_GET['size'] : 192;
$size = max(48, min(512, $size));

// Colors
$bgColor = [118, 75, 162];   // #764ba2
$fgColor = [255, 255, 255];  // white

// Create image
$img = imagecreatetruecolor($size, $size);
imagesavealpha($img, true);
$transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
imagefill($img, 0, 0, $transparent);

// Draw rounded rectangle background
$bg = imagecolorallocate($img, $bgColor[0], $bgColor[1], $bgColor[2]);
$radius = (int)round($size * 0.2);

// Helper to draw filled rounded rect
function filledRoundedRect($image, $x1, $y1, $x2, $y2, $radius, $color)
{
    imagefilledrectangle($image, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
    imagefilledrectangle($image, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);
    imagefilledellipse($image, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($image, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($image, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($image, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
}

filledRoundedRect($img, 0, 0, $size - 1, $size - 1, $radius, $bg);

// Draw letters FE (Ferme Élevage)
$text = 'FE';
$white = imagecolorallocate($img, $fgColor[0], $fgColor[1], $fgColor[2]);

// Use built-in font for portability
$font = 5; // largest built-in
$textWidth = imagefontwidth($font) * strlen($text);
$textHeight = imagefontheight($font);

// Scale up by writing multiple times (poor-man bold/scale)
$scale = max(1, (int)round($size / 64));
$x = (int)round(($size - $textWidth * $scale) / 2);
$y = (int)round(($size - $textHeight * $scale) / 2);

for ($dy = 0; $dy < $scale; $dy++) {
    for ($dx = 0; $dx < $scale; $dx++) {
        imagestring($img, $font, $x + $dx * $textWidth, $y + $dy * $textHeight, $text, $white);
    }
}

header('Content-Type: image/png');
imagepng($img);
imagedestroy($img);
?>


