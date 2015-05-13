<?php namespace Msieprawski\ResourceTable\Generators;

use DB;
use Input;
use Msieprawski\ResourceTable\Exceptions\CollectionException;
use Msieprawski\ResourceTable\Helpers\Column;
use Msieprawski\ResourceTable\ResourceTable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\BootstrapThreePresenter;

/**
 * Collection object which is representing given $builder's collection
 *
 * @ver 0.3
 * @package Msieprawski\ResourceTable
 */
class Collection
{
    /**
     * Plain Builder object
     *
     * @var \Illuminate\Database\Query\Builder
     */
    private $_builder;

    /**
     * An array with table columns
     *
     * @var array
     */
    private $_columns = [];

    /**
     * Use pagination or not?
     *
     * @var bool
     */
    private $_paginate = true;

    /**
     * Current collection page
     *
     * @var int
     */
    private $_page = 1;

    /**
     * Resources per page
     *
     * @var int
     */
    private $_perPage = ResourceTable::DEFAULT_PER_PAGE;

    /**
     * Will be total number of paginated items after calling _prepareBuilder method (if pagination enabled)
     *
     * @var int
     */
    private $_totalItems = 0;

    /**
     * Sort column and direction
     *
     * @var array
     */
    private $_sort = ['index' => '', 'dir' => ResourceTable::DEFAULT_SORT_DIR];

    /**
     * View name to render
     *
     * @var string
     */
    private $_viewName = ResourceTable::DEFAULT_VIEW_NAME;

    /**
     * Determine if column filter (search inputs for each searchable column) should be rendered or not
     *
     * @var bool
     */
    private $_columnsFilter = ResourceTable::DEFAULT_COLUMNS_FILTER;

    /**
     * Determine if global filter (search input) should be rendered or not
     *
     * @var bool
     */
    private $_globalFilter = ResourceTable::DEFAULT_GLOBAL_FILTER;

    /**
     * Sets builder object
     *
     * @param \Illuminate\Database\Query\Builder $builder
     */
    public function __construct(\Illuminate\Database\Query\Builder $builder)
    {
        $this->_builder = $builder;
    }

    /**
     * Sets $_paginate variable
     *
     * @param bool $paginate
     * @return $this
     * @throws CollectionException
     */
    public function paginate($paginate)
    {
        if (!is_bool($paginate)) {
            throw new CollectionException('Parameter paginate must be boolean.');
        }

        $this->_paginate = $paginate;
        return $this;
    }

    /**
     * Sets $_perPage variable
     *
     * @param int $perPage
     * @return $this
     * @throws CollectionException
     */
    public function perPage($perPage)
    {
        if (!is_int($perPage)) {
            throw new CollectionException('Parameter per page must be an integer.');
        }

        $this->_perPage = $perPage;
        return $this;
    }

    /**
     * Sets $_page variable
     *
     * @param int $page
     * @return $this
     * @throws CollectionException
     */
    public function page($page)
    {
        if (!is_int($page)) {
            throw new CollectionException('Parameter page must be an integer');
        }

        $this->_page = $page;
        return $this;
    }

    /**
     * Adds column to resource table
     *
     * @param array $data
     * @return $this
     * @throws CollectionException
     */
    public function addColumn(array $data)
    {
        $columnValidation = $this->_validateColumn($data);
        if (is_string($this->_validateColumn($data))) {
            throw new CollectionException('Invalid column data. '.$columnValidation);
        }

        $this->_columns[] = $data;

        return $this;
    }

    /**
     * Returns HTML with resource table
     *
     * @return string
     * @throws CollectionException
     */
    public function make()
    {
        if (empty($this->_columns)) {
            throw new CollectionException('At least one column is required to generate a resource table.');
        }

        // Prepare builder object before calling Table
        $this->_prepareBuilder();

        // Finally execute prepared query builder
        $items = $this->_builder->get();

        return with(new Table($items, [
            'columns'             => $this->_columns,
            'per_page'            => $this->_perPage,
            'paginate'            => $this->_paginate,
            'paginator_presenter' => $this->_getPaginatorPresenter($items),
            'view_name'           => $this->_viewName,
            'filter'              => [
                'global'  => $this->_globalFilter,
                'columns' => $this->_columnsFilter,
            ],
        ]))->make();
    }

    /**
     * Sets sort configuration
     *
     * @param string $index
     * @param string $direction
     * @return $this
     * @throws CollectionException
     */
    public function sort($index, $direction)
    {
        if (!in_array($direction, ['ASC', 'DESC'])) {
            throw new CollectionException('Sort direction must be ASC or DESC.');
        }

        $this->_sort = ['index' => $index, 'dir' => $direction];
        return $this;
    }

