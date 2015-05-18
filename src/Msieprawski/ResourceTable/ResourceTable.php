<?php namespace Msieprawski\ResourceTable;

use Input;
use Msieprawski\ResourceTable\Generators\Collection;

/**
 * Main ResourceTable Package object
 *
 * @ver 0.5
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

    // Use this as a default pagination presenter
    const DEFAULT_PAGINATION_PRESENTER = 'Illuminate\Pagination\BootstrapThreePresenter';

    /**
     * Will be "last set" Collection object after calling ResourceTable::of
     *
     * @var Collection|null
     */
    private static $_lastCollection = null;

    /*
     * List of custom configurable attributes
     * If set (not null) then will be set for Collection object when initializing
     */
    private static $_customPaginationPresenter;
    private static $_customView;
    private static $_customCustomView;
    private static $_customPaginate;
    private static $_customPerPage;
    private static $_customPage;
    private static $_customFilter;

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
        $collection = self::_addCustomAttributes($collection);
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

    /**
     * Set pagination presenter class name
     *
     * @param string $presenter
     */
    public static function setPaginationPresenter($presenter)
    {
        self::$_customPaginationPresenter = $presenter;
    }

    /**
     * Set ResourceTable built-in view name
     *
     * @param string $view
     */
    public static function setView($view)
    {
        self::$_customView = $view;
    }

    /**
     * Set custom view name if you've created your own ResourceTable template
     *
     * @param string $customView
     */
    public static function setCustomView($customView)
    {
        self::$_customCustomView = $customView;
    }

    /**
     * Set false if you dont want to use pagination
     *
     * @param bool $paginate
     */
    public static function setPaginate($paginate)
    {
        self::$_customPaginate = (bool)$paginate;
    }

    /**
     * How many results per page
     *
     * @param int $perPage
     */
    public static function setPerPage($perPage)
    {
        self::$_customPerPage = (int)$perPage;
    }

    /**
     * Current page
     *
     * @param int $page
     */
    public static function setPage($page)
    {
        self::$_customPage = (int)$page;
    }

    /**
     * Set true if you wan to enable filter
     *
     * @param bool $filter
     */
    public static function setFilter($filter)
    {
        self::$_customFilter = (bool)$filter;
    }

    /**
     * Sets custom configuration (if set) on given Collection
     *
     * @param Collection $collection
     * @return Collection
     */
    private static function _addCustomAttributes(Collection $collection)
    {
        if (self::$_customPaginationPresenter) {
            $collection->setPaginationPresenter(self::$_customPaginationPresenter);
        }
        if (self::$_customView) {
            $collection->view(self::$_customView);
        }
        if (self::$_customCustomView) {
            $collection->customView(self::$_customCustomView);
        }
        if (self::$_customPaginate) {
            $collection->paginate(self::$_customPaginate);
        }
        if (self::$_customPerPage) {
            $collection->perPage(self::$_customPerPage);
        }
        if (self::$_customPage) {
            $collection->page(self::$_customPage);
        }
        if (self::$_customFilter) {
            $collection->filter(self::$_customFilter);
        }

        return $collection;
    }
}