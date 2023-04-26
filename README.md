# Sortable behaviour for Eloquent models with gap algorithm

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/thoss/laravel-gap-sort/run-tests.yml?branch=main&label=tests)
[![Latest Version](https://img.shields.io/github/v/release/thoss/laravel-gap-sort.svg?style=flat-square)](https://github.com/thoss/laravel-gap-sort/releases)

This package provides a way to sort items in a table using the "Gap" algorithm, which is a more efficient way of reordering items in a table than using incremental values. It takes into account the gap between the order values of adjacent items and calculates the new order value for the main item based on the positions of the previous and next items.

[Description of the class Thoss\GapSort\SortItem](SORTITEM_DESCRIPTION.md)

## Requirement

- PHP 8.1
- Laravel 9/10
- Your order column must be an integer (recommended is unsigned integer)

## Installation

You can install this package using composer.  
Just run the command below.

```
composer require thoss/laravel-gap-sort
```


Optionally you can publish the config file with:

```bash
php artisan vendor:publish --tag=gap-sort-config
```

This is the content of the file that will be published in `config/gap-sort.php`

```php
return [
    /*
    * The name of the column that will be used to sort models.
    */
    'order_column' => 'order',

    /*
    * The gap between the sorted items
    */
    'order_gap' => 1000,

    /*
    * Indicates wheter the "/sort" route will be automaticaly added when you use the route ::register method
    */
    'resource_registrar_with_sort' => false,
];

```


## Usage

To add sortable behaviour to your model you must:

1. Use the trait `Thoss\GapSort\Traits\Sortable`.
2. Optionally specify which column will be used as the order column (unsignedInteger). The default is `sorting.column`.
3. Optionally specify which gap between the sorted items you want to use. The default is `sorting.gap`.

> The larger the gap, the lower the probability that the table will have to be reinitialized

4. You can initialize an existing Table with the order gap, maybe in a Migration file

```php
dispatch(new SortModel(modelString: YourModel::class, initTable:true));
```

### Use the sorting with an REST API

1. register `/sort` Route  
(with the enabled resource registrar you can easily add the `/sort` Route)
```php
Route::resource('salutations', 'SalutationsController', ['with' => ['sort']]);
```
2. Dispatch  the `SortModel` Job in your Controller

```php
use Thoss\GapSort\Requests\SortRequest;
use Thoss\GapSort\SortModel;

public function sort(SortRequest $request)
{
    return $this->dispatchSync(new SortModel(MyModel::class));
}
```


## Example Requests with a 100 gap

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


Item1 is sorted to the last
```
Current List:
- Item1 (order 100)
- Item2 (order 200)
- Item3 (order 300)

POST /api/myresource/sort
{
    "main": 1,
    "previous": 3, 
}

After Sort:
- Item2 (order 200)
- Item3 (order 300)
- Item1 (order 350)
```

Item3 is sorted to the first
```
Current List:
- Item1 (order 100)
- Item2 (order 200)
- Item3 (order 300)

POST /api/myresource/sort
{
    "main": 3,
    "next": 1, 
}

After Sort:
- Item3 (order 50)
- Item1 (order 100)
- Item2 (order 200)
```

## Tests

The package contains some tests, set up with Orchestra. The tests can be run via phpunit.

```bash
composer test
```

## Alternatives
- [https://github.com/ninoman/laravel-sortable](https://github.com/ninoman/laravel-sortable)
- [https://github.com/spatie/eloquent-sortable](https://github.com/spatie/eloquent-sortable)
