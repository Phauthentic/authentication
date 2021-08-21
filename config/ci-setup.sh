#!/usr/bin/env bash

wget -O phive.phar https://phar.io/releases/phive.phar
wget -O phive.phar.asc https://phar.io/releases/phive.phar.asc
gpg --keyserver pool.sks-keyservers.net --recv-keys 0x9D8A98B29B2D5D79
gpg --verify phive.phar.asc phive.phar
chmod +x phive.phar
sudo mv phive.phar /usr/local/bin/phive
phive --no-progress install --target ./bin --trust-gpg-keys 0x4AA394086372C20A,0x8E730BA25823D8B5,0x31C7E470E2138192,0x4AA394086372C20A,0x0F9684B8B16B7AB0,0xBB5F005D6FFDD89E
composer self-update
composer --version
composer global require hirak/prestissimo --no-plugins
composer install --prefer-dist --no-interaction
npm install yarn -g
chmod -R +x ./bin
