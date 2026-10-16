<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_page_contents', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('brand', 20);
            $table->string('locale', 12)->default('en');
            $table->string('hero_heading', 160);
            $table->text('hero_description');
            $table->string('guide_heading', 160);
            $table->text('guide_intro');
            $table->json('guide_sections');
            $table->json('faqs');
            $table->json('highlights');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->unique(['brand', 'locale']);
        });

        $pages = [
            'trex' => [
                'hero_heading' => 'Trex IPTV 8K. A year, your way.',
                'hero_description' => 'Explore the yearly subscription, check what your setup needs and review the current plan before you decide.',
                'guide_heading' => 'Think through the year. Start with your setup.',
                'guide_intro' => 'Choosing a twelve-month IPTV subscription is easier when you know what you need from it. This page lists the current Trex IPTV 8K yearly option from the Opplex IPTV catalogue. Prices and plan details load from that catalogue; your order continues through the central website to Opplexify checkout. Take a moment to check what matters to your household before paying.',
                'guide_sections' => [
                    ['heading' => 'Three things to confirm first', 'body' => 'A yearly plan is a longer commitment than a short test. Start with the screen you actually use, the content you want to watch and how many people need access at once. A familiar device name alone does not answer all three.'],
                    ['heading' => 'Your exact setup', 'body' => 'Share your device model, operating system and intended player with support. Ask whether that combination works and whether a separate app or app licence is needed.'],
                    ['heading' => 'Your viewing priorities', 'body' => 'Ask about the specific channels or languages important to you and availability in your location. Do not assume a brand name or broad catalogue description confirms an individual channel.'],
                    ['heading' => 'Your connections', 'body' => "Explain whether you plan to watch on one screen or several at the same time. Confirm the plan's simultaneous connection limit and any extra cost before choosing."],
                ],
                'faqs' => [
                    ['question' => 'Does “8K” in the name guarantee 8K playback?', 'answer' => 'No. Trex IPTV 8K is the brand name used on this page, not a promise that streams are available in 8K. Playback quality depends on the source, service, player, device, display and connection. Ask support what is available for your intended setup rather than buying on the name alone.'],
                    ['question' => 'What if I am still testing my setup?', 'answer' => 'Resolve compatibility questions before committing to a year. Ask support whether a suitable way to check the service is currently available. Do not assume a free trial, cancellation option or refund is included; read the current terms and ask about anything unclear.'],
                    ['question' => 'What should I check on the order screen?', 'answer' => 'Confirm the selected plan, its full duration, currency, final total and any fees. Check the customer name and email carefully, too. Keep your payment confirmation and order reference so support can identify the purchase if you need help later.'],
                ],
                'highlights' => ['Check the full yearly commitment', 'Confirm your exact device and player', 'Review the final total before paying'],
            ],
            'opplex' => [
                'hero_heading' => 'Opplex IPTV. Your plan. Your screen.',
                'hero_description' => "Start with the screen you already use. Compare the current subscription options, check your device with our support team and choose a plan when you're ready.",
                'guide_heading' => 'New to IPTV? Start with your setup.',
                'guide_intro' => "You don't need to know every technical term. A few checks now make it easier to choose an Opplex IPTV subscription that fits how you actually watch.",
                'guide_sections' => [
                    ['heading' => "A player and a subscription aren't the same thing", 'body' => 'An IPTV player is the app you use to open a service on your screen. Installing one does not, by itself, provide a subscription or confirm that your chosen plan works with it. Before paying, ask which player suits your device and whether that player has a separate licence or fee.'],
                    ['heading' => 'Tell support your exact device', 'body' => 'For a Smart TV, note the brand, model and operating system. For a Fire TV Stick or Android TV box, include the model and the name of any player you already use. That gives support something specific to check, rather than a guess based on the word “TV”.'],
                    ['heading' => 'Choose for the way you use it', 'body' => "One person moving between a phone and a television is not the same as two people watching at once. Check the simultaneous connection allowance on the selected plan. Also confirm any particular content you want before ordering; don't assume it is included because a service supports your device."],
                    ['heading' => 'What happens when you continue to checkout?', 'body' => 'This page gets its plan catalogue from the main Opplex IPTV website. When you choose a package, the central website passes that selection to Opplexify. Review the package and final amount there before continuing to SafePay. Opening checkout does not complete a payment.'],
                    ['heading' => 'Keep a useful order reference', 'body' => 'Keep your order confirmation or invoice after payment. If something needs checking, the order reference is more useful to support than a screenshot of your bank balance. Never send a card number, password or one-time verification code in chat.'],
                ],
                'faqs' => [
                    ['question' => 'Should I buy a new device first?', 'answer' => 'Not necessarily. Start by asking about the device you own. Share its model and player app with support, then decide whether you need a different setup. The device list above is a starting point, not a guarantee for every model.'],
                    ['question' => 'Can I ask a question before choosing a plan?', 'answer' => 'Yes. Use WhatsApp or the live chat icon on this page. Tell the team you are comparing Opplex IPTV plans and mention your device, how many people will watch at the same time, and anything you need confirmed before buying.'],
                ],
                'highlights' => ['Start with the screen you own', 'Separate player fees from the subscription', 'Keep your order reference after payment'],
            ],
            'geo' => [
                'hero_heading' => 'Geo IPTV. Find your fit.',
                'hero_description' => 'Compare GEO World IPTV plan lengths with your own viewing routine in mind. Check the full price, the connection allowance and your device before taking the next step.',
                'guide_heading' => 'Choose with context. Not just a price.',
                'guide_intro' => 'A useful comparison starts with what you actually need. Before choosing a Geo IPTV plan, write down your device, how many people may watch at once and the length of time you want to commit. Those details make the options easier to judge.',
                'guide_sections' => [
                    ['heading' => 'Put the whole term on the same scale', 'body' => 'Compare the 1-, 3-, 6- and 12-month options by looking at the total term price first. To work out a monthly equivalent, divide that total by the number of months. This is a comparison tool, not a promise of monthly billing. Check the amount due at checkout, including any charges shown there, and compare the same connection allowance. A longer term is not automatically better value for your circumstances.'],
                    ['heading' => 'Separate devices from simultaneous connections', 'body' => 'A list of supported devices does not tell you how many screens can play at the same time. Ask about the connection limit for the exact plan you are considering. Share your television or device model, operating system and preferred player with support. If you expect to switch between a television and a phone, ask how that works before paying rather than assuming both can play together.'],
                    ['heading' => 'Make your viewing checklist specific', 'body' => 'If a particular language or channel matters to you, ask support to confirm it for the selected plan. Give the exact name and explain where you intend to use the service. A broad catalogue label is not confirmation of every language, channel or location. Keep the reply alongside your chosen plan details, and review any access requirements and renewal terms before completing checkout. The aim is an informed choice, not simply the longest subscription.'],
                ],
                'faqs' => [
                    ['question' => 'Is a monthly equivalent the amount charged each month?', 'answer' => 'Not necessarily. It spreads a term price over its duration so you can compare options. Use the selected plan and checkout summary to confirm the actual amount and payment schedule. Ask support if either is unclear.'],
                    ['question' => 'What should I confirm before choosing a longer plan?', 'answer' => 'Confirm the device and player setup, simultaneous connection limit, required languages or channels, and when access begins and ends. Read the applicable renewal and refund terms as well. Talk through any unanswered questions with support before committing.'],
                ],
                'highlights' => ['Compare the total term price first', 'Check simultaneous connection allowances', 'Confirm your viewing priorities with support'],
            ],
            '5g' => [
                'hero_heading' => '5G Live IPTV. Your setup.',
                'hero_description' => 'Compare the current 5G IPTV plans, then check your home or mobile setup. Not sure where to start? Our connection guide walks through the useful checks before you choose.',
                'guide_heading' => 'Check the connection. Then choose the plan.',
                'guide_intro' => 'Before comparing 5G IPTV plans, check the connection your screen will use. 5G Live IPTV is a brand name, not a mobile network service or a requirement to use 5G. Your internet connection is separate. These checks help you prepare a home or mobile setup without assuming that a different plan will solve a connection problem.',
                'guide_sections' => [
                    ['heading' => 'Wi-Fi, mobile data or a cable?', 'body' => 'If your device supports Ethernet, a cable removes the local Wi-Fi hop, but it cannot fix broadband or service faults. With Wi-Fi, try the same device nearer the router and pause other large transfers as a comparison. Mobile data can be useful away from home, but check your carrier’s data allowance, tethering rules and possible extra charges first. Keep the device in one place while comparing connections; changing location and network together makes the result harder to interpret.'],
                    ['heading' => 'Change one thing at a time', 'body' => 'Note whether the problem is an app failing to open, an error signing in, or playback stopping. Check another ordinary website on the same device. If other services also struggle, investigate the connection first. If permitted by your account’s connection limit, compare the same app on another supported device. A difference is a clue, not proof of the cause. Record what changed before restarting or reinstalling anything; avoid factory resets as an initial test.'],
                    ['heading' => 'Give support a useful starting point', 'body' => 'Share the device model, operating system and app version, connection type, exact error text, and when it happened, including your time zone. Mention which checks you tried and whether the issue changes on another connection. Crop screenshots to hide personal information. Never send passwords, one-time codes, full payment details or service login credentials in a public message.'],
                ],
                'faqs' => [
                    ['question' => 'Do I need 5G mobile data?', 'answer' => 'No. The brand name does not specify a required network generation. Confirm your device, app and intended connection with support before purchasing.'],
                    ['question' => 'Will Ethernet stop buffering?', 'answer' => 'Not necessarily. It can help isolate local Wi-Fi as a factor, while issues elsewhere in the connection, device, app or service may remain.'],
                    ['question' => 'Should I change my plan first?', 'answer' => 'Check the setup first. Plan duration is not a diagnosis of a network problem. Compare current plan details and ask support before paying for a different option.'],
                ],
                'highlights' => ['No 5G mobile network requirement', 'Compare one setup variable at a time', 'Share error details without private credentials'],
            ],
        ];

        $now = now();
        foreach ($pages as $brand => $page) {
            foreach (['guide_sections', 'faqs', 'highlights'] as $field) {
                $page[$field] = json_encode($page[$field], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            }
            DB::table('landing_page_contents')->insert($page + [
                'brand' => $brand, 'locale' => 'en', 'is_published' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_page_contents');
    }
};
