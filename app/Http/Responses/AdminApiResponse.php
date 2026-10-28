<?php

namespace App\Http\Responses;

use Closure;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AdminApiResponse
{
    private const VIEWS = [
        'dashboard' => ['users', 'activeOrders', 'expiredOrders', 'totalOrders', 'earningsByCurrency', 'purchasingByCurrency', 'purchasingSeriesForChart', 'filter', 'startDate', 'endDate'],
        'auth.login' => ['num1', 'num2'],
        'auth.change-password' => [],
        'auth.two-factor-challenge' => ['admin'],
        'auth.confirm-sensitive' => [],
        'security.two-factor' => ['admin'],
        'clients.index' => ['clients', 'contactUpdateEligible', 'unlinkedActivity', 'admins', 'activitySources'],
        'clients.create' => ['countries'],
        'clients.edit' => ['client', 'countries'],
        'clients.show' => ['client', 'orders', 'trialClicks', 'checkoutDrafts', 'digitalOrders', 'referrals', 'marketingDeliveries', 'activities', 'followUps', 'conversations', 'conversation', 'messages', 'paidTotals', 'admins', 'activitySources', 'externalLinks'],
        'clients.unlinked' => ['type', 'records', 'previews', 'selectedConversation', 'unlinkedMessages'],
        'orders.index' => ['orders', 'today', 'tab', 'type'],
        'orders.show' => ['order', 'isReseller'],
        'orders.create' => ['clients'],
        'orders.edit' => ['order', 'clients'],
        'reseller-orders.index' => ['orders', 'today', 'tab', 'type'],
        'reseller-orders.create' => ['clients'],
        'reseller-orders.edit' => ['order', 'clients'],
        'purchasing.index' => ['purchases'],
        'purchasing.create' => [],
        'purchasing.edit' => ['purchasing'],
        'trial_clicks.index' => ['clicks', 'today', 'last7', 'last30', 'newLeads', 'paidLeads', 'statuses', 'landingBrands', 'landingFunnel'],
        'products.index' => ['products', 'type', 'search'],
        'blogs.index' => ['blogs', 'locale', 'status', 'featured', 'search', 'locales', 'siteKey', 'sites'],
        'blogs.create' => ['blog', 'locales', 'sites'],
        'blogs.edit' => ['blog', 'locales', 'sites'],
        'blogs.automation' => ['settings', 'runs', 'sites', 'siteKey', 'canManage'],
        'shop-products.index' => ['products', 'search', 'status'],
        'shop-products.create' => ['product', 'locales'],
        'shop-products.edit' => ['product', 'locales'],
        'home-services.index' => ['services', 'search', 'status'],
        'home-services.create' => ['service', 'locales'],
        'home-services.edit' => ['service', 'locales'],
        'testimonials.index' => ['testimonials', 'search', 'status'],
        'testimonials.create' => ['testimonial', 'locales'],
        'testimonials.edit' => ['testimonial', 'locales'],
        'channel-logos.index' => ['logos', 'status'],
        'channel-logos.create' => ['logo', 'locales'],
        'channel-logos.edit' => ['logo', 'locales'],
        'menu-items.index' => ['items'],
        'menu-items.create' => ['item', 'parents', 'locales'],
        'menu-items.edit' => ['item', 'parents', 'locales'],
        'packages.index' => ['packages', 'packageGroups', 'type', 'vendor', 'search'],
        'packages.create' => ['package', 'locales'],
        'packages.edit' => ['package', 'locales'],
        'pricing-section.edit' => ['section', 'locales'],
        'landing-pages.edit' => ['section', 'brand', 'brands', 'locale'],
        'landing-experience.index' => ['incidents', 'media', 'reviews', 'brands', 'locales', 'brand'],
        'footer-settings.edit' => ['setting', 'locales'],
        'footer-links.index' => ['links'],
        'footer-links.create' => ['link', 'locales'],
        'footer-links.edit' => ['link', 'locales'],
        'social-links.index' => ['links'],
        'social-links.create' => ['link', 'locales'],
        'social-links.edit' => ['link', 'locales'],
        'admin-users.index' => ['admins'],
        'admin-users.create' => ['adminUser', 'roles'],
        'admin-users.edit' => ['adminUser', 'roles'],
        'audit-logs.index' => ['logs', 'filters'],
        'audit-logs.show' => ['auditLog'],
        'maintenance.index' => ['actions'],
    ];

    private const FIELDS = [
        'CustomerReview' => ['id', 'order_id', 'user_id', 'brand', 'locale', 'display_name', 'rating', 'body', 'status', 'publication_consented_at', 'withdrawn_at', 'moderated_by_admin_id', 'moderated_at', 'moderation_note', 'published_at', 'created_at', 'updated_at'],
        'ServiceIncident' => ['id', 'brand', 'locale', 'title', 'body', 'state', 'started_at', 'resolved_at', 'is_published', 'published_at', 'updated_by_admin_id', 'created_at', 'updated_at'],
        'SetupMedia' => ['id', 'brand', 'locale', 'guide_slug', 'title', 'description', 'kind', 'media_url', 'alt_text', 'device_model', 'player_name', 'player_version', 'tested_at', 'evidence_reference', 'publication_permission_at', 'is_published', 'published_at', 'updated_by_admin_id', 'created_at', 'updated_at'],
        'Admin' => ['id', 'name', 'email', 'role', 'must_change_password', 'password_changed_at', 'two_factor_confirmed_at', 'created_at', 'updated_at'],
        'User' => ['id', 'name', 'email', 'phone', 'phone_normalized', 'country', 'notes', 'marketing_email_consented_at', 'marketing_email_opted_out_at', 'marketing_whatsapp_consented_at', 'marketing_whatsapp_opted_out_at', 'marketing_ads_consented_at', 'marketing_ads_opted_out_at', 'assigned_admin_id', 'email_verified_at', 'renewal_email_enabled', 'renewal_email_updated_at', 'orders_count', 'trial_clicks_count', 'checkout_drafts_count', 'digital_orders_count', 'pending_follow_ups_count', 'created_at', 'updated_at'],
        'Order' => ['id', 'user_id', 'package_id', 'type', 'package', 'custom_package', 'duration', 'device_id', 'price', 'currency', 'cost_price', 'sell_price', 'profit', 'credits', 'buying_date', 'expiry_date', 'status', 'payment_status', 'payment_provider', 'payment_method', 'custom_payment_method', 'paid_amount', 'paid_currency', 'paid_at', 'messaged_at', 'messaged_by', 'note', 'total_currency', 'paid_total', 'ga_purchase_sent_at', 'created_at', 'updated_at'],
        'Purchasing' => ['id', 'purchase_date', 'item_name', 'quantity', 'cost_price', 'currency', 'note', 'created_at', 'updated_at'],
        'Package' => ['id', 'type', 'vendor', 'title', 'display_price', 'price_amount', 'duration_months', 'credits', 'sort_order', 'icon', 'icons', 'features', 'button_link', 'delay', 'badge_key', 'is_featured', 'is_available', 'free_trial_hours', 'instant_activation', 'connection_prices', 'active', 'created_at', 'updated_at'],
        'PackageTranslation' => ['id', 'package_id', 'locale', 'title', 'features'],
        'Blog' => ['id', 'site_key', 'author_id', 'cover_image', 'status', 'published_at', 'reading_time', 'is_featured', 'views', 'created_at', 'updated_at'],
        'BlogSiteSetting' => ['id', 'site_key', 'enabled', 'timezone', 'publish_time', 'locales', 'created_at', 'updated_at'],
        'BlogGenerationRun' => ['id', 'site_key', 'publishing_date', 'blog_id', 'topic', 'primary_keyword', 'revision', 'status', 'attempts', 'errors', 'published_at', 'created_at', 'updated_at'],
        'BlogPublishDelivery' => ['id', 'run_id', 'channel', 'revision', 'status', 'attempts', 'http_status', 'error_code', 'last_attempt_at', 'next_attempt_at', 'created_at', 'updated_at'],
        'BlogTranslation' => ['id', 'blog_id', 'locale', 'title', 'slug', 'excerpt', 'content', 'seo_title', 'seo_description', 'seo_keywords', 'og_title', 'og_description', 'og_image', 'canonical_url', 'schema_json'],
        'ShopProduct' => ['id', 'name', 'asin', 'link', 'image', 'sort_order', 'is_active', 'created_at', 'updated_at'],
        'ShopProductTranslation' => ['id', 'shop_product_id', 'locale', 'name'],
        'HomeService' => ['id', 'title', 'description', 'link', 'icon', 'image', 'sort_order', 'is_active', 'created_at', 'updated_at'],
        'HomeServiceTranslation' => ['id', 'home_service_id', 'locale', 'title', 'description'],
        'Testimonial' => ['id', 'author_name', 'text', 'image', 'sort_order', 'is_active', 'is_verified', 'review_date', 'country', 'device', 'verification_source', 'proof_reference', 'publication_consented_at', 'created_at', 'updated_at'],
        'TestimonialTranslation' => ['id', 'testimonial_id', 'locale', 'author_name', 'text'],
        'ChannelLogo' => ['id', 'image', 'alt_text', 'sort_order', 'is_active', 'created_at', 'updated_at'],
        'ChannelLogoTranslation' => ['id', 'channel_logo_id', 'locale', 'alt_text'],
        'MenuItem' => ['id', 'label', 'url', 'parent_id', 'sort_order', 'is_active', 'open_new_tab', 'created_at', 'updated_at'],
        'MenuItemTranslation' => ['id', 'menu_item_id', 'locale', 'label'],
        'PricingSection' => ['id', 'heading', 'subheading', 'show_reseller_label', 'credit_info', 'created_at', 'updated_at'],
        'PricingSectionTranslation' => ['id', 'pricing_section_id', 'locale', 'heading', 'subheading', 'show_reseller_label', 'credit_info'],
        'FooterSetting' => ['id', 'brand_text', 'crypto_note', 'phone', 'email', 'address', 'rights_text', 'legal_note', 'created_at', 'updated_at'],
        'FooterSettingTranslation' => ['id', 'footer_setting_id', 'locale', 'brand_text', 'crypto_note', 'address', 'rights_text', 'legal_note'],
        'FooterLink' => ['id', 'group', 'label', 'url', 'sort_order', 'is_active', 'created_at', 'updated_at'],
        'FooterLinkTranslation' => ['id', 'footer_link_id', 'locale', 'label'],
        'SocialLink' => ['id', 'platform', 'url', 'icon_class', 'sort_order', 'is_active', 'created_at', 'updated_at'],
        'SocialLinkTranslation' => ['id', 'social_link_id', 'locale', 'platform'],
        'TrialClick' => ['id', 'user_id', 'event_id', 'contact_name', 'contact_email', 'phone_normalized', 'message', 'whatsapp_contact_consented_at', 'consent_version', 'consent_source', 'consent_locale', 'click_count', 'intent', 'placement', 'package_name', 'vendor', 'value', 'currency', 'status', 'page', 'destination', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'fbp', 'fbc', 'ip', 'referrer', 'user_agent', 'trial_package_id', 'trial_started_at', 'trial_expires_at', 'trial_owner_verified_at', 'created_at', 'updated_at'],
        'CustomerActivity' => ['id', 'user_id', 'order_id', 'admin_id', 'source', 'event_id', 'event_type', 'entity_type', 'entity_id', 'contact_name', 'contact_email', 'contact_phone', 'message', 'details', 'occurred_at', 'created_at', 'updated_at'],
        'CustomerConversation' => ['id', 'user_id', 'channel', 'status', 'property_id', 'external_id', 'visitor_name', 'visitor_email', 'visitor_phone', 'started_at', 'ended_at', 'messages_count', 'created_at', 'updated_at'],
        'CustomerMessage' => ['id', 'conversation_id', 'sender_type', 'sender_name', 'content', 'sent_at', 'created_at', 'updated_at'],
        'CustomerFollowUp' => ['id', 'user_id', 'assigned_admin_id', 'created_by_admin_id', 'note', 'status', 'due_at', 'completed_at', 'created_at', 'updated_at'],
        'CustomerExternalLink' => ['id', 'user_id', 'order_id', 'source', 'entity_type', 'external_id', 'state', 'created_at', 'updated_at'],
        'CheckoutDraft' => ['id', 'user_id', 'package_id', 'device_id', 'name', 'email', 'phone', 'email_normalized', 'phone_normalized', 'package_name', 'vendor', 'connection_name', 'email_consented_at', 'whatsapp_consented_at', 'status', 'completed_order_id', 'last_activity_at', 'created_at', 'updated_at'],
        'DigitalOrder' => ['id', 'user_id', 'order_number', 'customer_name', 'customer_email', 'status', 'payment_status', 'total', 'subtotal', 'currency', 'items_count', 'created_at', 'updated_at'],
        'Referral' => ['id', 'code', 'referrer_user_id', 'referred_user_id', 'source_order_id', 'referred_order_id', 'status', 'reward_days', 'reward_currency', 'reward_value', 'reward_applied_at', 'created_at', 'updated_at'],
        'MarketingDelivery' => ['id', 'user_id', 'order_id', 'checkout_draft_id', 'workflow', 'channel', 'status', 'scheduled_at', 'sent_at', 'failed_at', 'created_at', 'updated_at'],
        'Device' => ['id', 'name', 'image', 'created_at', 'updated_at'],
        'Picture' => ['id', 'imageable_id', 'imageable_type', 'created_at', 'updated_at'],
        'AdminAuditLog' => ['id', 'admin_id', 'admin_name', 'admin_email', 'action', 'route_name', 'method', 'path', 'target_type', 'target_id', 'request_data', 'ip_address', 'user_agent', 'response_status', 'created_at'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if (!$request->routeIs('api.browser.admin.*')) {
            return $response;
        }
        if ($response instanceof RedirectResponse) {
            $errors = $request->session()->pull('errors');
            $errors = $errors instanceof \Illuminate\Support\ViewErrorBag ? $errors->getBag('default')->messages() : [];
            $error = $request->session()->pull('error');
            $url = $response->getTargetUrl();
            $parts = parse_url($url);
            $safe = !isset($parts['host']) || strcasecmp($parts['host'], $request->getHost()) === 0;
            $path = preg_replace('#^/api/browser/admin(?=/|$)#', '/admin', $parts['path'] ?? '/admin/dashboard');
            $safe = $safe && preg_match('#^/admin(?:/|$)#', $path);
            return $this->json([
                'data' => null,
                'message' => $error ?: ($errors ? 'Please review the highlighted fields.' : $request->session()->get('success', $request->session()->get('warning', 'Request completed.'))),
                'errors' => $errors ?: (object) [],
                'redirect_url' => $safe ? $path.(isset($parts['query']) ? '?'.$parts['query'] : '').(isset($parts['fragment']) ? '#'.$parts['fragment'] : '') : '/admin/dashboard',
                'csrf_token' => csrf_token(),
            ], $error || $errors ? 422 : 200);
        }

        $view = method_exists($response, 'getOriginalContent') ? $response->getOriginalContent() : null;
        if (!$view instanceof View) {
            return $response;
        }
        $name = preg_replace('/^admin\./', '', $view->name());
        abort_unless(array_key_exists($name, self::VIEWS), 500, 'This admin view has no API contract.');
        $values = $view->getData();
        $data = [];
        foreach (self::VIEWS[$name] as $key) {
            if (array_key_exists($key, $values)) {
                $data[$key] = $this->value($values[$key]);
            }
        }
        if ($name === 'security.two-factor') {
            $admin = $request->user('admin');
            abort_unless($admin && (int) ($values['admin']->id ?? 0) === (int) $admin->id, 403);
            if (!$admin->two_factor_confirmed_at) {
                $data['setup'] = ['secret' => $admin->two_factor_secret, 'provisioning_uri' => $values['provisioningUri'] ?? null];
            }
            $data['recovery_codes'] = array_values((array) $request->session()->get('admin_recovery_codes', []));
        }
        return $this->json(['data' => $data, 'view' => $name, 'csrf_token' => csrf_token()]);
    }

    private function json(array $body, int $status = 200): JsonResponse
    {
        return response()->json($body, $status)->withHeaders([
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
        ]);
    }

    private function value(mixed $value, int $depth = 0): mixed
    {
        abort_if($depth > 12, 500, 'Admin API data is nested too deeply.');
        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }
        if ($value instanceof AbstractPaginator) {
            return ['data' => $this->value($value->getCollection(), $depth + 1), 'meta' => [
                'current_page' => $value->currentPage(), 'per_page' => $value->perPage(),
                'total' => method_exists($value, 'total') ? $value->total() : null,
                'last_page' => method_exists($value, 'lastPage') ? $value->lastPage() : null,
            ]];
        }
        if ($value instanceof Model) {
            $type = class_basename($value);
            abort_unless(isset(self::FIELDS[$type]), 500, 'This admin resource has no API contract.');
            $result = [];
            foreach (self::FIELDS[$type] as $field) {
                if (array_key_exists($field, $value->getAttributes())) {
                    $attribute = $value->getAttribute($field);
                    if ($type === 'CustomerActivity' && $field === 'details') {
                        $attribute = array_intersect_key((array) $attribute, array_flip([
                            'status', 'payment_status', 'amount_minor', 'currency', 'package_id', 'invoice_status',
                            'recipient', 'error_code', 'channel', 'recorded_manually', 'conversation_id', 'message_id',
                            'assigned_admin_id', 'previous_admin_id', 'follow_up_id', 'due_at', 'subject',
                        ]));
                    }
                    $result[$field] = $this->value($attribute, $depth + 1);
                }
            }
            if ($type === 'TrialClick') {
                $result['lead_code'] = $value->lead_code;
            }
            foreach (['translations', 'children', 'user', 'assignedAdmin', 'device', 'pictures', 'order', 'referral', 'referredBy', 'marketingDeliveries', 'sourceOrder', 'referredOrder', 'completedOrder', 'admin', 'deliveries'] as $relation) {
                if ($value->relationLoaded($relation)) {
                    $result[$relation] = $this->value($value->getRelation($relation), $depth + 1);
                }
            }
            return $result;
        }
        if ($value instanceof Collection) {
            return $this->value($value->all(), $depth + 1);
        }
        if (is_array($value)) {
            $result = [];
            foreach ($value as $key => $item) {
                if (is_string($key) && preg_match('/password|passwd|secret|token|captcha_sum|otp|recovery|authorization|cookie|api[_-]?key|private[_-]?key|card|cvv|cvc|tracker|checkout_key|signature|signed_url/i', $key)) {
                    continue;
                }
                $result[$key] = $this->value($item, $depth + 1);
            }
            return $result;
        }
        abort_unless(is_scalar($value) || $value === null, 500, 'Unsupported admin API value.');
        if (is_string($value) && preg_match('#^https?://#i', $value)) {
            $url = parse_url($value);
            if (isset($url['query']) && preg_match('/(?:token|signature|tracker|checkout_key|secret|password|code)=/i', $url['query'])) {
                return '[Private signed URL omitted]';
            }
        }
        return $value;
    }
}
