#!/usr/bin/env php
<?php

declare(strict_types=1);

use Swoole\Runtime;

ini_set('memory_limit', '-1');

defined('ROOT_PATH') || define('ROOT_PATH', __DIR__);
defined('PATH_CONFIG') || define('PATH_CONFIG', ROOT_PATH . '/config');
defined('PATH_PUBLIC') || define('PATH_PUBLIC', ROOT_PATH . '/public');
defined('PATH_SRC') || define('PATH_SRC', ROOT_PATH . '/src');
defined('PATH_VAR') || define('PATH_VAR', ROOT_PATH . '/runtime');

require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/utils/env.php';

Runtime::enableCoroutine();
