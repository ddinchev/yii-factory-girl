# yii-factory-girl

yii-factory-girl is a fixtures replacement with a straightforward definition syntax for [Yii 1.1](https://github.com/yiisoft/yii), inspired by [though_bot's factory_girl for Ruby](https://raw.githubusercontent.com/thoughtbot/factory_girl) and [kengo's FactoryGirlPhp](https://github.com/kengos/FactoryGirl).

It was created in attempt to address the following limitations of kengo's FactoryGirlPHP:
* Allow different strategies to store fixtures
* Allow type-hinting of objects
* Bypass foreign key constrains just like `Yii CDbFixtureManager`
* Be a native `Yii` extension.
* Have `composer` support
