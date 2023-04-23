<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @template TModelClass
 *
 * @method TModelClass create()
 *
 * @mixin \Illuminate\Database\Query\Builder
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
abstract class LaravelHasManyVirtualBuilder extends HasMany
{
}

/**
 * @template TModelClass
 *
 * @method TModelClass create()
 *
 * @mixin \Illuminate\Database\Query\Builder
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
abstract class LaravelBelongsToVirtualBuilder extends BelongsTo
{
}

/**
 * @template TModelClass
 *
 * @method TModelClass create()
 *
 * @mixin \Illuminate\Database\Query\Builder
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
abstract class LaravelBelongsToManyVirtualBuilder extends BelongsToMany
{
}

/**
 * @template TModelClass
 *
 * @method TModelClass create()
 *
 * @mixin \Illuminate\Database\Query\Builder
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
abstract class LaravelQueryVirtualBuilder extends Builder
{
}

