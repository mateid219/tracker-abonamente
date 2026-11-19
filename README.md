# Tracker Abonamente

Aplicație web pentru evidența abonamentelor personale.

**Stack:** Laravel 13 · PostgreSQL · Blade + Alpine.js (fără Livewire) · CSS vanilla (fără Tailwind/Bootstrap) · autentificare cu Laravel Fortify.

**Site online:** _adaugă aici adresa din Laravel Cloud_

Aplicația este publicată pe [Laravel Cloud]([https://cloud.laravel.com](https://tracker-abonamente-production-ywyctg.laravel.cloud), direct din acest repository: fiecare push pe `main` declanșează un deploy nou.

---

## Testare pe site-ul online

### 1. Înregistrare și verificarea emailului
1. Apasă **Cont nou** și creează un cont cu o adresă de email la care ai acces.
2. Parola trebuie să aibă minimum 10 caractere, litere mari și mici și o cifră. Lista de cerințe se bifează pe măsură ce scrii. Online, parola este verificată și în baze de date cu parole compromise, deci parolele foarte cunoscute sunt respinse.
3. După înregistrare apare pagina „Verifică-ți adresa de email”. Deschide emailul primit și apasă butonul de verificare.
4. Ești redirecționat pe dashboard. Fără verificare, dashboard-ul și setările nu sunt accesibile.

### 2. Resetarea parolei
1. Ieși din cont și, pe pagina de autentificare, apasă **Ai uitat parola?**
2. Introdu emailul contului și deschide linkul primit pe email.
3. Setează o parolă nouă și autentifică-te cu ea.

### 3. Limitarea încercărilor de autentificare
Introdu de 6 ori o parolă greșită pentru același email: după 5 încercări, autentificarea este blocată pentru un minut.

### 4. Autentificare în doi pași (2FA)
1. **Setări → Autentificare în doi pași → Activează**. Ți se cere parola contului.
2. Scanează codul QR cu o aplicație de autentificare (Google Authenticator, Microsoft Authenticator, 1Password etc.) și introdu codul de 6 cifre.
3. Salvează codurile de recuperare afișate (apar o singură dată).
4. Ieși din cont și autentifică-te din nou: după parolă ți se cere codul din aplicație. Poți folosi și un cod de recuperare, fiecare funcționează o singură dată.

### 5. Profil, parolă și dispozitive conectate
- **Setări → Profil**: schimbarea emailului cere o nouă verificare.
- **Setări → Schimbă parola**: cere parola actuală.
- **Setări → Dispozitive conectate**: sesiunile sunt salvate în PostgreSQL. Autentifică-te din alt browser sau de pe telefon, apoi apasă **Deconectează celelalte dispozitive** în primul browser. Celălalt dispozitiv va fi deconectat.

---

## Rulare locală

### Cerințe

| Unealtă | Versiune | Verificare |
|---|---|---|
| PHP | 8.3+ (cu extensia `pdo_pgsql`) | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js + npm | 20+ | `node -v` |
| PostgreSQL | 15+ | `psql --version` |

> Pe macOS:
> - PHP + Composer: `/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.4)"` (pentru Windows/Linux vezi [php.new](https://php.new))
> - PostgreSQL: [Postgres.app](https://postgresapp.com) (după instalare apasă **Initialize**)

### Instalare

```bash
# 1. Clonează proiectul
git clone https://github.com/mateid219/tracker-abonamente.git
cd tracker-abonamente

# 2. Creează baza de date
createdb tracker_abonamente

# 3. Creează fișierul de configurare
cp .env.example .env
```

Deschide `.env` și completează datele de conectare la PostgreSQL:

```
DB_USERNAME=   # la Postgres.app: numele utilizatorului tău de macOS
DB_PASSWORD=   # la Postgres.app: gol
```

```bash
# 4. Instalează și configurează totul
#    (pachete PHP și JS, cheia aplicației, tabele, cont demo, build CSS/JS)
composer run setup

# 5. Pornește aplicația
composer run dev
```

Aplicația pornește la **http://localhost:8000**.

### Cont demo (doar local)

| Email | Parolă |
|---|---|
| `demo@example.test` | `Demo-Parola-2026` |

Contul are emailul deja verificat, deci intri direct pe dashboard.

### Emailuri în dezvoltare

Local, emailurile **nu se trimit**: sunt scrise în `storage/logs/laravel.log` (`MAIL_MAILER=log`). Pentru linkul de verificare sau de resetare a parolei, după ce ai cerut emailul în aplicație, rulează:

```bash
grep -oE 'http://localhost:8000/[^ "<]+' storage/logs/laravel.log | tail -1 | sed 's/&amp;/\&/g'
```

Deschide linkul afișat în același browser în care ești autentificat. Linkurile expiră după 60 de minute.

---

## Teste automate

```bash
composer run test
```

Testele rulează pe o bază SQLite în memorie (configurată în `phpunit.xml`), deci nu ating datele din PostgreSQL.

---

## Configurare pe Laravel Cloud

Setările de producție se definesc în **Laravel Cloud → Environment → Variables**, nu în `.env.example`. Baza de date PostgreSQL este oferită de Laravel Cloud.

Pentru trimiterea emailurilor este nevoie de un serviciu SMTP, de exemplu Gmail cu o [parolă pentru aplicații](https://myaccount.google.com/apppasswords):

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=adresa@gmail.com
MAIL_PASSWORD=parola-pentru-aplicatii
MAIL_FROM_ADDRESS=adresa@gmail.com
MAIL_FROM_NAME="Tracker Abonamente"
```

> ⚠️ Parolele și cheile se pun doar în variabilele de mediu din Laravel Cloud, niciodată în cod sau pe GitHub.

---

## Comenzi utile

| Comandă | Ce face |
|---|---|
| `php artisan migrate` | Rulează migrările noi |
| `php artisan migrate:fresh --seed` | Recreează toate tabelele și contul demo (**șterge datele locale**) |
| `php artisan route:list` | Lista tuturor rutelor |
| `php artisan db:show` | Informații despre conexiunea la baza de date |

---

## Probleme frecvente

**`could not find driver`**: lipsește extensia PHP `pdo_pgsql`. Verifică cu `php -m | grep pgsql`.

**`database "tracker_abonamente" does not exist`**: rulează `createdb tracker_abonamente`.

**`connection refused` (port 5432)**: PostgreSQL nu rulează. Pornește Postgres.app sau serviciul PostgreSQL.

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
