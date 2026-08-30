<?php

return [
    'checkout' => [
        'title' => 'Optional updates and offers',
        'email' => 'Email me setup help, renewal reminders and occasional offers.',
        'whatsapp' => 'Send me setup help, renewal reminders and occasional offers on WhatsApp.',
        'ads' => 'I allow my contact details to be used for personalized advertising audiences.',
        'optional' => 'Optional. Your order is not affected and you can unsubscribe at any time.',
        'privacy' => 'Privacy policy',
    ],
    'privacy' => [
        'title' => 'Marketing choices and retention',
        'text' => 'We send marketing email or WhatsApp messages only for channels you explicitly select. We may use contact details for advertising audiences only with separate permission. You can withdraw permission at any time. Incomplete checkout details are retained for up to :draft_days days. Delivery details are retained for up to :delivery_days days, then removed; a minimal non-contact suppression key remains to prevent duplicate messages.',
    ],
    'unsubscribe' => [
        'title' => 'You are unsubscribed',
        'text' => 'Your preference has been saved. No more marketing messages will be sent on the selected channel.',
        'home' => 'Return home',
        'confirm_title' => 'Manage marketing preferences',
        'confirm_text' => 'Confirm that you want to stop email, WhatsApp and personalized-audience marketing linked to this contact.',
        'confirm_button' => 'Stop marketing',
        'keep_button' => 'Keep preferences',
    ],
    'email' => [
        'permission' => 'You received this because you selected this marketing channel at checkout.',
        'unsubscribe' => 'Unsubscribe',
    ],
    'workflows' => [
        'abandoned' => [
            'subject' => 'Need help finishing your Opplex order?',
            'body' => 'Hi :name, your selected plan is still waiting. Review the details or ask our team for help before placing your order.',
            'cta' => 'Review plans',
        ],
        'onboarding' => [
            'subject' => 'Set up your :package subscription',
            'body' => 'Hi :name, your subscription is active. Follow our setup guide for :device, then contact support if you need help signing in.',
            'cta' => 'Open setup guide',
            'default_device' => 'device',
        ],
        'renewal' => [
            'subject' => 'Your :package plan expires soon',
            'body' => 'Hi :name, your :package plan is due to expire on :expiry. Review the current plan and price before renewing.',
            'cta' => 'Review renewal options',
        ],
        'referral' => [
            'subject' => 'Share Opplex with someone you trust',
            'body' => 'Hi :name, your personal referral link is ready. Share it only with people who asked for a streaming recommendation. Any reward is subject to the current approved referral terms.',
            'cta' => 'Open referral link',
        ],
    ],
];
