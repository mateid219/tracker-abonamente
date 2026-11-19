# Tracker Abonamente

Aplicație web pentru evidența abonamentelor personale.

**Stack:** Laravel 13 · PostgreSQL · Blade + Alpine.js (fără Livewire) · CSS vanilla (fără Tailwind/Bootstrap) · autentificare cu Laravel Fortify.

Pentru dezvoltare și testare, PostgreSQL și [Mailpit](https://mailpit.axllent.org/) (un inbox local care prinde toate emailurile trimise de aplicație) rulează în Docker. Aplicația Laravel rulează direct pe calculator.

---

## Cerințe

| Unealtă | Versiune | Verificare |
|---|---|---|
| PHP | 8.3+ (cu extensia `pdo_pgsql`) | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js + npm | 20+ | `node -v` |
| Docker Desktop | orice versiune recentă, **pornit** | `docker compose version` |

> Pe macOS, PHP + Composer se pot instala cu o singură comandă:
> `/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.4)"`
> (pentru Windows/Linux vezi [php.new](https://php.new)).

---

## Pornire rapidă

```bash
# 1. Clonează proiectul
git clone https://github.com/mateid219/tracker-abonamente.git
cd tracker-abonamente

# 2. Pornește PostgreSQL și Mailpit
docker compose up -d

# 3. Instalează și configurează totul
#    (pachete PHP și JS, .env, cheia aplicației, tabele, cont demo, build CSS/JS)
composer run setup

# 4. Pornește aplicația
composer run dev
```

Apoi deschide:

| Adresă | Ce este |
|---|---|
| http://localhost:8000 | Aplicația |
| http://localhost:8025 | Mailpit: inbox-ul cu emailurile trimise de aplicație |

`.env.example` este deja configurat pentru serviciile din Docker, deci nu trebuie modificat nimic.

### Cont demo

| Email | Parolă |
|---|---|
| `demo@example.test` | `Demo-Parola-2026` |

Contul are emailul deja verificat, deci intri direct pe dashboard.

---

## Ce poți testa

### 1. Înregistrare și verificarea emailului
1. Mergi la **Cont nou** și creează un cont cu orice adresă (ex. `test@example.test`). Emailurile nu pleacă în afara calculatorului.
2. Parola trebuie să aibă minimum 10 caractere, litere mari și mici și o cifră. Lista de cerințe se bifează pe măsură ce scrii.
3. După înregistrare apare pagina „Verifică-ți adresa de email”.
4. Deschide **http://localhost:8025**, deschide emailul primit și apasă butonul de verificare.
5. Ești redirecționat pe dashboard. Fără verificare, dashboard-ul și setările nu sunt accesibile.

### 2. Resetarea parolei
1. Ieși din cont și, pe pagina de autentificare, apasă **Ai uitat parola?**
2. Introdu emailul contului și deschide emailul din Mailpit.
3. Setează o parolă nouă și autentifică-te cu ea.

### 3. Limitarea încercărilor de autentificare
Introdu de 6 ori o parolă greșită pentru același email: după 5 încercări, autentificarea este blocată pentru un minut.

### 4. Autentificare în doi pași (2FA)
1. **Setări → Autentificare în doi pași → Activează**. Ți se cere parola contului.
2. Scanează codul QR cu o aplicație de autentificare (Google Authenticator, Microsoft Authenticator, 1Password etc.) și introdu codul de 6 cifre.
3. Salvează codurile de recuperare afișate (apar o singură dată).
4. Ieși din cont și autentifică-te din nou: după parolă ți se cere codul din aplicație. Poți folosi și un cod de recuperare, fiecare funcționează o singură dată.

### 5. Profil, parolă și dispozitive conectate
- **Setări → Profil**: schimbarea emailului cere o nouă verificare (emailul apare în Mailpit).
- **Setări → Schimbă parola**: cere parola actuală.
- **Setări → Dispozitive conectate**: sesiunile sunt salvate în PostgreSQL. Autentifică-te din alt browser sau dintr-o fereastră privată, apoi apasă **Deconectează celelalte dispozitive** în primul browser. Celălalt browser va fi deconectat.

---

## Teste automate

```bash
composer run test
```

Testele rulează pe o bază SQLite în memorie (configurată în `phpunit.xml`), deci nu ating datele din PostgreSQL și nu au nevoie de Docker.

---

## Comenzi utile

| Comandă | Ce face |
|---|---|
| `docker compose up -d` | Pornește PostgreSQL și Mailpit |
| `docker compose down` | Oprește serviciile (datele rămân) |
| `docker compose down -v` | Oprește serviciile și **șterge baza de date** |
| `php artisan migrate:fresh --seed` | Recreează toate tabelele și contul demo |
| `php artisan route:list` | Lista tuturor rutelor |
| `php artisan db:show` | Informații despre conexiunea la baza de date |

---

## Probleme frecvente

**`Cannot connect to the Docker daemon`**: Docker Desktop nu este pornit. Deschide aplicația și așteaptă până devine activă.

**`port is already allocated`**: portul 5433, 1025 sau 8025 este folosit de alt program. Oprește programul respectiv sau schimbă portul din stânga în `compose.yaml` (ex. `"127.0.0.1:5434:5432"`) și aceeași valoare în `.env` (`DB_PORT=5434`).

**`could not find driver` (PostgreSQL)**: lipsește extensia PHP `pdo_pgsql`. Verifică cu `php -m | grep pgsql`.

**Nu apare niciun email în Mailpit**: verifică în `.env` că `MAIL_MAILER=smtp` și `MAIL_PORT=1025`, apoi rulează `php artisan config:clear`.

**Pagina se încarcă fără stiluri**: rulează `npm run build` sau pornește aplicația cu `composer run dev` (pornește și Vite).

---

## Structura proiectului (pe scurt)

| Locație | Conținut |
|---|---|
| `routes/web.php` | Rutele aplicației (rutele de autentificare sunt adăugate de Fortify) |
| `app/Http/Controllers/` | Controllere (ex. `SettingsController`) |
| `app/Actions/Fortify/` | Logica de creare cont, resetare și schimbare parolă |
| `resources/views/` | View-uri Blade (`auth/`, `settings`, `dashboard`, `layouts/`) |
| `resources/css/app.css` | CSS vanilla |
| `resources/js/app.js` | Alpine.js |
| `database/migrations/` | Structura bazei de date |
| `lang/ro/` | Traducerile în română |
| `compose.yaml` | Serviciile Docker pentru dezvoltare |

---

> ⚠️ Datele de conectare din `.env.example` și `compose.yaml` (`tracker` / `tracker`) și contul demo sunt **doar pentru dezvoltare locală**. Nu le folosi în producție.
