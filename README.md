# Tracker Abonamente

Aplicație pentru urmărirea abonamentelor lunare. Laravel 13 · PostgreSQL · Alpine.js · CSS vanilla · Reverb (WebSockets).

## Pornire

Necesită [Docker Desktop](https://www.docker.com/products/docker-desktop/) pornit.

```bash
git clone https://github.com/mateid219/tracker-abonamente.git
cd tracker-abonamente
docker compose up -d --build
```

- Aplicația: http://localhost:8080
- Emailuri (Mailpit): http://localhost:8080/mail

Link public pentru testeri:

```bash
docker compose --profile tunnel up -d --build
./docker/tunnel-url.sh
```

## Containere

| Container | Ce face |
|---|---|
| `web` | Nginx: primește cererile din browser, servește CSS/JS și le trimite mai departe la `app`, `reverb` (`/app/*`) și `mailpit` (`/mail`). Singurul expus, pe portul 8080. |
| `app` | Laravel (PHP-FPM): logica aplicației. La pornire rulează migrările și creează conturile demo. |
| `reverb` | Serverul WebSocket (Laravel Reverb): actualizări instant între ferestrele deschise. |
| `pgsql` | Baza de date PostgreSQL. Datele se păstrează în volumul `pgsql-data`. |
| `mailpit` | Prinde toate emailurile trimise de aplicație și le afișează într-un inbox web. |
| `tunnel` | Cloudflare Tunnel: link public HTTPS pentru testeri. Pornește doar cu `--profile tunnel`. |

## Emailuri (Mailpit)

Aplicația nu trimite emailuri reale: verificarea contului și resetarea parolei ajung în Mailpit.

1. Deschide http://localhost:8080/mail (sau `<link public>/mail`).
2. Autentifică-te cu `tester` / `tester` (datele inbox-ului, nu ale unui cont din aplicație).
3. Deschide emailul și apasă butonul din el.

Toți testerii văd același inbox, deci folosiți adrese inventate (ex. `ana@test.ro`). Pentru altă parolă a inbox-ului, adaugă în `.env` `MAILPIT_PASSWORD=parola-ta` și repornește cu `docker compose up -d`.

## Conturi demo

| Rol | Email | Parolă |
|---|---|---|
| Administrator | `admin@example.test` | `Admin-Parola-2026` |
| Utilizator | `demo@example.test` | `Demo-Parola-2026` |

## Structura proiectului

| Cale | Conținut |
|---|---|
| `routes/web.php` | Rutele aplicației |
| `routes/channels.php` | Cine poate asculta canalele WebSocket |
| `app/Http/Controllers/SubscriptionController.php` | CRUD abonamente și dashboard |
| `app/Http/Controllers/Admin/UserController.php` | CRUD utilizatori (soft delete, restaurare) |
| `app/Models/` | Modelele `User` și `Subscription` |
| `app/Events/` | Evenimentele trimise prin WebSockets |
| `app/Policies/` | Permisiuni (abonamente proprii, acces admin) |
| `app/Actions/Fortify/` | Înregistrare, profil, parolă |
| `database/migrations/` | Structura bazei de date |
| `resources/views/` | Paginile Blade (`dashboard`, `admin/users/`, `auth/`) |
| `resources/js/` | Alpine.js: `dashboard.js` (listă și total), `realtime.js` + `echo.js` (WebSockets) |
| `resources/css/app.css` | Stilurile (CSS vanilla) |
| `tests/Feature/` | Testele automate |
| `compose.yaml`, `Dockerfile`, `docker/` | Configurarea Docker (Nginx, PHP, Reverb, PostgreSQL, Mailpit, tunel) |
