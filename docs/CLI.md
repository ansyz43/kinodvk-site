# CLI — запуск и деплой

## Локально (Windows / PowerShell)

```powershell
cd c:\Users\Admin\Downloads\kino

# 1. Node 20+ (один раз)
node -v   # >= 20

# 2. Зависимости
npm install

# 3. .env c настройками SMTP
Copy-Item .env.example .env
notepad .env
# вписать SMTP_USER, SMTP_PASS (ящик noreply@<домен> на Beget) и MAIL_TO

# 4. Старт
npm run dev
# → http://localhost:3000
```

Открыть [http://localhost:3000](http://localhost:3000), нажать «Подать заявку», заполнить — письмо должно прилететь на `MAIL_TO` (по умолчанию `kisuke43@gmail.com`).

---

## Деплой на VPS Timeweb (Ubuntu 22.04)

### 1. Сервер
В панели Timeweb создаём VPS: 1 vCPU / 2 GB / 20 GB SSD / Ubuntu 22.04.
Привязываем домен `кинопремиядв.рф` → A-запись на IP VPS.

### 2. Подключаемся
```bash
ssh root@<IP>
adduser deploy && usermod -aG sudo deploy
su - deploy
```

### 3. Node 20 + Caddy
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs git caddy
node -v
```

### 4. Кладём проект
```bash
cd /srv
sudo mkdir kino && sudo chown deploy:deploy kino
cd kino
git clone https://github.com/ansyz43/Kinodvk.git .   # или scp с локальной машины
npm ci --omit=dev
cp .env.example .env
nano .env                     # вставить SMTP_USER/SMTP_PASS/MAIL_TO
```

### 5. systemd сервис
```bash
sudo tee /etc/systemd/system/kino.service > /dev/null <<'EOF'
[Unit]
Description=Kino site
After=network.target

[Service]
Type=simple
User=deploy
WorkingDirectory=/srv/kino
EnvironmentFile=/srv/kino/.env
ExecStart=/usr/bin/node server/index.mjs
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl daemon-reload
sudo systemctl enable --now kino
sudo systemctl status kino
```

### 6. Caddy с авто-HTTPS
```bash
sudo tee /etc/caddy/Caddyfile > /dev/null <<'EOF'
кинопремиядв.рф, www.кинопремиядв.рф {
    encode gzip zstd
    reverse_proxy 127.0.0.1:3000
}
EOF

sudo systemctl reload caddy
```
Caddy сам выпустит TLS-сертификат Let's Encrypt. Готово.

### 7. Обновление
```bash
cd /srv/kino
git pull
npm ci --omit=dev
sudo systemctl restart kino
```

---

## Проверка
```bash
curl http://localhost:3000/api/health
# {"ok":true}

curl -X POST http://localhost:3000/api/lead \
  -H "Content-Type: application/json" \
  -d '{"name":"Тест","phone":"+79991234567","company":""}'
# {"ok":true}  и письмо прилетает на MAIL_TO
```

## Логи
```bash
sudo journalctl -u kino -f
```
