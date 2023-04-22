<?php
/**
 * Stubs for laravel.
 */

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @template TModelClass
 * @extends \Illuminate\Database\Eloquent\Model
 */
abstract class LaravelHasManyVirtualBuilder extends HasMany
{
}

/**
 * @template TModelClass
 * @extends \Illuminate\Database\Eloquent\Model
 */
abstract class LaravelBelongsToManyVirtualBuilder extends BelongsToMany
{
}
