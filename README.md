# Nova Optimal Image field

This field differs from the standard Image field in that after the image is loaded using it, image optimization takes 
place. For image optimization the package (spatie/laravel-image-optimizer)[https://github.com/spatie/laravel-image-optimizer] 
is used. 

## Installation

Install the package into a Laravel app that uses [Nova](https://nova.laravel.com) with Composer:

```bash
composer require yarbala/nova-optimal-image-field
```

Read the documentation (spatie/laravel-image-optimizer)[https://github.com/spatie/laravel-image-optimizer] for
optimizers setup information on the server side. 

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
