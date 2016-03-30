# INSTALLATION

## Summary 
LaSalle Software email handling.  


## composer.json:

```
{
    "require": {
        "lasallecrm/lasallecrmemail": "1.*",
    }
}
```


## Service Provider

In config/app.php:
```
Lasallecrm\Lasallecrmemail\LasallecrmemailServiceProvider::class,
Lasallecrm\Lasallecrmemail\LasallecrmemailEventServiceProvider::class,
```


## Facade Alias

* none


## Dependencies
* none


## Publish the Package's Config

With Artisan:
```
php artisan vendor:publish
```

## Migration

With Artisan:
```
php artisan migrate
```

## Notes

* view files will be published to the main app's view folder
* first: install all your packages 
* second: run "vendor:publish" (once for all packages) 
* third:  run "migrate" (once for all packages)

## Nginx Note

The Digital Ocean nginx client_max_body_size default is 1m. This is too small to receive incoming emails from Mailgun, etc, especially when receiving attachments. So add this to your /etc/nginx/nginx.conf. I did not modify any php.ini.

```
       ##
       # increase client_max_body_size
       # https://easyengine.io/tutorials/php/increase-file-upload-size-limit/  (Bob Mar2016)
       ##

       client_max_body_size 20m;
```


## Serious Caveat 

This package is designed to run specifically with my LaSalle Software packages; and, integrates with my LaSalle Customer Relationship Management packages.