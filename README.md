# AWS Serverless Image Handler transformer for Imager X

A plugin for using AWS Serverless Image Handler as a transformer in Imager X.   
Also, an example of [how to make a custom transformer for Imager X](https://imager-x.spacecat.ninja/extending.html#transformers).

## Requirements

This plugin requires Craft CMS 5.0.0-beta.1 or later, [Imager X 5.0.0-beta.1](https://github.com/spacecatninja/craft-imager-x/) or later,
and a working [AWS Serverless Image Handler](https://aws.amazon.com/solutions/implementations/serverless-image-handler/) setup.
 
## Usage

First of all, you need to set up an [AWS Serverless Image Handler](https://aws.amazon.com/solutions/implementations/serverless-image-handler/)
in AWS. If you need help and pointers, Andrew Welch has [a thorough blog post about it](https://nystudio107.com/blog/setting-up-your-own-image-transform-service).

Next, install and configure this transformer as described below. Then, in your [Imager X config](https://imager-x.spacecat.ninja/configuration.html), 
set the transformer to `awsserverless`, ie:

```
'transformer' => 'awsserverless',
``` 

Transforms are now by default transformed with the AWS Serverless Image Handler. Test your configuration with a 
simple transform like this:

```
{% set transform = craft.imagerx.transformImage(asset, { width: 600 }) %}
<img src="{{ transform.url }}" width="600">
<p>URL is: {{ transform.url }}</p>
``` 

If this doesn't work, make sure the distribution URL is correct, and that the image handler is set
up to use the same buckets that you assets are on.

### Cave-ats, shortcomings, and tips

This transformer only supports a subset of what Imager X can do when using the default `craft` transformer. 
All the basic transform parameters are supported, with the following exceptions:

- Percentage based focal points are not supported by the image handler, it only supports edge-based positions like
`left top`, `right center`, etc. Craft focal points are automatically converted to the nearest possible position.
- Only assets that are stored in AWS buckets are possible to transform.
- The `cropOnly` resize mode is not supported.
- The `cropZoom` transform parameter is not supported.
- The `frames` and `preEffects` are not relevant and supported.
- The following `effects` are converted and supported: `grayscale`, `negative`, `normalize`, `sharpen` and `blur`. 
- The URL that comes from the image handler is... not pretty. 

To pass additional options that is understood by the underlying sharp.js library the Serverless
Image Handler uses, you can use the `transformerParams` transform parameter. Example:

```
{% set transforms = craft.imagerx.transformImage(asset, 
    [{width: 400}, {width: 600}, {width: 800}], 
    { ratio: 2/1, transformerParams: { tint: '#ff0066' } }
) %}
```   

You can also override the automatic converted parameters, like `toFormat` and `resize`, if you need to: 

```
{% set transforms = craft.imagerx.transformImage(asset, 
    [{width: 400}, {width: 600}, {width: 800}], 
    { ratio: 2/1, transformerParams: { tint: '#ff0066', toFormat: 'png', resize: { position: 'left top' } } }
) %}
```   

For more information, check out the [sharp.js documentation](https://sharp.pixelplumbing.com/).


## Installation

To install the plugin, follow these instructions:

1. Install with composer via `composer require spacecatninja/imager-x-aws-serverless-transformer` from your project directory.
2. Install the plugin in the Craft Control Panel under Settings > Plugins, or from the command line via `./craft plugin/install imager-x-aws-serverless-transformer`.


## Configuration

You can configure the transformer by creating a file in your config folder called
`imager-x-aws-serverless-transformer.php`, and override settings as needed.

### distributionUrl [string] (required)
Default: `'distributionUrl'`  
This is the URL to your AWS Serverless Image Handler distribution. Example:

```
'distributionUrl' => 'https://dg5e6u1m25arf.cloudfront.net',
```

### autoConvertGif [bool]
Default: `true`  
The AWS Image Handler can't output GIF. With this setting set to `true` (default), the transformer 
will automatically convert GIF images to PNG to avoid errors. If you manually would like to 
override this at the template level (by explicitly setting `format`), you can disable this.

### defaultBucket [string]
Default: `''`  
By default, Imager will try to get the bucket name of the AWS volume for the asset you're 
trying to transform, and send that to the image handler so that it knows where to get the 
image from. You can configure the default bucket it should fall back to with this setting. 
Probably not needed, but you never know.  

_Make sure you add all the buckets you've set up Craft to use, when you set up your
Image Handler in AWS._
 
### signatureKey [string]
Default: `''`  
The AWS Image Handler supports using [signed URLs](https://docs.aws.amazon.com/solutions/latest/serverless-image-handler/considerations.html#image-url-signature) 
to prevent third parties from generating their own transforms. Once you’ve configured the image handler to use them,
add your signing key and generated URLs will include a `?signature=` param that’s properly hashed and ready to go.

Be careful with this! Once enabled on the server side, any transform URL without a valid signature will return an error.

Price, license and support
---
The plugin is released under the MIT license. It requires Imager X, which is a commercial 
plugin [available in the Craft plugin store](https://plugins.craftcms.com/imager-x). If you 
need help, or found a bug, please post an issue in this repo, or in Imager X' repo. 
