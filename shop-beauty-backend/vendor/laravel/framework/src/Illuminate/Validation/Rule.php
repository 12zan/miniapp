<?php

namespace Illuminate\Validation;

use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;

class Rule
{
    use Macroable;

    /**
     * Get a dimensions constraint builder instance.
     *
     * @param  array  $constraints
     * @return \Illuminate\Validation\Rules\Dimensions
     */
    public static function dimensions(array $constraints = [])
    {
        return new Rules\Dimensions($constraints);
    }

    /**
     * Get a exists constraint builder instance.
     *
     * @param  string  $table
     * @param  string  $column
     * @return \Illuminate\Validation\Rules\Exists
     */
    public static function exists($table, $column = 'NULL')
    {
        return new Rules\Exists($table, $column);
    }

    /**
     * Get an in constraint builder instance.
     *
     * @param  array|string|\Illuminate\Support\Collection  $values
     * @return \Illuminate\Validation\Rules\In
     */
    public static function in($values)
    {
        if ($values instanceof Collection) {
            $values = $values->toArray();
        }

        return new Rules\In(is_array($values) ? $values : func_get_args());
    }

    /**
     * Get a not_in constraint builder instance.
     *
     * @param  array|string|\Illuminate\Support\Collection  $values
     * @return \Illuminate\Validation\Rules\NotIn
     */
    public static function notIn($values)
    {
        if ($values instanceof Collection) {
            $values = $values->toArray();
        }

        return new Rules\NotIn(is_array($values) ? $values : func_get_args());
    }

    /**
     * Get a unique constraint builder instance.
     *
     * @param  string  $table
     * @param  string  $column
     * @return \Illuminate\Validation\Rules\Unique
     */
    public static function unique($table, $column = 'NULL')
    {
        return new Rules\Unique($table, $column);
    }
}
