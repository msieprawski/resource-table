<table>
    {!! $table->head() !!}
    {!! $table->body() !!}
</table>
@if ($paginator)
{!! $paginator->render() !!}
@endif