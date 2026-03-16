@extends('layouts.default')
@section('title', __('messages.site_title'))
@push('styles')
<style>
    .skeleton-section {
        position: relative;
        overflow: hidden;
    }

    .skeleton-section:not(.is-loaded) > *:not(.section-skeleton__overlay) {
        opacity: 0;
        visibility: hidden;
    }

    .skeleton-section.is-loaded > *:not(.section-skeleton__overlay) {
        opacity: 1;
        visibility: visible;
        transition: opacity .35s ease;
    }

    .section-skeleton__overlay {
        position: absolute;
        inset: 0;
        z-index: 6;
        pointer-events: none;
        background: linear-gradient(135deg, rgba(244, 247, 252, .98) 0%, rgba(232, 238, 248, .94) 100%);
    }

    .section-skeleton__overlay::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, .12) 48%, transparent 100%);
        transform: translateX(-100%);
        animation: sectionSkeletonShimmer 1.5s linear infinite;
    }

    .section-skeleton__content {
        position: relative;
        height: 100%;
        padding: 32px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .section-skeleton__pill,
    .section-skeleton__line,
    .section-skeleton__card,
    .section-skeleton__button {
        display: block;
        border-radius: 999px;
        background: rgba(148, 163, 184, .22);
    }

    .section-skeleton__pill {
        width: 110px;
        height: 12px;
    }

    .section-skeleton__line {
        height: 16px;
        width: 100%;
        max-width: 520px;
        border-radius: 10px;
    }

    .section-skeleton__line--lg {
        height: 22px;
        max-width: 620px;
    }

    .section-skeleton__line--md {
        max-width: 420px;
    }

    .section-skeleton__button {
        width: 220px;
        height: 50px;
        margin-top: 10px;
        border-radius: 14px;
    }

    .section-skeleton__cards {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 22px;
        margin-top: 16px;
    }

    .section-skeleton__card {
        height: 180px;
        border-radius: 22px;
    }

    .skeleton-section--hero {
        min-height: 560px;
    }

    .skeleton-section--hero .section-skeleton__content {
        justify-content: center;
        padding: 70px 7vw;
    }

    .skeleton-section--hero .section-skeleton__cards {
        display: none;
    }

    .skeleton-section--pricing .section-skeleton__cards,
    .skeleton-section--products .section-skeleton__cards {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .skeleton-section--pricing .section-skeleton__card {
        height: 360px;
    }

    .skeleton-section--products .section-skeleton__card {
        height: 300px;
    }

    .skeleton-section--services .section-skeleton__card {
        height: 220px;
    }

    .skeleton-section--testimonials .section-skeleton__card {
        height: 260px;
    }

    .skeleton-section--logos .section-skeleton__cards {
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }

    .skeleton-section--logos .section-skeleton__card {
        height: 90px;
        border-radius: 18px;
    }

    .skeleton-section--cta .section-skeleton__content {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }

    .skeleton-section--cta .section-skeleton__cards {
        display: none;
    }

    .skeleton-section--cta .section-skeleton__meta {
        display: flex;
        flex-direction: column;
        gap: 14px;
        flex: 1 1 auto;
    }

    .skeleton-section--cta .section-skeleton__button {
        margin-top: 0;
        width: 280px;
        flex: 0 0 auto;
    }

    .skeleton-section.is-loaded .section-skeleton__overlay {
        opacity: 0;
        visibility: hidden;
        transition: opacity .28s ease, visibility .28s ease;
    }

    @keyframes sectionSkeletonShimmer {
        100% {
            transform: translateX(100%);
        }
    }

    .native-carousel {
        --native-gap: 30px;
        --native-items: 1;
        position: relative;
    }

    .native-carousel__viewport {
        overflow: hidden;
    }

    .native-carousel__track {
        display: flex;
        gap: var(--native-gap);
        transition: transform .55s ease;
        will-change: transform;
    }

    .native-carousel__slide {
        min-width: 0;
        flex: 0 0 calc((100% - (var(--native-gap) * (var(--native-items) - 1))) / var(--native-items));
    }

    .native-carousel__arrow {
        width: 42px;
        height: 42px;
        border: 1px solid #e6ebf3;
        border-radius: 999px;
        background: #ffffff;
        color: #0b1526;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 24px rgba(17, 27, 46, .12);
        transition: transform .2s ease, box-shadow .2s ease, opacity .2s ease;
    }

    .native-carousel__arrow:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 32px rgba(17, 27, 46, .16);
    }

    .native-carousel__arrow.is-hidden {
        opacity: .45;
    }

    .native-carousel--hero {
        height: 100%;
    }

    .native-carousel--hero .native-carousel__viewport,
    .native-carousel--hero .native-carousel__track,
    .native-carousel--hero .native-carousel__slide {
        height: 100%;
    }

    .native-carousel--hero .native-carousel__viewport {
        position: relative;
    }

    .native-carousel--hero .native-carousel__track {
        display: block;
    }

    .native-carousel--hero .native-carousel__slide {
        position: absolute;
        inset: 0;
        opacity: 0;
        visibility: hidden;
        transition: opacity .7s ease, visibility .7s ease;
    }

    .native-carousel--hero .native-carousel__slide.is-active {
        opacity: 1;
        visibility: visible;
        z-index: 1;
    }

    .native-home-hero .slide::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(8, 15, 28, .85) 0%, rgba(8, 15, 28, .52) 40%, rgba(8, 15, 28, .2) 100%);
        z-index: 0;
    }

    .native-home-hero .slide {
        position: absolute;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    .native-home-hero .slide .auto-container {
        position: relative;
        z-index: 1;
    }

    .native-home-hero .inner-box > * {
        animation: nativeHeroReveal .8s ease both;
    }

    .native-home-hero .native-carousel__arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 3;
    }

    .native-home-hero .native-carousel__arrow:hover {
        transform: translateY(-50%) scale(1.03);
    }

    .native-home-hero .native-carousel__arrow--prev {
        left: 24px;
    }

    .native-home-hero .native-carousel__arrow--next {
        right: 24px;
    }

    .home-products-shell {
        position: relative;
    }

    .home-products-headline {
        display: inline-block;
        width: 78px;
        height: 6px;
        border-radius: 999px;
        margin-bottom: 18px;
        background: linear-gradient(90deg, #ff2e18 0%, #ff6b5d 100%);
    }

    .home-products-shell .sec-title {
        margin-bottom: 28px !important;
    }

    .home-products-shell .sec-title h2 {
        color: #0f172a;
        font-size: clamp(34px, 4vw, 54px);
        line-height: 1.03;
        letter-spacing: -.04em;
    }

    .home-products-shell .sec-title p {
        max-width: 560px;
        color: #64748b !important;
        font-size: 17px !important;
        line-height: 1.7;
        margin-top: 10px;
    }

    .home-products-filter {
        display: inline-flex;
        background: #ffffff;
        border-radius: 999px;
        padding: 4px;
        gap: 4px;
        border: 1px solid #e8edf5;
        box-shadow: 0 8px 20px rgba(12, 22, 38, 0.08);
    }
    .home-products-filter a {
        text-decoration: none !important;
        border-radius: 999px;
        padding: 8px 15px;
        font-weight: 600;
        font-size: 12px;
        color: #4b4b4b;
        transition: all .2s ease;
    }
    .home-products-filter a:hover {
        color: #0a67ff;
    }
    .home-products-filter a:first-child,
    .home-products-filter .is-active {
        color: #fff;
        background: linear-gradient(90deg, #0454f7, #0a67ff);
    }
    .home-product-card {
        background: #ffffff;
        border-radius: 26px;
        border: 1px solid #e6edf7;
        overflow: hidden;
        box-shadow: none;
        transition: transform .22s ease, border-color .22s ease;
    }
    .home-product-card:hover {
        transform: translateY(-8px);
        border-color: rgba(37, 99, 235, 0.2);
    }
    .home-product-media {
        display:block;
        height:292px;
        padding: 12px;
        background: transparent;
    }
    .home-product-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
        border-radius: 20px;
    }
    .home-product-body {
        padding: 4px 18px 18px;
    }
    .home-product-title {
        font-size: 18px;
        line-height: 1.2;
        margin-bottom: 0;
        font-weight: 800;
        min-height: 44px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .home-product-title a { color:#141414; text-decoration:none; }
    .home-product-title a:hover { color:#0454f7; }
    .home-product-price {
        font-size:32px;
        font-weight:800;
        color:#0f172a;
        margin-bottom:14px;
        letter-spacing: -.03em;
    }
    .home-product-badge {
        border-radius:999px;
        padding:8px 12px;
        font-size:12px;
        font-weight:800;
        letter-spacing: .02em;
        white-space: nowrap;
    }
    .home-product-badge--digital { color:#0f766e; background:#ccfbf1; }
    .home-product-badge--affiliate { color:#1d4ed8; background:#dbeafe; }
    .home-product-action {
        width:100%;
        border-radius:16px;
        font-size:15px;
        font-weight:800;
        padding:14px 16px;
        letter-spacing:-.01em;
        box-shadow: none !important;
    }
    .home-product-actions {
        display:grid;
        grid-template-columns: 1fr 56px;
        gap:12px;
        margin-top:auto;
    }
    .home-product-share {
        border-radius: 16px;
        border: 1px solid #dbe4f3;
        background: #f8fbff;
        color: #1d4ed8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: 700;
        box-shadow: none;
    }
    .home-products-carousel {
        --native-gap: 0px;
    }

    .home-products-carousel .native-carousel__viewport {
        padding: 8px 2px 18px;
        background: transparent;
    }

    .home-products-carousel .native-carousel__arrow {
        margin-top: 0;
        gap: 10px;
        position: absolute;
        right: 0;
        top: -78px;
        z-index: 3;
    }

    .home-products-carousel .native-carousel__arrow--prev {
        right: 46px;
    }

    .home-products-carousel .native-carousel__arrow--next {
        right: 0;
    }

    [dir="rtl"] .home-products-carousel .native-carousel__arrow--prev,
    [dir="rtl"] .home-products-carousel .native-carousel__arrow--next {
        right: auto;
    }

    [dir="rtl"] .home-products-carousel .native-carousel__arrow--prev {
        left: 46px;
    }

    [dir="rtl"] .home-products-carousel .native-carousel__arrow--next {
        left: 0;
    }

    .native-carousel--services,
    .native-carousel--testimonials,
    .native-carousel--logos {
        padding: 0 58px;
    }

    .native-carousel--services .native-carousel__arrow,
    .native-carousel--testimonials .native-carousel__arrow,
    .native-carousel--logos .native-carousel__arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 3;
    }

    .native-carousel--services .native-carousel__arrow--prev,
    .native-carousel--testimonials .native-carousel__arrow--prev,
    .native-carousel--logos .native-carousel__arrow--prev {
        left: 0;
    }

    .native-carousel--services .native-carousel__arrow--next,
    .native-carousel--testimonials .native-carousel__arrow--next,
    .native-carousel--logos .native-carousel__arrow--next {
        right: 0;
    }

    .native-carousel--logos .native-carousel__slide {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .channel-showcase {
        padding: 8px 0 0;
    }

    .channel-showcase__header {
        display: block;
        margin-bottom: 26px;
    }

    .channel-showcase__content {
        display: flex;
        justify-content: center;
        min-width: 0;
    }

    .channel-showcase__copy h2 {
        text-align: center;
        margin: 0 0 10px;
        color: #09162b;
        font-size: 40px;
        line-height: 1.08;
        letter-spacing: -.03em;
    }

    .channel-showcase__copy p {
        margin: 0;
        max-width: 620px;
        color: #5a6780;
        font-size: 17px;
        line-height: 1.7;
    }

    .native-carousel--logos .image-box {
        width: 100%;
        display: flex;
        justify-content: center;
    }

    .channel-showcase__card {
        border-radius: 22px;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #ffffff;
        border: 1px solid #e6ecf5;
        box-shadow: none;
    }

    .channel-showcase__card .wrapper-circle {
        width: 100px;
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .channel-showcase__card img {
        width: 72px;
        height: 72px;
        object-fit: contain;
    }

    .services-section-two {
        padding: 110px 0;
        background-image: none !important;
        background:
            radial-gradient(circle at top left, rgba(255, 255, 255, .08), transparent 26%),
            linear-gradient(180deg, #0c1530 0%, #111d3e 56%, #132248 100%) !important;
    }

    .services-section-two::before {
        opacity: 1;
        background:
            linear-gradient(180deg, rgba(8, 13, 29, .18) 0%, rgba(8, 13, 29, .08) 100%);
    }

    .services-showcase__heading {
        margin-bottom: 42px;
    }

    .services-showcase__eyebrow {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 0 14px;
        margin-bottom: 16px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .08);
        border: 1px solid rgba(255, 255, 255, .12);
        color: #dbe7ff;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .services-showcase__heading h3 {
        margin-bottom: 14px;
        color: #ffffff;
        font-size: 52px;
        line-height: 1.03;
        letter-spacing: -.04em;
    }

    .services-showcase__heading p {
        max-width: 760px;
        margin: 0 auto;
        color: rgba(223, 232, 250, .78);
        font-size: 17px;
        line-height: 1.7;
    }

    .native-carousel--testimonials .testimonial-block,
    .native-carousel--services .service-block-two {
        height: 100%;
    }

    .native-carousel--testimonials .testimonial-block .inner-box,
    .native-carousel--services .service-block-two .inner-box {
        height: 100%;
    }

    .native-carousel--testimonials .testimonial-block .inner-box {
        display: flex;
        flex-direction: column;
    }

    .testimonial-showcase__heading .title {
        margin-bottom: 12px;
        letter-spacing: .12em;
    }

    .testimonial-showcase__heading h3 {
        margin-bottom: 16px;
        font-size: 54px;
        line-height: 1.05;
        letter-spacing: -.04em;
    }

    .testimonial-showcase__heading p {
        max-width: 760px;
        margin: 0 auto;
        color: #607089;
        font-size: 17px;
        line-height: 1.7;
    }

    .native-carousel--testimonials .testimonial-card,
    .testimonial-carousel .testimonial-card {
        position: relative;
        padding: 34px 30px 28px;
        border-radius: 28px;
        background: #ffffff;
        border: 1px solid #e8edf5;
        box-shadow: none;
        overflow: hidden;
        transition: border-color .2s ease;
    }

    .native-carousel--testimonials .testimonial-card .upper-box,
    .native-carousel--testimonials .testimonial-card .lower-box,
    .testimonial-carousel .testimonial-card .upper-box,
    .testimonial-carousel .testimonial-card .lower-box {
        padding: 0;
    }

    .native-carousel--testimonials .testimonial-card .lower-box::before,
    .testimonial-carousel .testimonial-card .lower-box::before,
    .native-carousel--testimonials .testimonial-card .color-layer,
    .native-carousel--testimonials .testimonial-card .pattern-layer,
    .testimonial-carousel .testimonial-card .color-layer,
    .testimonial-carousel .testimonial-card .pattern-layer {
        display: none !important;
    }

    .native-carousel--testimonials .testimonial-card:hover,
    .testimonial-carousel .testimonial-card:hover {
        border-color: #dce6f5;
    }

    .native-carousel--testimonials .testimonial-card::before,
    .testimonial-carousel .testimonial-card::before {
        display: none;
    }

    .native-carousel--testimonials .testimonial-card::after,
    .testimonial-carousel .testimonial-card::after {
        display: none;
    }

    .native-carousel--testimonials .testimonial-card__quote-mark,
    .testimonial-carousel .testimonial-card__quote-mark {
        margin-bottom: 24px;
        color: #d7deea;
        font-size: 62px;
        font-weight: 600;
        line-height: .8;
        text-align: left;
    }

    .native-carousel--testimonials .testimonial-block .upper-box {
        flex: 1 1 auto;
        display: flex;
    }

    .native-carousel--testimonials .testimonial-block .upper-box .text {
        width: 100%;
        color: #1c2740;
        font-size: 20px;
        font-weight: 600;
        line-height: 1.8;
        letter-spacing: -.01em;
        text-align: left;
    }

    .native-carousel--testimonials .testimonial-block .lower-box {
        margin-top: auto;
    }

    .native-carousel--testimonials .testimonial-card__footer,
    .testimonial-carousel .testimonial-card__footer {
        margin-top: 30px;
        padding-top: 22px;
        border-top: 1px solid #ecf1f7;
        background: transparent;
    }

    .native-carousel--testimonials .testimonial-card__author,
    .testimonial-carousel .testimonial-card__author {
        display: flex;
        align-items: center;
        gap: 16px;
        width: 100%;
        text-align: left;
    }

    .native-carousel--testimonials .testimonial-card__author.author-image-outer,
    .testimonial-carousel .testimonial-card__author.author-image-outer {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        margin: 0;
        margin-top: 10px;
        vertical-align: top;
    }

    .native-carousel--testimonials .testimonial-card__author .author-image,
    .testimonial-carousel .testimonial-card__author .author-image {
        width: 62px;
        height: 62px;
        flex: 0 0 62px;
        margin: 0;
        display: block;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #ffffff;
        box-shadow: 0 12px 24px rgba(15, 23, 42, .12);
    }

    .native-carousel--testimonials .testimonial-card__author .author-image img,
    .testimonial-carousel .testimonial-card__author .author-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .native-carousel--testimonials .testimonial-card__author-copy,
    .testimonial-carousel .testimonial-card__author-copy {
        min-width: 0;
    }

    .native-carousel--testimonials .author-name,
    .testimonial-carousel .author-name {
        margin-top: 0;
        color: #09162b;
        font-size: 20px;
        font-weight: 700;
        line-height: 1.2;
    }

    .native-carousel--testimonials .testimonial-card__author-role,
    .testimonial-carousel .testimonial-card__author-role {
        margin-top: 6px;
        color: #73819a;
        font-size: 14px;
        font-weight: 500;
        line-height: 1.5;
    }

    .native-carousel--testimonials .testimonial-block .inner-box:hover .author-name,
    .native-carousel--testimonials .testimonial-block .inner-box:hover .designation,
    .native-carousel--testimonials .testimonial-block .inner-box:hover .text,
    .testimonial-carousel .testimonial-block .inner-box:hover .author-name,
    .testimonial-carousel .testimonial-block .inner-box:hover .designation,
    .testimonial-carousel .testimonial-block .inner-box:hover .text,
    .native-carousel--testimonials .testimonial-card:hover .author-name,
    .native-carousel--testimonials .testimonial-card:hover .testimonial-card__author-role,
    .native-carousel--testimonials .testimonial-card:hover .text,
    .testimonial-carousel .testimonial-card:hover .author-name,
    .testimonial-carousel .testimonial-card:hover .testimonial-card__author-role,
    .testimonial-carousel .testimonial-card:hover .text {
        color: inherit !important;
    }

    .native-carousel--services .service-block-two .inner-box {
        padding: 28px 26px 24px;
        min-height: 0;
        border-radius: 26px;
        text-align: left;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, .08) 0%, rgba(255, 255, 255, .03) 100%);
        border: 1px solid rgba(255, 255, 255, .12);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .05);
        backdrop-filter: blur(10px);
    }

    .native-carousel--services .service-block-two h4 {
        margin-bottom: 12px;
        font-size: 29px;
        line-height: 1.14;
        letter-spacing: -.03em;
    }

    .native-carousel--services .service-block-two h4 a,
    .native-carousel--services .service-block-two h4 a:hover {
        color: #ffffff;
    }

    .native-carousel--services .service-block-two h4 a:focus,
    .native-carousel--services .service-block-two h4 a:active,
    .native-carousel--services .service-block-two .learn-more:hover,
    .native-carousel--services .service-block-two .learn-more:focus,
    .native-carousel--services .service-block-two .learn-more:active,
    .native-carousel--services .service-block-two .text:hover {
        color: #ffffff !important;
    }

    .native-carousel--services .service-block-two .text {
        margin-bottom: 18px;
        color: rgba(223, 232, 250, .78);
        font-size: 15px;
        line-height: 1.75;
        display: -webkit-box;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .native-carousel--services .service-block-two .icon {
        width: 62px;
        height: 62px;
        margin-bottom: 18px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, .08);
        border: 1px solid rgba(255, 255, 255, .12);
    }

    .native-carousel--services .service-block-two .icon img {
        width: 28px;
        height: 28px;
        object-fit: contain;
    }

    .native-carousel--services .service-block-two .learn-more {
        margin-top: auto;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding-right: 0;
        color: #ffffff;
        font-size: 16px;
        font-weight: 700;
        letter-spacing: -.01em;
    }

    .native-carousel--services .service-block-two .learn-more::before,
    .native-carousel--services .service-block-two .color-layer,
    .native-carousel--services .service-block-two .icon-layer-one,
    .native-carousel--services .service-block-two .icon-layer-two {
        display: none !important;
    }

    .native-carousel--services .service-block-two .inner-box:hover .icon {
        background: rgba(255, 255, 255, .08) !important;
        border-color: rgba(255, 255, 255, .12) !important;
    }

    @keyframes nativeHeroReveal {
        from {
            opacity: 0;
            transform: translateY(24px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 991px) {
        .section-skeleton__content {
            padding: 24px 18px;
        }

        .section-skeleton__cards,
        .skeleton-section--pricing .section-skeleton__cards,
        .skeleton-section--products .section-skeleton__cards,
        .skeleton-section--logos .section-skeleton__cards {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .skeleton-section--cta .section-skeleton__content {
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
        }

        .skeleton-section--cta .section-skeleton__button {
            width: 220px;
        }

        .home-products-shell {
            padding: 0;
            border-radius: 0;
        }

        .home-products-shell .sec-title h2 {
            font-size: 38px;
        }

        .home-products-shell .sec-title p {
            font-size: 15px !important;
            line-height: 1.6;
        }
        .home-product-media {
            height: 230px;
        }

        .home-product-body {
            padding: 4px 16px 16px;
        }

        .home-product-price {
            font-size: 28px;
        }
        .native-home-hero .native-carousel__arrow {
            top: auto;
            bottom: 82px;
            transform: none;
        }

        .native-home-hero .native-carousel__arrow:hover {
            transform: none;
        }

        .native-home-hero .native-carousel__arrow--prev {
            left: 16px;
        }

        .native-home-hero .native-carousel__arrow--next {
            right: 16px;
        }

        .native-carousel--services,
        .native-carousel--testimonials,
        .native-carousel--logos {
            padding: 0 0 58px;
        }

        .channel-showcase {
            padding: 4px 0 0;
        }

        .channel-showcase__header {
            margin-bottom: 22px;
        }

        .channel-showcase__copy h2 {
            font-size: 30px;
        }

        .services-showcase__heading h3 {
            font-size: 40px;
        }

        .native-carousel--services .service-block-two .inner-box {
            min-height: 0;
            padding: 24px 20px 22px;
        }

        .native-carousel--services .service-block-two h4 {
            font-size: 24px;
        }

        .testimonial-showcase__heading h3 {
            font-size: 40px;
        }

        .native-carousel--testimonials .testimonial-card,
        .testimonial-carousel .testimonial-card {
            padding: 28px 22px 24px;
            border-radius: 24px;
        }

        .native-carousel--testimonials .testimonial-block .upper-box .text {
            font-size: 18px;
        }

        .native-carousel--services .service-block-two .inner-box {
            min-height: 210px;
            padding: 16px 14px;
        }

        .native-carousel--services .native-carousel__arrow,
        .native-carousel--testimonials .native-carousel__arrow,
        .native-carousel--logos .native-carousel__arrow,
        .home-products-carousel .native-carousel__arrow {
            position: static;
            margin-top: 10px;
        }

        .home-products-carousel .native-carousel__arrow--prev,
        .home-products-carousel .native-carousel__arrow--next {
            right: auto;
            left: auto;
        }
    }

    @media (max-width: 767px) {
        .skeleton-section--hero {
            min-height: 320px;
        }

        .channel-showcase {
            padding: 0;
        }

        .home-products-shell .sec-title h2 {
            font-size: 28px;
        }

        .home-product-media {
            height: 210px;
            padding: 10px;
        }

        .home-product-media img {
            border-radius: 16px;
        }

        .home-product-card {
            border-radius: 20px;
        }

        .home-product-body {
            padding: 2px 14px 14px;
        }

        .home-product-title {
            font-size: 17px;
            min-height: 40px;
        }

        .home-product-price {
            font-size: 26px;
            margin-bottom: 12px;
        }

        .home-product-action {
            border-radius: 14px;
            font-size: 14px;
            padding: 12px 14px;
        }

        .home-product-actions {
            grid-template-columns: 1fr 52px;
            gap: 10px;
        }

        .home-product-share {
            border-radius: 14px;
            font-size: 16px;
        }

        .channel-showcase__copy h2 {
            font-size: 24px;
        }

        .channel-showcase__copy p {
            font-size: 15px;
            line-height: 1.6;
        }

        .services-section-two {
            padding: 86px 0;
        }

        .services-showcase__heading h3 {
            font-size: 30px;
        }

        .services-showcase__heading p {
            font-size: 15px;
            line-height: 1.6;
        }

        .testimonial-showcase__heading h3 {
            font-size: 28px;
        }

        .testimonial-showcase__heading p {
            font-size: 15px;
            line-height: 1.6;
        }

        .channel-showcase__card {
            min-height: 128px;
            padding: 18px 12px;
            border-radius: 18px;
        }

        .channel-showcase__card .wrapper-circle {
            width: 84px;
            height: 84px;
            border-radius: 0;
        }

        .channel-showcase__card img {
            width: 64px;
            height: 64px;
        }

        .native-carousel--services .service-block-two .inner-box {
            min-height: 0;
            padding: 20px 18px;
            border-radius: 20px;
        }

        .native-carousel--services .service-block-two h4 {
            font-size: 21px;
        }

        .native-carousel--services .service-block-two .text {
            font-size: 14px;
            line-height: 1.65;
        }

        .native-carousel--testimonials .testimonial-card,
        .testimonial-carousel .testimonial-card {
            padding: 24px 18px 20px;
            border-radius: 20px;
        }

        .native-carousel--testimonials .testimonial-card__quote-mark,
        .testimonial-carousel .testimonial-card__quote-mark {
            margin-bottom: 18px;
            font-size: 42px;
        }

        .native-carousel--testimonials .testimonial-block .upper-box .text {
            font-size: 16px;
            line-height: 1.7;
        }

        .native-carousel--testimonials .author-name,
        .testimonial-carousel .author-name {
            font-size: 20px;
        }

        .section-skeleton__cards,
        .skeleton-section--pricing .section-skeleton__cards,
        .skeleton-section--products .section-skeleton__cards,
        .skeleton-section--logos .section-skeleton__cards {
            grid-template-columns: 1fr;
        }
    }

</style>
@endpush
@section('content')
    @php
        $waTrial = 'https://wa.me/16393903194?text=' . urlencode(__('messages.whatsapp_trial'));
        $currency = config('services.app.default_currency', 'USD');
        $useNativeHomeCarousel = true;
        $useSectionSkeletons = true;
    @endphp

    @include('includes._slider', ['useNativeCarousel' => $useNativeHomeCarousel, 'useSectionSkeletons' => $useSectionSkeletons])

    @include('includes._best-packages', ['useSectionSkeletons' => $useSectionSkeletons])

    @if(!empty($homeProducts) && count($homeProducts) > 0)
        <section class="shop-section shop-section-2 skeleton-section skeleton-section--products"
            data-skeleton-section
            style="background-image: url('{{ asset('images/background/4.webp') }}'); direction: {{ $isRtl ? 'rtl' : 'ltr' }};">
            <div class="section-skeleton__overlay" aria-hidden="true">
                <div class="section-skeleton__content">
                    <span class="section-skeleton__pill"></span>
                    <span class="section-skeleton__line section-skeleton__line--lg"></span>
                    <span class="section-skeleton__line section-skeleton__line--md"></span>
                    <div class="section-skeleton__cards">
                        <span class="section-skeleton__card"></span>
                        <span class="section-skeleton__card"></span>
                        <span class="section-skeleton__card"></span>
                        <span class="section-skeleton__card"></span>
                    </div>
                </div>
            </div>
            <div class="auto-container">
                <div class="home-products-shell">
                    <div class="sec-title mb-4" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                        <span class="home-products-headline" aria-hidden="true"></span>
                        <div style="display:flex; align-items:center; justify-content: space-between; gap:12px; flex-wrap:wrap;">
                            <div>
                                <h3>{{ __('messages.home_products_digital_title') }}</h3>
                                <p class="text-muted mb-0" style="font-size:14px;">{{ __('messages.home_products_digital_desc') }}</p>
                            </div>
                            <span></span>
                        </div>
                    </div>

                    <div class="home-products-carousel native-carousel native-carousel--cards"
                        data-native-carousel
                        data-items-desktop="4"
                        data-items-tablet="2"
                        data-items-mobile="1"
                        data-gap="30"
                        data-autoplay="5000">
                        <div class="native-carousel__viewport">
                            <div class="native-carousel__track">
                        @foreach($homeProducts as $p)
                            <div class="native-carousel__slide px-2">
                                <article class="home-product-card h-100">
                                    <a class="home-product-media" href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif>
                                        @if(!empty($p['image']))
                                            <img src="{{ $p['image'] }}"
                                                 alt="{{ $p['name'] }}"
                                                 loading="lazy"
                                                 decoding="async">
                                        @endif
                                    </a>
                                    <div class="home-product-body">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                            <h3 class="home-product-title">
                                                <a href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif>
                                                    {{ $p['name'] }}
                                                </a>
                                            </h3>
                                            <span class="home-product-badge {{ $p['type'] === 'digital' ? 'home-product-badge--digital' : 'home-product-badge--affiliate' }}">
                                                {{ $p['type'] === 'digital' ? __('messages.home_products_type_digital') : __('messages.home_products_type_affiliate') }}
                                            </span>
                                        </div>
                                        @if(!empty($p['price']))
                                            <div class="home-product-price">{{ $p['currency'] }} {{ number_format((float) $p['price'], 2) }}</div>
                                        @endif
                                        <div class="home-product-actions">
                                            @if($p['type'] === 'digital' && !empty($p['buy_now_url']))
                                                <a href="{{ $p['buy_now_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary home-product-action">{{ __('messages.buy_now') }}</a>
                                            @else
                                                <a href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif class="btn btn-primary home-product-action">{{ __('messages.home_products_open_link') }}</a>
                                            @endif
                                            <button type="button"
                                                class="home-product-share"
                                                aria-label="{{ __('messages.home_products_share_label', ['name' => $p['name']]) }}"
                                                data-share-url="{{ $p['share_url'] ?? $p['url'] }}"
                                                data-share-title="{{ $p['name'] }}"
                                                data-share-text="{{ $p['share_text'] ?? __('messages.home_products_share_text', ['name' => $p['name']]) }}">
                                                <i class="fa fa-share-alt" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if(!empty($homeAffiliateProducts) && count($homeAffiliateProducts) > 0)
        <section class="shop-section shop-section-2 mt-5 skeleton-section skeleton-section--products"
            data-skeleton-section
            style="background-image: url('{{ asset('images/background/4.webp') }}'); direction: {{ $isRtl ? 'rtl' : 'ltr' }};">
            <div class="section-skeleton__overlay" aria-hidden="true">
                <div class="section-skeleton__content">
                    <span class="section-skeleton__pill"></span>
                    <span class="section-skeleton__line section-skeleton__line--lg"></span>
                    <span class="section-skeleton__line section-skeleton__line--md"></span>
                    <div class="section-skeleton__cards">
                        <span class="section-skeleton__card"></span>
                        <span class="section-skeleton__card"></span>
                        <span class="section-skeleton__card"></span>
                        <span class="section-skeleton__card"></span>
                    </div>
                </div>
            </div>
            <div class="auto-container">
                <div class="home-products-shell">
                    <div class="sec-title mb-4" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                        <span class="home-products-headline" aria-hidden="true"></span>
                        <div style="display:flex; align-items:center; justify-content: space-between; gap:12px; flex-wrap:wrap;">
                            <div>
                                <h3>{{ __('messages.home_products_affiliate_title') }}</h3>
                                <p class="text-muted mb-0" style="font-size:14px;">{{ __('messages.home_products_affiliate_desc') }}</p>
                            </div>
                            <span></span>
                        </div>
                    </div>

                    <div class="home-products-carousel native-carousel native-carousel--cards"
                        data-native-carousel
                        data-items-desktop="4"
                        data-items-tablet="2"
                        data-items-mobile="1"
                        data-gap="30"
                        data-autoplay="5000">
                        <div class="native-carousel__viewport">
                            <div class="native-carousel__track">
                        @foreach($homeAffiliateProducts as $p)
                            <div class="native-carousel__slide px-2">
                                <article class="home-product-card h-100">
                                    <a class="home-product-media" href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif>
                                        @if(!empty($p['image']))
                                            <img src="{{ $p['image'] }}"
                                                 alt="{{ $p['name'] }}"
                                                 loading="lazy"
                                                 decoding="async">
                                        @endif
                                    </a>
                                    <div class="home-product-body">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                            <h3 class="home-product-title">
                                                <a href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif>
                                                    {{ $p['name'] }}
                                                </a>
                                            </h3>
                                            <span class="home-product-badge {{ $p['type'] === 'digital' ? 'home-product-badge--digital' : 'home-product-badge--affiliate' }}">
                                                {{ $p['type'] === 'digital' ? __('messages.home_products_type_digital') : __('messages.home_products_type_affiliate') }}
                                            </span>
                                        </div>
                                        @if(!empty($p['price']))
                                            <div class="home-product-price">{{ $p['currency'] }} {{ number_format((float) $p['price'], 2) }}</div>
                                        @endif
                                        <div class="home-product-actions">
                                            @if($p['type'] === 'digital' && !empty($p['buy_now_url']))
                                                <a href="{{ $p['buy_now_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary home-product-action">{{ __('messages.buy_now') }}</a>
                                            @else
                                                <a href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif class="btn btn-primary home-product-action">{{ __('messages.home_products_open_link') }}</a>
                                            @endif
                                            <button type="button"
                                                class="home-product-share"
                                                aria-label="{{ __('messages.home_products_share_label', ['name' => $p['name']]) }}"
                                                data-share-url="{{ $p['share_url'] ?? $p['url'] }}"
                                                data-share-title="{{ $p['name'] }}"
                                                data-share-text="{{ $p['share_text'] ?? __('messages.home_products_share_text', ['name' => $p['name']]) }}">
                                                <i class="fa fa-share-alt" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @include('includes._we-provide-unlimited', ['useSectionSkeletons' => $useSectionSkeletons])

    @include('includes._services', ['useNativeCarousel' => $useNativeHomeCarousel, 'useSectionSkeletons' => $useSectionSkeletons])

    @unless ($isMobile)
        @include('includes._testimonials', ['useNativeCarousel' => $useNativeHomeCarousel, 'useSectionSkeletons' => $useSectionSkeletons])
        @include('includes._channels-carousel', ['useNativeCarousel' => $useNativeHomeCarousel, 'useSectionSkeletons' => $useSectionSkeletons])
    @endunless

    @include('includes._check-trail', ['useSectionSkeletons' => $useSectionSkeletons])
@stop

@section('jsonld')
<script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "Opplex IPTV",
    "url": "https://opplexiptv.com/",
    "description": "Opplex IPTV provides IPTV subscription services with live TV, sports, movies, and premium entertainment channels.",
    "logo": "https://opplexiptv.com/logo.png"
    }
</script>

<script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "Service",
    "name": "IPTV Subscription Service",
    "provider": {
        "@type": "Organization",
        "name": "Opplex IPTV"
    },
    "serviceType": "IPTV Streaming Service",
    "areaServed": "Worldwide"
    }
</script>

<script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "Opplex IPTV Subscription",
    "brand": {
        "@type": "Brand",
        "name": "Opplex IPTV"
    },
    "offers": {
        "@type": "Offer",
        "priceCurrency": "USD",
        "availability": "https://schema.org/InStock"
    }
    }
</script>

<script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {
        "@type": "ListItem",
        "position": 1,
        "name": "Home",
        "item": "https://opplexiptv.com/"
        }
    ]
    }
</script>
@endsection
