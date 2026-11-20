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
