![Insitaction](https://www.insitaction.com/assets/img/logo_insitaction.png)
# Field Encrypt

Field Encrypt is a symfony bundle which allows to encrypt the fields in the database as required by the RGPD.

## Installation:
```bash
composer require insitaction/field-encrypt-bundle 
```

## Environment:

You must define the ENCRYPT_KEY var in your .env file.

The ENCRYPT_KEY must be an aes-256-cbc key with the 32 first characters.

## Usage:

You must add the @Encrypt annotation or the #[Encrypt] attibute to the field you want to encrypt.

Let's see an example: 
```php
<?php

namespace App\Entity;

use Insitaction\FieldEncryptBundle\Annotations\Encrypt;

class MyEntity
{

    /**
     * @Encrypt
     */
    #[Encrypt]
    private mixed $myPrivateEncryptedField;
```

That's else !