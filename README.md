# Resource Table
## About
This module has been created as a alternative for DataTable. It doesn't use AJAX or any JavaScript. It's very light and scalable. I'll do my best to develop it all the time because I'll be using it on my projects.

## TO DO
 - pagination renderer
 - more table layouts *(bootstrap layout for example)*
 - searchable columns
 
## Compatibility
Currently package is compatible with Laravel 5

## Feature overview
 - supporting Eloquent ORM and Fluent Query Builder
 - ability to join tables and sort results by joined columns
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

## License
Licensed under the MIT License