# Portfolio

## Usage (License will be added soon)

1. Clone this repository
2. Create the file `/admin/php/config.php` with the following content:

```php
<?php

$CONFIG = [
    'password' => '$2y$10$sITUx72k6FuMw9nbEVibQOv/gt1WezWHuYOnOPbOsygeyk5ZXD5De',
];
```

3. Put it on a webspace you like that supports PHP with the imagick extension
4. Go to the admin page and login with the password `12345678`
5. Change the password in the admin panel

## Image Protection

The raw images are protected by appending ".protected" to their filenames.
If you want to disallow access to the raw images, you can disallow access to files with the ".protected" extension in your webserver configuration.

<details>
<summary>Apache Configuration</summary>

```apache
<FilesMatch "\.protected$">
    Require all denied
</FilesMatch>
```

</details>

<details>
<summary>Nginx Configuration</summary>

```nginx
location ~ /\.protected$ {
    deny all;
}
```

</details>
