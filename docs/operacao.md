# Operação e entrega iotnest

Este repositório entrega tema `iotnest` e plugin `iotnest-core`, com PHP 8.2, WordPress 7.1, schema editorial 1 e assets compilados. Banco, uploads, credenciais, SMTP, core e plugins de terceiros pertencem ao ambiente. Ativar ou promover o código não importa conteúdo.

Só existe PRD, conforme decisão do responsável. Nenhum workflow ou comando remoto foi executado nesta entrega. Os testes locais usam WordPress/MySQL descartáveis; não certificam recebimento de e-mail, CDN, OPcache/FPM, cron ou prazo de atualização em produção.

## Primeira instalação e conteúdo

1. Preparar backup de banco e uploads, com restauração testada. Instalar WordPress 7.1 e PHP 8.2 com as extensões previstas no ambiente de CI. Atualizações de core/plugins são um processo separado do release do tema.
2. Instalar o plugin próprio e o tema do mesmo ZIP verificado. Ativar o plugin e o tema. Sem conteúdo, a home continua válida e não apresenta seções vazias.
3. Em **Ferramentas → Importar conteúdo iotnest**, executar a importação institucional e conferir o relatório. Ela cria apenas seus registros ausentes; preserva alterações, campos intencionalmente apagados e tombstones. Mídia própria importada vai à biblioteca, não é sobrescrita por releases.
4. Executar a configuração inicial explícita oferecida pelo importador somente após revisar o resultado. Ela recusa home/slug conflitante, publica somente home e página do blog e configura os menus disponíveis. Os três documentos legais importados continuam em rascunho para revisão e publicação deliberada. Blog e cases não recebem exemplos. O post nativo “Hello world” só é retirado da publicação quando reconhecido como exemplar original intacto.
5. Conferir links, políticas, endereço, contatos e destinatário. Formulário permanece desligado até validar SMTP. YouTube carrega sob demanda; analytics/GTM ficam desligados inicialmente.

## SMTP e consentimento

Escolher/configurar o plugin SMTP e provedor no ambiente; senha, chave, remetente e domínio autenticado não são campos do tema. Em **Configurações → iotnest**, habilitar contato só com destinatário válido e transporte testado. O visitante fornece apenas `Reply-To` validado. `wp_mail()` positivo significa aceitação pelo transporte, nunca comprovação de entrega na caixa postal.

O handler não guarda o corpo ou os campos da submissão. Guarda apenas estado de idempotência por até 24 horas e limite de três tentativas por conexão/minuto, com identificadores HMAC. O cron limpa registros operacionais. Rever políticas de logging do provedor/plugin: elas podem armazenar conteúdo independentemente deste código. Falha mantém campos na página; timeout reutiliza a chave da operação para impedir duplicação.

Bridge da CMP: registrar `iotnest_consent_adapter_ready` como `true` somente depois de implementar `window.iotnestConsent.has(purpose)` e `request(purpose)`. Propósitos: `analytics` e, se configurado, `youtube`. Persistir a escolha **antes** de disparar `window.dispatchEvent(new Event('iotnest:consent'))`. Sem bridge, tags dependentes de consentimento ficam bloqueadas. GTM prevalece sobre GA4 direto para evitar duplicação. Revogar analytics recarrega a página após persistir a negativa, descartando timers e tags já executadas; retirar uma tag do DOM não descarrega seu código. Não criar script livre no painel.

## Cache e agendamento

O hook `iotnest_cache_purge` recebe `(false, event)` após as gravações completas, no shutdown. `event.scope` é `site`, abrangendo home, arquivos, páginas, menus condicionais, relacionados, SEO e identidade. O adapter precisa invalidar **todas** as camadas e retornar `true` somente após confirmação. Falha é registrada em `iotnest_cache_health`, sem dados editoriais ou credenciais.

Até existir adapter medido, o HTML emite `no-store` e `DONOTCACHEPAGE`. Cache de página executado antes do WordPress e proxy/CDN precisam obedecer à política da infraestrutura; o plugin não consegue invalidar uma resposta que nem chega ao PHP. Depois do aceite, definir `IOTNEST_HTML_CACHE_VERIFIED=true`; a resposta anônima permite no máximo `s-maxage=10`, sem stale-while-revalidate ou stale-if-error. Admin, preview, senha e POST permanecem privados. Endpoints de contato/token/saúde usam `no-store`. Assets com hash admitem cache longo.

O agendador deve rodar independentemente de visitas, sem sobreposição, com fuso e monitoramento documentados. Só definir `DISABLE_WP_CRON=true` após provar o executor externo. Exemplo Linux a adaptar: timer do sistema executando `flock -n /run/iotnest-cron.lock /usr/bin/php /CAMINHO/WORDPRESS/wp-cron.php`. Cron de um minuto não garante publicação em até dez segundos do horário previsto; a spec mede dez segundos desde a confirmação da transição efetiva. [Contrato do WordPress](https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/).

## Topologia de releases

Criar um diretório de releases **fora** da raiz pública WordPress, com marcador `.iotnest-releases` contendo `iotnest-release-root-v1`. O usuário de deploy só escreve nesse diretório e nos links próprios previamente provisionados. Nunca usar rsync com delete sobre a raiz WordPress ou `wp-content`.

