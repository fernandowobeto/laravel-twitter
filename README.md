laravel-twitter
===============

Laravel 4 Service Provider to interact with twitter account, like return a collection of the most recent Tweets posted by the user, post Tweets, delete status and get user profile.

[![Latest Stable Version](https://poser.pugx.org/wobeto/twitter/v/stable.svg)](https://packagist.org/packages/wobeto/twitter) [![Total Downloads](https://poser.pugx.org/wobeto/twitter/downloads.svg)](https://packagist.org/packages/wobeto/twitter) [![Latest Unstable Version](https://poser.pugx.org/wobeto/twitter/v/unstable.svg)](https://packagist.org/packages/wobeto/twitter) [![License](https://poser.pugx.org/wobeto/twitter/license.svg)](https://packagist.org/packages/wobeto/twitter) [![Code Climate](https://codeclimate.com/github/fernandowobeto/laravel-twitter/badges/gpa.svg)](https://codeclimate.com/github/fernandowobeto/laravel-twitter)


## Installation

Add wobeto/twitter to your composer.json file:

```
"require": {
  "wobeto/twitter": "0.5.0"
}
```

Use composer to install this package.

```
$ composer update
```

### Registering the Package

Register the service provider within the ```providers``` array found in ```app/config/app.php```:

```php
'providers' => array(
	// ...
	
	'Wobeto\Twitter\TwitterServiceProvider'
)
```

Add an alias within the ```aliases``` array found in ```app/config/app.php```:


```php
'aliases' => array(
	// ...
	
	'Twitter' => 'Wobeto\Twitter\Facade\Twitter',
)
```

Copy Config/twitter.php file to app/config/ and enter your data configurations
