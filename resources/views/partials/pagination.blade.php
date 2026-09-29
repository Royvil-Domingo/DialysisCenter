@if ($paginator->total())
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 no-print">
        <small class="text-muted">Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }}</small>
        {{ $paginator->links('pagination::bootstrap-5') }}
    </div>
@endif