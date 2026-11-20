# Tracker Abonamente

Aplicație web pentru urmărirea abonamentelor lunare (Netflix, Spotify, sală etc.).

**Stack:** Laravel 13 · PostgreSQL · Alpine.js · CSS vanilla · Reverb (WebSockets)

## Pornire

Cerințe: [Docker Desktop](https://www.docker.com/products/docker-desktop/) pornit și Git.

```bash
git clone https://github.com/mateid219/tracker-abonamente.git
cd tracker-abonamente
docker compose up -d --build
```

- Aplicația: http://localhost:8080
- Emailuri (Mailpit): http://localhost:8080/mail (utilizator `tester`, parolă `tester`)

## Link public pentru testeri

```bash
docker compose --profile tunnel up -d --build
./docker/tunnel-url.sh
```

Linkul afișat se schimbă la fiecare repornire a tunelului.

## Conturi demo

| Rol | Email | Parolă |
|---|---|---|
| Administrator | `admin@example.test` | `Admin-Parola-2026` |
| Utilizator | `demo@example.test` | `Demo-Parola-2026` |

## Funcționalități

- **Autentificare:** înregistrare, verificare email, resetare parolă.
- **Dashboard:** lista abonamentelor și „Cost Lunar Total” (suma abonamentelor active), actualizat instant la comutarea Activ / Pauză.
- **CRUD abonamente:** nume, cost lunar, status.
- **CRUD utilizatori** (cont administrator → Administrare): nume, prenume, email, soft delete cu restaurare.
- **WebSockets:** adăugarea sau ștergerea unui abonament într-o fereastră apare instant în celelalte ferestre deschise. Test: deschide dashboard-ul în două ferestre cu același cont; indicatorul „Live” arată că actualizarea automată e conectată.

## Comenzi utile

```bash
docker compose logs -f app                              # loguri
docker compose exec app php artisan app:make-admin EMAIL # face un cont administrator
docker compose down -v                                  # oprește tot și șterge datele
```

## Teste

```bash
composer run test
```
