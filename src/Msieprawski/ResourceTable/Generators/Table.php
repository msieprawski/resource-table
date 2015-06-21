<?php namespace Msieprawski\ResourceTable\Generators;

use Input;
use Request;
use Msieprawski\ResourceTable\Helpers\Column;
use Msieprawski\ResourceTable\Exceptions\TableException;

/**
 * Table object to represent a table :)
 *
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
        return view($this->_config['view_name'], $this->_dataForView());
    }

    /**
     * Returns table head
     *
     * @return string
     */
    public function head()
    {
        $head = '';
        $head .= '<thead>';
        $head .= '<tr class="resource-table-headings">';
        foreach ($this->_getColumns() as $column) {
            $head .= '<th class="'.($column->sortActive() ? 'warning' : '').'">';
            $head .= $column->content();
            $head .= '</th>';
        }
        $head .= '</tr>';

        if ($this->_config['filter']) {
            // Render row with search inputs
            $head .= '<tr class="resource-table-filter">';

            foreach ($this->_getColumns() as $column) {
                // Render each column

                if (!$column->searchable()) {
                    // Column is not searchable - do not print form elements
                    $head .= '<td></td>';
                    continue;
                }

                $head .= '<td>'.$column->searchableContent().'</td>';
            }

            $head .= '</tr>';
        }

        $head .= '</thead>';
        return $head;
    }

    /**
     * Returns table body
     *
     * @return string
     */
    public function body()
    {
        $columns = $this->_getColumns();

        $body = '';
        $body .= '<tbody>';
        if (empty($this->_collection)) {
            // No results if collection is empty
            $body .= '<tr><td colspan="'.count($columns).'">'.trans('resource-table::default.No_records').'</td></tr>';
        } else {
            foreach ($this->_collection as $row) {
                // Generate each row

                $body .= '<tr>';
                foreach ($columns as $column) {
                    // Generate each column

                    $body .= '<td>';
                    $body .= $column->content($row);
                    $body .= '</td>';
                }
                $body .= '</tr>';
            }
        }
        $body .= '</tbody>';

        return $body;
    }

    /**
     * Returns filter form URL
     *
     * @return string
     */
    public function filterFormAction()
    {
        return Request::url().'?'.http_build_query(Input::query());
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
            $columns[] = new Column($data, $this->_config['view_name']);
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
            'collection_generator' => $this->_config['collection_generator'],
            'columns'              => $this->_getColumns(),
            'collection'           => $this->_collection,
            'paginator'            => $this->_config['paginator_presenter'],
            'table'                => $this,
            'extra'                => $this->_config['extra'],
        ];
    }
}