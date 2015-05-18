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
<div class="row" style="margin-top:10px">
    <table class="table table-bordered table-striped">
        {!! $table->head() !!}
        {!! $table->body() !!}
    </table>
</div>
@if ($collection_generator->renderFilterForm())
</form>
@endif
@if ($paginator)
{!! $paginator->render() !!}
@endif