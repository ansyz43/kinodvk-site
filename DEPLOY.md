# Деплой на Timeweb Cloud Apps

> Для деплоя на полноценный **Timeweb VPS** (с nginx + Let's Encrypt) см. [DEPLOY_TIMEWEB.md](DEPLOY_TIMEWEB.md).
> Для деплоя на **Beget** (shared-хостинг, PHP) см. [BEGET_DEPLOY.md](BEGET_DEPLOY.md).

## Подготовка (локально, один раз)

```powershell
cd C:\Users\Admin\Downloads\kino
git init   # если ещё нет
git add .
git commit -m "init: дальневосточная кинопремия 2026"

git branch -M main
git remote add origin https://github.com/ansyz43/Kinodvk.git
git push -u origin main
```

## На Timeweb Cloud Apps

1. **timeweb.cloud** → войти → **Облачные сервисы → Apps → Создать**
2. **Тип приложения:** Backend → **Node.js** (либо шаблон **Fastify**, версия Node ≥ 20)
3. **Источник:** подключить GitHub → выбрать репо `Kinodvk` → ветка `main`
4. **Команда сборки:** `npm install`
5. **Команда запуска:** `npm start`
6. **Порт приложения:** `3000`
7. **Переменные окружения:**
   ```
   NODE_ENV     = production
   PORT         = 3000
   HOST         = 0.0.0.0

   SMTP_HOST    = smtp.beget.com
   SMTP_PORT    = 465
   SMTP_SECURE  = ssl
   SMTP_USER    = noreply@kinodvk.ru
   SMTP_PASS    = <пароль почтового ящика>
   MAIL_FROM    = noreply@kinodvk.ru
   MAIL_TO      = kisuke43@gmail.com
   ```
8. **Тариф:** минимальный (256–512 МБ RAM хватит с запасом)
9. **Создать приложение** → ждать ~1–2 мин сборки
10. Получите URL вида `https://your-app.twc1.net` — открывайте, проверяйте `/api/health`

## Обновление сайта после правок

```powershell
git add .
git commit -m "обновление текстов"
git push
```

Timeweb Apps автоматически пересобирает приложение при пуше в `main`.

## Кастомный домен

В настройках приложения → **Домены** → добавить свой → прописать у регистратора CNAME на адрес который покажет Timeweb. HTTPS-сертификат Let's Encrypt выпускается автоматически.

## Чек-лист перед деплоем

- [ ] `.env` НЕ закоммичен (он в `.gitignore`)
- [ ] `node_modules/` НЕ закоммичен
- [ ] `.env.example` закоммичен (как образец)
- [ ] Почтовый ящик `noreply@<домен>` создан на Beget, пароль в SMTP_PASS
- [ ] Отправил тестовую заявку через форму — письмо пришло на kisuke43@gmail.com
- [ ] Кастомный домен настроен (опционально)
