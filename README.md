## PROJECT MANAGER 

-----

### Prerequisites:
- docker / docker compose plugin



After cloning this project on your local machine, you should follow these steps in order to have it up and running:
- [ ] add the `.env` file: you can stick with copying the `.env.example` for a local development;
- [ ] Build docker container, by running

```shell
docker compose up -d --build
```
- [ ] Get in the container:

```shell
docker exec -it project-manager bash
```

- [ ] Once inside the container, you have to install composer dependencies:

```php
  composer install
```

- [ ] After composer dependencies, run migrations and seed tables:

```php
  php artisan migrate --seed
```
---

For testing purposes use the swagger api documentation: you can reach it on this url: `http://localhost/api/documentation`