    /**
     * Sets package view name for further rendering
     *
     * @param string $name
     * @return $this
     * @throws CollectionException
     */
    public function view($name)
    {
        return $this->_setView($name);
    }

    /**
     * Sets custom view name for further rendering
     *
     * @param string $name
     * @return $this
     * @throws CollectionException
     */
    public function customView($name)
    {
        return $this->_setView($name, true);
    }

    /**
     * Sets view name for further rendering
     *
     * @param string $name
     * @param bool $custom
     * @return $this
     * @throws CollectionException
     */
    private function _setView($name, $custom = false)
    {
        if (!is_string($name)) {
            throw new CollectionException('View name must be a string.');
        }

        $this->_viewName = (!$custom ? 'resource-table::' : '').$name;
        return $this;
    }

    /**
     * Returns $_sort variable
     *
     * @return array
     */
    public function getSort()
    {
        return $this->_sort;
    }

    /**
     * Sets true if column filter should be rendered
     *
     * @param bool $enabled
     * @return $this
     * @throws CollectionException
     */
    public function columnsFilter($enabled)
    {
        if (!is_bool($enabled)) {
            throw new CollectionException('Columns filter must be enabled or disabled. Bool is required.');
        }

        $this->_columnsFilter = $enabled;
        return $this;
    }

    /**
     * Sets true if global filter should be rendered
     *
     * @param bool $enabled
     * @return $this
     * @throws CollectionException
     */
    public function globalFilter($enabled)
    {
        if (!is_bool($enabled)) {
            throw new CollectionException('Global filter must be enabled or disabled. Bool is required.');
        }

        $this->_globalFilter = $enabled;
        return $this;
    }

    /**
     * Checks if provided column data is valid
     * Returns bool if it's valid
     * Returns string it it's not valid
     *
     * @param array $data
     * @return bool|string
     */
    private function _validateColumn(array $data)
    {
        if (!isset($data['label'])) {
            return 'Label key is required.';
        }
        if (!isset($data['index'])) {
            return 'Index key is required.';
        }

        if (isset($data['renderer']) && !$data['renderer'] instanceof \Closure) {
            return 'Renderer must be instance of Closure object.';
        }

        return true;
    }

    /**
     * Prepares builder object before calling get method on it
     *
     * @throws CollectionException
     */
    private function _prepareBuilder()
    {
        $builder = $this->_builder;
        $params = Input::get();

        /*
         * START pagination
         */
        if ($this->_paginate) {
            if (isset($params['per_page']) && is_numeric($params['per_page']) && $params['per_page'] > 0) {
                // Get per_page from GET
                $this->_perPage = (int)$params['per_page'];
            }

            if (isset($params['page']) && is_numeric($params['page']) && $params['page'] > 1) {
                // Get page from GET
                $this->_page = (int)$params['page'];
            }
            $toSkip = ($this->_page * $this->_perPage) - $this->_perPage;

            // A the end set pagination
            $this->_totalItems = $builder->getCountForPagination();
            $builder = $builder->skip($toSkip)->take($this->_perPage);
        }
        /*
         * END pagination
         */

        /*
         * START sort
         */
        if ($this->_validSort($params)) {
            // If sort configuration is valid then set it
            $this->sort($params['order_by'], strtoupper($params['order_dir']));
        }

        // Set sort configuration
        $sort = $this->getSort();
        if ($sort['index'] && $sort['dir']) {
            $builder = $builder->orderBy($sort['index'], $sort['dir']);
        }
        /*
         * END sort
         */

        $this->_builder = $builder;
    }

    /**
     * Checks if provided order configuration (from GET) is valid
     *
     * @param array $params
     * @return bool
     */
    private function _validSort(array $params)
    {
        if (!isset($params['order_by']) || !isset($params['order_dir'])) {
            return false;
        }

        if (!in_array(strtoupper($params['order_dir']), ['ASC', 'DESC'])) {
            return false;
        }

        foreach ($this->_columns as $data) {
            $column = new Column($data);
            if ($column->index() == $params['order_by']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns Paginator Presenter object if pagination is enabled
     *
     * @param array $items
     * @return mixed
     */
    private function _getPaginatorPresenter(array $items)
    {
        if (!$this->_paginate) {
            return null;
        }

        $params = Input::get();
        if (!empty($params)) {
            // There are parameters in the URL - pass them to paginator
            if (isset($params['page'])) {
                // We don't need that - paginator will add new one
                unset($params['page']);
            }
        }

        // Prepare paginator and pass it to presenter
        $paginator = new LengthAwarePaginator($items, $this->_totalItems, $this->_perPage, $this->_page);
        $paginator->appends($params);
        return new BootstrapThreePresenter($paginator);
    }
}