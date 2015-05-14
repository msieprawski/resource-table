# Resource Table
## About
This Laravel package has been created as a alternative for DataTable. It doesn't use AJAX or any JavaScript. It's very light and scalable. Use it for generating table with data without paying attention to searching/sorting/paginating results. It'll do it for yourself! I'll do my best to develop it all the time because I'll be using it on my projects. 

## TO DO
 - searchable columns
 - custom pagination layouts *(not available in Laravel 5 but it'll be using this package)*
 - add some tests
 
## Compatibility
Currently package is compatible with Laravel 5

## Feature overview
 - supporting Eloquent ORM and Fluent Query Builder
 - ability to join tables and sort results by joined columns
 - uses built in Laravel's paginator
 - more coming...
 
## Installation
Add the following to your `composer.json` file:

```json
"msieprawski/resource-table": "*"
```

Then register this service provider with Laravel:

```php
'Msieprawski\ResourceTable\ResourceTableServiceProvider',
```

and add class alias for easy usage
```php
'ResourceTable' => 'Msieprawski\ResourceTable\ResourceTable',
```

Don't forget to use ``composer update`` and ``composer dump-autoload`` when is needed!

## Usage
All you need to do is creating a `ResourceTable` with prepared builder object. Then add columns and call `make()`. That's it!

## Examples
### Example 1: Simple use
```php
$news = DB::table('news')
    ->select(['news.id', 'news.subject']);

echo ResourceTable::of($news)
    ->addColumn([
        'index' => 'id',
        'label' => 'ID',
        'sortable' => true,
    ])
    ->addColumn([
        'index' => 'subject',
        'label' => 'Subject',
        'sortable' => true,
    ])
    ->make();
```

### Example 2: Adding columns
```php
$news = DB::table('news')
    ->select(['news.id', 'news.subject']);

echo ResourceTable::of($news)
    ->addColumn([
        'index' => 'id',
        'label' => 'ID',
        'sortable' => true,
    ])
    ->addColumn([
        'index' => 'subject',
        'label' => 'Subject',
        'sortable' => true,
    ])
    ->addColumn([
        'index' => 'operations',
        'label' => 'Operations',
        'sortable' => false,
        'renderer' => function ($row) {
            return '<a href="'.url('news/'.$row->id.'/delete').'">Delete</a>';
        }
    ])
    ->make();
```

### Example 3: Joining tables
```php
$news = DB::table('news')
    ->select(['news.id', 'news.subject', 'categories.name AS category_name'])
    ->leftJoin('categories', 'news.category_id', '=', 'categories.id');

return ResourceTable::of($news)
    ->addColumn([
        'index' => 'id',
        'label' => 'ID',
        'sortable' => true,
    ])
    ->addColumn([
        'index' => 'category_name',
        'label' => 'Category',
        'sortable' => true,
    ])
    ->addColumn([
        'index' => 'subject',
        'label' => 'Subject',
        'sortable' => true,
    ])
    ->addColumn([
        'index' => 'operations',
        'label' => 'Operations',
        'sortable' => false,
        'renderer' => function ($row) {
            return '<a href="'.url('news/'.$row->id.'/delete').'">Delete</a>';
        }
    ])
    ->make();
```

### Example 4: Set custom conditions
```php
$news = DB::table('news')
    ->select(['news.id', 'news.subject']);

echo ResourceTable::of($news)
    ->addColumn([
        'index' => 'id',
        'label' => 'ID',
        'sortable' => true,
    ])
    ->addColumn([
        'index' => 'subject',
        'label' => 'Subject',
        'sortable' => true,
    ])
    ->perPage(20)
    ->page(2)
    ->orderBy('id', 'DESC')
    ->make();
```

Where `perPage(20)` sets resources per page. Method `page(2)` sets current page. Method `orderBy('id', 'DESC')` sets default sorting.

### Example 5: Setting up the searchable columns
Let's say your news can be `event` or `hot_topic` type.

```php
$news = DB::table('news')
    ->select(['news.id', 'news.subject', 'news.type']);

echo ResourceTable::of($news)
    ->addColumn([
        'index' => 'id',
        'label' => 'ID',
        'sortable' => true,
    ])
    ->addColumn([
        'index' => 'subject',
        'label' => 'Subject',
        'sortable' => true,
        'searchable' => true,
    ])
    ->addColumn([
        'index' => 'type',
        'label' => 'Type',
        'sortable' => true,
        'searchable' => true,
        'type' => 'select',
        'options' => [
            'event' => 'Event',
            'hot_topic' => 'Hot topic',
        ]
    ])
    ->make();
```

Resource Table will generate a `thead` tag with two rows. First will contain standard `th` columns but the second one will contain text inputs or select fields *(depends on column configuration)*.
Current Resource Table version supports following column types:
 - string - script will be looking for value matching pattern `index LIKE '%value%'`
 - select - script will be looking for value matching pattern `index = 'value'`
 
#### Note
Resource Table will automatically inject All option with `_all` for your all select type columns.

## Templating
Resource Table allows you to create your own templates! However if you don't need to use own templates, then you are free to use one of the following built-in views:
 - `simple` *(default)*
 - `bootstrap` *(supported by Bootstrap 3)*
 - `advanced_example` - it's just a advanced template example, you can use it as a blueprint of your own one!

### Using built-in table templates
If you want to use core template just call `view()` method on your `ResourceTable` object:
```php
$collection = ResourceTable::of($news)
    ->addColumn...
    ...
    ->view('bootstrap');
```

### Creating custom table template
Create your own blade view file, name it as you want. For this example I named my file `my_table.blade.php` under `tables` directory.
Let's say that I need to put custom attribute on each `<tr>` node in `<tbody>`:
```html
@if ($collection_generator->renderFilterForm())
<form method="GET" action="{{ $table->filterFormAction() }}">
<div class="resource-table-buttons">
    <a href="{{ $collection_generator->resetFormUrl() }}" class="btn btn-default">Reset form</a>
    <button type="submit" class="btn btn-success">Search</button>
</div>
@endif

<table class="my-resource-table">
    {!! $table->head() !!}
    <tbody>
    @if (empty($collection))
        <tr><td colspan="{{ count($columns) }}">No records found.</td></tr>
    @else
        @foreach ($collection as $row)
            
            <!-- Here is my custom attribute -->
            <tr data-id="{{ $row->id }}">
            
                @foreach ($columns as $column)
                <td>{!! $column->content($row) !!}</td>
                @endforeach
            </tr>
        @endforeach
    @endif
    </tbody>
</table>

@if ($collection_generator->renderFilterForm())
</form>
@endif

@if ($paginator)
{!! $paginator->render() !!}
@endif
```

In every view template you are free to use following variables:
 - `$collection` - an array with all results *(literally array with arrays)*
 - `$columns` - an array with table columns objects (see `Msieprawski\ResourceTable\Helpers\Column` for available methods)
 - `$paginator` - an Laravel's built-in pagination presenter (for now it's a `Illuminate\Pagination\BootstrapThreePresenter`)
 - `$table` - table generator object (see `Msieprawski\ResourceTable\Generators\Table` for available methods)
 - `$collection_generator` - collection generator object (see `Msieprawski\ResourceTable\Generators\Collection` for available methods)
 
At the end just tell your `ResourceTable` object to use your custom template:
```php
$collection = ResourceTable::of($news)
    ->addColumn...
    ...
    ->customView('tables.my_table');
```

## License
Licensed under the MIT License