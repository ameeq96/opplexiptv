@php
  $rtl = $isRtl ?? false;
  $items = is_iterable($products ?? []) ? $products : [];
  $carouselId = $id ?? 'productCarousel';
@endphp

<div id="{{ $carouselId }}" class="pcarousel" dir="{{ $rtl ? 'rtl' : 'ltr' }}">

  <div class="pcarousel__track">
    @foreach ($items as $p)
      @php
        $name = $p['name'] ?? '';
        $asin = $p['asin'] ?? '';
        $link = $p['link'] ?? '#';
        $image = $p['image'] ?? '';
      @endphp
      <div class="pcarousel__item">
        <article class="product-card" aria-label="{{ $name }}">
          <a href="{{ $link }}" target="_blank" rel="nofollow sponsored noopener" title="{{ $name }}" style="display:block; position:relative;">
            <img class="product-card__image" src="{{ asset('images/shop/' . $image) }}" alt="{{ $name }}" loading="lazy" decoding="async" width="466" height="350">
            <span class="product-card__badge" aria-hidden="true">Amazon</span>
            <span class="product-card__cta" aria-hidden="true">View</span>
          </a>
          <div class="product-card__body">
            <h3 class="product-card__title">{{ \Illuminate\Support\Str::limit($name, 120) }}</h3>
            @if($asin)
              <div class="product-card__meta">ASIN: {{ $asin }}</div>
            @endif
            <a href="{{ $link }}" target="_blank" rel="nofollow sponsored noopener" class="product-card__btn">Buy on Amazon</a>
          </div>
        </article>
      </div>
    @endforeach
  </div>
</div>

<script>
  (function(){
    var root = document.getElementById(@json($carouselId));
    if(!root) return;
    var track = root.querySelector('.pcarousel__track');
    if(!track) return;

    function step(){ return Math.max(track.clientWidth * 0.9, 280); }
    function isRTL(){ return root.getAttribute('dir') === 'rtl'; }
    function bounds(){
      var min = Math.min(0, track.scrollWidth - track.clientWidth);
      var max = Math.max(0, track.scrollWidth - track.clientWidth);
      return { min: min, max: max };
    }

    var timer;
    function tick(){
      var dir = isRTL() ? -1 : 1;
      var dx = step() * dir;
      var b = bounds();

      // If at the boundary for the current direction, wrap and continue immediately
      if (dir > 0) { // LTR moving towards max
        if (track.scrollLeft >= b.max - 2) {
          track.scrollTo({ left: 0, behavior: 'auto' });
          requestAnimationFrame(function(){ track.scrollBy({ left: dx, behavior: 'smooth' }); });
          return;
        }
      } else { // RTL moving towards min
        if (track.scrollLeft <= b.min + 2) {
          track.scrollTo({ left: b.max, behavior: 'auto' });
          requestAnimationFrame(function(){ track.scrollBy({ left: dx, behavior: 'smooth' }); });
          return;
        }
      }

      track.scrollBy({ left: dx, behavior: 'smooth' });
    }
    function start(){ if(!timer) timer = setInterval(tick, 4000); }
    start();
  })();
</script>
