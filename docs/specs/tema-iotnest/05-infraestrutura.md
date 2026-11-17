# Contrato com infraestrutura — WordPress convencional

Este documento descreve a configuração a executar nas etapas de implementação. Nenhum workflow ou acesso a servidor foi configurado nesta entrega documental.

## Arquitetura para o time

O site será renderizado pelo próprio WordPress/PHP. O repositório de entrega é `iot-cms`, com tema `iotnest` e plugin próprio `iotnest-core`. O repositório `iot-front` serve como referência da migração. Não há aplicação frontend separada para hospedar nem build acionado quando alguém edita conteúdo.

| Evento | Fluxo |
| --- | --- |
| Alteração de PHP, Sass, JS ou estrutura de campos | Branch → PR → checks/build → merge → artefato → deploy. |
| Edição/publicação pelo painel | WordPress grava dados → invalida caches afetados → nova requisição recebe conteúdo atualizado. |
| Upload de imagem | Biblioteca de mídia → arquivos persistentes + metadados → atualização editorial e purge, sem Git. |
| Publicação agendada | Executor do agendador → transição para `publish` → purge → novas requisições. |

Node compila assets no desenvolvimento/CI. Produção precisa do runtime WordPress/PHP, banco, servidor web, armazenamento persistente e serviços operacionais definidos pelo time.

## Workflows propostos

Os nomes são propostas para arquivos em `.github/workflows/`. Desenvolver os scripts de build/checks junto com o código; infra configura acesso, ambientes e execução do deploy.

| Workflow | Gatilho proposto | Responsabilidade |
| --- | --- | --- |
| `ci.yml` | PR e push na `main` | Instalar por lockfiles, lint PHP/JS/Sass, testes pertinentes, compilar e verificar pacote. PR não recebe credenciais de produção. |
| `deploy-staging.yml` | Commit integrado na `main`, após CI aprovado para o mesmo SHA | Disponibilizar artefato identificado pelo commit, transferir por SSH, atualizar homologação e executar smoke checks. |
| `deploy-production.yml` | Release/tag de commit já aprovado, ou execução manual de promoção | Promover o mesmo artefato homologado, verificar dependências/migrações e saúde, registrar versão publicada. |
| `rollback.yml` | Execução manual com versão anterior conhecida | Reaplicar artefato compatível e validar saúde, sem substituir banco ou uploads. Pode ser também um modo explícito do workflow de deploy. |

Durante a conversão, `main` atualiza homologação. Produção começa após o aceite da etapa 08. Depois, o time pode optar por produção automática a cada merge aprovado, mantendo a dependência dos checks e o controle de versões. Uma execução de PR ou código não confiável não deve disparar promoção com secrets.

