<div class="contact-message">
    <p>{{ __('interface.email.contact_reply.intro') }}</p>
    <p>
        {{ __('interface.email.contact_reply.whatsapp_info') }}:
        <a href="https://wa.me/{{ config('services.whatsapp.number') }}" target="_blank">
            {{ config('services.whatsapp.display') }}
        </a>
    </p>
    <p>{{ __('interface.email.contact_reply.signature') }}</p>
</div>
