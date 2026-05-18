# Сайт «Дальневосточная Кинопремия»

Промо-лендинг (HTML/CSS из брифа — точная копия) + форма «Оставить заявку» с отправкой по email.

## Документы
- [docs/TZ.md](docs/TZ.md) — техническое задание.
- [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) — архитектура и хостинг.
- [DEPLOY_TIMEWEB.md](DEPLOY_TIMEWEB.md) — деплой на Timeweb VPS.
- [BEGET_DEPLOY.md](BEGET_DEPLOY.md) — деплой на Beget (shared, PHP).

## Структура
```
site/index.html   — макет 1:1 (с добавленной модалкой формы)
site/app.js       — модалка + fetch /mail.php или /api/lead
site/mail.php     — PHP-обработчик для Beget (mail() / SMTP)
server/index.mjs  — Fastify: статика + /api/lead → SMTP
.env.example      — SMTP_HOST, SMTP_USER, SMTP_PASS, MAIL_TO …
```

## Быстрый старт (Node.js)
```powershell
npm install
Copy-Item .env.example .env   # вписать SMTP_* и MAIL_TO
npm run dev
# открыть http://localhost:3000
```

## Хостинг
- **Timeweb VPS** (Node.js) — [DEPLOY_TIMEWEB.md](DEPLOY_TIMEWEB.md).
- **Beget** (shared, PHP) — [BEGET_DEPLOY.md](BEGET_DEPLOY.md).
