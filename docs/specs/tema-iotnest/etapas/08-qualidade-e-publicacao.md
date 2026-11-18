# Etapa 08 — SEO, cache, qualidade e publicação

Situação: implementação e QA local concluídos; aceite integral pendente. SMTP/cache/agendador/infraestrutura de PRD não foram exercitados, e Safari nativo não estava disponível para automação. Não houve autorização de deploy. Depende da etapa 07. Requisitos: R01–R14, com foco em R06, R11, R12 e R14. Branch sugerida: `feat/08-qualidade-publicacao`.

## Entregáveis

- Integrar Yoast, análise dos campos relevantes, title/canonical/Open Graph/schema, sitemap e breadcrumbs sem duplicação; validar comportamento com Yoast desativado.
- Confirmar domínio, permalinks, redirects e indexação de produção. Homologação permanece protegida; não copiar bloqueios globais da Vercel.
- Integrar purge ao cache real após a gravação de dados e transições de status. Cobrir home, arquivos, menus, relacionados, cores/logos e SEO; excluir preview/admin do cache público.
- Validar agendador independente de visitas e documentar precisão da publicação futura. Definir limites operacionais e procedimento para falha de purge.
- Executar a matriz integrada T01–T24 e o protocolo visual/acessível/CLS, com evidências de fidelidade ao front e conteúdo de teste removido da entrega inicial.
- Finalizar workflows, pacote de tema/plugin, rollout compatível e rollback; entregar manual de edição e operação, incluindo limites de campos, SMTP, cache e importação.

## Aceite e verificações

T21: com caches previamente preenchidos, editar/publicar/despublicar e medir novas requisições anônimas até o conteúdo correto, sem disparar Actions, respeitando até 10 segundos no ambiente acordado. Registrar timestamps e camadas de cache. Repetir para retirada do último artigo/case e alteração global.

T22/T23: promover o mesmo artefato verificado, conferir páginas e assets, reverter para versão compatível preservando uma edição recente no banco. Validar envio real de formulário, indexação, permissões e ausência de exemplos publicados. Não declarar CLS de campo comprovado antes de existirem dados; apresentar medições de laboratório separadamente.

## Commits e merge

1. `feat: integrar SEO e invalidacao de cache`
2. `ci: preparar entrega e rollback do tema e plugin`
3. `docs: registrar aceite e operacao do site`

Merge com verificações concluídas e limitações resolvidas ou explicitamente acordadas. Produção recebe somente o release completo após esta etapa. Infra segue o [contrato de entrega](../05-infraestrutura.md), preservando banco/uploads e compatibilidade entre tema, plugin e schema.

## Execução local em 18/11/2026

Branch criada da main `eebfe5f`. Yoast real ativo/inativo e análise editorial, paleta completa, invalidação depois das gravações, política sem cache não certificado, saúde de release, promoção atômica e rollback implementados. Principais arquivos: `includes/{seo,cache,health}.php` no Core, `inc/{breadcrumbs,customizer}.php` no tema, `ops/`, `.github/workflows/` e manuais em `docs/`.

Commits funcionais `511d6a1`, `3ca1447`, `81c899c`, `372d076`, `a781efb` e `f59d71d`; testes/manuais em commits separados. Verificações: 201 asserções PHP, 81/81 testes Chromium integrados, 36 amostras CLS iguais a zero, T24 nas cinco larguras e movimento real, testes focados em Chrome/Firefox/WebKit, zoom nativo a 200%, build em checkout novo e ZIP reproduzido byte a byte. Actionlint validou CI/deploy/rollback; nenhuma Action remota executada. [Evidências, artefatos e matriz T01–T24](../../../evidencias/etapa-08.md).

A integração local entrega código, pacote e documentação revisáveis; **não encerra o aceite integral da etapa**. Pendências reais: transporte SMTP/recebimento, medição de até dez segundos com cache preenchido, executor de cron, domínio/indexação, FPM/OPcache/SSH e Safari nativo/mobile. O formulário continua desligado até o transporte ser validado. Políticas legais importadas continuam em rascunho para revisão. Essas pendências não autorizam publicação nesta execução.
