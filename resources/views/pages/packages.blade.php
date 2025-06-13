@extends('layouts.default')
@section('title', 'IPTV Packages | Opplex IPTV - Affordable Plans for Every Viewer')
@section('content')

	<!-- Page Title -->
    <section class="page-title" style="background-image: url(images/background/9.webp)">
        <div class="auto-container">
			<h2>Packages</h2>
			<ul class="bread-crumb clearfix">
				<li><a href="/">Home</a></li>
				<li>Our Packages</li>
			</ul>
        </div>
    </section>
    <!--End Page Title-->
	
	
	<!-- Pricing Section -->
	@include('includes._best-packages')
	<!-- End Pricing Section -->

	
	<!-- Internet Section -->
	<section class="internet-section" style="background-image: url(images/background/1.webp)">
		<div class="auto-container">
			<div class="clearfix">
				<div class="content-column">
					<h2>Find Your Subscription
						Now Only Few Left.</h2>
					<div class="text">Hurry! Find Your Subscription Now, Only a Few Left!
						Grab the opportunity to secure your subscription before it's too late. Limited availability remaining!</div>
					<div class="price">PKR 350/ per month</div>
					<a href="about" class="theme-btn btn-style-four"><span class="txt">Read More <i class="lnr lnr-arrow-right"></i></span></a>
				</div>
			</div>
		</div>
	</section>
	<!-- End Internet Section -->
	
	<!-- Start Check Trail Section -->
	@include('includes._check-trail')
	<!-- Start Check Trail Section -->
	
	<!-- Start Choose US Section -->
	@include('includes._choose-us')
	<!-- END Choose US Section -->

@stop
