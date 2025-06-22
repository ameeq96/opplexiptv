@extends('layouts.default')
@section('title', 'Pricing | Opplex IPTV - Competitive Rates for Premium IPTV Services')
@section('content')

@php
    use Jenssegers\Agent\Agent;
    $agent = new Agent();
    $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';
@endphp

<!-- Page Title -->
<section class="page-title" style="background-image: url(images/background/8.webp)">
    <div class="auto-container">
        <h2>Our Packages</h2>
        <ul class="bread-crumb clearfix">
            <li><a href="/">Home</a></li>
            <li>Packages</li>
        </ul>
    </div>
</section>
<!-- End Page Title -->

<!-- Pricing Section -->
@include('includes._best-packages')
<!-- End Pricing Section -->

<!-- Internet Section Three -->
<section class="internet-section-three" style="background-image: url(images/background/1.webp)">
    <div class="auto-container">
        <div class="row clearfix">
         
            <!-- Image Column -->
            <div class="image-column col-lg-6 col-md-12 col-sm-12">
                <div class="inner-column">
                    <div class="transparent-image">
                        <img src="images/resource/internet-video-transparent.webp" alt="" />
                        <iframe src="https://www.facebook.com/plugins/video.php?height=476&href=https%3A%2F%2Fwww.facebook.com%2F61565476366548%2Fvideos%2F8317598665026269%2F&show_text=false&width=476&t=0" width="476" height="476" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>
                    </div>
                </div>
            </div>
        
            <!-- Content Column -->
            <div class="content-column col-lg-6 col-md-12 col-sm-12">
                <div class="inner-column">
                    <div class="sec-title light">
                        <div class="separator"></div>
                        <h2>Find Your Subscription Now Only Few Left</h2>
                    </div>
                    <div class="text">Hurry! Find Your Subscription Now, Only a Few Left! Grab the opportunity to secure your subscription before it's too late. Limited availability remaining!</div>
                    <div class="price">PKR 350/ per month</div>
                    <a href="{{ route('about') }}" class="theme-btn btn-style-two"><span class="txt">Read More <i class="lnr lnr-arrow-right"></i></span></a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Internet Section Three -->

<!-- START Reseller pricing Section -->
@include('includes._reseller-packages')
<!-- END Reseller pricing Section -->

@stop