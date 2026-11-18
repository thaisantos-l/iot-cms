# Etapa 05 — Publicações, rotas e páginas internas

Implementada e validada localmente em 18/11/2026, no recorte da etapa. Branch `feat/05-blog-cases-paginas`, criada da main `a9474d2`.

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

## Aceite local

Lints PHP/WPCS, JavaScript/Sass e cinco testes Node aprovados. Cenários PHP reais aprovados: ciclo de estados para posts/cases, filtro de categoria, reutilização de consultas, paginação, senha, busca pública do administrador, transição futura, permissões de setup, conflito de slugs, sumário e IDs.

Rodada integrada no artefato `a82933d`: 49/51 testes Chromium aprovados; somente duas falhas do roteador de QA no admin. Após a correção, sete testes focados no artefato `4917f7768fb4bdb58372d741c771c935022f9043` aprovados, incluindo os dois fluxos administrativos e os cinco diffs legais, com todos os cenários PHP repetidos e sem diagnósticos PHP. A mudança final de runtime só preserva aspas literais no template legal; não altera as páginas previamente aprovadas. A rodada focada inclui o teste adicional de categoria.

T24: 25 comparações de páginas internas, geometria e pixels coincidentes (delta máximo tolerado de 2 por canal). Vinte comparações de arquivos/individuais e cinco de documento legal. Resultados e capturas selecionadas em `etapa-05/`; a tabela de normalizações está em cada comparação. Os 80 diffs da home e 15 de navegação também passaram na rodada integrada.

`640c52c` conserva a pontuação legal, corrige o roteador e completa os testes; `4917f77` registra rotas/evidências. Sem pendência local do recorte 05. Yoast, precisão do agendador e cache real continuam nos aceites posteriores.
