![Insitaction](https://www.insitaction.com/assets/img/logo_insitaction.png)
# Field Encrypt

Field Encrypt is a symfony bundle which allows to encrypt the fields in the database as required by the GDPR.

## Installation:
```bash
composer require insitaction/field-encrypt-bundle 
```

## Environment:

You must define the ENCRYPT_KEY var in your .env file.

The ENCRYPT_KEY must be an aes-256-cbc key with the 32 first characters.

## Usage:

You must add the EncryptedString::ENCRYPTED_STRING type attribute to the field you want to encrypt/decrypt.

Let's see an example: 
```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Insitaction\FieldEncryptBundle\Doctrine\DBAL\Types\EncryptedString;

class MyEntity
{

    #[ORM\Column(type: EncryptedString::ENCRYPTED_STRING, unique: true)]
    private mixed $myPrivateEncryptedField;
    
    #[ORM\Column(type: 'string', unique: true)]
    private string $email;

    public function getUniqueIdentifier(): string
    {
        return $this->email;
    }
}
```

That's else !
