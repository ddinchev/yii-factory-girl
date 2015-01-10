# YiiFactoryGirl

YiiFactoryGirl is a fixtures replacement with a straightforward definition syntax for [Yii 1.1](https://github.com/yiisoft/yii), inspired by [though_bot's factory_girl for Ruby](https://raw.githubusercontent.com/thoughtbot/factory_girl) and [kengos' FactoryGirl](https://github.com/kengos/FactoryGirl).

It was created in attempt to address the following limitations of kengos' FactoryGirlPHP:
- [x] Bypass foreign key constrains just like `Yii CDbFixtureManager`
- [x] Be a native `Yii` extension
- [ ] Allow IDE autocompletion of created factories
- [ ] Allow different strategies to store fixtures

