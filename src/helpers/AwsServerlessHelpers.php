<?php
/**
 * AWS Serverless Image Handler transformer for Imager X
 *
 * @link      https://www.spacecat.ninja
 * @copyright Copyright (c) 2022 André Elvan
 */

namespace spacecatninja\awsserverlesstransformer\helpers;

use Craft;
use craft\elements\Asset;
use craft\helpers\App;
use spacecatninja\imagerx\models\ConfigModel;
use function SSNepenthe\ColorUtils\{
    color
};

class AwsServerlessHelpers
{

    /**
     * @param Asset $image
     *
     * @return string
     */
    public static function getImageKey(Asset $image): string
    {
        $fsSubfolder = null;
        $volumeSubfolder = null;

        try {
            $volume = $image->getVolume();
            $fs = $volume->getFs();
            $fsSubfolder = $fs->subfolder ?? null;
            $volumeSubfolder = $volume->getSubpath() ?? null;
        } catch (\Throwable $e) {
            Craft::error('Could not get filesystem from image: '.$e->getMessage(), __METHOD__);
        }

        $imagePath = $image->getPath();

        return ltrim(
            ($fsSubfolder ? trim(App::parseEnv($fsSubfolder), '/') : '').'/'.
            ($volumeSubfolder ? trim(App::parseEnv($volumeSubfolder), '/') : '').'/'.
            $imagePath,
            '/');
    }

    /**
     * @param array $letterboxDef
     *
     * @return array
     */
    public static function getLetterboxColor(array $letterboxDef): array
    {
        $color = $letterboxDef['color'] ?? '#000000';
        $opacity = $letterboxDef['opacity'] ?? null;

        return self::parseColor($color, $opacity);
    }

    /**
     * @param string     $color
     * @param float|null $opacity
     *
     * @return array|null
     */
    public static function parseColor(string $color, float $opacity = null): ?array
    {
        $col = color($color);

        $rgb = $col->getRgb();

        return [
            'r' => $rgb->getRed(),
            'g' => $rgb->getGreen(),
            'b' => $rgb->getBlue(),
            'alpha' => $opacity ?: $rgb->getAlpha()
        ];
    }

    /**
     * @param string      $format
     * @param ConfigModel $config
     *
     * @return array
     */
    public static function getFormatOptions(string $format, ConfigModel $config): array
    {
        return match ($format) {
            'jpeg' => [
                'quality' => $config->jpegQuality,
                'progressive' => $config->interlace !== false,
                'optimiseCoding' => true,
                'trellisQuantisation' => true,
                'overshootDeringing' => true,
            ],
            'png' => [
                'compressionLevel' => $config->pngCompressionLevel,
                'progressive' => $config->interlace !== false,
            ],
            'webp' => [
                'quality' => $config->webpQuality
            ],
            default => [],
        };
    }

    /**
     * @param array $effects
     *
     * @return array
     */
    public static function convertEffects(array $effects): array
    {
        $r = [];

        if ((isset($effects['greyscale']) && $effects['greyscale']) || (isset($effects['grayscale']) && $effects['grayscale'])) {
            $r['grayscale'] = true;
        }

        if (isset($effects['negative']) && $effects['negative']) {
            $r['negate'] = true;
        }

        if (isset($effects['normalize']) && $effects['normalize']) {
            $r['normalize'] = true;
        }

        if (isset($effects['sharpen']) && $effects['sharpen']) {
            $r['sharpen'] = true;
        }

        if (isset($effects['blur'])) {
            if (is_bool($effects['blur'])) {
                $r['blur'] = 1;
            } else {
                $r['blur'] = $effects['blur'];
            }
        }

        return $r;
    }


    /**
     * @param string $position
     *
     * @return string
     */
    public static function getPosition(string $position): string
    {
        $positionArr = explode(' ', $position);

        $positionCovertArr = [
            0 => [0 => 'left top', 1 => 'top', 2 => 'right top'],
            1 => [0 => 'left', 1 => 'center', 2 => 'right'],
            2 => [0 => 'left bottom', 1 => 'bottom', 2 => 'right bottom']
        ];

        $x = round(($positionArr[0] / 100) * 2);
        $y = round(($positionArr[1] / 100) * 2);

        return $positionCovertArr[$y][$x];
    }

}
