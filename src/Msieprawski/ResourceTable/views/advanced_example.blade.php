@if ($collection_generator->renderFilterForm())
<form method="GET" action="{{ $table->filterFormAction() }}">
<div class="row">
    <div class="pull-right">
        <div class="btn-group" role="group">
            <a href="{{ $collection_generator->resetFormUrl() }}" class="btn btn-default">{{ trans('resource-table::default.Reset_form') }}</a>
            <button type="submit" class="btn btn-success">{{ trans('resource-table::default.Search') }}</button>
        </div>
    </div>
</div>
@endif

<table class="table table-striped table-bordered">
    <thead>
    <tr>
        @foreach ($columns as $column)
        <th style="background-color:{{ $column->sortActive() ? 'yellow' : 'white' }}">
            {{ $column->label() }}
            @if ($column->sortable())
                <a href="{{ $column->sortUrl() }}">
                    {{ $column->sortDirection() === 'DESC' ? '&#8595;' : '&#8593;' }}
                </a>
            @endif
        </th>
        @endforeach
    </tr>
    @if ($collection_generator->renderFilterForm())
    <tr>
        @foreach ($columns as $column)
            @if (!$column->searchable())
            <td></td>
            @else
            <td>{!! $column->searchableContent() !!}</td>
            @endif
        @endforeach
    </tr>
    @endif
    </thead>
    <tbody>
    @if (empty($collection))
        <tr><td colspan="{{ count($columns) }}">{{ trans('resource-table::default.No_records') }}</td></tr>
    @else
        @foreach ($collection as $row)
        <tr>
            @foreach ($columns as $column)
                <td>
                    @if (!$column->hasRenderer())
                        {{ $row->{$column->index()} }}
                    @else
                        {!! $column->renderer($row) !!}
                    @endif
                </td>
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