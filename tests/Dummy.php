<?php

namespace Thoss\GapSort\Tests;

use Illuminate\Database\Eloquent\Model;
use Thoss\GapSort\Traits\Sortable;

class Dummy extends Model
{
    use Sortable;

    protected $fillable = [
        'name',
    ];
}
