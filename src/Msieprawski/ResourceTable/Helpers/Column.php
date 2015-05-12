<?php namespace Msieprawski\ResourceTable\Helpers;

use Input;
use Request;
use Msieprawski\ResourceTable\ResourceTable;

/**
 * An representative object of table column
 *
 * @ver 0.1
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
     * Sets column data provided by user with addColumn method
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->_data = $data;
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