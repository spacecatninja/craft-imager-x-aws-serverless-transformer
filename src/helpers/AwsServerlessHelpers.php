<?php
/**
 * AWS Serverless Image Handler transformer for Imager X
 *
 * @link      https://www.spacecat.ninja
 * @copyright Copyright (c) 2020 André Elvan
 */

namespace spacecatninja\awsserverlesstransformer\helpers;

use Craft;
use craft\elements\Asset;
use spacecatninja\imagerx\models\ConfigModel;
use function SSNepenthe\ColorUtils\{
    color
};

class AwsServerlessHelpers
{

    /**
     * @param Asset $image
     * @return string
     */
    public static function getImageKey($image): string
    {
        try {
            $volume = $image->getVolume();
            $volumeSubfolder = $volume->subfolder ?? null;
        } catch (\Throwable $e) {
            Craft::error('Could not get volume from image: ' . $e->getMessage(), __METHOD__);
            $volumeSubfolder = '';
        }

        $imagePath = $image->getPath();

        return ltrim(($volumeSubfolder ? rtrim(Craft::parseEnv($volumeSubfolder), '/') : '') . '/' . $imagePath, '/');
    }

    /**
     * @param array $letterboxDef
     * @return array
     */
    public static function getLetterboxColor($letterboxDef): array
    {
        $color = $letterboxDef['color'] ?? '#000000';
        $opacity = $letterboxDef['opacity'] ?? null;

        return self::parseColor($color, $opacity);
    }

    /**
     * @param string $color
     * @param null|float $opacity
     * @return array|null
     */
    public static function parseColor($color, $opacity = null)
    {
        $col = color($color);

        if (!$col) {
            return null;
        }

        $rgb = $col->getRgb();

        return [
            'r' => $rgb->getRed(),
            'g' => $rgb->getGreen(),
            'b' => $rgb->getBlue(),
            'alpha' => $opacity ?: $rgb->getAlpha()
        ];
    }

    /**
     * @param string $format
     * @param ConfigModel $config
     * @return array
     */
    public static function getFormatOptions($format, $config): array
    {
        switch ($format) {
            case 'jpeg':
                return [
                    'quality' => $config->jpegQuality,
                    'progressive' => $config->interlace !== false,
                    'optimiseCoding' => true,
                    'trellisQuantisation' => true,
                    'overshootDeringing' => true,
                ];
            case 'png':
                return [
                    'compressionLevel' => $config->pngCompressionLevel,
                    'progressive' => $config->interlace !== false,
                ];
            case 'webp':
                return [
                    'quality' => $config->webpQuality
                ];
        }

        return [];
    }

    /**
     * @param array $effects
     * @return array
     */
    public static function convertEffects($effects): array
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
     * @return string
     */
    public static function getPosition($position): string
    {
        $positionArr  = explode(' ', $position);
        
        $positionCovertArr = [
            0 => [0 => 'left top', 1 => 'top', 2 => 'right top'],
            1 => [0 => 'left', 1 => 'center', 2 => 'right'],
            2 => [0 => 'left bottom', 1 => 'bottom', 2 => 'right bottom']
        ];

        $x = round(($positionArr[0]/100) * 2);
        $y = round(($positionArr[1]/100) * 2);

        return $positionCovertArr[$y][$x];
    }

}
