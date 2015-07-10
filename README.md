# Resource Table
## About
This Laravel package has been created as a alternative for DataTable. It doesn't use AJAX or any JavaScript. It's very light and scalable. Use it for generating table with data without paying attention to searching/sorting/paginating results. It'll do it for yourself! I'll do my best to develop it all the time because I'll be using it on my projects. 

## TO DO
 - default value for searchable columns
 - more searchable columns types *(date, datetime, range)*
 - add some tests
 
## Compatibility
Currently package is compatible with Laravel 5

## Feature overview
 - supporting Eloquent ORM and Fluent Query Builder
 - ability to join tables and sort results by joined columns
 - searchable columns - select or text fields!
 - supporting filter callbacks
 - custom pagination layouts *(called presenters in Laravel 5)*
 - translations
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
    ->paginate(true)
    ->sort('id', 'DESC')
    ->filter(true)
    ->customView('my.custom.view.name')
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

### Example 6: Custom filter logic

```php
$news = DB::table('news')
    ->select(['news.subject']);

echo ResourceTable::of($news)
    ->addColumn([
        'index' => 'subject',
        'label' => 'Subject',
        'sortable' => true,
        'searchable' => true,
        'filter' => function($value) {
            // Do whatever you want with given value!
            return trim(mb_strtolower(($value));
        },
        
        // You can specify column name to search
        'filter_column' => 'subject_alias',
    ])
    ->make();
```

Resource Table will generate a `thead` tag with two rows. First will contain standard `th` columns but the second one will contain text inputs or select fields *(depends on column configuration)*.
Currently Resource Table version supports following column types:
 - string - script will be looking for value matching pattern `index LIKE '%value%'` *(used by default)*
 - select - script will be looking for value matching pattern `index = 'value'`
 
#### Note
Resource Table will automatically inject `All` option with `_all` key to your all select type columns.

## Creating pagination presenters
Let's say you don't want to use default built-in Bootstrap 3 pagination HTML structure for your pagination. With ResourceTable you can create your own one or use built-in `AdminLTEPresenter`. So if you'are using Admin LTE Admin Theme you don't have to worry about pagination HTML!
By default ResourceTable use Bootstrap 3 presenter which is default for Laravel 5.

### Using Admin LTE pagination presenter
```php
$collection = ResourceTable::of($news)
    ->addColumn...
    ...
    ->setPaginationPresenter('Msieprawski\ResourceTable\Presenters\AdminLTEPresenter');
```

#### Note
Remember to use full path to class!

### Creating your own pagination presenter
You can create your pagination presenter wherever you want but it's recommended to create `Presenters` directory under your `app` directory. For this example I created `MyCustomPresenter` under `app/Presenters` directory:
```php
<?php namespace App\Presenters;

use Msieprawski\ResourceTable\Presenters\DefaultPresenter;

class MyCustomPresenter extends DefaultPresenter
{
    protected function getAvailablePageWrapper($url, $page, $rel = null)
    {
        $rel = is_null($rel) ? '' : ' rel="'.$rel.'"';
        return '<li class="my-custom-class-here"><a href="'.htmlentities($url).'"'.$rel.'>'.$page.'</a></li>';
    }
}
```
All of your custom presenters must extends `DefaultPresenter` class. Feel free to see how it works *(it's strongly based on Laravel's BootstrapThreePresenter)*. Just copy method which is responsible for element that you want to customize and change it! That's it!

After creating your custom presenter - don't forget to set it in `ResourceTable`:
```php
$collection = ResourceTable::of($news)
    ->addColumn...
    ...
    ->setPaginationPresenter('App\Presenters\MyCustomPresenter');
```

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

### Auto-generated searchable content for column
Let's say you want to use `$column->searchableContent()` in your custom template but you want to add custom class for every input or placeholder. It's very easy:
```php
@foreach ($columns as $column)
    @if (!$column->searchable())
    <td></td>
    @else
    <td>{!! $column->searchableContent(['control_class' => 'form-control input-sm my-custom-class', 'placeholder' => 'Custom placeholder for '.$column->label()]) !!}</td>
    @endif
@endforeach
```

## Setting default configuration for each ResourceTable object
If you want to set `bootstrap` view for each ResourceTable object or you want to set 100 elements per page you can use ResourceTable available static functions:
  - `ResourceTable::setPaginationPresenter()` - set custom or buil-in pagination presenter object name
  - `ResourceTable::setView()` - use this if you want to use buil-in template
  - `ResourceTable::setCustomView()` - use this if you've created your own template
  - `ResourceTable::setPaginate()` - enable/disable pagination
  - `ResourceTable::setPerPage()` - how many results to display on page
  - `ResourceTable::setPage()` - hardcode specific page *(not sure why I've created this)*
  - `ResourceTable::setFilter()` - enable/disable results filter
You can call it within `boot` method in `AppServiceProvider` object:
```php
public function boot()
{
    ResourceTable::setView('bootstrap')
    ResourceTable::setPaginationPresenter('App\Presenters\MyCustomPresenter')
}
```

## Translations
ResourceTable has built-in polish and english translations. Please contact me if you've created translations for more languages - I'll be happy to share it with others!
If you want to use your own translations or override existing please follow Laravel's instructions available [here](http://laravel.com/docs/5.0/localization#overriding-package-language-files). Please use `resource-table` as package name and `default.php` as translations file.
```php
<?php

return [
    'No_records' => 'No records found.',
    'Search' => 'Search',
    'Reset_form' => 'Reset form',
    'All' => 'All',
    'Search_for' => 'Search for',
];
```

## License
Licensed under the MIT License