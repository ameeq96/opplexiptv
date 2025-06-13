<section class="clients-section">
    <div class="auto-container">
        <ul class="sponsors-carousel owl-carousel owl-theme">
            @foreach ($logos as $index => $logo)
                <li>
                    <div class="image-box">
                        <div class="wrapper-circle"><img src="{{ asset($logo) }}" alt=""></div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</section>
