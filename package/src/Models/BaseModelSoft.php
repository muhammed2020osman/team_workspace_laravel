<?php

namespace LaraCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LaraCore\Traits\FilterableTrait;
use LaraCore\Traits\SearchableTrait;
use LaraCore\Traits\SortableTrait;

abstract class BaseModelSoft extends BaseModel
{
 use SoftDeletes;

}
