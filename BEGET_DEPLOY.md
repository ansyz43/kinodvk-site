# Деплой на Beget

## Что у нас за сайт

Сайт собирается локально в папку `site`.

На Beget нужно загружать не весь проект, а только содержимое папки:

```text
C:\Users\Admin\Downloads\kino\site
```

Готовый архив для Beget создается командой:

```powershell
powershell -ExecutionPolicy Bypass -File tools\package-beget.ps1
```

После этого появится файл:

```text
C:\Users\Admin\Downloads\kino\dist\beget-site.zip
```

Именно его нужно загрузить в Beget.

## Первый запуск на аккаунте заказчика

1. Зайти в панель Beget заказчика.
2. Открыть раздел `Сайты`.
3. Проверить, к какой папке привязан домен.
4. Открыть файловый менеджер этой папки.
5. Перейти в корень сайта, где должен лежать `index.html`.
6. Загрузить `dist\beget-site.zip`.
7. Распаковать архив в корень сайта.
8. Проверить, что структура такая:

```text
index.html
styles.css
app.js
anim.js
assets/
about.html
nominations.html
archive.html
privacy.html
...
```

Неправильно:

```text
site/index.html
```

Правильно:

```text
index.html
```

## Как обновлять сайт после правок

Каждый раз после правок:

```powershell
powershell -ExecutionPolicy Bypass -File tools\package-beget.ps1
```

Потом:

1. В Beget открыть файловый менеджер.
2. Загрузить новый `dist\beget-site.zip`.
3. Распаковать с заменой файлов.
4. Открыть сайт в браузере и проверить.

## Более удобный вариант для частых правок

Если правок будет много, лучше подключить WinSCP по SFTP/FTP:

1. В Beget открыть FTP/SFTP-доступы.
2. Создать или взять существующий FTP-доступ.
3. В WinSCP подключиться к серверу.
4. Слева открыть локальную папку:

```text
C:\Users\Admin\Downloads\kino\site
```

5. Справа открыть папку сайта на Beget.
6. После каждой правки запускать:

```powershell
powershell -ExecutionPolicy Bypass -File tools\package-beget.ps1
```

7. В WinSCP синхронизировать папку `site` с папкой сайта.

Так обновлять быстрее, чем каждый раз вручную распаковывать архив.

## Форма обратной связи на Beget (`mail.php`)

В `site/` лежит файл `mail.php` — он работает на обычном Beget-хостинге (PHP 8.x).
Фронтенд (`app.js`) сначала пробует `/mail.php`, и только если его нет — `/api/lead` (это для VPS-сценария).

### Шаг 1. Создать конфиг

Скопируйте `site/mail.config.example.php` → `site/mail.config.php` и впишите свои данные:

```php
<?php
return [
    'mode'        => 'mail',                 // 'mail' или 'smtp'
    'to'          => 'kisuke43@gmail.com',
    'from_email'  => 'noreply@kinodvk.ru',   // ВАЖНО: ящик должен существовать в Beget
    'from_name'   => 'Кинопремия — форма',
    'subject'     => 'Новая заявка — Кинопремия',

    // нужно только если mode = 'smtp':
    'smtp_host'   => 'smtp.beget.com',
    'smtp_port'   => 465,                    // 465=SSL, 587/2525=STARTTLS
    'smtp_secure' => 'ssl',
    'smtp_user'   => 'noreply@kinodvk.ru',
    'smtp_pass'   => 'пароль_от_ящика',
];
```

`mail.config.php` загружается в `site/` рядом с `mail.php`. В git его не коммитим
(он уже в `.gitignore`).

### Шаг 2. Создать почтовый ящик в Beget

В панели Beget → «Почта» → создать ящик вида `noreply@вашдомен.ru`
(или `info@`, как удобно). Запомните пароль — он пойдёт в `smtp_pass`.

Без ящика на домене провайдер забракует поле `From` и письма уедут в спам.

### Шаг 3. Выбрать режим отправки

Режим              | Когда выбирать
------------------ | ---------------------------------------------------------
`mail` (по умолч.) | Простой вариант. Лимит Beget — **30 писем/мин**. Можно тестировать без SMTP-пароля.
`smtp`             | Надёжнее: письма реже попадают в спам, есть аутентификация. SMTP-сервер `smtp.beget.com`, порт **465 (SSL)** или **587 / 2525 (STARTTLS)**.

### Шаг 4. Загрузить и проверить

После того как загрузили `site/` через ZIP или WinSCP, проверьте:

1. Откройте `https://вашдомен.ru/submit.html`, нажмите «Связаться», заполните форму.
2. Через несколько секунд должно прийти письмо на `kisuke43@gmail.com`.
3. Если письмо не пришло — гляньте логи в панели Beget («Журналы» → `error.log` сайта).
   Текст ошибки SMTP/`mail()` выводится туда через `error_log()`.

### Быстрая ручная проверка из PowerShell

```powershell
curl.exe -X POST https://вашдомен.ru/mail.php `
  -H "Content-Type: application/json" `
  -d '{"name":"Тест","phone":"+79991234567","page":"/test"}'
```

Должно вернуться `{"ok":true}`.
