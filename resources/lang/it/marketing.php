<?php

return [
    'checkout' => [
        'title' => 'Aggiornamenti e offerte facoltativi',
        'email' => 'Desidero ricevere via e-mail assistenza alla configurazione, promemoria di rinnovo e offerte occasionali.',
        'whatsapp' => 'Desidero ricevere su WhatsApp assistenza alla configurazione, promemoria di rinnovo e offerte occasionali.',
        'ads' => 'Autorizzo l’uso dei miei dati di contatto per pubblici pubblicitari personalizzati.',
        'optional' => 'Facoltativo. Non influisce sull’ordine e puoi annullare l’iscrizione in qualsiasi momento.',
        'privacy' => 'Informativa sulla privacy',
    ],
    'privacy' => ['title' => 'Preferenze marketing e conservazione', 'text' => 'Inviamo comunicazioni marketing via e-mail o WhatsApp solo sui canali selezionati espressamente. Usiamo i dati di contatto per pubblici pubblicitari solo con un consenso separato. Puoi revocare il consenso in qualsiasi momento. I dati di un acquisto incompleto restano per :draft_days giorni. I dettagli di consegna sono rimossi dopo :delivery_days giorni; resta solo una chiave minima senza contatti per evitare duplicati.'],
    'unsubscribe' => ['title' => 'Iscrizione annullata', 'text' => 'La preferenza è stata salvata. Non riceverai altri messaggi marketing sul canale selezionato.', 'home' => 'Torna alla home', 'confirm_title' => 'Gestisci le preferenze marketing', 'confirm_text' => 'Conferma di voler interrompere il marketing via e-mail, WhatsApp e pubblico personalizzato associato a questo contatto.', 'confirm_button' => 'Interrompi marketing', 'keep_button' => 'Mantieni preferenze'],
    'email' => ['permission' => 'Ricevi questo messaggio perché hai selezionato questo canale marketing durante il checkout.', 'unsubscribe' => 'Annulla iscrizione'],
    'workflows' => [
        'abandoned' => ['subject' => 'Serve aiuto per completare l’ordine Opplex?', 'body' => 'Ciao :name, il piano selezionato è ancora in attesa. Controlla i dettagli o chiedi aiuto al nostro team prima di effettuare l’ordine.', 'cta' => 'Controlla i piani'],
        'onboarding' => ['subject' => 'Configura l’abbonamento :package', 'body' => 'Ciao :name, il tuo abbonamento è attivo. Segui la guida per :device e contatta l’assistenza se ti serve aiuto per accedere.', 'cta' => 'Apri la guida', 'default_device' => 'dispositivo'],
        'renewal' => ['subject' => 'Il piano :package scade a breve', 'body' => 'Ciao :name, il piano :package scade il :expiry. Controlla piano e prezzo attuali prima di rinnovare.', 'cta' => 'Vedi le opzioni di rinnovo'],
        'referral' => ['subject' => 'Condividi Opplex con una persona fidata', 'body' => 'Ciao :name, il tuo link personale è pronto. Condividilo solo con chi ha chiesto un consiglio. Eventuali premi sono soggetti ai termini del programma approvati e in vigore.', 'cta' => 'Apri il link'],
    ],
];
