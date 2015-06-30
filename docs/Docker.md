Dockerized testing and analysis tools for developers
======================================================

These services are split into two primary groups: development services and integration services. Development services are for running static analysis and unit tests. Integration services are aimed at creating and running a complete M2 environment with the extension installed.

## Development setup

### Start the dev file system:

`docker-compose up devfs`

This is simply a data volume container that holds files for use in development. The container for "devfs" is used for volumes by any other service that will operate on development files: static checks and unit tests. This _should_ be optional as other services will create the container when necessary but race conditions in docker-compose currently prevent this from being overly consistent. Safest just to run it before attempting to run any services that use it.

### Install PHP dependencies:

`docker-compose run composer install`

Install PHP dependencies listed in the extensions composer.json. Packages will be downloaded to the vendor directory in the container on a linked volume to the host file system, so files will persist on the local file system across containers using the "devfs" container for volumes. Currently, Magento 2 modules will also be copied to a `build/magento` directory. This should only include the modules needed by the extension. The extension's composer.json includes an autoload mapping for Magento modules to this location - Magento 2 modules do not include their own autoload mappings, this works around that limitation.

### Run tests:

`docker-compose run phpunit` et al.

Static tests use the "devfs" container for volumes so files being worked on will be tested. These tests currently include: phpunit, phpmd, document, pdepend, phpcpd, phpcs, phploc, phplint.

## Integration setup

### Start the integration file system:

`docker-compose up intfs`

A data volume container that holds files for the integration environment containers. This _should_ be optional as services that need the volume will create it if it does not yet exist but a race condition in docker-compose currently prevents this from being overly consistent. Safest to just run it before attempting to run any services that use it.

### Install Magento

`docker-compose run install [Magento 2 version [extension version]]`

Creates the integration environment and runs the Magento 2 installer. Uses a composer metapackage to install Magento 2 and the extension using the local clone of the extension as a composer repository.

This service also accepts two positional arguments, a Magento 2 version to install and a version of the extension to install. If no Magento 2 version is given, it will currently default to installing "~0.0" - the latest beta version. If no version of the extension is given, it will default to the current branch checked out of the local repository.

### Start the web server

`docker-compose up web`

Starts a web server container for the integration environment. Container will use the "intfs" container for volumes.

### Redeploy the extension

`docker-compose run deploy [extension version]`

As the integration environment is a complete, separate instance of Magento and the ROM extension, changes made in the dev environment will not be reflected in the integration environment until the changes are deployed.

The "deploy" service will run a composer update in the integration environment, updating the extension to the provided version. If no version is given, it will default to the branch currently checked out in the local repository. Only changes that have been committed to the local repository will be deployed to the integration environment.

### Run functional tests

`docker-compose up -d seleniumserver`

Functional tests use the Selenium web driver to test the web application. Due to some startup issues with the Selenium container, it is often best to start the Selenium server independently before running the functional tests.

`docker-compose run functionaltests`

Run the functional Behat tests. This will run all tests in the "functional" profile in Behat.

### Run integration tests

`docker-compose run -e STORE_ID={ID} -e API_KEY={KEY} -e API_HOSTNAME={HOSTNAME} integrationtests`

Runs the integration Behat tests. This will run all tests in the "integration" profile in Behat.

To properly configure the extension, the following environment variable must be set when running the servide:

- STORE_ID: the store id to authenticate to the public API with
- API_KEY: the api key to autnehticate to the public API with
- API_HOSTNAME: hostname of the public API

### Accessing integration file system

To improve performance of the integration environment - especially in cases where docker is being run via boot2docker - the Magento 2 webroot is not mounted from the host system. This means that files created within the container, such as log files, are not immediately available on the host system. To work around this, two options are available.

`docker run --rm -ti --volumes-from $(docker-compose ps -q intfs) busybox`

The most basic option would be to run a container using the same shared data volume container the integration environment is using. In the example above, a "busybox" container is run using the container provided by the "intfs" service as a volume. The "busybox" container will drop you into a `sh` shell where the files used by the integration environment will be available. From there, you'll have access to the files and can read or edit files as necessary.

`docker run --rm -v $(type -P docker):/docker -v /var/run/docker.sock:/docker.sock svendowideit/samba $(docker-compose ps -q intfs)`

A similar but more sophisticated approach would be to use a container that will allow the volumes in the "intfs" container to be mounted to the host. In the example above, the "svendowideit/samba" image is used to create a samba server container with shares for all volumes attached to the "intfs" service's container. The exact command may vary depending on the specifics of the docker host and client. The command above has been tested to work using boot2docker on OS X.
