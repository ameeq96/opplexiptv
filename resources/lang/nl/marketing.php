<?php

return [
    'checkout' => [
        'title' => 'Optionele updates en aanbiedingen',
        'email' => 'Stuur mij per e-mail installatiehulp, verlengingsherinneringen en af en toe een aanbieding.',
        'whatsapp' => 'Stuur mij via WhatsApp installatiehulp, verlengingsherinneringen en af en toe een aanbieding.',
        'ads' => 'Ik geef toestemming om mijn contactgegevens te gebruiken voor gepersonaliseerde advertentiedoelgroepen.',
        'optional' => 'Optioneel. Dit heeft geen invloed op je bestelling en afmelden kan altijd.',
        'privacy' => 'Privacybeleid',
    ],
    'privacy' => ['title' => 'Marketingkeuzes en bewaartermijn', 'text' => 'We sturen alleen marketing via e-mail of WhatsApp voor kanalen die je uitdrukkelijk kiest. Contactgegevens worden alleen met aparte toestemming voor advertentiedoelgroepen gebruikt. Je kunt toestemming altijd intrekken. Gegevens van een onafgeronde checkout blijven :draft_days dagen. Bezorgdetails worden na :delivery_days dagen verwijderd; alleen een minimale sleutel zonder contactgegevens blijft tegen dubbele berichten.'],
    'unsubscribe' => ['title' => 'Je bent afgemeld', 'text' => 'Je voorkeur is opgeslagen. Via het gekozen kanaal sturen we geen marketingberichten meer.', 'home' => 'Terug naar home', 'confirm_title' => 'Marketingvoorkeuren beheren', 'confirm_text' => 'Bevestig dat je e-mail-, WhatsApp- en gepersonaliseerde doelgroepmarketing voor dit contact wilt stoppen.', 'confirm_button' => 'Marketing stoppen', 'keep_button' => 'Voorkeuren behouden'],
    'email' => ['permission' => 'Je ontvangt dit omdat je dit marketingkanaal bij het afrekenen hebt gekozen.', 'unsubscribe' => 'Afmelden'],
    'workflows' => [
        'abandoned' => ['subject' => 'Hulp nodig bij het afronden van je Opplex-bestelling?', 'body' => 'Hallo :name, je gekozen abonnement staat nog klaar. Controleer de gegevens of vraag ons team om hulp voordat je bestelt.', 'cta' => 'Abonnementen bekijken'],
        'onboarding' => ['subject' => 'Stel je :package-abonnement in', 'body' => 'Hallo :name, je abonnement is actief. Volg onze installatiegids voor :device en neem contact op met support als inloggen niet lukt.', 'cta' => 'Installatiegids openen', 'default_device' => 'apparaat'],
        'renewal' => ['subject' => 'Je :package-abonnement verloopt binnenkort', 'body' => 'Hallo :name, je :package-abonnement verloopt op :expiry. Controleer het huidige abonnement en de prijs voordat je verlengt.', 'cta' => 'Verlengopties bekijken'],
        'referral' => ['subject' => 'Deel Opplex met iemand die je vertrouwt', 'body' => 'Hallo :name, je persoonlijke verwijzingslink is klaar. Deel hem alleen met mensen die om een streamingadvies hebben gevraagd. Een beloning valt onder de geldende goedgekeurde voorwaarden.', 'cta' => 'Verwijzingslink openen'],
    ],
];
