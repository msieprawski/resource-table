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
    </thead>
    <tbody>
        @if (empty($collection))
        <tr><td colspan="{{ count($columns) }}">No records found.</td></tr>
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
@if ($paginator)
{!! $paginator->render() !!}
@endif