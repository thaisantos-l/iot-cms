# Inventário da referência estática

Leitura dos arquivos locais em 17/11/2026. Este inventário orienta a migração; não representa uma auditoria de execução ou uma medição de performance.

## Origem e destino

| Origem em `iotnest-site/` | Destino na implementação |
| --- | --- |
| `index.html` | `front-page.php` e 20 seções previstas em template parts, com campos da página inicial. |
| `blog.html` | `home.php`, página de posts e cards com consultas nativas. |
| `artigo-1.html` a `artigo-4.html` | Referência de apresentação para `single.php`; seus registros não serão importados. |
| `cases.html` | `archive-iot_case.php`. |
| `case-autoparts-brasil.html`, `case-embalagens-premium.html`, `case-plasticos-nordeste.html` | Referência para `single-iot_case.php`; seus registros não serão importados. |
| `privacidade.html`, `cookies.html`, `termos.html` | Páginas nativas com template legal, conteúdo inicial para revisão e menus configuráveis. |
| Header/footer repetidos nos HTMLs | `header.php`, `footer.php`, menus e componentes únicos. |
| `css/styles.css` | Sass dividido por base, layout, componentes, seções, páginas e utilidades. |
| `js/main.js` | Módulos ES organizados por comportamento. |
| `assets/vendor/lenis.min.js` | Dependência local opcional, com origem/versão/licença documentadas. |
| Imagens e vídeos editoriais | Biblioteca de mídia, IDs de attachments e proporções reservadas. |
| Ícones e elementos de interface | Assets locais do tema, com identificadores permitidos. |
| `robots.txt` e `vercel.json` | Referência do ambiente antigo; políticas de indexação e cache precisam ser definidas no WordPress/servidor. |

São 13 arquivos HTML, um CSS de aproximadamente 64 KB e um JS de aproximadamente 29 KB. Há 27 imagens WebP e três arquivos de vídeo, estes somando aproximadamente 13 MB. Avaliar codecs, posters, dimensões e carregamento por componente durante a implementação; não carregar todos os vídeos antecipadamente.

## Comportamentos que exigem conversão

| Evidência na referência | Tratamento previsto |
| --- | --- |
| `index.html:547`: seção blog dentro de comentário HTML. | Criar seção real em PHP, escondida inicialmente pela ausência de artigos publicados. |
| Cases e artigos usam registros fixos; cases têm conteúdo provisório. | Reaproveitar o desenho; começar as coleções vazias. |
| `index.html:100`: classe `clients` contém recursos textuais. | Modelar como faixa de recursos, não como lista de clientes/logos. |
| `js/main.js:235`: textos das quatro etapas estão no bundle. | Importar para campos e ler do HTML/JSON gerado pelo CMS. |
| `js/main.js:323`: envio de contato por `mailto:`. | Substituir por handler próprio com `wp_mail()` e SMTP. |
| `js/main.js:164`: acesso direto a `cards[0]`. | Inicialização tolerante a listas vazias e controles proporcionais ao número de itens. |
| `js/main.js:40`: menu assume elementos presentes. | Guardas por componente e funcionamento nas páginas sem certas estruturas. |
| `js/main.js:118`: `lockHeight` altera textos para medir depois da carga. | Estabilizar painéis antes da troca; medir CLS da solução, sem assumir que a atual tem uma pontuação específica. |
| `js/main.js:388`: duplicação de vídeo para transição do hero. | Avaliar custo e preservar fallback por poster, pausa e movimento reduzido. |
| `js/main.js:502`: efeito de retenção/ajuste de rolagem na Dadá. | Preservar comportamento, temporização, viewport e movimento reduzido da referência; configuração posterior por flag. |
| Logos compostas por vários SVGs posicionados. | Preservar aparência exata; unificar apenas com equivalência comprovada, mantendo edição pelo painel. |
| Google Fonts externo. | Preparar Inter local e ajustar fallback com métricas próximas. |
| Links sociais `#` e URLs fixas `.html`. | Omitir destinos incompletos; usar menus, permalinks e URLs do CMS. |
| HTMLs e configuração Vercel bloqueiam indexação. | Separar homologação de produção; validar Yoast, domínio e indexação na entrega. |

As linhas são referências desta leitura e podem mudar durante a edição. O CSS já reserva várias proporções úteis: logo 188/47, solução 722/451, IA 16/10, Dadá e vídeos 16/9, energia 692/565, sobre 487/397 e contato 499/320. Preservar essa intenção nos componentes novos e testar conteúdo variável.

## Conteúdo institucional identificado

A home contém hero, faixa de recursos, solução, hardware, IA, Dadá, apontamento de defeitos, energia, relatórios, MES, calculadora, comparação, cases, como funciona, depoimentos, blog, vídeos, sobre, FAQ e contato. Blog está comentado, mas faz parte do desenho a implementar.

Há quatro etapas em “Como funciona”, cinco módulos MES, três recursos da IA, três entradas de vídeos e 21 perguntas de FAQ. A estrutura do painel deve aceitar outras quantidades sem depender desses números. A faixa de recursos também deve aceitar inclusão e remoção de itens.

O valor inicial do contador vem do HTML/conteúdo efetivo, não de comentário divergente no JavaScript. Depoimentos, métricas e textos comerciais são conteúdo fornecido e devem passar por revisão editorial. A importação não cria comprovação desses dados.

## Situação dos repositórios

O repositório `iot-front` possui alterações locais em `cases.html`, `cookies.html`, `index.html`, `privacidade.html` e `termos.html`. Esses arquivos atuais compõem a referência; não restaurar ou descartar suas alterações durante a migração.

No CMS, não foi encontrado tema próprio, plugin próprio ou build do projeto. Os diretórios de plugins contêm Akismet e Hello Dolly; Yoast e SMTP ainda são dependências a preparar. O valor declarado em `wp-includes/version.php` é WordPress `7.1.4`; a versão do PHP efetivamente usada pelo servidor e as versões de produção precisam ser verificadas na etapa 01. Não confundir requisitos mínimos declarados pelo core com a versão do runtime local.

Esta entrega não consultou credenciais, não alterou banco, não ativou tema ou plugin e não modificou o front. Acrescenta documentos em `cms/docs/specs/tema-iotnest` e a permissão para versionar `docs` no `.gitignore`.
