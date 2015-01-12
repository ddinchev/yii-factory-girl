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

Usage
------

*Make sure you have separate database for running unit tests. As with fixtures, any records in a table you are creating factories for, will be truncated first!*

1) add the component to your test config (`protected\config\test.php`). The `YiiFactoryGirl\Factory` component has more attributes than the listed below. They are well documented in the class itself.

```php
return array(
    // ...
    'components' => array(
        // ...
        'factorygirl' => array(
            'class' => 'YiiFactoryGirl\Factory',
            // the following properties have the corresponding default values,
            // if they don't work out for you, feel free to change them
            // 'basePath' => 'protected/tests/factories',
            // 'factoryFileSuffix' => Factory, // so it would expect $basePath/UsersFactory.php for Users class factory
        )
    )
);
``` 

2) optionally create predefined factory data files under `protected\tests\factories` (configurable). They use the following file format:

```php
// The following is a factory config of Users model.
// protected/tests/factories/UsersFactory.php
// FileName UsersFactory.php
return array(
  'attributes' => array(
    'username' => 'Average Joe', // $user->username = 'test user'
    'type' => Users::TYPE_NORMAL_USER, // $user->type = Users::TYPE_NORMAL_USER
  ),
  'admin' => array(
    'name' => 'The Boss',
    'type' => Users::TYPE_ADMIN_USER
  )
);
```

3) now, if you call in your tests `Yii::app()->factorygirl->create('Users');` this would create a `Users` record.

```php

class FactoryGirlIntegrationTest extends CTestCase
{

    public function testIntegration() {
        // this will create a model without actually inserting it in the db, so no primary key / id
        $unsaved = Yii::app()->factorygirl->build('Users');
        $this->assertTrue($unsaved->id == null);
    
        // this on the other hand will create the user!
        $user = Yii::app()->factorygirl->create('Users');
        $this->assertFalse($user->id == null);
        $this->assertInstanceOf('Users', $user);
        $this->assertEquals('Average Joe', $user->username);
        
        $admin = Yii::app()->factorygirl->create('Users', array(), 'admin');
        $this->assertEquals(Users::TYPE_ADMIN_USER, $user->type);
        
        $demonstrateOverwrites = Yii::app()->factorygirl->create('Users', array('type' => Users::TYPE_ADMIN_USER));
        $this->assertEquals(Users::TYPE_ADMIN_USER, $user->type);
        $this->assertEquals('Average Joe', $demonstrateOverwrites->username);
        
        // you can even also do this, even we don't have VehiclesFactory.php
        $vehicle = Yii::app()->factorygirl->create('Vehicles', array(
            'make' => 'Honda',
            'model' => 'Accord',
            'user_id' => Yii::app()->factorygirl->create('Users')->id
        ));
        $this->assertInstanceOf('Vehicles', $vehicle);
    }

}
```


In the above last example, if you haven't defined `VehiclesFactory.php` file, you can create factories just the same. All that `YiiFactoryGirl\Factory` expects is that a class named `Vehicles` exists (or whatever you are trying to create) and you won't benefit from having predefined aliases/attributes which in many cases is just fine!

But there is one unpleasant small thing that you won't be able to use IDE auto-completion on your model created with `Yii::app()->factorygirl->create()`. First thing you can do is define a shortcut function in your tests bootstrap:

```php
/**
 * @return YiiFactoryGirl\Factory
 */
function fg() {
    return Yii::app()->factorygirl;
}
```

Now you can create new models with just `$user = fg()->create('Users');`. Now your IDE should know that `$user` is CActiveRecord, even if not the specific `Users` one. Of course, you could create a shortcut for creation of specific models. You could create a `protected\tests\factories\shortcuts.php` file and require it in your `bootstrap.php` with a definition like this:

```php
class UsersFactory {
    /**
     * @return Users
     */
    public static function create(array $attributes = array(), $alias = null) {
        return Yii::app()->factorygirl->create('Users', $attributes, $alias); 
    }
    
    /**
     * @return Users
     */
    public static function build(array $attributes = array(), $alias = null) {
        return Yii::app()->factorygirl->build('Users', $attributes, $alias); 
    }
}
```

From now on you will be able to create user factories in your tests just calling `$testuser = UsersFactory::create(['username' => 'testname']);` and your IDE will know that `$testuser` is instance of `Users`! This of course is some repetetive work, so it makes sense for models used a lot through the application.

## FactoryGirl Sequence

This has been "borrowed" directly from kengos' FactoryGirl.

```php
<?php

return array(
  'attributes' => array(
    'name' => 'bar_{{sequence}}',
  ),
);
?>
```

```
Yii::app()->factorygirl->build('Foo')->name // -> bar_0
Yii::app()->factorygirl->build('Foo')->name // -> bar_1
```


## Contributing

Opening issues for feature requests/bug reports is an option but proposing a solution is much more helpful!

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Added some feature / fixed some bug.'`)
4. Push to the branch (`git push origin my-new-feature`)
5. Create new Pull Request
