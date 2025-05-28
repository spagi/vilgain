
## Nastavení a spuštění

1.  **Příprava Symfony aplikace:**
    *   **Pokud adresář `./symfony` je prázdný:**
        Vytvořte Symfony projekt. Nejprve spusťte PHP službu (aby byl dostupný Composer):
        ```bash
        docker-compose up -d --build php
        ```
        Poté v adresáři `./symfony` (který je připojen do kontejneru) vytvořte Symfony projekt:
        ```bash
        docker-compose exec php composer create-project symfony/skeleton:"^6.4" . --prefer-dist
        # nebo pro PHP 8.2 můžete použít i symfony/skeleton:"^7.0" pokud chcete
        ```
        Po instalaci Symfony aplikaci zastavte:
        ```bash
        docker-compose down
        ```
    *   **Pokud již máte Symfony aplikaci v `./symfony`:**
        Přeskočte předchozí krok.

2.  **Instalace Symfony závislostí (pokud je potřeba):**
    Pokud máte `composer.json` v `./symfony`, ale chybí adresář `vendor`:
    ```bash
    docker-compose run --rm php composer install
    ```
    Nainstalujte potřebné balíčky pro API, databázi (Doctrine) a případně JWT:
    ```bash
    docker-compose run --rm php composer require symfony/orm-pack symfony/maker-bundle doctrine/annotations symfony/security-bundle symfony/validator symfony/serializer-pack
    # Pro JWT (např. LexikJWTAuthenticationBundle):
    # docker-compose run --rm php composer require lexik/jwt-authentication-bundle
    ```

3.  **Konfigurace Symfony (`./symfony/.env`):**
    Ujistěte se, že soubor `./symfony/.env` obsahuje správné nastavení. `DATABASE_URL` je již nastaveno přes `docker-compose.yml`, ale je dobré to mít i zde:
    ```env
    # ./symfony/.env (příklad)
    APP_ENV=dev
    APP_DEBUG=1
    APP_SECRET=vygenerovanytajnyklic # Vygeneruje Symfony
    DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"

    # Pro JWT (pokud používáte lexik/jwt-authentication-bundle)
    # JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
    # JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
    # JWT_PASSPHRASE=YourSuperSecretPassphrase # Musí odpovídat docker-compose.yml
    ```
    Pokud používáte JWT s `lexik/jwt-authentication-bundle`, vygenerujte klíče (z kořenového adresáře projektu):
    ```bash
    docker-compose exec php mkdir -p config/jwt
    # Zadejte heslo (JWT_PASSPHRASE) když budete dotázáni
    docker-compose exec php openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:YourSuperSecretPassphrase
    docker-compose exec php openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:YourSuperSecretPassphrase
    ```
    *Nezapomeňte přidat `config/jwt/private.pem` a `JWT_PASSPHRASE` (pokud je v .env) do `.gitignore`!*


4.  **Spuštění Docker kontejnerů:**
    Z kořenového adresáře projektu (kde je `docker-compose.yml`):
    ```bash
    docker-compose up -d --build
    ```

5.  **Vytvoření databázového schématu:**
    SQLite soubor databáze bude v `./symfony/var/data.db`.
    Pro vytvoření tabulek pomocí Doctrine Migrations:
    ```bash
    docker-compose exec php php bin/console doctrine:database:create --if-not-exists
    docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction
    ```
    Pokud nemáte migrace, ale chcete vytvořit schéma přímo z entit (pouze pro vývoj!):
    ```bash
    # docker-compose exec php php bin/console doctrine:schema:update --force
    ```

6.  **Přístup k aplikaci:**
    Aplikace by měla být dostupná na [http://localhost:8000](http://localhost:8000).

## Základní Docker Compose příkazy

*   Spuštění na pozadí: `docker-compose up -d`
*   Zastavení: `docker-compose stop`
*   Zastavení a odstranění kontejnerů: `docker-compose down`
*   Zobrazení logů: `docker-compose logs -f php` (nebo `nginx`, nebo bez názvu pro všechny)
*   Spuštění příkazu v kontejneru: `docker-compose exec php php bin/console list`
*   Sestavení obrazů znovu: `docker-compose build`

## Řešení problémů s oprávněními

Pokud PHP nemůže zapisovat do adresářů `var/cache` nebo `var/log` v `./symfony/var/`, nejjednodušší (ale méně bezpečné) řešení pro lokální vývoj je nastavit plná oprávnění na tyto adresáře z vašeho hostitelského systému:
```bash
# Ujistěte se, že adresář ./symfony/var existuje
mkdir -p ./symfony/var
sudo chmod -R 777 ./symfony/var