<?php

namespace Thoss\GapSort\Traits;

trait Sortable
{
    // Sorgt dafür dass neue Elemente immer ans Ende gestellt werden
    public static function bootSortable()
    {
        static::creating(function ($model) {
            $gap = config('laravel-gap-sort.sorting.gap');
            $orderColumn = config('laravel-gap-sort.sorting.column');

            $lastItem = $model->orderBy($orderColumn, 'DESC')->first();
            $newOrder = null !== $lastItem ? $lastItem->{$orderColumn} + $gap : $gap;

            $model->{$orderColumn} = $newOrder;
        });
    }
}
