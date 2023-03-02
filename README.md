

Quick Start
-----------
Install the library using [composer](https://getcomposer.org):
```console
    composer require free2one/php-accessor
```

Add the following configuration to your `composer.json` file:
```json
{
  "scripts":{
    "php-accessor":"php-accessor generate $1"
  }
}
```
Add the corresponding annotation to the class that needs to generate accessors:
```php
<?php
namespace App;

use PhpAccessor\Attribute\Data;

#[Data]
class Entity
{
    private int $id;

    private string $name;
}

```

Run the following command to generate proxy class:
```console
    composer run-script php-accessor CLASS_PATH
```
