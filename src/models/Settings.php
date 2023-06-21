<?php

namespace spacecatninja\awsserverlesstransformer\models;

use craft\base\Model;

class Settings extends Model
{
    public string $distributionUrl = '';
    public string $defaultBucket = '';
    public bool $autoConvertGif = true;

    /**
     * @var string Optional secret to be used for signing transform URLs
     */
    public string $signatureKey = '';
}
