<?php

return [
    'checkout' => [
        'title' => 'Actualités et offres facultatives',
        'email' => 'Recevoir par e-mail des conseils d’installation, des rappels de renouvellement et des offres occasionnelles.',
        'whatsapp' => 'Recevoir sur WhatsApp des conseils d’installation, des rappels de renouvellement et des offres occasionnelles.',
        'ads' => 'J’autorise l’utilisation de mes coordonnées pour des audiences publicitaires personnalisées.',
        'optional' => 'Facultatif. Votre commande n’est pas affectée et vous pouvez vous désabonner à tout moment.',
        'privacy' => 'Politique de confidentialité',
    ],
    'privacy' => [
        'title' => 'Choix marketing et conservation',
        'text' => 'Nous envoyons des communications marketing par e-mail ou WhatsApp uniquement sur les canaux sélectionnés explicitement. Les coordonnées ne sont utilisées pour des audiences publicitaires qu’avec une autorisation distincte. Vous pouvez retirer votre accord à tout moment. Les données d’un achat inachevé sont conservées :draft_days jours. Les détails de livraison sont supprimés après :delivery_days jours ; seule une clé minimale sans coordonnées demeure pour éviter les doublons.',
    ],
    'unsubscribe' => ['title' => 'Vous êtes désabonné', 'text' => 'Votre choix a été enregistré. Aucun autre message marketing ne sera envoyé sur le canal sélectionné.', 'home' => 'Retour à l’accueil', 'confirm_title' => 'Gérer les préférences marketing', 'confirm_text' => 'Confirmez l’arrêt du marketing par e-mail, WhatsApp et audiences personnalisées lié à ce contact.', 'confirm_button' => 'Arrêter le marketing', 'keep_button' => 'Conserver mes choix'],
    'email' => ['permission' => 'Vous recevez ce message car vous avez choisi ce canal marketing lors du paiement.', 'unsubscribe' => 'Se désabonner'],
    'workflows' => [
        'abandoned' => ['subject' => 'Besoin d’aide pour terminer votre commande Opplex ?', 'body' => 'Bonjour :name, le forfait sélectionné est toujours en attente. Vérifiez les détails ou demandez de l’aide à notre équipe avant de commander.', 'cta' => 'Voir les forfaits'],
        'onboarding' => ['subject' => 'Configurez votre abonnement :package', 'body' => 'Bonjour :name, votre abonnement est actif. Suivez notre guide pour :device, puis contactez l’assistance si vous avez besoin d’aide pour vous connecter.', 'cta' => 'Ouvrir le guide', 'default_device' => 'appareil'],
        'renewal' => ['subject' => 'Votre forfait :package expire bientôt', 'body' => 'Bonjour :name, votre forfait :package expire le :expiry. Vérifiez le forfait et le tarif actuels avant de renouveler.', 'cta' => 'Voir les options de renouvellement'],
        'referral' => ['subject' => 'Partagez Opplex avec une personne de confiance', 'body' => 'Bonjour :name, votre lien de parrainage personnel est prêt. Partagez-le uniquement avec des personnes ayant demandé une recommandation. Toute récompense dépend des conditions de parrainage approuvées en vigueur.', 'cta' => 'Ouvrir le lien de parrainage'],
    ],
];
