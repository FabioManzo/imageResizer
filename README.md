# 📖 ImageResizer Documentation
A lightweight PHP application for image resizing based on an XML configuration file. It supports configurable parameters such as dimensions, cropping, filters, and caching to optimize performance.

This script is designed to run in a Dockerized environment to ensure the correct PHP version (__8.2.27__) and avoid installing PHP locally.

# 🐳 How to run and enter the Docker container
Build the Docker image

`docker compose build`

Run the containers

`docker compose up -d`

Enter the PHP container

`docker compose exec php_8 bash`

# 📦 Install Composer dependencies
From within the PHP container, execute:

`composer install`

# 📷 How to run the imageResizer
The ImageResizer can be run with a bin/console command. From within the PHP container, execute:

`bin/console image:resize Dark_Side_of_the_Moon.jpg  --xml myConfig.xml --size thumbnail`

- The generated images go to the cache/images folder
- The generated jsons go to the cache/config folder (cached xml content)


# ✅ How to run tests
From within the PHP container, execute:

`composer test` to run all the tests at once

`vendor/bin/phpunit tests/ --filter testGet` to run a specific test (change "testGet" with the test you want to run)
