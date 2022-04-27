<?php

namespace spacecatninja\awsserverlesstransformer\models;

use craft\base\Model;

class Settings extends Model
{
    public string $distributionUrl = '';
    public string $defaultBucket = '';
    public bool $autoConvertGif = true;
}
