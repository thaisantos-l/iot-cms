<?php
/** Yoast integration fixture on the disposable installation only. */
require __DIR__ . '/bootstrap.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
$action=$argv[1]??'';
if('activate'===$action){$result=activate_plugin('wordpress-seo/wp-seo.php');if(is_wp_error($result))throw new RuntimeException($result->get_error_message());update_option('blog_public',1);flush_rewrite_rules(false);}
elseif('deactivate'===$action){deactivate_plugins('wordpress-seo/wp-seo.php');flush_rewrite_rules(false);}
elseif('flush'===$action)flush_rewrite_rules(false);
elseif('meta'===$action){$payload=json_decode($argv[2],true,32,JSON_THROW_ON_ERROR);foreach($payload['fields']as$key=>$value)update_post_meta((int)$payload['id'],$key,$value);}
else throw new RuntimeException('Unknown SEO fixture action.');
