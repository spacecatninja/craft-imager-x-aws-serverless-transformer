<?php
/**
 * AWS Serverless Image Handler transformer for Imager X
 *
 * @link      https://www.spacecat.ninja
 * @copyright Copyright (c) 2022 André Elvan
 */

namespace spacecatninja\awsserverlesstransformer\models;

use craft\elements\Asset;
use spacecatninja\imagerx\models\BaseTransformedImageModel;
use spacecatninja\imagerx\models\TransformedImageInterface;

class AwsServerlessTransformedImageModel extends BaseTransformedImageModel implements TransformedImageInterface
{
    /**
     * ImgixTransformedImageModel constructor.
     *
     * @param string|null $imageUrl
     * @param Asset|null  $source
     * @param array|null  $params
     */
    public function __construct(string $imageUrl = null, Asset $source = null, array $params = null)
    {
        if ($imageUrl !== null) {
            $this->url = $imageUrl;
        }

        $resize = $params['edits']['resize'];

        if (isset($resize['width'], $resize['height'], $source)) {
            [$sourceWidth, $sourceHeight] = $this->getSourceImageDimensions($source);

            $this->width = (int)$resize['width'];
            $this->height = (int)$resize['height'];

            if ($sourceWidth !== 0 && $sourceHeight !== 0) {
                if ($resize['fit'] === 'inside' || $resize['fit'] === 'outside') {
                    $paramsW = (int)$resize['width'];
                    $paramsH = (int)$resize['height'];

                    if ($sourceWidth / $sourceHeight > $paramsW / $paramsH) {
                        $useW = min($paramsW, $sourceWidth);
                        $this->width = $useW;
                        $this->height = round($useW * ($sourceHeight / $sourceWidth));
                    } else {
                        $useH = min($paramsH, $sourceHeight);
                        $this->width = round($useH * ($sourceWidth / $sourceHeight));
                        $this->height = $useH;
                    }
                }
            }
        } else if (isset($resize['width']) || isset($resize['height'])) {
            if ($source !== null && $resize !== null) {
                [$sourceWidth, $sourceHeight] = $this->getSourceImageDimensions($source);

                if ((int)$sourceWidth === 0 || (int)$sourceHeight === 0) {
                    if (isset($resize['width'])) {
                        $this->width = (int)$resize['width'];
                    }
                    if (isset($resize['height'])) {
                        $this->height = (int)$resize['height'];
                    }
                } else {
                    [$w, $h] = $this->calculateTargetSize($resize, $sourceWidth, $sourceHeight);

                    $this->width = $w;
                    $this->height = $h;
                }
            }
        } else { // Neither is set, image is not resized. Just get dimensions and return.
            [$sourceWidth, $sourceHeight] = $this->getSourceImageDimensions($source);

            $this->width = $sourceWidth;
            $this->height = $sourceHeight;
        }

        // Add padding if set, it was removed in the transformer.
        if (isset($params['edits']['extend'])) {
            $extend = $params['edits']['extend'];

            $this->width += $extend['left'] + $extend['right'];
            $this->height += $extend['top'] + $extend['bottom'];
        }
    }

    /**
     * @param $source
     *
     * @return array
     */
    protected function getSourceImageDimensions($source): array
    {
        if ($source instanceof Asset) {
            return [$source->getWidth(), $source->getHeight()];
        }

        return [0, 0];
    }

    /**
     * @param array $params
     * @param int $sourceWidth
     * @param int $sourceHeight
     *
     * @return array
     */
    protected function calculateTargetSize(array $params, int $sourceWidth, int $sourceHeight): array
    {
        $fit = $params['fit'];
        $ratio = $sourceWidth / $sourceHeight;

        $w = $params['width'] ?? null;
        $h = $params['height'] ?? null;

        // `fill` is a bit weird in sharp. If both width and height isn't set, the missing dimension is set to the source size.
        if ($fit === 'fill' && !isset($w, $h)) {
            if (isset($w)) {
                return [$w, $sourceHeight];
            }
            if (isset($h)) {
                return [$sourceWidth, $h];
            }
        }

        if ($w) {
            return [$w, round($w / $ratio)];
        }
        if ($h) {
            return [round($h * $ratio), $h];
        }


        return [0, 0];
    }
    
}
