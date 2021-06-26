# Heidrun

Introduction coming soon...

### Minimum Requirements

* Linux or Mac Operating System
* Docker

### Local Installation

1. Clone the repo with: `git clone git@github.com:adosia/Heidrun.git && cd Heidrun`
2. Generate new self-signed ssl certificates with: `make self-signed-ssl` 
3. Build & run the app with: `make build`
4. Make a new admin account with: `make admin-account`
5. Visit https://localhost:8006 to access the application

### Available `make` Commands

* `build` Rebuild all docker containers
* `up` Restart all docker containers
* `down` Shutdown all docker containers
* `composer-install` Run composer install
* `db-migrate` Run database migration(s)
* `db-refresh` Drop all database tables, re-run the migration(s) with seeds
* `admin-account` Create a new Heidrun admin account
* `status` View the status of all running containers
* `logs` View the logs out of all running containers
* `shell` Drop into an interactive shell inside _heidrun-web_ container
* `stats` View the resource usage of all running containers
* `artisan` Execute Laravel `artisan` command inside _heidrun-web_ container
* `self-signed-ssl` Generates new apache compatible self-signed SSL certificates

### Production Setup Notes

> Coming Soon
