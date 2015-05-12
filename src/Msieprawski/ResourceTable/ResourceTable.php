<?php namespace Msieprawski\ResourceTable;

use Msieprawski\ResourceTable\Generators\Collection;

/**
 * Main ResourceTable Package object
 *
 * @ver 0.1
 * @package Msieprawski\ResourceTable
 */
class ResourceTable
{
    // Use this default as a default per page
    const DEFAULT_PER_PAGE = 30;

    // Use this order direction when order_dir not set or invalid
    const DEFAULT_SORT_DIR = 'DESC';

    /**
     * Will be "last set" Collection object after calling ResourceTable::of
     *
     * @var Collection|null
     */
    private static $_lastCollection = null;

    /**
     * Sets builder and returns collection
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $builder
     * @return Collection
     */
    public static function of($builder)
    {
        if ($builder instanceof \Illuminate\Database\Eloquent\Builder) {
            $builder = $builder->getQuery();
        }

        $collection = new Collection($builder);
        self::$_lastCollection = $collection;
        return self::collection();
    }

    /**
     * Returns "last set" Collection object after calling ResourceTable::of
     *
     * @return Collection
     */
    public static function collection()
    {
        return self::$_lastCollection;
    }
}