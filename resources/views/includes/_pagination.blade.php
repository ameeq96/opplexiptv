@php
  /** @var \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Contracts\Pagination\Paginator $paginator */
  $paginator = $paginator ?? null;
  $rtl = $isRtl ?? false;
@endphp

@if($paginator && method_exists($paginator, 'lastPage') && $paginator->hasPages())

  <div class="pg" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
    <nav class="pg__nav" role="navigation" aria-label="Pagination Navigation">
      @if ($paginator->onFirstPage())
        <span class="pg__btn" aria-disabled="true" aria-label="@lang('pagination.previous')">
          @if($rtl)
            <svg class="pg__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 6l6 6-6 6" stroke="#111" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          @else
            <svg class="pg__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 6l-6 6 6 6" stroke="#111" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          @endif
          @lang('pagination.previous')
        </span>
      @else
        <a class="pg__btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
          @if($rtl)
            <svg class="pg__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 6l6 6-6 6" stroke="#111" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          @else
            <svg class="pg__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 6l-6 6 6 6" stroke="#111" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          @endif
          @lang('pagination.previous')
        </a>
      @endif

      @php
        $last = (int) $paginator->lastPage();
        $current = (int) $paginator->currentPage();
        $window = 2; // pages around current

        // Build compact page list with ellipses
        $pages = [];
        for ($i = 1; $i <= $last; $i++) {
            if ($i === 1 || $i === $last || ($i >= $current - $window && $i <= $current + $window)) {
                $pages[] = $i;
            } else {
                // use -1 as ellipsis marker
                if (end($pages) !== -1) { $pages[] = -1; }
            }
        }
      @endphp

      <ul class="pg__list">
        @foreach ($pages as $page)
          @if ($page === -1)
            <li class="pg__ellipsis" aria-hidden="true">&hellip;</li>
          @elseif ($page === $paginator->currentPage())
            <li aria-current="page"><span class="pg__page pg__page--active">{{ $page }}</span></li>
          @else
            <li><a class="pg__page" href="{{ $paginator->url($page) }}">{{ $page }}</a></li>
          @endif
        @endforeach
      </ul>

      @if ($paginator->hasMorePages())
        <a class="pg__btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
          @lang('pagination.next')
          @if($rtl)
            <svg class="pg__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 6l-6 6 6 6" stroke="#111" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          @else
            <svg class="pg__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 6l6 6-6 6" stroke="#111" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          @endif
        </a>
      @else
        <span class="pg__btn" aria-disabled="true" aria-label="@lang('pagination.next')">
          @lang('pagination.next')
          @if($rtl)
            <svg class="pg__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 6l-6 6 6 6" stroke="#111" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          @else
            <svg class="pg__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 6l6 6-6 6" stroke="#111" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          @endif
        </span>
      @endif
    </nav>

    <div class="pg__stats">
      Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
    </div>
  </div>
@endif
