<?php

require_once __DIR__ . '/../../config.php';

$base_factory = \local_mxaimanager\app\factory::make();

$view = required_param('view', PARAM_TEXT);
$action = optional_param('action', 'index', PARAM_TEXT);

echo $base_factory->controller()->view($view)->action($action);
