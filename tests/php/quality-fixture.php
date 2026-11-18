<?php
/** Mutable identity only on the isolated QA site. */
require __DIR__ . '/bootstrap.php';
$action=$argv[1]??'';
if('palette'===$action){foreach(Iotnest\Theme\palette()as$key=>$value)set_theme_mod('iotnest_color_'.$key,'#123456');echo wp_json_encode(array_keys(Iotnest\Theme\palette()));}
elseif('restore'===$action){foreach(Iotnest\Theme\palette()as$key=>$value)remove_theme_mod('iotnest_color_'.$key);}
else throw new RuntimeException('Unknown quality fixture action.');
