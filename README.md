# oabos

With oabos, simply subscribe to YouTube channels without a Google account and display them in a clear, tracking- and ad-free list without "recommendations" or "autoplay". 

## setup

1. start container
    1. `docker-compose up`
2. update config.php
    1. Create new Odmin service
    2. `callback-url: https://localhost:4003/api/odmin/oauth.php`
3. goto http://localhost:4003/