<?php
/** Local hook coverage does not certify the production cache SLA. */
require __DIR__ . '/bootstrap.php';
$events=[];$accept=true;
$adapter=function($value,$event)use(&$events,&$accept){$events[]=$event;return $accept;};
add_filter('iotnest_cache_purge',$adapter,10,2);
$previous=get_option('iotnest_settings',null);$health=get_option('iotnest_cache_health',null);$id=0;
try{
    Iotnest\Core\flush_purge();$events=[];
    $id=wp_insert_post(['post_type'=>'iot_case','post_status'=>'draft','post_title'=>'QA cache','meta_input'=>['_iotnest_case'=>['company'=>'QA']]]);
    update_post_meta($id,'_iotnest_case',['company'=>'Final after save']);
    iotnest_check(count($events)===0,'purge is deferred until all writes complete');
    $event=Iotnest\Core\flush_purge();iotnest_check(count($events)===1&&$event['scope']==='site'&&$event['verified_adapter'],'writes are coalesced in one all-dependent-pages purge');
    foreach(['publish','private','draft','future']as$status){wp_update_post(['ID'=>$id,'post_status'=>$status,'post_date'=>gmdate('Y-m-d H:i:s',time()+3600),'post_date_gmt'=>gmdate('Y-m-d H:i:s',time()+3600)]);iotnest_check(Iotnest\Core\flush_purge()!==null,'status change queues purge');}
    wp_publish_post($id);iotnest_check(Iotnest\Core\flush_purge()!==null,'actual scheduled publication queues purge');
    update_post_meta($id,'_yoast_wpseo_title','QA title');iotnest_check(Iotnest\Core\flush_purge()!==null,'SEO field update invalidates public HTML');
    update_option('iotnest_settings',['contact_enabled'=>false]);iotnest_check(Iotnest\Core\flush_purge()!==null,'global settings invalidate all dependents');
    $accept=false;Iotnest\Core\queue_purge('failure');Iotnest\Core\flush_purge();iotnest_check(get_option('iotnest_cache_health')['success']===false,'failed adapter is recorded and does not authorize public caching');
    wp_delete_post($id,true);$id=0;iotnest_check(Iotnest\Core\flush_purge()!==null,'deletion invalidates dependent lists');
    $args=Iotnest\Core\sitemap_public_query([]);iotnest_check($args['has_password']===false&&$args['post_status']==='publish','native sitemap excludes protected and unpublished records');
}finally{
    if($id)wp_delete_post($id,true);remove_filter('iotnest_cache_purge',$adapter,10);
    foreach(['iotnest_settings'=>$previous,'iotnest_cache_health'=>$health]as$key=>$value){if($value===null)delete_option($key);else update_option($key,$value);}
    $GLOBALS['iotnest_purge_queue']=[];
}
