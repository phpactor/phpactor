<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @template TModelClass
 *
 * @method TModelClass create()
 */
abstract class LaravelBuilder extends Builder
{
    public function whereIn(string $arg, array $in): static {
        return $this;
    }

    public function whereNull(string $arg): static {
        return $this;
    }
    
    public function whereNotNull(string $arg): static {
        return $this;
    }
}

/**
 * @template TModelClass
 *
 * @method TModelClass create()
 */
abstract class LaravelHasManyVirtualBuilder extends HasMany
{
}

/**
 * @template TModelClass
 *
 * @method TModelClass create()
 * @method TModelClass first()
 */
abstract class LaravelBelongsToVirtualBuilder extends BelongsTo
{
}

/**
 * @template TModelClass
 *
 * @method TModelClass create()
 */
abstract class LaravelBelongsToManyVirtualBuilder extends BelongsToMany
{
}

/**
 * @template TModelClass
 *
 * @method TModelClass create()
 * @method TModelClass first()
 */
abstract class LaravelQueryVirtualBuilder extends Builder
{
    public function whereIn(string $arg, array $in): static {
        return $this;
    }

    public function whereNull(string $arg): static {
        return $this;
    }
    
    public function whereNotNull(string $arg): static {
        return $this;
    }
}

