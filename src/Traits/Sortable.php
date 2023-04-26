<?php

namespace Thoss\GapSort\Traits;

trait Sortable
{
    // Sorgt dafür dass neue Elemente immer ans Ende gestellt werden
    public static function bootSortable()
    {
        static::creating(function ($model) {
            $gap = config('gap-sort.order_gap');
            $orderColumn = config('gap-sort.order_column');

            $lastItem = $model->orderBy($orderColumn, 'DESC')->first();
            $newOrder = null !== $lastItem ? $lastItem->{$orderColumn} + $gap : $gap;

            $model->{$orderColumn} = $newOrder;
        });
    }
}
