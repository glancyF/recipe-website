<?php
/**
 * Resize and center-crop an image to exact target dimensions.
 *
 * Given a source image, this function:
 * 1) Proportionally resizes it so that the shorter side fits the target box
 *    (cover strategy), then
 * 2) Center-crops the resized image to exactly `$tw × $th`, and
 * 3) Saves the output as JPEG (quality 85) or PNG (compression 6) based on the source type.
 *
 * Supported input types: JPEG, PNG.
 * Alpha transparency for PNG is preserved.
 *
 * @package App\Utils\Images
 * @license MIT
 * @author  Valentyn Deshel
 *
 * @param string $src Absolute or relative path to the source image.
 * @param string $dest Destination file path for the resulting image.
 * @param int    $tw Target width in pixels.
 * @param int    $th Target height in pixels.
 *
 * @return bool True on success, false if the image type is unsupported or saving fails.
 *
 * @requires ext-gd
 */
function resizeCover(string $src, string $dest, int $tw, int $th): bool {
    /** @var array{0:int,1:int,2:int} $info */
    [$sw, $sh, $type] = getimagesize($src);

    switch ($type) {
        case IMAGETYPE_JPEG: $srcIm = imagecreatefromjpeg($src); break;
        case IMAGETYPE_PNG:  $srcIm = imagecreatefrompng($src);  break;
        default: return false;
    }
    $srcRatio = $sw / $sh;
    $tgtRatio = $tw / $th;
    if ($srcRatio > $tgtRatio) {
        $nh = $th;
        $nw = (int) round($th * $srcRatio);
    } else {
        $nw = $tw;
        $nh = (int) round($tw / $srcRatio);
    }

    $tmp = imagecreatetruecolor($nw, $nh);
    if ($type === IMAGETYPE_PNG) { imagealphablending($tmp,false); imagesavealpha($tmp,true); }
    imagecopyresampled($tmp, $srcIm, 0,0,0,0, $nw,$nh, $sw,$sh);

    $x = (int) floor(($nw - $tw)/2);
    $y = (int) floor(($nh - $th)/2);
    $out = imagecreatetruecolor($tw, $th);
    if ($type === IMAGETYPE_PNG) { imagealphablending($out,false); imagesavealpha($out,true); }
    imagecopy($out, $tmp, 0,0, $x,$y, $tw,$th);

    $ok = ($type === IMAGETYPE_JPEG) ? imagejpeg($out, $dest, 85) : imagepng($out, $dest, 6);
    imagedestroy($srcIm); imagedestroy($tmp); imagedestroy($out);
    return $ok;
}
