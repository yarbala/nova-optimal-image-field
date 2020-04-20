# Nova Optimal Image field



## Installation

Install the package into a Laravel app that uses [Nova](https://nova.laravel.com) with Composer:

```bash
composer require yarbala/nova-optimal-image-field
```

## Usage

Add the field to your resource in the ```fields``` method:
```php
use Yarbala\OptimalImage\OptimalImage;

OptimalImage::make('Image'),
```

Field uses local disk for converting images, to change this behavior change use localDisk method:

```php
OptimalImage::make('Image')->localDisk('local'),
``` 

The field extends the `Laravel\Nova\Fields\Image` field, so all the usual methods are available.
