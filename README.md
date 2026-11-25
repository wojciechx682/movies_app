# Movies App

Movies App to rozwijana aplikacja MVP do przeglądania katalogu filmów, oceniania ich i dodawania recenzji. Użytkownicy mogą tworzyć konta, a administratorzy zarządzać filmami i gatunkami.

## Funkcje

- Katalog filmów z wyszukiwaniem po tytule
- Filtrowanie filmów według gatunku i roku wydania; gatunek i rok można łączyć
- Sortowanie według tytułu A–Z, tytułu Z–A, najnowszego lub najstarszego roku wydania oraz daty dodania filmu
- Paginacja katalogu filmów
- Szczegóły filmu: opis, reżyser, gatunki, plakat i recenzje
- Średnia ocena prezentowana liczbowo i w postaci gwiazdek, liczba ocen oraz liczba komentarzy
- Rejestracja, logowanie i wylogowanie; hasła są przechowywane jako hash
- Role `ROLE_USER` i `ROLE_ADMIN`; rejestracja nadaje zwykłe uprawnienia użytkownika
- Administrator może dodawać i edytować filmy oraz zarządzać gatunkami
- Zalogowany użytkownik może dodać jedną ocenę do filmu, a potem edytować lub usunąć własną recenzję
- Ocena jest w skali 1–10; komentarz do oceny jest opcjonalny
- Dodawanie plakatów JPG, JPEG, PNG lub WebP do 5 MB
- Komunikaty flash po wybranych operacjach
- Walidacja filmu: wymagany tytuł, opis i reżyser; tytuł i reżyser do 255 znaków; rok wydania 1888–2100; opcjonalny czas trwania 1–32767 minut
- Walidacja recenzji: ocena 1–10; nazwa gatunku jest wymagana, ma limit 100 znaków i musi być unikalna
- Walidacja rejestracji: poprawny i unikalny email do 180 znaków, hasło 6–4096 znaków, zgodność powtórzenia hasła i akceptacja regulaminu

## Technologie

- PHP 8.5 (obraz Dockera używa PHP 8.4; projekt wymaga PHP 8.4 lub nowszego)
- Symfony 8.1
- Doctrine ORM i Doctrine Migrations
- MySQL
- Twig
- Bootstrap 5.3
- Docker i Docker Compose (Apache + PHP, MySQL 8.4)

## Uruchomienie w Dockerze (zalecane)

Najszybszy sposób na uruchomienie projektu. Nie wymaga lokalnej instalacji PHP, Composera ani MySQL.

### Wymagania

- Git
- Docker Desktop (Windows, macOS) albo Docker Engine z wtyczką Docker Compose (Linux)

W instrukcji używana jest komenda `docker compose` (ze spacją, Docker Compose v2).

### Pierwsze uruchomienie

1. Sklonuj repozytorium i przejdź do katalogu projektu

   ```bash
   git clone <adres-repozytorium>
   cd movies_app
   ```

2. Zbuduj obrazy i uruchom kontenery w tle

   ```bash
   docker compose up -d --build
   ```

   Pierwsze budowanie trwa kilka minut i wymaga dostępu do internetu. Aplikacja (`server`) startuje dopiero wtedy, gdy baza danych (`db`) jest gotowa.

3. Utwórz tabele w bazie danych

   ```bash
   docker compose exec server php bin/console doctrine:migrations:migrate --no-interaction
   ```

4. Utwórz konto administratora

   ```bash
   docker compose exec server php bin/console app:create-admin
   ```

   Komenda jest interaktywna: poprosi o adres email, hasło i jego powtórzenie, a na końcu o potwierdzenie (wpisz `y`).

5. Otwórz aplikację w przeglądarce: http://localhost:9000/home

   Zaloguj się kontem administratora na `/login`, a następnie dodaj gatunki (`/genre`) i filmy (`/movie/new`). Projekt nie zawiera przykładowych danych.

   UPDATE po wykryciu błędu - nie można dodać gatunku - następuje wylogowanie po kliknięciu na "Create new" na stronie `/genre`.

   ROZWIĄZANIE: od razu po zalogowaniu wejść pod `/genre/new` i dodać nowy gatunek filmu, zanim doda się sam film

### Przydatne komendy

| Cel | Komenda |
|---|---|
| Zatrzymanie kontenerów (dane zostają) | `docker compose down` |
| Ponowne uruchomienie | `docker compose up -d` |
| Przebudowanie po zmianie kodu | `docker compose up -d --build` |
| Status kontenerów | `docker compose ps` |
| Logi aplikacji | `docker compose logs -f server` |
| Komenda konsoli Symfony | `docker compose exec server php bin/console <komenda>` |
| Konsola MySQL | `docker compose exec db mysql -uapp -papp movies_app` |
| Usunięcie wszystkiego, razem z bazą i plakatami | `docker compose down -v` |

### Jak działa konfiguracja

- Aplikacja jest dostępna pod adresem `http://localhost:9000`. Port zmienisz w `compose.yaml` w sekcji `ports` (lewa liczba)
- Kod trafia do obrazu podczas budowania, bez podpiętego katalogu projektu, dlatego po zmianach w kodzie trzeba wykonać `docker compose up -d --build`
- Dane bazy są w wolumenie `db-data`, a wgrane plakaty w wolumenie `uploads`. Przetrwają `docker compose down`, ale znikną po `docker compose down -v`
- Baza danych: `movies_app`, użytkownik `app`, hasło `app`. Port bazy nie jest wystawiony poza Dockera
- Konfiguracja jest przeznaczona do lokalnego uruchomienia i demonstracji (`APP_ENV=dev`, proste hasła). Nie używaj jej w produkcji

### Rozwiązywanie problemów

