<section class="page-title"
    style="background-image: url('{{ asset($background) }}'); direction: {{ $rtl ? 'rtl' : 'ltr' }};"
    @if($ariaLabel) aria-label="{{ $ariaLabel }}" @endif>
    <div class="auto-container" style="text-align: {{ $rtl ? 'right' : 'left' }};">
        <h2>{{ $title }}</h2>
        <ul class="bread-crumb clearfix"
            style="display: flex; justify-content: {{ $rtl ? 'flex-end' : 'flex-start' }}; flex-direction: {{ $rtl ? 'row-reverse' : 'row' }};"
            @if($ariaLabel) aria-label="Breadcrumb navigation" @endif>
            @foreach($breadcrumbs as $crumb)
                <li @if($loop->last) aria-current="page" @endif>
                    @if(isset($crumb['url']))
                        <a href="{{ $crumb['url'] }}" @if(isset($crumb['aria'])) aria-label="{{ $crumb['aria'] }}" @endif>
                            {{ $crumb['label'] }}
                        </a>
                    @else
                        {{ $crumb['label'] }}
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</section>
