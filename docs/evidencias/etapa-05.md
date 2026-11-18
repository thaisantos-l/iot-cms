# Etapa 05 — Publicações, rotas e páginas internas

Em validação local; sem merge ou aceite final. Branch `feat/05-blog-cases-paginas`, criada da main `a9474d2`.

## Implementação

`8b2880b`: consultas públicas reutilizadas no request, política equivalente nas listas/contagens/menus, setup explícito de rotas e conteúdo institucional das páginas de arquivo. `d5eacb6`: templates de blog, cases, individuais, páginas, busca, 404, cards, paginação, relacionados, breadcrumbs e documento legal. `a82933d`: testes reais de estado, HTTP e T24.

Consultas usam `publish`, ausência de senha, data descendente e ID de desempate. O registro de consultas acompanha a geração nativa de cache de posts/termos; publicar ou proteger o último item invalida sua disponibilidade dentro do próprio request. Nenhuma query por card refaz a coleção. Preview/editor não recebem o filtro de listagem pública.

Cards sem imagem mantêm a área neutra da referência; individuais sem arquivo omitem a capa. Tempo de leitura é calculado do corpo (220 palavras/minuto). Documentos legais mantêm a marcação, geram sumário em PHP e IDs únicos; links internos usam destinos públicos reais. Configurações de arquivos/CTAs são editáveis; a importação só inicializa dados novos.

Mapa de URL, conflitos e configuração: [URLs legadas](../urls-legadas.md).

## QA e limites

Fixtures de artigos/cases existem somente na instalação descartável protegida por guardas de ambiente. Não são seed, publicação de entrega ou dependência do runtime. Para T24, os HTMLs estáticos são lidos sem alterações e usados só para popular essas fixtures.

Comparação de páginas internas nas cinco larguras: mesma Inter e mesmo estado reduzido. Normalizações estritamente previstas na spec: tempo de leitura calculado, breadcrumb do artigo com título nativo e remoção da capa placeholder sem arquivo. Comentários de orientação de migração nos exemplos não são conteúdo editorial da fixture. Capturas isolam apenas `main` igualmente dos dois lados; a navegação tem comparação própria.

PHP 8.2.4/WordPress 7.1.4. O roteador do servidor descartável trata o alias `/var`/`/private/var` do macOS, arquivos estáticos e índices dos diretórios nativos. Rodadas anteriores com CSS não carregado ou redirecionamento de `/wp-admin/` foram descartadas como evidência visual e de administração.

SEO com Yoast e cache/agendador reais pertencem à etapa 08. O teste local de `wp_publish_post` comprova a transição/visibilidade, sem alegar precisão do agendador de PRD. Sem push ou deploy.
