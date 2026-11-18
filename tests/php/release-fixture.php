<?php
/** Editorial data used only to prove code rollback preserves new writes. */
require __DIR__ . '/bootstrap.php';
$action=$argv[1]??'';$state=get_option('iotnest_qa_release',[]);
if('create'===$action){
    if($state)throw new RuntimeException('Release fixture already exists.');
    $id=wp_insert_post(['post_type'=>'post','post_status'=>'draft','post_title'=>'QA release','post_content'=>'Antes da troca']);
    $upload=wp_upload_bits('qa-release.txt',null,'Mídia independente do release');if($upload['error'])throw new RuntimeException($upload['error']);
    $state=['id'=>$id,'file'=>$upload['file'],'hash'=>hash_file('sha256',$upload['file'])];update_option('iotnest_qa_release',$state);
}elseif('edit'===$action){wp_update_post(['ID'=>$state['id'],'post_content'=>'Edição editorial depois do deploy']);update_post_meta($state['id'],'_iotnest_qa_after_deploy','preservado');}
elseif('check'===$action){iotnest_check(get_post_field('post_content',$state['id'])==='Edição editorial depois do deploy'&&get_post_meta($state['id'],'_iotnest_qa_after_deploy',true)==='preservado','rollback preserves recent editorial body and metadata');iotnest_check(hash_file('sha256',$state['file'])===$state['hash'],'rollback preserves uploaded media bytes');}
elseif('delete'===$action){wp_delete_post($state['id'],true);unlink($state['file']);delete_option('iotnest_qa_release');}
else throw new RuntimeException('Unknown release fixture action.');
