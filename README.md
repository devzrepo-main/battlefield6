# Battlefield 6 — Cross-Platform LAMP Stats (EA-ID only)

A tiny LAMP app for Raspberry Pi that tracks Battlefield 6 stats in a **single table**, keyed by **EA Account ID**. Platform-agnostic by design.

## Features
- One DB table (`players`), key = `ea_id`
- Add player by platform + username (resolved to EA ID)
- Refresh stats on demand or periodically
- Battlefield-style dark UI
- Built on plain PHP + MySQL (MariaDB) + vanilla JS

## Setup (Pi)
```bash
sudo apt update
sudo apt install apache2 mariadb-server php php-mysql libapache2-mod-php curl -y
sudo systemctl enable apache2 mariadb
sudo mariadb < sql/schema.sql

