<?php namespace Msieprawski\ResourceTable;

use Input;
use Msieprawski\ResourceTable\Generators\Collection;

/**
 * Main ResourceTable Package object
 *
 * @ver 0.3
 * @package Msieprawski\ResourceTable
 */
class ResourceTable
{
    // Use this default as a default per page
    const DEFAULT_PER_PAGE = 30;

    // Use this order direction when order_dir not set or invalid
    const DEFAULT_SORT_DIR = 'DESC';

    // Use this if view name not provided
    const DEFAULT_VIEW_NAME = 'resource-table::simple';

    // Use this to determine if search form is enabled by default
    const DEFAULT_FILTER = true;

    // Use this if column type (type property) not provided
    const DEFAULT_COLUMN_TYPE = 'string';

    // Used in GET query for all values (for select columns)
    const ALL_SELECT_VALUES_KEY = '_all';

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

    /**
     * Returns array with valid column type options
     *
     * @param string $type
     * @return bool
     */
    public static function validColumnType($type)
    {
        return in_array($type, [
            'string', 'select',
        ]);
    }

    /**
     * Returns search form default value
     *
     * @param string $name
     * @return mixed
     */
    public static function getSearchValue($name)
    {
        $getName = 'resource_table_'.$name;
        return Input::get($getName);
    }
}