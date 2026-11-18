import {execFileSync} from 'node:child_process';
import {mkdtemp,writeFile,readFile,rm} from 'node:fs/promises';
import {tmpdir} from 'node:os';
import path from 'node:path';
import {createHash} from 'node:crypto';
const mode=process.argv[2];if(!['deploy','rollback'].includes(mode))throw Error('Modo inválido.');
const env=process.env;
const required=name=>{if(!env[name])throw Error(`Configuração ausente: ${name}`);return env[name];};
const validated=(name,pattern)=>{const value=required(name);if(!pattern.test(value))throw Error(`Formato inválido: ${name}`);return value;};
const commit=validated('RELEASE_COMMIT',/^[a-f0-9]{40}$/),host=validated('DEPLOY_HOST',/^[a-zA-Z0-9][a-zA-Z0-9.-]*$/),user=validated('DEPLOY_USER',/^[a-z_][a-z0-9_-]*$/),port=validated('DEPLOY_PORT',/^[0-9]{1,5}$/);
if(Number(port)<1||Number(port)>65535)throw Error('Porta inválida.');
const releaseRoot=validated('RELEASE_ROOT',/^\/[a-zA-Z0-9_./-]+$/),wpRoot=validated('WORDPRESS_ROOT',/^\/[a-zA-Z0-9_./-]+$/);
for(const dir of [releaseRoot,wpRoot])if(dir==='/'||dir.split('/').includes('..'))throw Error('Diretório inválido.');
const site=new URL(required('SITE_URL'));if(site.protocol!=='https:'||site.username||site.password||site.search||site.hash)throw Error('SITE_URL requer HTTPS público.');
const execute=(command,args,options={})=>execFileSync(command,args,{stdio:'pipe',...options});
// O workflow deve executar na main protegida; um commit arbitrário não é promovível.
if(env.GITHUB_REF!=='refs/heads/main')throw Error('Promoção exige a main protegida.');
execute('git',['merge-base','--is-ancestor',commit,'HEAD']);
const temporary=await mkdtemp(path.join(tmpdir(),'iotnest-promotion-'));
const quote=value=>`'${value.replaceAll("'", "'\\''")}'`;
try{
  let approved;
  if(mode==='deploy'){
    const run=validated('CI_RUN_ID',/^[0-9]+$/),expected=validated('RELEASE_SHA256',/^[a-f0-9]{64}$/),repo=validated('GITHUB_REPOSITORY',/^[a-zA-Z0-9_.-]+\/[a-zA-Z0-9_.-]+$/);
    const info=JSON.parse(execute('gh',['api',`repos/${repo}/actions/runs/${run}`],{encoding:'utf8'}));
    if(info.conclusion!=='success'||info.head_sha!==commit||info.head_branch!=='main'||!['push','workflow_dispatch'].includes(info.event)||info.path!=='.github/workflows/ci.yml')throw Error('Execução CI não aprova este commit da main.');
    execute('gh',['run','download',run,'--repo',repo,'--name',`iotnest-${commit}`,'--dir',temporary]);
    const zip=path.join(temporary,`iotnest-${commit}.zip`);if(createHash('sha256').update(await readFile(zip)).digest('hex')!==expected)throw Error('Checksum aprovado difere do artefato CI.');
    approved={zip,expected};
  }
  const key=path.join(temporary,'key'),known=path.join(temporary,'known_hosts');
  await writeFile(key,required('DEPLOY_SSH_KEY'),{mode:0o600});await writeFile(known,required('DEPLOY_KNOWN_HOSTS'),{mode:0o600});
  const sshArgs=['-i',key,'-p',port,'-o','BatchMode=yes','-o','IdentitiesOnly=yes','-o','StrictHostKeyChecking=yes','-o',`UserKnownHostsFile=${known}`];
  const remote=`${user}@${host}`,incoming=`${releaseRoot}/incoming`;
  const ssh=command=>execute('ssh',[...sshArgs,remote,command],{stdio:'inherit'});
  ssh(`test -f ${quote(releaseRoot+'/.iotnest-releases')} && mkdir -p ${quote(incoming)}`);
  const config=path.join(temporary,'config.json');await writeFile(config,JSON.stringify({release_root:releaseRoot,wordpress_root:wpRoot,site_url:site.href.replace(/\/$/,'')}));
  const transfer=files=>execute('rsync',['--archive','--chmod=F600,D700','--protect-args','-e',['ssh',...sshArgs].map(quote).join(' '),...files,`${remote}:${incoming}/`],{stdio:'inherit'});
  // Helper/config são ferramentas operacionais; não entram nos diretórios públicos do tema/plugin.
  await transfer(['ops/release.php',config]);
  if(approved){transfer([approved.zip]);ssh(['php',`${incoming}/release.php`,'stage',`${incoming}/config.json`,`${incoming}/iotnest-${commit}.zip`,approved.expected].map(quote).join(' '));}
  ssh(['php',`${incoming}/release.php`,mode==='deploy'?'activate':'rollback',`${incoming}/config.json`,commit].map(quote).join(' '));
}finally{await rm(temporary,{recursive:true,force:true});}
