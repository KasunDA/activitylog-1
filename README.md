# Log the activity of your users

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

This Laravel 5 package provides a very easy to use solution to log the activities of the users of your Laravel 5 app. All the activities will be logged in a db-table. Optionally the activities can also be logged against the default Laravel Log Handler.

This package was originally created by Spatie. Spatie is a web design agency in Antwerp, Belgium. You'll find an overview of all their open source projects [on their website](https://spatie.be/opensource).

Later we in CodeMyViews decided to modify Spatie version to better fit our needs. You can read about us on [our site](https://CodeMyViews.com/).

## Installation

This package can be installed through Composer.
```bash
composer require codemyviews/activitylog
```


This service provider must be registered.
```php
// config/app.php

'providers' => [
    '...',
    'CodeMyViews\Activitylog\ActivitylogServiceProvider',
];
```


You'll also need to publish and run the migration in order to create the db-table.
```
php artisan vendor:publish --provider="CodeMyViews\Activitylog\ActivitylogServiceProvider" --tag="migrations"
php artisan migrate
```


Activitylog also comes with a facade, which provides an easy way to call it.
```php
// config/app.php

'aliases' => [
    ...
    'Activity' => 'CodeMyViews\Activitylog\ActivitylogFacade',
];
```


Optionally you can publish the config file of this package.
```
php artisan vendor:publish --provider="CodeMyViews\Activitylog\ActivitylogServiceProvider" --tag="config"
```
The configuration will be written to  ```config/activitylog.php```. The options provided are self explanatory.


## Usage

### Manual logging

Logging some activity is very simple.
```php
// at the top of your file, you should import the facade.
use Activity;
...
/*
  The log function takes three parameters:
      - $text: the activity you wish to log.
      - $user:  optional can be a user id or a user object.
                if not proved the id of Auth::user() will be used
      - $model: optional can be an instance of Illuminate\Database\Eloquent\Model
                if not provided, none will be used
                if provided, activity will be referencing this model
*/
Activity::log('Some activity that you wish to log');
Activity::log('User has updated a post', $user, $post);
```
The string you pass to function gets written in a db-table together with a timestamp, the IP address, the user agent of the user, and reference to the model.

### Log model events
This package can log the events from your models. To do so, your model must use the `LogsActivity`-trait and implement `LogsActivityInterface`.

```php
use Spatie\Activitylog\LogsActivityInterface;
use Spatie\Activitylog\LogsActivity;

class Article implements LogsActivityInterface {

   use LogsActivity;
...
```

The interface expects you to implement the `getActivityDescriptionForEvent`-function.

Here's an example of a possible implementation.

```php
/**
 * Get the message that needs to be logged for the given event name.
 *
 * @param string $eventName
 * @return string
 */
public function getActivityDescriptionForEvent($eventName)
{
    if ($eventName == 'created')
    {
        return 'Article "' . $this->name . '" was created';
    }

    if ($eventName == 'updated')
    {
        return 'Article "' . $this->name . '" was updated';
    }

    if ($eventName == 'deleted')
    {
        return 'Article "' . $this->name . '" was deleted';
    }

    return '';
}
```
The result of this function will be logged unless the result is an empty string. Logged activities will be referencing the model, which has created them.

### Using a before handler.
If you want to disable logging under certain conditions,
such as for a particular user, create a class in your application
namespace that implements the `CodeMyViews\Activitylog\Handlers\BeforeHandlerInterface`.

This interface defines a `shouldLog()` method in which you can code any custom logic to determine
whether logging should be ignored or not. You must return `true` the call should be logged.

To en the namespaced class name to the `beforeHandler` field in the configuration file:
```php
'beforeHandler' => '\App\Handlers\BeforeHandler',
```

For example, this callback class could look like this to disable
logging a user with the id of 1:
```php
<?php

namespace App\Handlers;

use CodeMyViews\Activitylog\Handlers\BeforeHandlerInterface;

class BeforeHandler implements BeforeHandlerInterface
{
    public function shouldLog($text, $userId)
    {
        if ($userId == 1) return false;

        return true;
    }
}
```

### Retrieving logged entries
All events will be logged in the `activity_log`-table. This package provides an Eloquent model to work with the table. You can use all the normal Eloquent methods that you know and love. Here's how you can get the last 100 activities together with the associated users.

```php
use CodeMyViews\Activitylog\Models\Activity;

$latestActivities = Activity::with('user')->latest()->limit(100)->get();
```

### Cleaning up the log

Over time, your log will grow. To clean up the database table you can run this command:
```php
Activity::cleanLog();
```
By default records, older than 2 months will be deleted. The number of months can be modified in the configuration file of the package.

## Security

If you discover any security related issues, please email connor@codemyviews.com instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [Ivan Kulikov](https://github.com/ikoolik)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
