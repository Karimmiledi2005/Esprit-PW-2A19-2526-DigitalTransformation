<?php
/**
 * config.env.php — Variables d'environnement locales (NE PAS COMMITTER)
 * Copier ce fichier et adapter les valeurs à votre environnement.
 */
return [
    'db_host'     => 'localhost',
    'db_user'     => 'root',
    'db_password' => '',
    'db_name'     => 'assurance',

    // SMTP (Gmail ou autre) — mettre vos propres credentials
    'mail_host'     => 'smtp.gmail.com',
    'mail_port'     => 587,
    'mail_username' => 'votre_email@gmail.com',
    'mail_password' => 'votre_app_password',
    'mail_from'     => 'votre_email@gmail.com',
    'mail_from_name'=> 'Protex Assurance',

    // Stripe (test mode) — mettre vos propres clés
    'stripe_secret_key'      => 'sk_test_VOTRE_CLE_SECRETE',
    'stripe_publishable_key' => 'pk_test_VOTRE_CLE_PUBLIQUE',

    // Infobip SMS
    'infobip_api_key'  => 'votre_cle_infobip',
    'infobip_base_url' => 'https://XXXXXX.api.infobip.com',

    // Claude API (antifraud)
    'claude_api_key' => 'votre_cle_claude',

    // GitHub OAuth
    'github_client_id'     => 'votre_client_id',
    'github_client_secret' => 'votre_client_secret',
];
