<?php namespace Msieprawski\ResourceTable\Generators;

use Input;
use Request;
use Msieprawski\ResourceTable\Exceptions\CollectionException;
use Msieprawski\ResourceTable\Helpers\Column;
use Msieprawski\ResourceTable\ResourceTable;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Collection object which is representing given $builder's collection
 *
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
    private $_filter = ResourceTable::DEFAULT_FILTER;

    /**
     * Pagination presenter object name
     *
     * @var string
     */
    private $_paginationPresenter = ResourceTable::DEFAULT_PAGINATION_PRESENTER;

    /**
     * An extra (custom) data for table view
     *
     * @var array
     */
    private $_extraViewData = [];

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
     * Sets $_paginationPresenter object name
     *
     * @param string $presenter
     * @return $this
     */
    public function setPaginationPresenter($presenter)
    {
        $this->_paginationPresenter = $presenter;
        return $this;
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
            'collection_generator' => $this,
            'columns'              => $this->_columns,
            'per_page'             => $this->_perPage,
            'paginate'             => $this->_paginate,
            'paginator_presenter'  => $this->_getPaginatorPresenter($items),
            'view_name'            => $this->_viewName,
            'filter'               => $this->_filter,
            'extra'                => $this->_extraViewData,
        ]))->make();
    }

    /**
     * Checks if filter form should be generated
     *
     * @return bool
     */
    public function renderFilterForm()
    {
        if (!$this->_filter) {
            // Disabled
            return false;
        }

        foreach ($this->_columns as $columnData) {
            $column = new Column($columnData);
            if ($column->searchable()) {
                // At least one column is searchable - form should be generated
                return true;
            }
        }

        return false;
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
     * Sets extra (custom) view data
     *
     * @param array $data
     * @return $this
     */
    public function setExtraViewData(array $data)
    {
        $this->_extraViewData = $data;
        return $this;
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
     * Sets true if filter should be rendered
     *
     * @param bool $enabled
     * @return $this
     * @throws CollectionException
     */
    public function filter($enabled)
    {
        if (!is_bool($enabled)) {
            throw new CollectionException('Filter must be enabled or disabled. Bool is required.');
        }

        $this->_filter = $enabled;
        return $this;
    }

    /**
     * Returns URL to reset search form
     *
     * @return string
     */
    public function resetFormUrl()
    {
        $params = Input::get();

        $fieldsToReset = [];
        foreach ($this->_columns as $columnData) {
            // Add each column to reset
            $column = new Column($columnData);
            $fieldsToReset[] = 'resource_table_'.$column->index();
        }

        // Now remove all fields to reset from GET query
        foreach ($fieldsToReset as $fieldName) {
            if (isset($params[$fieldName])) {
                unset($params[$fieldName]);
            }
        }

        return Request::url().'?'.http_build_query($params);
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
            return 'Renderer function must be instance of Closure object.';
        }

        if (isset($data['filter']) && !$data['filter'] instanceof \Closure) {
            return 'Filter function must be instance of Closure object.';
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
         * START filters
         */
        if ($this->renderFilterForm()) {
            foreach ($this->_columns as $columnData) {
                $column = new Column($columnData);
                if (!$column->searchable()) {
                    // Not a searchable column - skip it
                    continue;
                }

                if (!isset($params['resource_table_'.$column->index()])) {
                    // Searched string not found in GET query
                    continue;
                }

                $value = $params['resource_table_'.$column->index()];
                if (!$value || !is_string($value)) {
                    // Skip empty values
                    continue;
                }

                $value = $this->_prepareFilterValue($columnData, $value);
                switch ($column->searchType()) {

                    // Use simple WHERE = 'value' for selects
                    case 'select':
                        if (ResourceTable::ALL_SELECT_VALUES_KEY == $value) {
                            // Any value in select - skip it
                            continue;
                        }
                        $builder = $builder->where($column->getDatabaseName(), '=', $value);
                        break;

                    // Use LIKE '%value' for strings
                    case 'string':
                    default:
                        $builder = $builder->where($column->getDatabaseName(), 'LIKE', '%'.$value.'%');
                        break;
                }
            }
        }
        /*
         * END filters
         */

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
     * Basing on column configuration prepares given field value
     * If column configuration has filter function - use it to prepare value
     *
     * @param array $column
     * @param mixed $value
     * @return mixed
     */
    private function _prepareFilterValue(array $column, $value)
    {
        if (!isset($column['filter'])) {
            // There is no filter function so we'll search for plain value
            return $value;
        }

        // Filter function found - lets use it!
        return $column['filter']($value);
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
        $paginator = new LengthAwarePaginator($items, $this->_totalItems, $this->_perPage, $this->_page, [
            'path' => Request::url(),
        ]);
        $paginator->appends($params);
        return new $this->_paginationPresenter($paginator);
    }
}