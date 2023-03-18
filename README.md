# PHP Accessor

生成类访问器（Getter & Setter）

## 快速入门

### 安装
```console
    composer require free2one/php-accessor
```

项目`composer.json` 文件中配置以下信息信息
```json
{
  "scripts":{
    "php-accessor": "@php vendor/bin/php-accessor generate"
  }
}
```
将相应的注释添加到需要生成访问器的类中:
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
运行命令生成代理类
```console
    composer run-script php-accessor CLASS_PATH
```

## 注解说明

### `#[Data]`
`PhpAccessor\Attribute\Data`

用于PHP Accessor识别是否需要生成访问器.

可配置项

`namingConvention`

访问器命名方式设置,暂支持以下类别:
  - `NamingConvention::UPPER_CAMEL_CASE`: 大驼峰
  - `NamingConvention::LOWER_CAMEL_CASE`: 小驼峰
  - `NamingConvention::NONE`: 首字母大写,系统默认配置

### `#[Overlook]`
`PhpAccessor\Attribute\Overlook`

用于类字段,设置后该字段将不生成访问器.

## 要点说明

### 如何使用生成的代理类

如果你的项目使用的是Hyperf框架，则可直接引入<a href="https://github.com/kkguan/hyperf-php-accessor">Hyperf PHP Accessor</a>包。其他情况下，请参考以下示例。

待生成访问器的类`Entity`

```php
<?php

namespace App;

use PhpAccessor\Attribute\Data;
use PhpAccessor\Attribute\Overlook;

#[Data()]
class Entity
{
    #[Overlook]
    private string $ignore;

    private int $id;
}
```

执行文件示例

```php
<?php

require_once "vendor/autoload.php";

use App\Entity;
use Composer\Autoload\ClassLoader;
use PhpAccessor\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Finder\Finder;

$scanDir = ['PROJECT_ROOT_PATH/app']; //需要扫描的项目目录
$proxyDir = 'PROJECT_ROOT_PATH/.php-accessor'; //代理类存放目录

//生成代理类
$input = new ArrayInput([
    'command' => 'generate',
    'path' => $scanDir,
    '--dir' => $proxyDir,
    '--gen-meta' => 'yes',  //发布线上时，可设置为no
    '--gen-proxy' => 'yes',
]);
$app = new Application();
$app->setAutoExit(false);
$app->run($input);

//利用composer注册自动加载
$finder = new Finder();
$finder->files()->name('*.php')->in($proxyDir);
$classLoader = new ClassLoader();
$classMap = [];
foreach ($finder->getIterator() as $value) {
    $classname = str_replace('@', '\\', $value->getBasename('.' . $value->getExtension()));
    $classname = substr($classname, 1);
    $classMap[$classname] = $value->getRealPath();
}
$classLoader->addClassMap($classMap);
$classLoader->register(true);

//Entity已被替换为代理类😸
$entity = new Entity();
$entity->setId(222);
var_dump($entity);
```





## 相关资源

#### <a href="https://github.com/kkguan/hyperf-php-accessor">Hyperf PHP Accessor</a>: 服务启动时将自动生成访问器代理类,同时对原始类进行替换.

#### <a href="https://github.com/kkguan/php-accessor-idea-plugin">PHP Accessor IDEA Plugin</a>: Phpstorm辅助插件,文件保存时自动生成访问器.支持访问器的跳转,代码提示,查找及类字段重构等.

