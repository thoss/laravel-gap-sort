<?php

namespace Thoss\GapSort\Traits;

trait Sortable
{
    // Sorgt dafür dass neue Elemente immer ans Ende gestellt werden
    public static function bootSortable()
    {
        static::creating(function ($model) {
            $gap = config('laravel-gap-sort.sorting.gap');
            $lastItem = $model->orderBy(config('laravel-gap-sort.sorting.column'), 'DESC')->first();
            $newOrder = null !== $lastItem ? $lastItem->order + $gap : $gap;

            $model->{config('laravel-gap-sort.sorting.column')} = $newOrder;
        });
    }
}
