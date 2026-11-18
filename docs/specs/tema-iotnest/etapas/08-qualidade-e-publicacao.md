# Etapa 08 — SEO, cache, qualidade e publicação

Situação: em implementação e validação local. Aceites reais de PRD não estão autorizados nesta execução. Depende da etapa 07. Requisitos: R01–R14, com foco em R06, R11, R12 e R14. Branch sugerida: `feat/08-qualidade-publicacao`.

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
