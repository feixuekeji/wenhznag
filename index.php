<?php
namespace think;

// 加载框架基础引导文件
require __DIR__ . '/thinkphp/base.php';
// 添加额外的代码
// ...
define('ROOT_PATH', __DIR__ . '/');
// 执行应用并响应
Container::get('app')->run()->send();