Configurar ambientes separados, secrets por destino, restrição de branches/tags e histórico de deploy. A disponibilidade de proteções depende do plano e da visibilidade do repositório; confirmar o que a conta suporta. [Ambientes do GitHub Actions](https://docs.github.com/en/actions/concepts/workflows-and-actions/deployment-environments), [disponibilidade de proteções](https://docs.github.com/en/actions/how-tos/deploy/configure-and-manage-deployments/review-deployments).

Serializar deploys por ambiente, sem cancelar uma transferência/publicação já em andamento. CI de commits ultrapassados pode ser cancelado. Também impedir concorrência entre deploy e rollback. [Controle de concorrência](https://docs.github.com/en/actions/concepts/workflows-and-actions/concurrency).

## Artefato e transferência

O artefato inclui somente os arquivos necessários do tema e plugin próprio, assets compilados e manifestos. Ele recebe identificador de versão/commit e checksum. Builds dependem dos lockfiles e não buscam uma versão flutuante diferente na promoção para produção.

Usar rsync sobre SSH, com usuário de deploy restrito aos destinos, chave dedicada e validação da chave do servidor. FTP simples não é a proposta desta arquitetura. Credenciais são configuração do ambiente, nunca parte do Git ou dos bundles.

Transferir primeiro para uma área de release e depois fazer troca coordenada, sem servir arquivos parcialmente copiados. Tema/plugin precisam ser compatíveis durante a troca e no rollback. Se a topologia exigir alteração de dois diretórios, documentar a sequência, compatibilidade e procedimento de retorno. Atualizar/invalidate OPcache conforme o runtime exigir.

Validar home, página interna, arquivo de cases/blog, HTTP 404 e carregamento de assets após a troca. Falha de saúde deve interromper a promoção ou retornar ao último release compatível. Registrar resultado no workflow.

Não executar rsync destrutivo sobre toda a raiz WordPress ou `wp-content`. O deploy não transporta banco, `wp-config.php`, uploads, caches, credenciais, SMTP, `node_modules` ou dependências de desenvolvimento. Core WordPress e plugins de terceiros têm processo próprio de atualização, testado em homologação.

Git preserva histórico de código; **não substitui backup de banco e mídia**. Infra define frequência, retenção e teste de restauração. Rollback de código não implica restaurar um banco antigo.

## Atualização editorial em até 10 segundos

O objetivo é: após a confirmação de uma publicação/edição bem-sucedida, uma **nova requisição pública** deve apresentar o estado correto em no máximo 10 segundos, com os serviços operacionais e sob carga acordada. Isso não exige atualizar automaticamente a aba que o visitante já abriu.

Identificar todas as camadas: cache de objetos/transients, cache de página/plugin, proxy, CDN e navegador. Desenvolvimento integra os eventos do WordPress ao mecanismo de purge definido por infra, depois da gravação completa; infra garante que a invalidação alcance as instâncias e bordas existentes.

Cobrir também dependências: home, arquivo, paginação, relacionados, menus condicionais e metadados SEO. Ao despublicar o último case, por exemplo, invalidar a seção da home e seus links, além do item. Cores/logos/menus/opções precisam de política de purge correspondente.

Sem purge confiável e medido, não habilitar uma política de cache de HTML que possa servir conteúdo anterior por mais de 10 segundos. Considerar também opções de servir conteúdo antigo durante revalidação. Assets com hash podem ter cache longo; HTML, preview e imagens substituídas precisam de tratamento distinto.

Teste de aceite: preencher os caches com a URL pública normal, registrar o instante de confirmação da alteração e consultar a mesma URL como visitante até verificar o novo estado. Não usar parâmetro aleatório que burle o cache. Validar edição, primeiro publicado, retirada do último e identidade global. Registrar timestamps e resultado; a configuração por si só não comprova o prazo.

## Agendador e formulário

Configurar execução de tarefas WordPress independente de visitas. Só desabilitar o disparo por tráfego depois de confirmar o executor do sistema. O WordPress documenta a integração com o [agendador do servidor](https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/).

O prazo de 10 segundos adotado nesta spec começa na publicação efetivamente concluída. Se o negócio exigir também 10 segundos contados do horário previsto de um post futuro, infra deve oferecer e medir um executor compatível; cron de um minuto pode atrasar mais do que isso. Registrar fuso horário e impedir execuções sobrepostas.

Para o formulário, liberar conectividade para o provedor SMTP, configurar credenciais e remetente pelo plugin definido, preparar autenticação do domínio de e-mail e verificar recebimento real em homologação. Preservar configuração e credenciais entre releases. Usar destinatário de teste em homologação.

## Responsabilidades e decisões pendentes

| Assunto | Desenvolvimento | Infraestrutura / operação |
| --- | --- | --- |
| Build e pacote | Scripts, lockfiles, testes, loaders e compatibilidade. | Runner, variáveis, armazenamento/retenção de artefatos e conectividade. |
| Deploy | Contrato de paths, comandos e smoke checks. | SSH, permissões, destinos, promoção, rollback e monitoramento. |
| Conteúdo e cache | Hooks de gravação/status e URLs dependentes. | Motor de cache, purge, TTLs, todas as instâncias e evidência do prazo. |
| Agendamentos | Testar transição e visibilidade. | Executor, intervalo, fuso e acompanhamento de falhas. |
| E-mail | Handler, validação e mensagens. | Plugin/provedor, credenciais, rede, remetente e domínio. |
| Backups/atualizações | Compatibilidade de schema e instruções de migração. | Banco/mídia, restauração, core/plugins de terceiros e patches do ambiente. |

Antes das etapas dependentes, preencher no runbook: domínios e paths de homologação/produção, versões suportadas de PHP/WordPress, plugin SMTP, cache/proxy/CDN existentes, política de promoção e retenção. Definir também o gerenciador de consentimento antes de habilitar fornecedores que dependam dele. Esses dados não impedem escrever a spec ou implementar os contratos independentes.

O time deve confirmar a conectividade do runner com o servidor. Se o servidor não aceitar conexão do runner hospedado pelo GitHub, usar acesso de rede controlado ou runner próprio em ambiente separado e com permissões limitadas, conforme a infraestrutura disponível.
