# Tracker Abonamente

Aplicație web pentru evidența abonamentelor personale.

**Stack:** Laravel 13 · PostgreSQL · Blade + Alpine.js (fără Livewire) · CSS vanilla (fără Tailwind/Bootstrap) · autentificare cu Laravel Fortify.

Toată aplicația rulează local, în Docker: server web, Laravel, baza de date, procesarea joburilor și un inbox pentru emailuri. Pentru testerii de la distanță, aplicația primește un link public HTTPS printr-un tunel Cloudflare, fără porturi deschise pe router și fără server în cloud.

```
Tester (browser) ──HTTPS──► Cloudflare Tunnel ──► calculatorul care rulează Docker
                                                    ├─ web      Nginx (aplicație + /mail)
                                                    ├─ app      Laravel (PHP-FPM)
                                                    ├─ queue    joburi în fundal
                                                    ├─ pgsql    PostgreSQL
                                                    └─ mailpit  inbox pentru emailuri
```

---

## Pentru testeri

Ai primit un link de forma `https://ceva-aleator.trycloudflare.com`, plus un utilizator și o parolă pentru inbox.

1. Deschide linkul. Poți intra direct cu contul demo sau îți poți crea unul nou.
2. Pentru un cont nou poți folosi **o adresă inventată** (ex. `ana@test.ro`). Emailurile nu pleacă nicăieri: ajung în inbox-ul de test.
3. Inbox-ul este la **`<link>/mail`**. Autentifică-te cu utilizatorul și parola primite. Toți testerii văd același inbox, deci nu folosi adrese reale.

**Conturi demo:**

| Rol | Email | Parolă |
|---|---|---|
| Administrator | `admin@example.test` | `Admin-Parola-2026` |
| Utilizator | `demo@example.test` | `Demo-Parola-2026` |

### Ce poți testa

**1. Înregistrare și verificarea emailului**
1. Apasă **Cont nou** și creează un cont.
2. Parola trebuie să aibă minimum 10 caractere, litere mari și mici și o cifră. Este verificată și în baze de date publice cu parole compromise, deci parolele foarte cunoscute sunt respinse.
3. Apare pagina „Verifică-ți adresa de email”. Deschide `<link>/mail`, deschide emailul primit și apasă butonul de verificare.
4. Ajungi pe dashboard. Fără verificare, dashboard-ul și setările nu sunt accesibile.

**2. Resetarea parolei**
1. Ieși din cont și apasă **Ai uitat parola?** pe pagina de autentificare.
2. Introdu emailul contului, apoi deschide emailul din `<link>/mail`.
3. Setează o parolă nouă și autentifică-te cu ea.

**3. Limitarea încercărilor de autentificare**
Introdu de 6 ori o parolă greșită pentru același email: după 5 încercări, autentificarea este blocată pentru un minut.

**4. Profil, parolă și dispozitive conectate**
- **Setări → Profil**: schimbarea emailului cere o nouă verificare.
- **Setări → Schimbă parola**: cere parola actuală.
- **Setări → Dispozitive conectate**: autentifică-te din alt browser sau de pe telefon, apoi apasă **Deconectează celelalte dispozitive** în primul browser. Celălalt dispozitiv va fi deconectat.

**5. Administrarea conturilor (CRUD)**
Autentifică-te cu contul **administrator** și deschide **Administrare** din meniu.
- **Listă**: toate conturile, cu rol și stare de verificare. Caută după nume sau email.
- **Creare**: **Cont nou**. Poți marca emailul ca verificat sau lăsa sistemul să trimită emailul de verificare (apare în `/mail`).
- **Editare**: nume, email, rol de administrator și starea de verificare. O parolă nouă setată de admin deconectează contul de pe toate dispozitivele.
- **Ștergere**: cere confirmare și șterge și sesiunile active ale contului.
- Restricții: un administrator nu își poate șterge propriul cont și nu își poate retrage propriile drepturi. Un utilizator obișnuit primește eroarea 403 pe orice pagină `/admin`.

---

## Pornire cu Docker

### Cerințe

