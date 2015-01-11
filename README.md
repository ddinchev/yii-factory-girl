# yii-factory-girl

yii-factory-girl is a fixtures replacement with a straightforward definition syntax for [Yii 1.1](https://github.com/yiisoft/yii), inspired by [though_bot's factory_girl for Ruby](https://raw.githubusercontent.com/thoughtbot/factory_girl) and [kengos' FactoryGirl](https://github.com/kengos/FactoryGirl).

It was created in attempt to address the following limitations of kengos' FactoryGirlPHP:
- [x] Bypass foreign key constrains just like `Yii CDbFixtureManager`
- [x] Be a native `Yii` extension
- [ ] Allow IDE autocompletion of created factories
- [ ] Allow different strategies to store fixtures


Install
--------

yii-factory-girl is installed most easily via [Composer](https://getcomposer.org/).

If you don't already have composer support in your Yii project just follow the steps.

* Install `composer`
* Init `composer` in your project 
```shell
cd protected/
composer init
```
* Add `yii-factory-girl` to the `require-dev` part of the newly created `protected/composer.json`. Example `composer.json` for Yii1.1 project might look just like this:
```
{
    "require": {
        "php": ">=5.3.0",
        "yiisoft/yii": "~1.1",
    },
    "require-dev": {
        "phpunit/phpunit": "~3.7",
        "phpunit/dbunit": ">=1.2",
        "phpunit/php-invoker": ">=1.1",
        "phpunit/phpunit-selenium": ">=1.2",
        "phpunit/phpunit-story": ">=1.0",
        "ddinchev/yii-factory-girl": "dev-master"
    }
}
```

Setup
------

First, add the component to your test config (`protected\config\test.php`). The `YiiFactoryGirl\Factory` component has more attributes than the listed below. They are well documented in the class itself.

```php
return array(
    // ...
    'components' => array(
        // ...
        'factorygirl' => array(
            'class' => 'YiiFactoryGirl\Factory',
            // the following properties have the following default values,
            // if they don't work out for you, feel encouraged to set them accordingly
            // 'basePath' => 'protected/tests/factories',
            // 'factoryFileSuffix' => Factory, // so it would expect $basePath/UsersFactory.php for Users class factory
        )
    )
);
```

Second, optionally create predefined factory data files under `protected\tests\factories` (configurable). They use the following file format.