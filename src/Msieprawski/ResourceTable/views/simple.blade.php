@if ($collection_generator->renderFilterForm())
<form method="GET" action="{{ $table->filterFormAction() }}">
<div style="width:100%">
    <a href="{{ $collection_generator->resetFormUrl() }}"><button type="button">{{ trans('resource-table::default.Reset_form') }}</button></a>
    <button type="submit">{{ trans('resource-table::default.Search') }}</button>
</div>
@endif
<table>
    {!! $table->head() !!}
    {!! $table->body() !!}
</table>
@if ($collection_generator->renderFilterForm())
</form>
@endif
@if ($paginator)
{!! $paginator->render() !!}
@endif