- [Docker Desktop](https://www.docker.com/products/docker-desktop/), instalat și **pornit**
- Git

Nu este nevoie de PHP, Composer, Node.js sau PostgreSQL pe calculator: totul rulează în containere.

### 1. Pornire locală

```bash
git clone https://github.com/mateid219/tracker-abonamente.git
cd tracker-abonamente
docker compose up -d --build
```

Prima pornire durează câteva minute, cât se construiesc imaginile. La pornire, aplicația creează automat tabelele, conturile demo (administrator și utilizator) și cheia de criptare.

| Adresă | Ce este |
|---|---|
| http://localhost:8080 | Aplicația |
| http://localhost:8080/mail | Inbox-ul cu emailuri (utilizator `tester`, parolă `tester`) |

### 2. Link public pentru testeri (Cloudflare Tunnel)

```bash
docker compose --profile tunnel up -d --build
```

Afișează linkul public (rulează comanda din folderul proiectului; așteaptă singură până apare linkul, de obicei 5–10 secunde):

```bash
./docker/tunnel-url.sh
```

Trimite testerilor linkul, plus utilizatorul și parola pentru `/mail`.

Ce trebuie să știi:
- **Linkul se schimbă** la fiecare repornire a tunelului. După repornire, trimite noul link.
- Aplicația e accesibilă **doar cât timp calculatorul și Docker sunt pornite**.
- Nu e nevoie de cont Cloudflare și nici de porturi deschise pe router.
- Când testarea s-a încheiat, oprește tunelul:
  ```bash
  docker compose stop tunnel
  ```

### 3. Parola pentru inbox (recomandat înainte de a publica linkul)

Inbox-ul `/mail` are implicit utilizatorul `tester` și parola `tester`. Ca să le schimbi, adaugă în fișierul `.env` din folderul proiectului (fișierul nu ajunge pe GitHub):

```
MAILPIT_USER=tester
MAILPIT_PASSWORD=o-parola-a-ta
```

Apoi repornește: `docker compose --profile tunnel up -d`.

### 4. Adresă fixă (opțional)

Pentru un link care nu se schimbă, ai nevoie de un cont Cloudflare gratuit și de un domeniu administrat în Cloudflare:

1. În [Cloudflare Zero Trust](https://one.dash.cloudflare.com) → **Networks → Tunnels**, creează un tunel de tip *Cloudflared*.
2. Adaugă un **Public Hostname** (ex. `tracker.domeniul-tau.ro`) cu serviciul `HTTP` → `web:80`.
3. Copiază **token-ul** tunelului în `.env`:
   ```
   CLOUDFLARE_TUNNEL_TOKEN=token-ul-copiat
   ```
4. Pornește cu profilul pentru tunel fix:
   ```bash
   docker compose --profile tunnel-named up -d --build
   ```

### Actualizare după modificări în cod

```bash
git pull
docker compose --profile tunnel up -d --build
```

Doar containerele modificate sunt reconstruite. Datele din baza de date se păstrează.

### Comenzi utile

| Comandă | Ce face |
|---|---|
| `docker compose ps` | Starea containerelor |
| `docker compose logs -f app` | Logurile aplicației (erori Laravel) |
| `docker compose logs -f web` | Logurile serverului web |
| `docker compose exec app php artisan route:list` | Rulează o comandă `artisan` în container |
| `docker compose exec app php artisan app:make-admin email@exemplu.ro` | Face un cont administrator (`--revoke` pentru a retrage drepturile) |
| `docker compose exec pgsql psql -U tracker tracker_abonamente` | Consola PostgreSQL |
| `docker compose --profile tunnel down` | Oprește tot (datele rămân) |
| `docker compose --profile tunnel down -v` | Oprește tot și **șterge toate datele** (conturi, emailuri) |

### Probleme frecvente

**`Cannot connect to the Docker daemon`**: Docker Desktop nu este pornit.

**`port is already allocated` (8080)**: portul e folosit de alt program. Adaugă în `.env` `APP_PORT=8081` și pornește din nou.

**Linkul trycloudflare nu se deschide**: tunelul poate avea nevoie de câteva secunde după pornire. Verifică `docker compose logs tunnel`. Dacă tunelul a fost repornit, linkul s-a schimbat.

**Eroare 500**: cauza apare în `docker compose logs app`.

---

## Dezvoltare fără Docker (opțional)

Pentru lucrul zilnic la cod, aplicația poate rula și direct pe calculator, cu reîncărcare automată a CSS/JS.

Cerințe: PHP 8.3+ (cu `pdo_pgsql`), Composer, Node.js 20+, PostgreSQL (pe macOS: [php.new](https://php.new) și [Postgres.app](https://postgresapp.com)).

```bash
createdb tracker_abonamente
cp .env.example .env        # completează DB_USERNAME / DB_PASSWORD
composer run setup          # pachete, cheie, tabele, cont demo, build
composer run dev            # http://localhost:8000
```

În acest mod emailurile sunt scrise în `storage/logs/laravel.log` (`MAIL_MAILER=log`). Ultimul link din emailuri îl afișezi cu:

```bash
grep -oE 'http://localhost:8000/[^ "<]+' storage/logs/laravel.log | tail -1 | sed 's/&amp;/\&/g'
```

### Teste automate

```bash
composer run test
```

Testele rulează pe o bază SQLite în memorie (configurată în `phpunit.xml`) și nu ating datele din PostgreSQL.

---

## Structura proiectului (pe scurt)

| Locație | Conținut |
|---|---|
| `compose.yaml` | Serviciile Docker: web, app, queue, pgsql, mailpit, tunnel |
| `Dockerfile` | Imaginile `app` (PHP-FPM + Laravel) și `web` (Nginx) |
| `docker/` | Configurarea Nginx, PHP și scriptul de pornire |
| `routes/web.php` | Rutele aplicației (rutele de autentificare sunt adăugate de Fortify) |
| `app/Http/Controllers/` | Controllere (`SettingsController`, `Admin/UserController` pentru CRUD-ul de conturi) |
| `app/Policies/UserPolicy.php` | Cine are voie să administreze conturi |
| `app/Actions/Fortify/` | Logica de creare cont, resetare și schimbare parolă |
| `resources/views/` | View-uri Blade (`auth/`, `admin/users/`, `settings`, `dashboard`, `layouts/`) |
| `resources/css/app.css` | CSS vanilla |
| `resources/js/app.js` | Alpine.js |
| `database/migrations/` | Structura bazei de date |
| `lang/ro/` | Traducerile în română |

---

> ⚠️ Datele de acces din `compose.yaml` (baza de date `tracker`/`tracker`, inbox `tester`/`tester`) și conturile demo sunt pentru testare. Contul `admin@example.test` are drepturi de administrator: schimbă-i parola (din **Setări**) dacă linkul ajunge la persoane necunoscute. Când publici linkul, schimbă cel puțin parola inbox-ului și oprește tunelul după testare.
