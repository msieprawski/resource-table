<?php namespace Msieprawski\ResourceTable\Helpers;

use Input;
use Request;
use Msieprawski\ResourceTable\ResourceTable;

/**
 * An representative object of table column
 *
 * @package Msieprawski\ResourceTable
 */
class Column
{
    /**
     * Column data provided by user with addColumn method
     *
     * @var array
     */
    private $_data;

    /**
     * View name
     *
     * @var string
     */
    private $_viewName = '';

    /**
     * Sets column data provided by user with addColumn method
     *
     * @param array $data
     * @param string $viewName - used when calling content method
     */
    public function __construct(array $data, $viewName = '')
    {
        $this->_data = $data;
        $this->_viewName = $viewName;
    }

    /**
     * Returns column's database name
     *
     * @return null|string
     */
    public function getDatabaseName()
    {
        if (isset($this->_data['filter_column']) && is_string($this->_data['filter_column'])) {
            return $this->_data['filter_column'];
        }
        return $this->index();
    }

    /**
     * Returns column label - to be used by view when rendering table
     *
     * @return string
     */
    public function label()
    {
        return $this->_data['label'];
    }

    /**
     * Returns column index
     *
     * @return null|string
     */
    public function index()
    {
        return $this->_data['index'];
    }

    /**
     * Returns renderer result (if exists) or null when not defined
     *
     * @param stdClass $row
     * @return Closure|null
     */
    public function renderer($row)
    {
        return isset($this->_data['renderer']) ? $this->_data['renderer']($row) : null;
    }

    /**
     * Checks whether column has a defined renderer
     *
     * @return bool
     */
    public function hasRenderer()
    {
        return isset($this->_data['renderer']);
    }

    /**
     * Checks whether column is sortable
     *
     * @return bool
     */
    public function sortable()
    {
        return isset($this->_data['sortable']) && $this->_data['sortable'];
    }

    /**
     * Returns column sort link
     *
     * @return string
     */
    public function sortUrl()
    {
        $url = Request::url();
        $params = Input::get();

        $params['order_by'] = $this->index();
        if ($this->sortActive()) {
            $params['order_dir'] = mb_strtolower($this->_sortDirection()) == 'desc' ? 'ASC' : 'DESC';
        } else {
            $params['order_dir'] = ResourceTable::DEFAULT_SORT_DIR;
        }

        return $url.'?'.http_build_query($params);
    }

    /**
     * Checks whether column is active by current sort index
     *
     * @return bool
     */
    public function sortActive()
    {
        $sortIndex = $this->_sortIndex();
        if ($sortIndex === null) {
            return false;
        }
        return $sortIndex == $this->index();
    }

    /**
     * Returns current column's sort direction
     *
     * @return string
     */
    public function sortDirection()
    {
        if (!$this->sortActive()) {
            // Column is not active - return default sort direction
            return ResourceTable::DEFAULT_SORT_DIR;
        }
        return $this->_sortDirection();
    }

    /**
     * Returns column content (depends on column configuration)
     *
     * @param null|stdClass|array $row
     * @return string
     */
    public function content($row = null)
    {
        if (null === $row) {
            // Generate content for table head column

            $result = $this->label();
            if ($this->sortable()) {
                // Column is sortable - get anchor HTML
                $result .= $this->_getSortAnchor();
            }
            return $result;
        }

        if (!$this->hasRenderer()) {
            return (string)$row->{$this->index()};
        }

        return (string)$this->renderer($row);
    }

    /**
     * Checks if column is searchable
     *
     * @return bool
     */
    public function searchable()
    {
        return isset($this->_data['searchable']) && $this->_data['searchable'];
    }

    /**
     * Returns column search type (if valid and provided)
     *
     * @return string
     */
    public function searchType()
    {
        return isset($this->_data['type']) && ResourceTable::validColumnType($this->_data['type']) ? $this->_data['type'] : ResourceTable::DEFAULT_COLUMN_TYPE;
    }

    /**
     * Returns column array with options for searchable form
     *
     * @return array
     */
    public function options()
    {
        if (!isset($this->_data['options']) || (isset($this->_data['options']) && empty($this->_data['options']))) {
            // No options provided
            return [];
        }
        if (!is_array($this->_data['options'])) {
            // An empty array?
            return [];
        }

        return ([ResourceTable::ALL_SELECT_VALUES_KEY => trans('resource-table::default.All')] + $this->_data['options']);
    }

