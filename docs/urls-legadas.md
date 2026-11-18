# Rotas e URLs legadas

O setup explícito da importação configura `/blog/%postname%/` somente quando a estrutura está vazia. Uma estrutura editorial existente permanece intacta. `/blog/` é a página de posts nativa; `/cases/` é o arquivo de `iot_case`, com individuais em `/cases/{slug}/`. Uma página existente com slug `cases` bloqueia a configuração e exige resolver o conflito no painel; o setup não renomeia conteúdo.

| URL antiga | Destino equivalente | Condição |
| --- | --- | --- |
| `index.html` | Home real | Home pública sem senha. |
| `blog.html` | Página de posts | Página configurada, publicada e sem senha; arquivo vazio permanece acessível. |
| `cases.html` | Arquivo do CPT | Tipo registrado; arquivo vazio permanece acessível. |
| `privacidade.html` | Página `/privacidade/` | Página existente, publicada e sem senha. |
| `cookies.html` | Página `/cookies/` | Página existente, publicada e sem senha. |
| `termos.html` | Página `/termos/` | Página existente, publicada e sem senha. |
| `artigo-1.html`…`artigo-4.html` e três cases estáticos | Nenhum | HTTP 404; os exemplos não são importados nem redirecionados para conteúdo inexistente. |

O 301 ocorre apenas para o caminho legado exato, dentro da instalação. Não há redirecionamento genérico para a home ou tentativa de adivinhar conteúdo equivalente. Links `.html` no corpo dos documentos legais são resolvidos em PHP para páginas públicas; se a página ainda estiver em revisão, a legenda permanece sem link. A alteração não regrava o corpo editorial.

O servidor deve encaminhar caminhos inexistentes ao `index.php` do WordPress e servir arquivos/diretórios nativos diretamente. `flush_rewrite_rules(false)` ocorre apenas na configuração explícita, nunca em toda requisição; regras de Apache/Nginx pertencem ao contrato de infraestrutura. Alterações posteriores de permalink devem ser revisadas com seu mapa próprio antes da promoção.

Implementação: `iotnest-core/includes/queries.php`. APIs: [WP_Query](https://developer.wordpress.org/reference/classes/wp_query/), [pre_get_posts](https://developer.wordpress.org/reference/hooks/pre_get_posts/), [processador de HTML](https://developer.wordpress.org/reference/classes/wp_html_tag_processor/).
