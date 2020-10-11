<?php

namespace spacecatninja\awsserverlesstransformer\models;

use craft\base\Model;

class Settings extends Model
{
    public $distributionUrl = '';
    public $defaultBucket = '';
    public $autoConvertGif = true;
}
