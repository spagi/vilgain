# REST API pro Správu Uživatelů a Článků (Symfony & Docker)

Toto je malá REST API aplikace vytvořená v PHP 8.2 s využitím frameworku Symfony a Dockeru. Umožňuje základní CRUD operace pro entity User a Article a implementuje jednoduchou logiku rolí.

## Použité Technologie

*   PHP 8.2
*   Symfony (s API Platform, Doctrine ORM, Security, LexikJWTAuthenticationBundle)
*   Docker & Docker Compose
*   SQLite (pro ukládání dat)
*   PHPUnit (pro testování)

## Nastavení a Spuštění Projektu

Následujte tyto kroky pro lokální spuštění aplikace:

1.  **Konfigurace Prostředí:**
    *   Zkopírujte soubor `.env.dist` (pokud existuje) do `.env` v kořenovém adresáři projektu (tam, kde je `docker-compose.yml`).
        ```bash
        cp .env.dist .env 
        ```       
    *   Upravte soubor `.env` podle potřeby. Klíčové proměnné pro Docker Compose jsou:
        
    *   **Důležité:** Soubor `symfony/.env` uvnitř adresáře `symfony/`. Proměnné jako `DATABASE_URL`, `JWT_SECRET_KEY`, `JWT_PUBLIC_KEY`, `JWT_PASSPHRASE` musi správně nastaveny.
        *Příklad obsahu pro `./symfony/.env`:*
        ```env
        # ./symfony/.env
        APP_ENV=dev
        APP_DEBUG=1 # Povoleno pro vývoj
        APP_SECRET=vygenerovanytajnyklic # Měl by být unikátní a bezpečný
        
        DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
        
        JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
        JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
        JWT_PASSPHRASE=${JWT_PASSPHRASE_FROM_DOCKER_ENV:-TvojeSuperSilneHesloProJWTKlice} # Můžeš načíst z Docker env nebo mít fallback
        ```

2.  **Sestavení a Spuštění Docker Kontejnerů:**    
    ```bash
    docker-compose up -d --build
    ```
   
3.  **Instalace Composer Závislostí:**   
    ```bash
    docker-compose exec php composer install --no-interaction --prefer-dist --optimize-autoloader
    ```   

4.  **Generování JWT Klíčů:**
    Klíče potřebné pro LexikJWTAuthenticationBundle:
    ```bash
    docker-compose exec php php bin/console lexik:jwt:generate-keypair
    ```
  5.  **Vytvoření Databáze a Spuštění Migrací:**   
        ```bash
        docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
        ```

6.  **Načtení Fixtur (Volitelné, ale doporučené pro testování a vývoj):**    
    ```bash
    docker-compose exec php php bin/console doctrine:fixtures:load --no-interaction
    ```

## Přístup k Aplikaci

*   **API Aplikace:** Aplikace je dostupná na adrese:
    [http://localhost:8088](http://localhost:8088).
*   **API Dokumentace :** 
    [http://localhost:8088/docs](http://localhost:8088/docs)

## Příklady Volání API

Pro detailní příklady volání jednotlivých endpointů (registrace, přihlášení, práce s články a uživateli) prosím nahlédni do adresáře `/postman/`. Obsahuje Postman kolekci, kterou můžeš importovat a používat pro interakci s API.

**Základní flow:**

1.  **Registrace uživatele:** `POST /auth/register` s JSON tělem (viz DTO nebo příklady).
2.  **Přihlášení uživatele:** `POST /auth/login` s JSON tělem obsahujícím email a heslo. Odpověď bude obsahovat JWT token.
3.  **Volání chráněných endpointů:** Pro endpointy vyžadující autentizaci přidej do hlavičky requestu:
    `Authorization: Bearer <VAŠ_JWT_TOKEN>`

## Testování


```bash
docker-compose exec php php bin/phpunit