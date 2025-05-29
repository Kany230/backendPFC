<?php

use Laravel\Sanctum\Sanctum;

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Listez ici les domaines qui devraient être traités comme "stateful".
    | Pour les applications SPA, vous devez inclure votre domaine local.
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    |
    | This array contains the authentication guards that will be checked when
    | Sanctum is trying to authenticate a request. If none of these guards
    | are able to authenticate the request, Sanctum will use the bearer
    | token that's present on an incoming request for authentication.
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration des jetons Sanctum
    |--------------------------------------------------------------------------
    |
    | Ce paramètre contrôle la durée de vie des jetons d'accès générés par
    | Sanctum. Il est défini en minutes et doit être raisonnable.
    |
    */

    'expiration' => null,

    /*
    |--------------------------------------------------------------------------
    | Token Prefix
    |--------------------------------------------------------------------------
    |
    | Sanctum peut stocker les jetons dans une table de base de données en utilisant
    | ce préfixe. Vous pouvez le modifier si vous le souhaitez, mais en utilisant
    | un préfixe unique, vous évitez les conflits avec d'autres tables.
    |
    */

    'prefix' => 'sanctum_',

    /*
    |--------------------------------------------------------------------------
    | Nom du cookie Sanctum
    |--------------------------------------------------------------------------
    |
    | Sanctum créera un cookie avec ce nom. Vous pouvez le modifier si vous
    | le souhaitez, mais assurez-vous d'utiliser un nom unique pour éviter
    | les conflits avec d'autres cookies dans votre application.
    |
    */

    'cookie' => env(
        'SANCTUM_COOKIE',
        'sanctum_token'
    ),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    |
    | Lors de l'authentification de votre premier jeton d'API, Sanctum utilisera
    | ce middleware pour s'assurer que les requêtes proviennent d'une source autorisée.
    |
    */

    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],

];
