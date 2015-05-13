<?php namespace Msieprawski\ResourceTable\Generators;

use Msieprawski\ResourceTable\Helpers\Column;
use Msieprawski\ResourceTable\Exceptions\TableException;

/**
 * Table object to represent a table :)
 *
 * @ver 0.1
 * @package Msieprawski\ResourceTable
 */
class Table
{
    /**
     * Table configuration (per_page, columns, paginate...)
     *
     * @var array
     */
    private $_config;

    /**
     * Resources collection (result of builder->get() method)
     *
     * @var array
     */
    private $_collection;

    /**
     * Sets collection data and config (per_page, columns, paginate...)
     *
     * @param array $collection
     * @param array $config
     */
    public function __construct(array $collection, array $config)
    {
        $this->_collection = $collection;
        $this->_config = $config;
    }

    /**
     * Returns view object with table
     *
     * @return \Illuminate\View\View
     */
    public function make()
    {
        return view('resource-table::table', $this->_dataForView());
    }

    /**
     * Returns array with Column objects
     *
     * @return array
     */
    private function _getColumns()
    {
        $columns = [];
        foreach ($this->_config['columns'] as $data) {
            $columns[] = new Column($data);
        }
        return $columns;
    }

    /**
     * Returns array with view data
     *
     * @return array
     */
    private function _dataForView()
    {
        return [
            'columns'    => $this->_getColumns(),
            'collection' => $this->_collection,
            'paginator'  => $this->_config['paginator_presenter'],
        ];
    }
}