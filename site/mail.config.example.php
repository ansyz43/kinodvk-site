<?php
// Скопируйте этот файл в mail.config.php рядом и заполните.
// mail.config.php НЕ должен попадать в git (см. .gitignore).
return [
    // 'mail' — через PHP mail() (лимит Beget ≈30 писем/мин)
    // 'smtp' — через smtp.beget.com (надёжнее, требует ящик на домене)
    'mode'        => 'mail',

    'to'          => 'kisuke43@gmail.com',
    'from_email'  => 'noreply@kinodvk.ru',   // ВАЖНО: ящик должен существовать на домене
    'from_name'   => 'Кинопремия — форма',
    'subject'     => 'Новая заявка — Кинопремия',

    // SMTP параметры (используются только при mode=smtp):
    'smtp_host'   => 'smtp.beget.com',
    'smtp_port'   => 465,         // 465=SSL, 587/2525=STARTTLS
    'smtp_secure' => 'ssl',       // 'ssl' | 'tls'
    'smtp_user'   => 'noreply@kinodvk.ru',
    'smtp_pass'   => 'ПАРОЛЬ_ОТ_ПОЧТОВОГО_ЯЩИКА',
];
