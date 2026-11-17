@props([
    'src' => null,
    'alt' => 'Menu Geprek GLO',
    'ratio' => '4-3',
    'class' => '',
])

<div class="product-image-box {{ $class }}">
    @if($src && \Illuminate\Support\Facades\Storage::disk('public')->exists($src))
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($src) }}" 
             alt="{{ $alt }}" 
             loading="lazy" 
             width="400" 
             height="300">
    @else
        <div class="placeholder-content">
            <svg class="placeholder-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="placeholder-text">Foto belum tersedia</span>
        </div>
    @endif
</div>
