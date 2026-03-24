## Pendientes

## Tomar en cuenta
***Cambiar locale en el archivo .env***
de 
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
a
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_MX

## Ajustes útiles

Cambiar donde se guardan los logs de dejarlo solo en el archivo de log
LOG_STACK=single
 a mostrarlo en la consola y en el archivo de log pero diario
LOG_STACK=stderr,daily

## Comandos útiles

**Crear un nuevo proyecto**
composer create-project laravel/laravel nombre_proyecto

**Levantar el servidor**
php artisan serve
php artisan serve --host 0.0.0.0
npm run dev
Si es para producción
npm run build

**Eliminar la bd y volver a crearla con su semillas**
php artisan migrate:fresh --seed

**Instalar laravel/jetstream**
composer require laravel/jetstream    
php artisan jetstream:install livewire
php artisan migrate:fresh --seed

**Regresar a un commit anterior**
git reset --soft HEAD~1

**Publicar traducciones**
php artisan lang:publish
composer require laravel-lang/common --dev
php artisan lang:add es
php artisan lang:update

***Crear un controlador con recursos y modelo**
Única función
php artisan make:controller PostController --invokable
Simple
php artisan make:controller PostController
Con recursos y apunta aun modelo
php artisan make:controller PostController --resource --model=Post
Con recursos y apunta aun modelo y solicitudes
php artisan make:controller PhotoController --model=Photo --resource --requests

***Crear una solicitud y una regla***
php artisan make:request StorePostRequest
php artisan make:rule Uppercase

**Crear un modelo**
En si ocupamos
php artisan make:model Post -mfs
pero te dejo otras opciones
```
# Generate a model and a FlightFactory class...
php artisan make:model Flight --factory
php artisan make:model Flight -f
 
# Generate a model and a FlightSeeder class...
php artisan make:model Flight --seed
php artisan make:model Flight -s
 
# Generate a model and a FlightController class...
php artisan make:model Flight --controller
php artisan make:model Flight -c
 
# Generate a model, FlightController resource class, and form request classes...
php artisan make:model Flight --controller --resource --requests
php artisan make:model Flight -crR
 
# Generate a model and a FlightPolicy class...
php artisan make:model Flight --policy
 
# Generate a model and a migration, factory, seeder, and controller...
php artisan make:model Flight -mfsc
 
# Shortcut to generate a model, migration, factory, seeder, policy, controller, and form requests...
php artisan make:model Flight --all
php artisan make:model Flight -a
 
# Generate a pivot model...
php artisan make:model Member --pivot
php artisan make:model Member -p
```


**Crear un email**
php artisan make:mail OrderShipped
//Crea la vista correspondiente
