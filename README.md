# Heidrun

Introduction coming soon...

### Minimum Requirements

* Linux or Mac Operating System
* Docker

### Local Installation

1. Clone the repo with: `git clone git@github.com:adosia/Heidrun.git`
2. Build & run the app with: `cd Heidrun && make build`
3. App will be accessible via: http://localhost:8006

### Available `make` Commands

* `build` Rebuild all docker containers
* `up` Restart all docker containers
* `down` Shutdown all docker containers
* `composer-install` Run composer install
* `db-migrate` Run database migration(s)
* `db-refresh` Drop all database tables, re-run the migration(s) with seeds
* `status` View the status of all running containers
* `logs` View the logs out of all running containers
* `shell` Drop into an interactive shell inside _heidrun-web_ container

### Notes Regarding Cardano Full Node `heidrun-cnode`

> Coming Soon

### Production Setup Notes

> Coming Soon