```
RELEASE_ROOT/
  .iotnest-releases
  releases/COMMIT/              # artefato completo, imutável
  current -> releases/COMMIT    # uma troca atômica para tema e plugin
  asset-pool/themes/iotnest/    # hashes ainda usados por HTML/cache/abas antigas
  asset-pool/plugins/iotnest-core/
  hooks/after-switch            # executável da infraestrutura
  incoming/                    # ZIP/helper/config fora da raiz pública
  deploy-log.jsonl
WORDPRESS_ROOT/wp-content/themes/iotnest
  -> RELEASE_ROOT/current/wp-content/themes/iotnest
WORDPRESS_ROOT/wp-content/plugins/iotnest-core
  -> RELEASE_ROOT/current/wp-content/plugins/iotnest-core
```

O onboarding dos dois links é manual e ocorre em janela controlada, preservando qualquer diretório existente. O helper recusa substituir diretórios reais. Tema/plugin sempre atravessam o mesmo `current`; não existe intervalo com um componente copiado e outro antigo. Só releases de schema 1 são compatíveis neste contrato. Uma futura mudança incompatível exige migração versionada e novo plano de rollback.

Configurar o servidor para servir URLs `/wp-content/themes/iotnest/assets/dist/NOME.HASH.css|js` e o equivalente do plugin pelo pool persistente correspondente. Expor **apenas** arquivos CSS/JS com nomes válidos, nunca PHP, manifestos privados ou todo o release root. A retenção dos hashes deve cobrir a duração máxima do cache de assets e das páginas/abas ainda suportadas; não limpar automaticamente no deploy. O smoke check valida também um asset do release anterior, detectando configuração incompleta desse pool.

O hook `hooks/after-switch` recebe o commit como argumento, invalida o OPcache/realpath cache de **todos** os workers FPM e faz purge de HTML em todas as camadas. Deve retornar zero só após concluir. Um `opcache_reset()` de CLI não limpa necessariamente o cache do FPM. A infraestrutura define o mecanismo e suas permissões; o código recusa promoção em PRD sem esse hook.

## CI, promoção e rollback

`ci.yml` instala lockfiles, faz lint/build/testes e instala o ZIP em WordPress descartável. Comparações com o front local só rodam quando a referência está disponível; CI remoto não deve alegar T24 executado quando não tem esse material. As evidências locais versionadas registram a referência efetivamente comparada. Produção recebe o mesmo ZIP aprovado, sem novo build.

Configurar o ambiente protegido `production`, permissões e revisores conforme os recursos do plano GitHub. Deploy/rollback aceitam somente execução manual na main, usam o mesmo grupo de concorrência e não cancelam operação em andamento. [Proteções e disponibilidade](https://docs.github.com/en/actions/how-tos/deploy/configure-and-manage-deployments/review-deployments), [sintaxe de concorrência](https://docs.github.com/en/actions/reference/workflows-and-actions/workflow-syntax#concurrency).

Variáveis: `DEPLOY_HOST`, `DEPLOY_USER`, `DEPLOY_PORT`, `RELEASE_ROOT`, `WORDPRESS_ROOT`, `SITE_URL` HTTPS. Secrets: `DEPLOY_SSH_KEY` dedicada e `DEPLOY_KNOWN_HOSTS` conferido por canal confiável. Não usar `ssh-keyscan` como aceitação automática de identidade no deploy. Paths operacionais usam caracteres ASCII sem espaços. Runner deve alcançar o servidor; se não puder, preparar acesso controlado ou runner separado com permissões limitadas.

Promoção: informar commit completo, ID do CI aprovado da main e SHA-256 do ZIP revisado. O script confere origem/conclusão/commit do workflow, ancestralidade na main, checksum externo, manifesto interno e cada arquivo. Transfere por rsync/SSH para incoming, prepara release e troca o link. Verifica commit realmente carregado pelo PHP em tema/plugin, runtime/schema, home/blog/cases/404 e checksums dos assets atuais/anteriores. Falha reverte para o release anterior e repete o hook/saúde; a primeira instalação sem anterior remove o link incompleto e exige correção de onboarding.

Rollback: informar commit anterior já presente e compatível. Não importa dados, não restaura banco e não toca uploads. Revalidar uma edição editorial recente após o retorno. Retenção mínima de releases e backup é decisão operacional; conservar pelo menos o release atual e o anterior validado. Não excluir releases ou hashes ainda referenciados.

Comandos do helper, com JSON de paths preparado fora do Git:

```
php ops/release.php stage CONFIG.json PACOTE.zip SHA256
php ops/release.php activate CONFIG.json COMMIT
php ops/release.php rollback CONFIG.json COMMIT_ANTERIOR
```

## Aceites externos antes da publicação

Registrar domínio/caminhos, plugin/provedor/remetente/destinatário SMTP, cache/plugin/proxy/CDN, CMP, chave SSH do servidor, proteção de branches/ambiente e executor do cron. Depois de autorização específica para PRD, testar recebimento real com e sem JS, falha/timeout, domínio/indexação, saúde FPM e rollout/rollback. Para T21, aquecer as URLs normais, gravar timestamps de confirmação da edição e repetir requisições anônimas sem cache-buster até refletir edição, primeiro publicado, retirada do último artigo/case e identidade global. Limite de dez segundos só recebe aceite com essas medições. Nenhuma dessas validações pode ser inferida do QA local.
