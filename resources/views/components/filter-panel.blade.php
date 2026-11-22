@props(['action' => null])

<form
    method="GET"
    @if($action) action="{{ $action }}" @endif
    class="filter-panel"
    role="search"
>
    <div class="fp-fields">
        {{ $slot }}
    </div>
    @isset($buttons)
        <div class="fp-actions">
            {{ $buttons }}
        </div>
    @endisset
</form>
