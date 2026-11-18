<?php
/** CLI de promoção de bytes verificados. Nunca carrega wp-config nem altera banco/uploads. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
function ensure(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
function read_json(string $file): array { $data=json_decode((string)file_get_contents($file),true,64,JSON_THROW_ON_ERROR); ensure(is_array($data),'JSON inválido.'); return $data; }
function sha_valid(string $sha): bool {return (bool)preg_match('/\A[a-f0-9]{40}\z/',$sha);}
function replace_link(string $root,string $target): void {
    $next=$root.'/.current-'.bin2hex(random_bytes(8));ensure(symlink($target,$next),'Não foi possível preparar troca.');
    try {ensure(rename($next,$root.'/current'),'Falha na troca atômica.');}finally{if(is_link($next))unlink($next);}
}
function request_url(string $url,int $expected=200): string {
    $context=stream_context_create(['http'=>['timeout'=>12,'ignore_errors'=>true,'follow_location'=>0,'header'=>"Cache-Control: no-cache\r\n"]]);
    $body=@file_get_contents($url,false,$context);$headers=$http_response_header??[];
    ensure(is_string($body) && preg_match('~^HTTP/\S+ '.$expected.'(?: |$)~',$headers[0]??'')===1,'Saúde HTTP falhou: '.$expected.' esperado.');return $body;
}
function hook(array $config,string $target,bool $qa): void {
    if($qa)return;
    $path=$config['release_root'].'/hooks/after-switch';ensure(is_file($path)&&is_executable($path),'Hook de OPcache/purge obrigatório.');
    $process=proc_open([$path,$target],[0=>['file','/dev/null','r'],1=>['file','/dev/null','w'],2=>['file','/dev/null','w']],$pipes);
    ensure(is_resource($process)&&proc_close($process)===0,'Hook de OPcache/purge falhou.');
}
function health(array $config,string $commit,?array $previous): void {
    $base=rtrim($config['site_url'],'/');$status=json_decode(request_url($base.'/wp-json/iotnest/v1/health'),true,16,JSON_THROW_ON_ERROR);
    ensure(($status['theme']??'')===$commit&&($status['plugin']??'')===$commit,'FPM/tema/plugin não confirmaram o commit promovido.');
    ensure(($status['schema']??0)===1&&str_starts_with($status['php']??'','8.2.')&&preg_match('/^7\.1(?:\.|$)/',$status['wordpress']??''),'Runtime/schema incompatível.');
    foreach(['/','/blog/','/cases/']as$path)ensure(str_contains(request_url($base.$path),'</html>'),'Página incompleta.');
    request_url($base.'/iotnest-release-health-404-'.substr($commit,0,12).'/',404);
    $metadata=read_json($config['release_root'].'/releases/'.$commit.'/release.json');
    foreach([$metadata,$previous]as$release){if(!$release)continue;$checked=[];
        foreach($release['files']as$name=>$hash){if(!preg_match('~^wp-content/(themes/iotnest|plugins/iotnest-core)/assets/dist/[^/]+\.(css|js)$~',$name,$match)||isset($checked[$match[1]]))continue;
            ensure(hash('sha256',request_url($base.'/'.$name))===$hash,'Asset atual ou retido não corresponde ao checksum.');$checked[$match[1]]=true;
        }
    }
}
try{
    [$script,$action,$configPath,$argument]=array_pad($argv,4,'');
    ensure(in_array($action,['stage','activate','rollback'],true),'Uso: release.php stage CONFIG ZIP SHA256 | activate/rollback CONFIG COMMIT');
    $config=read_json($configPath);foreach(['release_root','wordpress_root','site_url']as$key)ensure(is_string($config[$key]??null),'Configuração incompleta.');
    $root=realpath($config['release_root']);$wp=realpath($config['wordpress_root']);
    ensure($root!==false&&$wp!==false&&$root!==$wp&&!str_starts_with($root,$wp.'/')&&!str_starts_with($wp,$root.'/'),'Releases devem ficar fora da raiz WordPress.');
    $config['release_root']=$root;$config['wordpress_root']=$wp;
    ensure(trim((string)@file_get_contents($root.'/.iotnest-releases'))==='iotnest-release-root-v1','Diretório sem marcador de entrega.');
    $parsed=parse_url($config['site_url']);$qa=($config['local_test']??false)===true&&is_file($wp.'/.iotnest-test')&&($parsed['host']??'')==='127.0.0.1';
    ensure($qa||(($parsed['scheme']??'')==='https'&&empty($parsed['user'])&&empty($parsed['pass'])&&empty($parsed['query'])),'Produção requer URL HTTPS pública sem credenciais.');
    ensure(PHP_MAJOR_VERSION===8&&PHP_MINOR_VERSION===2,'CLI requer PHP 8.2.');
    $version=(string)file_get_contents($wp.'/wp-includes/version.php');ensure((bool)preg_match('/\$wp_version\s*=\s*[\x27\x22]7\.1(?:\.[0-9]+)?[\x27\x22]/',$version),'WordPress fora da série 7.1.');
    foreach(['releases','asset-pool']as$dir)if(!is_dir($root.'/'.$dir))ensure(mkdir($root.'/'.$dir,0755),'Falha ao criar diretório de release.');
    $lock=fopen($root.'/.deploy.lock','c');ensure($lock!==false&&flock($lock,LOCK_EX|LOCK_NB),'Outra promoção/rollback está em andamento.');
    if($action==='stage'){
        $expected=$argv[4]??'';ensure((bool)preg_match('/\A[a-f0-9]{64}\z/',$expected)&&hash_file('sha256',$argument)===$expected,'Checksum do pacote inválido.');
        $zip=new ZipArchive();ensure($zip->open($argument)===true,'ZIP inválido.');$raw=$zip->getFromName('release.json');ensure(is_string($raw),'Manifesto ausente.');$metadata=json_decode($raw,true,64,JSON_THROW_ON_ERROR);
        $commit=$metadata['commit']??'';ensure(sha_valid($commit)&&($metadata['dirty']??true)===false&&($metadata['format']??0)===2&&($metadata['schema']??0)===1,'Release não aprovado ou incompatível.');
        ensure(($metadata['php']??'')==='8.2'&&($metadata['wordpress']??'')==='7.1','Contrato de runtime incompatível.');
        $destination=$root.'/releases/'.$commit;ensure(!file_exists($destination),'Release imutável já existe; use activate/rollback.');
        $temporary=$root.'/releases/.staging-'.bin2hex(random_bytes(8));ensure(mkdir($temporary,0755),'Falha ao preparar release.');$seen=[];$total=0;
        try{
            for($index=0;$index<$zip->numFiles;$index++){
                $stat=$zip->statIndex($index);$name=$stat['name'];ensure(!isset($seen[$name]),'Entrada ZIP duplicada.');$seen[$name]=true;$total+=$stat['size'];ensure($total<=150*1024*1024,'Pacote excede 150 MiB.');
                ensure($name==='release.json'||(preg_match('~\Awp-content/(themes/iotnest|plugins/iotnest-core)/[a-zA-Z0-9_./-]+\z~',$name)&&!in_array('..',explode('/',$name),true)&&isset($metadata['files'][$name])),'Arquivo fora do contrato.');
                $os=0;$attributes=0;$zip->getExternalAttributesIndex($index,$os,$attributes);ensure((($attributes>>16)&0170000)!==0120000,'Symlink no ZIP não permitido.');
                $bytes=$zip->getFromIndex($index);ensure(is_string($bytes),'Entrada ZIP ilegível.');if($name!=='release.json')ensure(hash('sha256',$bytes)===$metadata['files'][$name],'Checksum de arquivo inválido.');
                $path=$temporary.'/'.$name;if(!is_dir(dirname($path)))mkdir(dirname($path),0755,true);ensure(file_put_contents($path,$bytes)===strlen($bytes),'Falha ao extrair release.');
            }
            ensure(count($seen)===count($metadata['files'])+1,'Manifesto contém arquivos ausentes.');
            foreach($metadata['files']as$name=>$hash){
                if(!preg_match('~\Awp-content/(themes/iotnest|plugins/iotnest-core)/assets/dist/([a-z0-9-]+\.[a-f0-9]{12}\.(?:css|js))\z~',$name,$match))continue;
                $pool=$root.'/asset-pool/'.$match[1].'/'.$match[2];if(!is_dir(dirname($pool)))mkdir(dirname($pool),0755,true);
                if(file_exists($pool))ensure(hash_file('sha256',$pool)===$hash,'Colisão no pool de assets.');else ensure(copy($temporary.'/'.$name,$pool),'Falha ao reter asset.');
            }
            ensure(rename($temporary,$destination),'Falha ao concluir release imutável.');
        }catch(Throwable $error){$iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($temporary,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);foreach($iterator as$file){if($file->isDir())rmdir($file->getPathname());else unlink($file->getPathname());}rmdir($temporary);throw$error;}finally{$zip->close();}
        echo json_encode(['staged'=>$commit,'sha256'=>$expected],JSON_THROW_ON_ERROR)."\n";
    }else{
        ensure(sha_valid($argument),'Commit inválido.');$target=$root.'/releases/'.$argument;ensure(is_dir($target)&&!is_link($target),'Release não encontrado.');$metadata=read_json($target.'/release.json');ensure(($metadata['schema']??0)===1&&($metadata['commit']??'')===$argument&&($metadata['dirty']??true)===false,'Release incompatível.');
        foreach($metadata['files']as$name=>$hash)ensure(hash_file('sha256',$target.'/'.$name)===$hash,'Release foi alterado após staging.');
        foreach(['themes/iotnest','plugins/iotnest-core']as$path)ensure(is_link($wp.'/wp-content/'.$path)&&readlink($wp.'/wp-content/'.$path)===$root.'/current/wp-content/'.$path,'Onboarding dos links WordPress está incompleto.');
        ensure(!file_exists($root.'/current')||is_link($root.'/current'),'current deve ser symlink.');
        $previous=is_link($root.'/current')?readlink($root.'/current'):null;
        if($previous)ensure(str_starts_with($previous,$root.'/releases/')&&sha_valid(basename($previous)),'Destino anterior fora do contrato.');
        $old=$previous?read_json($previous.'/release.json'):null;ensure(!$old||($old['schema']??0)===1,'Rollback de schema requer plano explícito.');
        if(!$qa)ensure(is_executable($root.'/hooks/after-switch'),'Hook obrigatório antes da troca.');
        replace_link($root,$target);
        try{hook($config,$argument,$qa);health($config,$argument,$old);}
        catch(Throwable$error){if($previous){replace_link($root,$previous);hook($config,basename($previous),$qa);health($config,basename($previous),null);}else unlink($root.'/current');throw$error;}
        file_put_contents($root.'/deploy-log.jsonl',json_encode(['time'=>gmdate('c'),'action'=>$action,'commit'=>$argument,'previous'=>$previous?basename($previous):null],JSON_THROW_ON_ERROR)."\n",FILE_APPEND|LOCK_EX);
        echo json_encode(['action'=>$action,'commit'=>$argument,'health'=>'passed'],JSON_THROW_ON_ERROR)."\n";
    }
}catch(Throwable$error){fwrite(STDERR,'Entrega interrompida: '.$error->getMessage()."\n");exit(1);}
