laravel-twitter
===============

Laravel 4 Service Provider to interact with twitter account, like return a collection of the most recent Tweets posted by the user, post Tweets and delete.

## Installation

Add wobeto/twitter to your composer.json file:

```
"require": {
  "wobeto/twitter": "dev-master"
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
