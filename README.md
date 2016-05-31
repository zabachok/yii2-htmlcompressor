# Compressing HTML before submit to client

HTML compressor.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist zabachok/yii2-htmlcompressor "*"
```

or add

```
"zabachok/yii2-htmlcompressor": "*"
```

to the require section of your `composer.json` file.


## Usage

Once the extension is installed, simply use it in your code by  :


### By View component
In your config file in components:

```php
'view' => [
	'class' => '\zabachok\htmlcompressor\View',
	'compress' => YII_ENV_DEV ? false : true,
	'compressCode' => false,
	'compressScript' => false
],
```


### By event
In your config file in components:

```php
'response' => [
	'on beforeSend' => function ($event)
	{
		/** @var $event yii\base\ViewEvent */
		$response = $event->sender;
		$compressor = new \zabachok\htmlcompressor\HtmlCompressor(false, false);
		$response->data = $compressor->make($response->data);
	},
],
```

### By behavior
If you already have custom `View` component, you can use behavior.

```php
class View extends \yii\web\View
{
    public function behaviors()
    {
        return [
            [
                'class' => HtmlCompressorBehavior::className(),
                'compress' => true,
                'compressScript' => true,
                'compressCode' => true,
            ],
        ];
    }
    ...
}
```

### Withoit Yii2
Using without Yii2.

```php
$compressor = new HtmlCompressor();
$result = $compressor->make($html);
```