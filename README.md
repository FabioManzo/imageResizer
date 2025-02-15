# ImageResizer
A lightweight PHP application for image resizing based on an XML configuration file. It supports configurable parameters such as dimensions, cropping, filters, and caching to optimize performance.

# 📖 Documentation
This script is designed to run in a Dockerized environment to ensure the correct PHP version (__8.2.27__) and avoid installing PHP locally.

# 📌 How to run the Docker container
Build the Docker image

`docker compose build`

Run the containers

`docker compose up -d`

Enter the PHP container

`docker compose exec php_8 bash`

# 📷 How to run the imageResizer
The ImageResizer can be run with a bin/console command:

`bin/console image:resize assets/images/Dark_Side_of_the_Moon.png  --xml assets/xml/myConfig.xml --size thumb`