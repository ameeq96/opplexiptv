<?php

return [
    'common' => [
        'send' => 'Envoyer',
        'read_more_about' => 'En savoir plus sur :title',
        'invalid_phone' => 'Veuillez saisir un numéro de téléphone valide.',
        'copy_link_prompt' => 'Copiez ce lien :',
        'default_meta_title' => 'Opplex IPTV',
        'default_meta_description' => 'Abonnements IPTV premium, guides de streaming et assistance Opplex IPTV.',
    ],

    'native_shell' => [
        'menu_toggle' => 'Ouvrir ou fermer le menu',
    ],

    'fields' => [
        'quantity' => 'Quantité',
        'coupon' => 'Code promo',
        'payment_method' => 'Mode de paiement',
    ],

    'blog' => [
        'page_aria' => 'Page du blog',
        'page_summary' => 'Guides IPTV, conseils de configuration et actualités du streaming',
        'breadcrumb_aria' => 'Fil d’Ariane',
        'date_format' => 'j M Y',
        'views' => 'Vues : :count',
        'share' => 'Partager',
        'copy_link' => 'Copier le lien',
        'copied' => 'Copié !',
        'share_on' => 'Partager sur :network',
        'written_by' => 'Rédigé par',
    ],

    'activation' => [
        'page_title' => 'Guide légal de l’IPTV',
        'get_instructions' => 'Voir les instructions',
        'instructions_heading' => 'Saisissez votre numéro de commande',
        'order_number' => 'Numéro de commande',
        'order_placeholder' => 'p. ex. 12345 ou ABC-789',
        'request_help' => 'Nous enverrons votre demande d’activation.',
        'activate' => 'Activer',
        'valid_order' => 'Veuillez saisir un numéro de commande valide.',
        'trouble' => 'Un problème ? Vous pouvez aussi nous écrire directement après avoir saisi votre code.',
        'send_label' => 'Envoyer la demande d’activation',
        'howto_title' => 'Comment activer votre abonnement Opplex IPTV',
        'howto_description' => 'Activez votre abonnement IPTV en quelques étapes simples après votre achat.',
        'steps' => [
            'enter_name' => 'Saisissez votre numéro de commande',
            'enter_text' => 'Ouvrez la page d’activation et saisissez le numéro de commande reçu après votre achat.',
            'request_name' => 'Envoyez votre demande d’activation',
            'request_text' => 'Appuyez sur Activer pour envoyer votre numéro de commande à notre équipe d’assistance sur WhatsApp.',
            'verify_name' => 'Nous vérifions et activons',
            'verify_text' => 'Notre équipe vérifie votre commande et active votre ligne IPTV, généralement en quelques minutes.',
            'stream_name' => 'Commencez à regarder',
            'stream_text' => 'Ouvrez votre application IPTV, connectez-vous avec les informations que nous vous envoyons et regardez vos contenus sur n’importe quel appareil.',
        ],
        'whatsapp_request' => 'Demande d’activation',
        'whatsapp_order' => 'Commande n° : :order',
        'whatsapp_from' => 'Depuis : :url',
    ],

    'redirect' => [
        'title' => 'Redirection…',
        'preparing' => 'Préparation de votre contenu',
        'ad_loading' => 'Veuillez patienter pendant le chargement de la page.',
        'click_to_download' => 'Continuer vers le téléchargement',
        'noscript' => 'JavaScript est désactivé. Utilisez le lien direct ci-dessous pour continuer.',
        'open_direct' => 'Ouvrir le lien direct',
        'wait_seconds' => 'Veuillez patienter :seconds secondes…',
        'wait_one_second' => 'Veuillez patienter 1 seconde…',
        'wait_moment' => 'Veuillez patienter un instant…',
        'redirecting' => 'Redirection…',
    ],

    'not_found' => [
        'title' => '404 - Page introuvable',
        'message' => 'Oups ! La page que vous recherchez n’existe pas.',
        'home_button' => 'Retour à l’accueil',
        'try_links' => 'Essayez plutôt l’un de ces liens :',
        'home' => 'Accueil',
        'about' => 'À propos',
        'contact' => 'Contact',
        'faqs' => 'FAQ',
        'pricing' => 'Tarifs',
    ],

    'email' => [
        'common' => [
            'greeting' => 'Bonjour,',
            'name' => 'Nom',
            'email' => 'E-mail',
            'phone' => 'Téléphone',
            'message' => 'Message',
            'regards' => 'Cordialement,',
            'not_available' => 'Non disponible',
        ],
        'contact' => [
            'page_title' => 'Envoi du formulaire de contact',
            'heading' => 'Formulaire de contact',
            'intro' => 'Vous avez reçu un nouveau message depuis le formulaire de contact de votre site. En voici les détails :',
        ],
        'contact_reply' => [
            'subject' => 'Merci d’avoir contacté Opplex IPTV',
            'intro' => 'Merci d’avoir contacté Opplex IPTV. Nous avons bien reçu votre message et vous répondrons dès que possible.',
            'whatsapp_info' => 'Pour une réponse plus rapide, vous pouvez également nous contacter sur WhatsApp',
            'signature' => 'Cordialement, Opplex IPTV',
        ],
        'subscribe' => [
            'page_title' => 'Inscription à la newsletter',
            'heading' => 'Confirmation d’inscription',
            'intro' => 'Merci de vous être inscrit à notre newsletter. Voici les informations que nous avons reçues :',
        ],
        'buy_now' => [
            'subject' => 'Merci pour votre intérêt - Opplex IPTV',
            'whatsapp_message' => 'Bonjour, le forfait :package (:price) m’intéresse.',
        ],
        'checkout' => [
            'customer_subject' => 'Votre commande a bien été reçue :order_suffix',
            'admin_title' => 'Une nouvelle commande vient d’être passée.',
            'customer_title' => 'Merci pour votre commande ! Nous avons reçu les informations ci-dessous.',
            'admin_intro' => 'Un client vient de remplir le formulaire de commande. En voici le récapitulatif.',
            'customer_intro' => 'Nous traiterons votre demande sous peu. Un membre de notre équipe vous contactera si nécessaire.',
            'order_number' => 'Commande n°',
            'customer' => 'Client',
            'phone' => 'Téléphone',
            'package' => 'Forfait',
            'type' => 'Type',
            'provider' => 'Fournisseur',
            'device' => 'Appareil',
            'quantity' => 'Quantité',
            'payment_method' => 'Mode de paiement',
            'subscription_price' => 'Prix de l’abonnement',
            'connection_price' => 'Prix de la connexion',
            'total' => 'Total',
            'expiry' => 'Expiration',
            'notes' => 'Notes',
            'admin_follow_up' => 'Veuillez contacter le client et terminer l’activation.',
            'customer_follow_up' => 'Si vous avez des questions, répondez simplement à cet e-mail.',
        ],
    ],

    'product' => [
        'share' => 'Partager le produit',
        'buy' => 'Acheter le produit',
        'digital_badge' => 'Numérique',
        'affiliate_badge' => 'Affilié',
        'digital_purchase_message' => 'Bonjour, je souhaite acheter :name (:price). Lien du produit : :url',
        'affiliate_purchase_message' => 'Bonjour, le produit :name m’intéresse. Lien du produit : :url',
        'digital_description' => 'Achetez :name sur Opplex IPTV. Prix : :price.',
        'affiliate_description' => 'Découvrez :name sur Opplex IPTV.',
    ],

    'checkout' => [
        'order_received' => 'Commande reçue. Votre numéro de commande est le n° :id.',
        'default_plan' => 'Abonnement premium, 1 mois × 1',
        'month_one' => '/ 1 mois',
        'months' => '/ :count mois',
        'credits' => '/ :count crédits',
        'credits_label' => '/ Crédits',
    ],

    'movies' => [
        'no_overview' => 'Aucun résumé n’est disponible.',
        'featured_title' => 'Contenu IPTV à la une',
        'fallback_movies' => 'Regardez des films premium, des chaînes en direct, du sport et du divertissement en HD/4K avec Opplex IPTV.',
        'fallback_series' => 'Profitez de séries populaires et de divertissements du monde entier avec une diffusion IPTV fluide sur tous vos appareils.',
        'fallback_action' => 'Regardez de l’action, du sport, les actualités et des contenus familiaux grâce à des forfaits IPTV fiables.',
    ],

    'assistant' => [
        'quick_echo' => 'Ouvrir :target',
    ],

    'phone' => [
        'country_list_aria' => 'Sélecteur de pays',
    ],

    'fancybox' => [
        'close' => 'Fermer',
        'next' => 'Suivant',
        'previous' => 'Précédent',
        'error' => 'Impossible de charger le contenu demandé. Veuillez réessayer plus tard.',
        'play_start' => 'Démarrer le diaporama',
        'play_stop' => 'Mettre le diaporama en pause',
        'full_screen' => 'Plein écran',
        'thumbnails' => 'Miniatures',
        'download' => 'Télécharger',
        'share' => 'Partager',
        'zoom' => 'Zoomer',
        'trailer_unavailable' => 'Bande-annonce indisponible.',
    ],

    'admin' => [
        'flash' => [
            'client_created' => 'Client créé avec succès.',
            'client_updated' => 'Client mis à jour avec succès.',
            'client_deleted' => 'Client supprimé avec succès.',
            'no_clients_selected' => 'Aucun client n’a été sélectionné.',
            'purchase_created' => 'Achat créé avec succès.',
            'purchase_updated' => 'Achat mis à jour avec succès.',
            'purchase_deleted' => 'Achat supprimé avec succès.',
            'reseller_order_created' => 'Commande revendeur créée avec succès.',
            'reseller_order_updated' => 'Commande revendeur mise à jour avec succès.',
            'reseller_order_deleted' => 'Commande revendeur supprimée avec succès.',
        ],
    ],
];
