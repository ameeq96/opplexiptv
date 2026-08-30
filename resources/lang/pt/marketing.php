<?php

return [
    'checkout' => [
        'title' => 'Atualizações e ofertas opcionais',
        'email' => 'Quero receber por e-mail ajuda de configuração, lembretes de renovação e ofertas ocasionais.',
        'whatsapp' => 'Quero receber no WhatsApp ajuda de configuração, lembretes de renovação e ofertas ocasionais.',
        'ads' => 'Autorizo a utilização dos meus contactos para públicos de publicidade personalizada.',
        'optional' => 'Opcional. Não afeta a encomenda e pode cancelar a qualquer momento.',
        'privacy' => 'Política de privacidade',
    ],
    'privacy' => ['title' => 'Preferências de marketing e conservação', 'text' => 'Só enviamos marketing por e-mail ou WhatsApp nos canais escolhidos expressamente. Só usamos contactos para públicos publicitários com autorização separada. Pode retirar a autorização a qualquer momento. Os dados de uma compra incompleta ficam :draft_days dias. Os detalhes de entrega são removidos após :delivery_days dias; fica apenas uma chave mínima sem contactos para impedir duplicados.'],
    'unsubscribe' => ['title' => 'Subscrição cancelada', 'text' => 'A sua preferência foi guardada. Não enviaremos mais mensagens de marketing no canal selecionado.', 'home' => 'Voltar ao início', 'confirm_title' => 'Gerir preferências de marketing', 'confirm_text' => 'Confirme que pretende parar o marketing por e-mail, WhatsApp e públicos personalizados associado a este contacto.', 'confirm_button' => 'Parar marketing', 'keep_button' => 'Manter preferências'],
    'email' => ['permission' => 'Recebeu esta mensagem porque selecionou este canal de marketing no checkout.', 'unsubscribe' => 'Cancelar subscrição'],
    'workflows' => [
        'abandoned' => ['subject' => 'Precisa de ajuda para concluir a encomenda Opplex?', 'body' => 'Olá :name, o plano escolhido continua pendente. Reveja os dados ou peça ajuda à nossa equipa antes de encomendar.', 'cta' => 'Rever planos'],
        'onboarding' => ['subject' => 'Configure a subscrição :package', 'body' => 'Olá :name, a sua subscrição está ativa. Siga o nosso guia para :device e contacte o suporte se precisar de ajuda para iniciar sessão.', 'cta' => 'Abrir guia', 'default_device' => 'dispositivo'],
        'renewal' => ['subject' => 'O plano :package expira em breve', 'body' => 'Olá :name, o plano :package expira em :expiry. Reveja o plano e o preço atuais antes de renovar.', 'cta' => 'Ver opções de renovação'],
        'referral' => ['subject' => 'Partilhe a Opplex com alguém de confiança', 'body' => 'Olá :name, o seu link pessoal está pronto. Partilhe-o apenas com quem pediu uma recomendação. Qualquer recompensa depende dos termos do programa aprovados e em vigor.', 'cta' => 'Abrir link'],
    ],
];