- `service "server" is not running`: kontenery są zatrzymane, uruchom `docker compose up -d`
- `port is already allocated`: port 9000 jest zajęty. Zmień lewą wartość w `ports` w `compose.yaml` (np. `8080:80`) i uruchom `docker compose up -d`
- `the input device is not a TTY` w Git Bashu: użyj PowerShella albo cmd, albo poprzedź komendę słowem `winpty`
- Błędy bazy danych na stronie po pierwszym uruchomieniu: sprawdź, czy wykonano migracje (krok 3)

## Instalacja lokalna (bez Dockera)

### Wymagania

- PHP 8.4+ z rozszerzeniem PDO MySQL
- Composer
- Serwer MySQL
- Symfony CLI — opcjonalnie, do uruchamiania lokalnego serwera (symfony serve)

### Kroki

1. Sklonuj repozytorium i przejdź do katalogu projektu

   ```bash
   git clone <adres-repozytorium>
   cd movies_app
   ```

2. Zainstaluj zależności

   ```bash
   composer install
   ```

3. Utwórz plik `.env.local` w katalogu głównym i ustaw adres lokalnej bazy MySQL

   ```dotenv
   DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/movies_app?serverVersion=8.0.32&charset=utf8mb4"
   ```

   Zastąp nazwę użytkownika, hasło i `serverVersion` wartościami dla swojej instalacji MySQL. Nie zapisuj prawdziwych danych dostępowych w repozytorium.

4. Utwórz bazę danych i zastosuj migracje

   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. Upewnij się, że katalog `public/uploads/posters` istnieje i aplikacja ma do niego prawo zapisu

6. Uruchom lokalny serwer

   ```bash
   symfony serve
   ```

   Następnie otwórz adres podany przez Symfony CLI, zwykle `https://127.0.0.1:8000` lub `http://127.0.0.1:8000`

## Konto administratora

Publiczna rejestracja tworzy zwykłe konto `ROLE_USER`. Konto administratora utwórz interaktywną komendą w terminalu:

```bash
# w Dockerze
docker compose exec server php bin/console app:create-admin

# lokalnie
php bin/console app:create-admin
```

Komenda poprosi o adres email, hasło i jego powtórzenie. Hasło jest wpisywane bez wyświetlania znaków w terminalu. Email musi być poprawny i unikalny, a hasło musi mieć co najmniej 6 znaków — tak jak w formularzu rejestracji. Na końcu potwierdź utworzenie konta.

Uruchom komendę po skonfigurowaniu połączenia z bazą i wykonaniu migracji. Nie dodawaj `--no-interaction`, ponieważ komenda celowo wymaga interaktywnego terminala.

Uprawnienia administracyjne nie są nadawane przez publiczny formularz rejestracji.

<!--
Poprzednia ręczna metoda nadania roli istniejącemu kontu:

UPDATE `user`
SET roles = JSON_ARRAY('ROLE_ADMIN')
WHERE email = 'admin@example.com';
-->

## Główne adresy

| Adres | Opis | Dostęp |
|---|---|---|
| `/home` | Katalog filmów, wyszukiwanie, filtry i sortowanie | Publiczny |
| `/movie/{id}` | Szczegóły filmu i jego recenzje | Publiczny |
| `/movie/new` | Dodawanie filmu | Administrator |
| `/movie/{id}/edit` | Edycja filmu | Administrator |
| `/genre` | Lista i zarządzanie gatunkami | Administrator |
| `/login` | Logowanie | Gość |
| `/register` | Rejestracja | Gość |

## Plakaty filmów

Plakaty są przechowywane jako pliki w `public/uploads/posters`, a w bazie zapisywana jest ich ścieżka. Formularz opcjonalnie przyjmuje pliki JPG, JPEG, PNG i WebP o maksymalnym rozmiarze 5 MB.

## Dane i ograniczenia MVP

- Projekt nie zawiera fixtures z przykładowymi filmami. Administrator dodaje filmy przez formularz
- Wyszukiwanie obejmuje tytuł filmu
- Testy funkcjonalne aplikacji nie są jeszcze przygotowane

## Zrzuty ekranu

### Katalog filmów

<p>
  <img src="docs/screenshots/home_page.png" alt="Strona główna" width="700">
</p>

<!-- ![Strona główna](docs/screenshots/home_page.png) -->

### Filtry

<!-- ![Filtry](docs/screenshots/filters.png) -->

<p>
  <img src="docs/screenshots/filters.png" alt="Filtry" width="700">
</p>

### Sortowanie

<!-- ![Sortowanie](docs/screenshots/sorting.png) -->

<p>
  <img src="docs/screenshots/sorting.png" alt="Sortowanie" width="700">
</p>

### Logowanie i rejestracja

<!-- ![Ekran logowania](docs/screenshots/login.png)

![Ekran rejestracji](docs/screenshots/register.png) -->

<p>
  <img src="docs/screenshots/login.png" alt="Logowanie" width="700">
</p>

<p>
  <img src="docs/screenshots/register.png" alt="Rejestracja" width="700">
</p>

### Szczegóły filmu i recenzje

<!-- ![Widok szczegółów filmu](docs/screenshots/movie_details.png) -->

<p>
  <img src="docs/screenshots/movie_details.png" alt="Widok szczegółów filmu" width="700">
</p>

### Zarządzanie filmami

<!-- ![Dodawanie filmu przez administratora](docs/screenshots/add_movie.png) -->

<p>
  <img src="docs/screenshots/add_movie.png" alt="Dodawanie filmu przez administratora" width="700">
</p>


### Zarządzanie gatunkami

<!-- ![CRUD gatunków](docs/screenshots/genres.png) -->

<p>
  <img src="docs/screenshots/genres.png" alt="CRUD gatunków" width="700">
</p>

