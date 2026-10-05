<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">{{ $title }}</h4>
            @isset($subtitle)
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ {{ $subtitle }}</span>
            @endisset
        </div>
    </div>
    @isset($action)
        <div class="page-header-action">{!! $action !!}</div>
    @endisset
</div>
