import {test,expect} from '@playwright/test';
import {execFileSync} from 'node:child_process';
import {readFile,writeFile,mkdir,realpath,rename,symlink,unlink,rm} from 'node:fs/promises';
import path from 'node:path';
import {createHash} from 'node:crypto';
import {zipSync,unzipSync} from 'fflate';
const php=process.env.PHP_BIN||'php';
const fixture=action=>execFileSync(php,['tests/php/release-fixture.php',action],{encoding:'utf8'});
const hash=bytes=>createHash('sha256').update(bytes).digest('hex');
test('T23: promoção atômica, assets retidos, checksum e rollback preservam edição recente',async({page},testInfo)=>{
  test.setTimeout(120000);const artifact=process.env.IOTNEST_TEST_ARTIFACT;test.skip(!artifact,'Executar pelo runner com artefato.');
  const bytes=await readFile(artifact),files=unzipSync(bytes),meta=JSON.parse(Buffer.from(files['release.json']).toString());test.skip(meta.dirty,'Aceite de release exige artefato limpo.');
  const wp=await realpath(process.env.IOTNEST_TEST_WP_ROOT);await readFile(path.join(wp,'.iotnest-test'));const root=path.join(path.dirname(wp),'release-qa');await mkdir(root);await writeFile(path.join(root,'.iotnest-releases'),'iotnest-release-root-v1');
  const config=path.join(root,'config.json');await writeFile(config,JSON.stringify({release_root:root,wordpress_root:wp,site_url:process.env.IOTNEST_TEST_URL,local_test:true}));
  const command=(action,...args)=>execFileSync(php,['ops/release.php',action,config,...args],{encoding:'utf8'});
  const componentPaths=['themes/iotnest','plugins/iotnest-core'];const moved=[];let created=false,createdHome=false;
  const candidate=async(commit,bad=false)=>{
    const entries={...files},metadata=structuredClone(meta);metadata.commit=commit;
    for(const name of ['wp-content/themes/iotnest/inc/release.php','wp-content/plugins/iotnest-core/includes/release.php']){entries[name]=Buffer.from(Buffer.from(entries[name]).toString().replaceAll(meta.commit,bad?meta.commit:commit));metadata.files[name]=hash(entries[name]);}
    const manifestPath='wp-content/themes/iotnest/assets/dist/manifest.json',manifest=JSON.parse(Buffer.from(entries[manifestPath]).toString());const old=`wp-content/themes/iotnest/assets/dist/${manifest['app.css']}`;
    const css=Buffer.concat([Buffer.from(entries[old]),Buffer.from(`\n/* QA ${commit} */`)]),filename=`app.${hash(css).slice(0,12)}.css`,next=`wp-content/themes/iotnest/assets/dist/${filename}`;
    delete entries[old];delete metadata.files[old];entries[next]=css;metadata.files[next]=hash(css);manifest['app.css']=filename;entries[manifestPath]=Buffer.from(JSON.stringify(manifest));metadata.files[manifestPath]=hash(entries[manifestPath]);
    entries['release.json']=Buffer.from(JSON.stringify(metadata));const output=zipSync(entries);const file=path.join(root,commit+'.zip');await writeFile(file,output);return {file,sha:hash(output)};
  };
  try{
    execFileSync(php,['tests/php/home-fixture.php','create']);createdHome=true;
    expect(()=>command('stage',artifact,'0'.repeat(64))).toThrow(/Checksum/);command('stage',artifact,hash(bytes));
    for(const component of componentPaths){const source=path.join(wp,'wp-content',component),backup=path.join(root,component.replace('/','-'));await rename(source,backup);moved.push({source,backup});await symlink(path.join(root,'current/wp-content',component),source);}
    await writeFile(path.join(wp,'.iotnest-asset-pool'),path.join(root,'asset-pool'));
    command('activate',meta.commit);fixture('create');created=true;
    const second='b'.repeat(40),bad='c'.repeat(40);const good=await candidate(second),broken=await candidate(bad,true);
    command('stage',good.file,good.sha);command('activate',second);fixture('edit');command('rollback',meta.commit);const preserved=fixture('check');
    command('stage',broken.file,broken.sha);expect(()=>command('activate',bad)).toThrow(/confirmaram/);expect(await realpath(path.join(root,'current'))).toBe(path.join(root,'releases',meta.commit));
    await page.goto('/');expect((await page.request.get('/wp-json/iotnest/v1/health')).status()).toBe(200);
    await writeFile(testInfo.outputPath('rollback.json'),JSON.stringify({artifactCommit:meta.commit,secondFixture:second,rejectedFixture:bad,corruptChecksumRejected:true,atomicPairVerified:true,retainedAssetsVerified:true,editorial:preserved.trim(),scope:'Somente instalação descartável; versões B/C sintéticas para exercitar troca/falha.'},null,2));
  }finally{
    if(created)fixture('delete');for(const {source,backup}of moved.reverse()){await unlink(source);await rename(backup,source);}if(createdHome)execFileSync(php,['tests/php/home-fixture.php','delete']);await rm(path.join(wp,'.iotnest-asset-pool'),{force:true});await rm(root,{recursive:true,force:true});
  }
});
