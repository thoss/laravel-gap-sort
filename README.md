# Sortable behaviour for Eloquent models

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

This package provides a way to sort items in a table using the "Gap" algorithm, which is a more efficient way of reordering items in a table than using incremental values. It takes into account the gap between the order values of adjacent items and calculates the new order value for the main item based on the positions of the previous and next items.

[Description of the class](SORTITEM_DESCRIPTION.md)

## Installation

You can install this package using composer.  
Just run the command below.

```
composer require thoss/laravel-gap-sort
```


Optionally you can publish the config file with:

```bash
php artisan vendor:publish --tag=laravel-gap-sort-config
```

This is the content of the file that will be published in `config/laravel-gap-sort.php`

```php
return [
    'sorting' => [
        /*
        * The name of the column that will be used to sort models.
        */
        'column' => 'order',

        /*
        * The gap between the sorted items
        */
        'gap' => 100,
    ],

    /*
    * Indicates wheter the "/sort" route will be automaticaly added when you use the route ::register method
    */
    'resource_registrar' => false,
];

```


## Usage

To add sortable behaviour to your model you must:

1. Use the trait `Thoss\GapSort\Traits\Sortable`.
2. Optionally specify which column will be used as the order column (unsignedInteger). The default is `sorting.column`.
3. Optionally specify which gap between the sorted items you want to use. The default is `sorting.gap`.

> The larger the gap, the lower the probability that the table will have to be reinitialized

### Use the sorting with an REST API

1. register `/sort` Route  
(with the enabled resource registrar you can easily add the `/sort` Route)
```php
Route::resource('salutations', 'SalutationsController', ['with' => ['sort']]);
```
2. Dispatch  the `SortItem` Job

```php
use Thoss\GapSort\Requests\SortRequest;
use Thoss\GapSort\SortItem;

public function sort(SortRequest $request)
{
    return $this->dispatchSync(new SortItem(MyModel::class));
}
```


## Example Requests

Item1 is sorted between 2 and 3
```
Current List:
- Item1 (order 100)
- Item2 (order 200)
- Item3 (order 300)

POST /api/myresource/sort
{
    "main": 1,
    "previous": 2, 
    "next": 3,
}

After Sort:
- Item2 (order 200)
- Item1 (order 250)
- Item3 (order 300)
```


Item1 is sorted to the very end
```
Current List:
- Item1 (order 100)
- Item2 (order 200)
- Item3 (order 300)

POST /api/myresource/sort
{
    "main": 1,
    "previous": 2, 
}

After Sort:
- Item2 (order 200)
- Item3 (order 300)
- Item1 (order 400)
```

Item3 is sorted to the very front
```
Current List:
- Item1 (order 100)
- Item2 (order 200)
- Item3 (order 300)

POST /api/myresource/sort
{
    "main": 3,
    "next": 2, 
}

After Sort:
- Item3 (order 50)
- Item2 (order 200)
- Item1 (order 300)
```

## Tests

The package contains some tests, set up with Orchestra. The tests can be run via phpunit.

```bash
vendor/bin/phpunit
```

## Alternatives
- [Laravel Sortable](https://github.com/ninoman/laravel-sortable)
- [Eloquent-sortable](https://github.com/spatie/eloquent-sortable)
