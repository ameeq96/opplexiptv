@php
  $rtl = $isRtl ?? false;
  $items = is_iterable($products ?? []) ? $products : [];
@endphp


<div class="product-grid" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
  <div class="product-grid__list">
    @forelse ($items as $p)
      @php
        $name = $p['name'] ?? '';
        $displayName = trim((string) preg_replace('/^\s*[\(\[][^\)\]]*[\)\]]\s*/', '', (string) $name));
        if ($displayName === '') {
          $displayName = $name;
        }
        $asin = $p['asin'] ?? '';
        $link = $p['link'] ?? '#';
        $image = $p['image'] ?? '';
        $altText = trim((string) \Illuminate\Support\Str::limit($displayName, 90, '')) ?: 'Shop product';
      @endphp
      <article class="product-card" aria-label="{{ $displayName }}">
        <a href="{{ $link }}" target="_blank" rel="nofollow sponsored noopener" title="{{ $displayName }}" style="display:block; position:relative;">
          <img class="product-card__image" src="{{ asset('images/shop/' . $image) }}" alt="{{ $altText }}" loading="lazy">
          <span class="product-card__badge" aria-hidden="true">Amazon</span>
          <span class="product-card__cta" aria-hidden="true">View</span>
        </a>
        <div class="product-card__body">
          <h3 class="product-card__title">{{ \Illuminate\Support\Str::limit($displayName, 120) }}</h3>
          @if($asin)
            <div class="product-card__meta">ASIN: {{ $asin }}</div>
          @endif
          <a href="{{ $link }}" target="_blank" rel="nofollow sponsored noopener" class="product-card__btn">Buy on Amazon</a>
        </div>
      </article>
    @empty
      <div style="grid-column: 1 / -1; text-align:center; color:#666;">No products yet.</div>
    @endforelse
  </div>
</div>
