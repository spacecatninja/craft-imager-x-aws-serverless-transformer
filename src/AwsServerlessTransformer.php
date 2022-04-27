<?php
/**
 * AWS Serverless Image Handler transformer for Imager X
 *
 * @link      https://www.spacecat.ninja
 * @copyright Copyright (c) 2022 André Elvan
 */

namespace spacecatninja\awsserverlesstransformer;

use craft\base\Model;
use craft\base\Plugin;

use spacecatninja\awsserverlesstransformer\models\Settings;
use spacecatninja\awsserverlesstransformer\transformers\AwsServerless;

use yii\base\Event;


class AwsServerlessTransformer extends Plugin
{
    public function init(): void
    {
        parent::init();

        // Register transformer with Imager
        Event::on(\spacecatninja\imagerx\ImagerX::class,
            \spacecatninja\imagerx\ImagerX::EVENT_REGISTER_TRANSFORMERS,
            static function (\spacecatninja\imagerx\events\RegisterTransformersEvent $event) {
                $event->transformers['awsserverless'] = AwsServerless::class;
            }
        );
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Model 
    {
        return new Settings();
    }

}
