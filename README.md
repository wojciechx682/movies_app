# Movie App

Aplikacja webowa MVP do przeglądania filmów i dodawania do nich ocen oraz recenzji.

## Funkcje

- Katalog filmów z wyszukiwaniem po tytule.
- Widok szczegółów filmu z opisem, gatunkami, plakatem i recenzjami.
- Rejestracja i logowanie użytkowników.
- Role `ROLE_USER` i `ROLE_ADMIN`.
- Dodawanie, edycja i usuwanie filmów oraz gatunków przez administratora.
- Dodawanie jednej recenzji do filmu przez użytkownika.
- Ocena w skali 1–10 i opcjonalny komentarz.
- Edycja i usuwanie własnej recenzji.
- Wyświetlanie średniej oceny w formie gwiazdek oraz liczby ocen i komentarzy.
- Przesyłanie plakatów filmów z walidacją formatu i rozmiaru pliku.

## Technologie

- PHP 8.5
- Symfony 8.1
- Doctrine ORM i Doctrine Migrations
- MySQL
- Twig
- Bootstrap 5

## Wymagania

- PHP zgodne z wersją wymaganą w `composer.json` (`>=8.4`)
- Composer
- MySQL
- Symfony CLI — opcjonalnie, do uruchomienia lokalnego serwera

## Instalacja lokalna

1. Sklonuj repozytorium i przejdź do katalogu projektu.
2. Zainstaluj zależności:

   ```bash
   composer install
   ```

3. Utwórz plik `.env.local` w katalogu głównym i ustaw w nim `DATABASE_URL` dla swojej lokalnej bazy MySQL. Przykład:

   ```dotenv
   DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/movies_app?serverVersion=8.0.32&charset=utf8mb4"
   ```

   Zastąp dane użytkownika, hasło i `serverVersion` wartościami dla swojej instalacji. Nie umieszczaj prawdziwych danych dostępowych w repozytorium.

4. Utwórz bazę i zastosuj migracje:

   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. Upewnij się, że katalog `public/uploads/posters` istnieje i aplikacja może zapisywać w nim pliki.
6. Uruchom aplikację:

   ```bash
   symfony serve
   ```

## Role i konto administratora

Nowe konto otrzymuje rolę `ROLE_USER`. Aby nadać istniejącemu kontu rolę administratora, można zaktualizować jego wpis w lokalnej bazie danych:

```sql
UPDATE `user`
SET roles = JSON_ARRAY('ROLE_ADMIN')
WHERE email = 'admin@example.com';
```

Zmień adres e-mail na adres zarejestrowanego konta. Nie zapisuj haseł ani danych dostępowych do bazy w repozytorium.

## Główne adresy

- `/home` — katalog filmów i wyszukiwarka
- `/movie/{id}` — szczegóły filmu, recenzje i formularz dodania recenzji
- `/genre` — lista gatunków
- `/login` — logowanie
- `/register` — rejestracja

## Dane przykładowe

Projekt nie zawiera fixtures z przykładowymi filmami. Administrator dodaje filmy przez formularz w aplikacji.

## Status projektu

Projekt jest rozwijanym MVP. Wyszukiwanie działa po tytule; sortowanie wyników i paginacja nie są obecnie zaimplementowane.

## Zrzuty ekranu

![Strona główna](docs/screenshots/1.png)

![Ekran logowania](docs/screenshots/2.png)

![Ekran rejestracji](docs/screenshots/3.png)

![Widok szczegółów filmu](docs/screenshots/4.png)

![Widok edycji recenzji filmu](docs/screenshots/5.png)

![Widok dodawania recenzji do filmu](docs/screenshots/6.png)

![Widok dodawania filmu przez administratora](docs/screenshots/7.png)








































