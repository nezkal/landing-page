Landing Page
============================

This is an simple way to use the Silex Microframework for build an Landing Page.


> Used Frameworks

> * Silex
> * Twitter Bootstrap
> * Recaptcha
> * Swiftmail


##Configuration


**Recaptcha**

`index.php`

```php

    // defines the SITE_KEY in Google ReCaptcha
    define('SITE_KEY', 'NONE');
    
    // define the SECRET_KEY in Recaptcha
    define('SECRET_KEY', 'NONE');

```

**SwiftMail**

`index.php`

```php
    
    $mailerOptions = array(
        'host' => 'host',
        'port' => '25',
        'username' => 'username@username.com.br',
        'password' => 'password',
        'encryption' => null,
        'auth_mode' => null
    );
```

##Templates

The templates path views/

Index: `views/default.html.twig`

E-mail template: `views/email/contact.html.twig`

