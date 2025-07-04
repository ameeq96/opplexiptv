 <!-- Main Footer -->
 <footer class="main-footer">
     <div class="pattern-layer-one" style="background-image: url('{{ asset('images/background/pattern-12.webp') }}')">
     </div>
     <div class="pattern-layer-two" style="background-image: url('{{ asset('images/background/pattern-13.webp') }}')">
     </div>

     <div class="auto-container">
         <div class="widgets-section">
             <div class="logo">
                 <img src="{{ asset('images/opplexiptvlogo.webp') }}" alt="Logo" title="" width="386"
                     height="100" />
             </div>
             <ul class="contact-info-list">
                 <li>
                     <span class="icon"><img src="{{ asset('images/icons/icon-1.webp') }}" alt="" /></span>
                     {{-- <a href="tel:+3453-909-6565">+3453-909-6565</a> --}}<br>
                     <a href="tel:+923121108582">{{ __('messages.footer_phone') }}</a>
                 </li>
                 <li>
                     <span class="icon"><img src="{{ asset('images/icons/icon-2.webp') }}" alt="" /></span>
                     <a href="mailto:info@opplexiptv.com">{{ __('messages.footer_email_1') }}</a><br>
                     <a href="mailto:support@opplexiptv.com">{{ __('messages.footer_email_2') }}</a><br>
                 </li>
                 <li>
                     <span class="icon"><img src="{{ asset('images/icons/icon-3.webp') }}" alt="" /></span>
                     <br> {{ __('messages.footer_address') }}
                 </li>
             </ul>

             <!-- Social Box -->
             <ul class="social-box">
                 <li><a href="https://www.facebook.com/profile.php?id=61565476366548" class="fa fa-facebook-f"
                         target="_blank"></a></li>
                 <li><a href="https://www.linkedin.com/company/digitalize-store/company/digitalize-store/"
                         class="fa fa-linkedin" target="_blank"></a></li>
                 <li><a href="https://www.instagram.com/oplextv/" class="fa fa-instagram" target="_blank"
                         rel="noopener noreferrer"></a></li>

             </ul>

         </div>

     </div>
     <!-- Footer Bottom -->
     <div class="footer-bottom">
         <div class="auto-container">
             <div class="copyright">&copy; 2022 - {{ date('Y') }} Opplex IPTV. {{ __('messages.footer_rights') }}
             </div>
         </div>
     </div>
 </footer>
 <!-- End Main Footer -->

 </div>
 <!--End pagewrapper-->


 <div class="scroll-to-top scroll-to-target" data-target="html"><span class="fa fa-arrow-up"></span></div>
 <!-- Load jQuery early, but defer all other scripts -->
 <script src="https://code.jquery.com/jquery-1.12.4.min.js" defer></script>

 <!-- Third-party libraries (defer to prevent layout shift) -->
 <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" defer></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/mixitup/2.1.10/jquery.mixitup.min.js" defer></script>
 <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js" defer></script>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" defer></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.js"
     defer></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js" defer></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-appear/0.1/jquery.appear.min.js" defer></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/parallax/3.1.0/parallax.min.js" defer></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/paroller.js/1.4.6/jquery.paroller.min.js" defer></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.1.1/owl.carousel.min.js" defer></script>


 <!-- Your local scripts (Load last to prevent blocking) -->
 <script src="{{ asset('js/nav-tool.js') }}" defer></script>
 <script src="{{ asset('js/script.js') }}" defer></script>

 <script>
     document.addEventListener("DOMContentLoaded", function() {
         const lazyBackgrounds = document.querySelectorAll('.lazy-background');

         const observer = new IntersectionObserver((entries, observer) => {
             entries.forEach(entry => {
                 if (entry.isIntersecting) {
                     const element = entry.target;
                     const bgUrl = element.getAttribute('data-bg');
                     element.style.backgroundImage = `url(${bgUrl})`;
                     observer.unobserve(element);
                 }
             });
         });

         lazyBackgrounds.forEach(element => {
             observer.observe(element);
         });

         const realToggle = document.getElementById('real-toggle');

         if (realToggle) realToggle.style.display = 'block';

     });

     window.onload = function() {
         const toggle = document.getElementById('resellerToggle');
         const normal = document.getElementById('normalPackages');
         const reseller = document.getElementById('resellerPackages');
         const creditInfo = document.getElementById('creditInfo');

         toggle.addEventListener('change', function() {
             const isChecked = this.checked;

             normal.style.display = isChecked ? 'none' : 'flex';
             reseller.style.display = isChecked ? 'flex' : 'none';
             creditInfo.style.display = isChecked ? 'block' : 'none';
         });
     };
 </script>
