
## Nastavení a spuštění

1.  **Spusteni:**
    *   **Docker:**
        ```bash
        docker-compose up -d --build php
        ```
    *   **Composer:** 
    ```bash
    docker-compose run --rm php composer install
    ```
    *   **JWT bundle nastaveni:**
     ```bash
    docker-compose exec php php bin/console lexik:jwt:generate-keypair
    ```

3.  **Konfigurace Symfony (`./symfony/.env`):**    
    ```env
    # ./symfony/.env 
    APP_ENV=dev
    APP_DEBUG=1
    APP_SECRET=vygenerovanytajnyklic
    DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
    JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
    JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
    JWT_PASSPHRASE=YourSuperSecretPassphrase # Musí odpovídat docker-compose.yml
   
5.  **Spusteni Migraci:**
    SQLite soubor databáze bude v `./symfony/var/data.db`.
   
    ```bash    
    docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction
    ```
   
6.  **Přístup k aplikaci:**
    Aplikace by měla být dostupná na [http://localhost:8088](http://localhost:8088).