    /**
     * Returns HTML with column search field
     *
     * @param array $config
     * @return string
     */
    public function searchableContent(array $config = array())
    {
        $result = '';
        $type = $this->searchType();

        /*
         * Plain Bootstrap with glyphicons
         */
        if ('resource-table::bootstrap' === $this->_viewName) {
            switch ($type) {

                // Select
                case 'select':
                    if (!count($this->options())) {
                        // Options must be provided for select
                        return '';
                    }
                    $result .= '<select name="resource_table_'.$this->index().'" class="'.array_get($config, 'control_class', 'form-control input-sm').' resource-table-column-filter">'.$this->_optionsHTML().'</select>';
                    break;

                // Simple string input
                case 'string':
                default:
                    $result .= '<input type="text" placeholder="'.array_get($config, 'placeholder', trans('resource-table::default.Search_for').' '.$this->label()).'" class="'.array_get($config, 'control_class', 'form-control input-sm').' resource-table-column-filter" name="resource_table_'.$this->index().'" value="'.ResourceTable::getSearchValue($this->index()).'" />';
                    break;
            }
        }

        /*
         * Just simple table
         */
        if (!$result) {
            switch ($type) {

                // Select
                case 'select':
                    if (!count($this->options())) {
                        // Options must be provided for select
                        return '';
                    }
                    $result .= '<select name="resource_table_'.$this->index().'" class="'.array_get($config, 'control_class').' resource-table-column-filter">'.$this->_optionsHTML().'</select>';
                    break;

                // Simple string input
                case 'string':
                default:
                    $result .= '<input type="text" class="'.array_get($config, 'control_class').' resource-table-column-filter" name="resource_table_'.$this->index().'" value="'.ResourceTable::getSearchValue($this->index()).'" placeholder="'.array_get($config, 'placeholder').'" />';
                    break;
            }
        }

        return $result;
    }

    /**
     * Returns HTML with select options for searchable column
     *
     * @return string
     */
    private function _optionsHTML()
    {
        $result = '';
        foreach ($this->options() as $key => $label) {
            $selected = ResourceTable::getSearchValue($this->index()) == $key ? ' selected="selected"' : '';
            $result .= '<option value="'.$key.'"'.$selected.'>'.$label.'</option>';
        }
        return $result;
    }

    /**
     * Returns column sort anchor (result depends on view name)
     *
     * @return string
     */
    private function _getSortAnchor()
    {
        $result = '';

        /*
         * Plain Bootstrap with glyphicons
         */
        if ('resource-table::bootstrap' === $this->_viewName) {
            $result .= '<a href="'.$this->sortUrl().'" class="pull-right">';
            $glyphicon = 'glyphicon-sort';
            if ($this->sortActive()) {
                // Column is active - we can show proper icon
                $glyphicon = $this->sortDirection() === 'DESC' ? 'glyphicon-sort-by-attributes' : 'glyphicon-sort-by-attributes-alt';
            }
            $result .= '<i class="glyphicon '.$glyphicon.'"></i>';
            $result .= '</a>';
        }

        /*
         * Just simple table
         */
        if (!$result) {
            $result .= '<a href="'.$this->sortUrl().'" style="font-weight:'.($this->sortActive() ? 'bold' : 'normal').'">';
            $result .= $this->sortDirection() === 'DESC' ? '&#8595;' : '&#8593;';
            $result .= '</a>';
        }

        return $result;
    }

    /**
     * Returns current sort index (order_by)
     *
     * @return string|null
     */
    private function _sortIndex()
    {
        $collection = ResourceTable::collection();
        $sort = $collection->getSort();

        return $sort['index'] ? $sort['index'] : null;
    }

    /**
     * Returns current sort direction (order_dir)
     * Returns default direction if not defined
     *
     * @return string
     */
    private function _sortDirection()
    {
        $collection = ResourceTable::collection();
        $sort = $collection->getSort();

        return $sort['dir'] ? $sort['dir'] : ResourceTable::DEFAULT_SORT_DIR;
    }
}