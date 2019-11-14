### 功能说明   
1、能替换某个项目下的所有文件内容   
2、能复制原目录文件生成一个内容被替换过新的项目

### 使用   
```shell
composer require phper666/generate-file:~1.0
```

### 例子   
```shell
namespace Phper666\GenerateFile;
require "GenerateFile.php";
$gf = new GenerateFile();

// 要替换的参数 key为文件名称、目录名称、文件内容，value为要替换的值
$params = [
    'wxapp-template' => 'wxapp-test',
    'wxapp_template' => 'wxapp_test',
    'WxappTemplate' => 'WxappTest',
];

// 设置要处理的目录，必须要绝对路径,并且目录一定要存在
$dir = '/app/www/TmgAdminBE/tmg-addons-stub/wxapp-template';

$gf->setDefaultParams()  // 初始化参数，主要是为了解决常驻内存单例的问题
    ->setReplaceDir($dir) // 设置处理的目录
    ->setReplaceParams($params) // 设置要替换的参数
    ->setProjectName('wxapp-test') // 设置生成新的项目名称，为空则不会生成新的项目，会默认替换当前项目的文件、目录、文件内容，如果设置了项目名称，则不会修改当前目录，会copy一份新的项目，并且替换新的项目文件内容
    ->setReplaceFileName(true) // 是否开启文件名称替换，开启后会自动替换文件的名称，不会替换目录的名称
    ->setReplaceFileExt(['*']) // 设置支持替换文件的后缀，默认替换项目下的所有的文件
    ->run();

echo true;
```
