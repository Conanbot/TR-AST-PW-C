{{--
    Reusable filter panel component.
    Usage:
        <x-filter-panel :action="route('admin.orders.index')">
            ... fields ...
            <x-slot name="buttons">
                <button class="fp-btn-primary" type="submit">Filter</button>
                @if(request()->anyFilled(...))
                    <a href="..." class="fp-btn-reset">Reset</a>
                @endif
            </x-slot>
        </x-filter-panel>
--}}
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
