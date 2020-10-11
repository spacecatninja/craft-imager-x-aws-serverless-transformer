<?php
/**
 * AWS Serverless Image Handler transformer for Imager X
 *
 * @link      https://www.spacecat.ninja
 * @copyright Copyright (c) 2020 André Elvan
 */

namespace spacecatninja\awsserverlesstransformer;

use craft\base\Plugin;

use spacecatninja\awsserverlesstransformer\models\Settings;
use spacecatninja\awsserverlesstransformer\transformers\AwsServerless;

use yii\base\Event;


class AwsServerlessTransformer extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var AwsServerlessTransformer
     */
    public static $plugin;

    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        self::$plugin = $this;
        
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
    protected function createSettingsModel()
    {
        return new Settings();
    }

}
