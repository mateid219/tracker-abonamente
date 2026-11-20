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
- Emailuri: http://localhost:8080/mail (`tester` / `tester`)

Link public pentru testeri:

```bash
docker compose --profile tunnel up -d --build
./docker/tunnel-url.sh
```

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
