<table class="table table-bordered table-striped">
    {!! $table->head() !!}
    {!! $table->body() !!}
</table>
@if ($paginator)
{!! $paginator->render() !!}
@endif