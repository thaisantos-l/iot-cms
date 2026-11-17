# Arquitetura e componentes do tema iotnest

## Estrutura de código

Estrutura proposta para a implementação, não arquivos já criados:

```text
cms/
  docs/specs/tema-iotnest/
  .github/workflows/
  package.json
  package-lock.json
  composer.json
  composer.lock
  scripts/
    build.mjs
    package.mjs
  tests/
    php/
    js/
    e2e/
  wp-content/
    plugins/iotnest-core/
      iotnest-core.php
      includes/
        post-types.php
        meta-registry.php
        meta-boxes.php
        settings.php
        contact.php
        integrations.php
        importer.php
        cache.php
      resources/seed/
      assets/src/js/
      assets/src/scss/
      assets/dist/
    themes/iotnest/
      style.css
      functions.php
      header.php
      footer.php
      front-page.php
      home.php
      single.php
      archive-iot_case.php
      single-iot_case.php
      archive.php
      page.php
      search.php
      404.php
      index.php
      page-templates/legal.php
      inc/
        setup.php
        assets.php
        customizer.php
        queries.php
        view-models.php
        visibility.php
        navigation.php
        template-tags.php
      template-parts/
        layout/
        components/
        sections/
        content/
      assets/
        src/scss/
        src/js/
        dist/
        icons/
        fonts/
      languages/
```

`style.css` mantém a identificação do tema; o CSS de produção é gerado a partir de Sass. `functions.php` inicializa módulos, sem concentrar todos os campos e HTML. Código PHP do projeto usa prefixo ou namespace `Iotnest` para evitar colisões.

O plugin registra dados e regras operacionais; o tema consulta, prepara os dados e renderiza. Não colocar caminhos de templates no banco nem executar PHP configurado pelo administrador. O site não pode produzir erro fatal se Yoast ou o plugin SMTP estiverem desativados. A ausência do plugin próprio gera aviso administrativo e uma apresentação mínima segura, sem acesso a funções inexistentes.

O CMS atualmente ignora arquivos na raiz exceto `wp-content` e as exceções explícitas. A etapa 01 deve liberar os arquivos de build, testes e workflows adotados e manter core, configurações, banco, uploads, caches e segredos fora do Git. Esta etapa de especificação libera somente `docs`.

## Templates e apresentação

`header.php` e `footer.php` são únicos e chamados pelos templates. Manter `wp_head()`, `wp_body_open()`, `wp_footer()`, `body_class()`, idioma e charset nativos. Registrar suporte para título, imagem destacada, logo e HTML5. Usar funções do WordPress para URLs e caminhos de arquivos.

Cada seção fica em `template-parts/sections/{section}.php`. Componentes compartilhados incluem título de seção, CTA, imagem, vídeo, card de artigo, card de case, paginação, breadcrumbs, estado vazio, item de FAQ, grupo de sinais e mensagens do formulário. Os componentes recebem dados normalizados por argumentos; não fazem consultas independentes para cada item. [Template parts com argumentos](https://developer.wordpress.org/reference/functions/get_template_part/).

Uma função de disponibilidade gera o registro de seções visíveis antes da renderização. Home, menus, CTAs e seleção de assets consultam esse registro. O template recebe apenas itens válidos. Não gerar a seção inteira e removê-la com JavaScript após a carga.

O heading principal vem do título editorial do hero na home e do título nativo nas páginas internas. Se o hero estiver oculto, manter um H1 acessível da página sem recriar uma seção editorial vazia. Headings internos seguem hierarquia coerente. IDs permanecem estáveis ao ordenar seções; componentes repetidos recebem IDs únicos.

## Sass e personalização visual

Usar Dart Sass com módulos `@use` e `@forward`, sem manter o arquivo monolítico como fonte paralela. Estrutura sugerida:

```text
assets/src/scss/
  abstracts/      # tokens, funções e mixins sem saída CSS duplicada
  base/           # reset, tipografia, foco e elementos
  layout/         # containers, grids, header e footer
  components/     # botões, campos, cards, mídia, tabs, modal, FAQ
  sections/       # hero, hardware, IA, MES, calculadora, contato etc.
  pages/          # home, arquivos, artigo, case, páginas legais
  utilities/     # acessibilidade, alinhamento e helpers pequenos
  app.scss
  editor.scss
```

Preservar tokens, valores, proporções e breakpoints da referência, incluindo o resultado da cascata existente. A divisão em Sass não autoriza mudar especificidade, espaçamentos ou aparência. Evitar seletores profundamente aninhados, dependência de posição no DOM e `!important` para compensar conflitos novos. Media queries ficam junto do componente que alteram; breakpoints compartilhados usam os mesmos valores da referência. Validar equivalência visual após a reorganização.

Cores editáveis são CSS custom properties aplicadas no servidor antes do primeiro paint. Substituir também as cores fixas presentes em gradientes, transparências e variações de componentes, sem permitir que uma parte fique presa à paleta antiga. Cores de marca de terceiros podem ser preservadas quando pertencerem à identidade do serviço. A alteração administrativa não dispara Sass nem deploy.

Fontes: priorizar Inter hospedada localmente em WOFF2, com licença preservada e somente pesos necessários. Ajustar métricas do fallback e medir a troca; `font-display: swap` sozinho não elimina CLS. A primeira versão mantém a família do desenho, sem carregar fontes arbitrárias do painel.

[Módulos Sass](https://sass-lang.com/documentation/at-rules/use/).

## JavaScript modular

Escrever módulos ES e compilar com esbuild em bundles compatíveis com o enqueue do WordPress. Entradas: aplicação comum, home e administração; módulos adicionais somente quando houver justificativa de carregamento. O plugin mantém sua UI administrativa em fontes próprias, compiladas pelo mesmo build do repositório.

| Módulo | Responsabilidade |
| --- | --- |
| `navigation` | Menu mobile, submenu, foco, Escape e estado acessível. |
| `anchor-scroll` | Navegação por âncoras válidas e offset do header. |
| `carousel` | Listas roláveis e controles que respeitam zero, um e vários itens. |
| `tabs` e `steps` | IA, módulos MES e como funciona, alimentados por dados do CMS. |
| `media` e `video-dialog` | Vídeo próprio, poster, pausa e incorporação externa sob demanda. |
| `calculator` | Fórmulas, validação e resultado da calculadora de OEE. |
| `motion` e `counter` | Efeitos opcionais, contadores e observação de visibilidade. |
| `contact-form` | Melhoria progressiva do formulário e estados de envio. |
| `admin-repeaters` e `admin-media` | Edição de grupos, ordem e seleção de mídia no painel. |

Cada inicializador recebe um elemento raiz, verifica suas dependências e evita inicialização duplicada. Remover listeners, timers e observers quando aplicável. Não assumir que o menu, um botão, o primeiro card ou qualquer seção existem. Arrays vazios não podem produzir divisão por zero ou acesso a `items[0]` inexistente.

Conteúdo editorial vem do HTML renderizado ou de JSON seguro gerado com `wp_json_encode()`. Não manter os textos das etapas, caminhos de imagens ou descrições dentro de bundles. Preferir `textContent` para texto; HTML editorial deve chegar sanitizado. Interações não podem recriar conteúdo antigo sobre os valores do painel.

Respeitar `prefers-reduced-motion`, foco, hover, aba oculta e presença em viewport. Autoplay só funciona com mais de um item e deve poder ser pausado. Preservar os estados iniciais, intervalos, easing e comportamento de Lenis, autoplay, contadores e rolagem da Dadá conforme a referência, inclusive suas condições de viewport e movimento reduzido. Flags permitem alteração posterior pelo administrador; não desligar esses efeitos por padrão durante a conversão. Manter fallback utilizável quando uma dependência estiver ausente, sem alterar o comportamento normal. Melhorias de acessibilidade não autorizam redesenhar controles ou remover interações; conflitos que exijam mudança perceptível devem ser apresentados antes de executar essa mudança.

Calculadora: preservar `total = valor_hora × horas_dia × dias_mes` e `perda = total × (1 − OEE / 100)`. OEE atual e meta entre 0 e 100; meta não inferior ao atual; horas até 24; dias inteiros entre 1 e 31; taxa positiva. Testar zero de perda para OEE 100, meta igual ao atual, decimais, entradas inválidas e formatação pt-BR. Não permitir editar fórmulas como JavaScript no painel.

Carregar scripts pelo WordPress, com estratégia adequada à ordem de dependências. [Enqueue de scripts](https://developer.wordpress.org/reference/functions/wp_enqueue_script/).

## Build e artefato de deploy

O repositório terá lockfiles e comandos documentados para instalação, build, lint e testes. Proposta: `npm ci`, `npm run dev`, `npm run build`, `npm run lint`, `npm run test:js`, `npm run test:e2e`; verificações PHP por Composer. Registrar versões de Node/PHP e compatibilidade com a instalação local e com produção na etapa 01.

O build compila Sass, JS de tema e plugin, gera arquivos identificados por hash e um manifesto. O loader PHP resolve arquivos pelo manifesto. O pacote final contém tema, plugin próprio e assets compilados. Não publicar um tema que só tenha fontes Sass/JS e dependa de Node no servidor.

`assets/dist` pode ficar fora do Git, desde que CI gere e valide o artefato. O rsync usado por infra deve copiar o pacote construído, não apenas o checkout cru. Não embutir domínios e secrets nos bundles. Fontes, ícones e dependências locais necessárias acompanham o pacote, com licenças.

## Formulário próprio e SMTP

Campos públicos: nome e e-mail obrigatórios; telefone, empresa e mensagem conforme o layout atual. Configurar destinatário, título, mensagens e habilitação no painel. Sem destinatário válido, não exibir um formulário que pareça funcionar; apresentar os canais válidos e um aviso somente para o administrador.

Criar handler no plugin próprio para visitantes e usuários autenticados. O envio HTML deve funcionar sem JavaScript; a melhoria por `fetch` usa a mesma validação e o mesmo serviço. O endereço de destino nunca vem da submissão do visitante.

Validar tipos, comprimentos, e-mail, nonce, honeypot e limite de frequência no servidor. Nonce não é mecanismo anti-spam suficiente. Bloquear injeção de cabeçalhos e HTML não permitido. Usar remetente autenticado definido pelo SMTP e e-mail do visitante somente em `Reply-To` após validação. Não expor credenciais no tema, HTML, JS ou logs.

Estados: pronto, enviando, enviado ao serviço, erro de validação, limite de frequência, erro de envio e timeout. Reservar área para mensagens, manter o tamanho do botão, informar por `aria-live`, devolver foco quando necessário e preservar campos em falha. Evitar envios duplicados por clique repetido e não repetir automaticamente uma operação cujo resultado ficou incerto.

O retorno positivo de `wp_mail()` indica processamento pelo mecanismo de envio, não confirmação da caixa de entrada. O aceite exige verificar também o recebimento real com SMTP em homologação. Falhas devem ter diagnóstico administrativo sem registrar o corpo da mensagem ou dados pessoais completos. [Contrato de wp_mail](https://developer.wordpress.org/reference/functions/wp_mail/).

## Flags e scripts externos

Separar flags de interface das integrações de terceiros. Menu e formulário essenciais não dependem de flags comerciais.

| Recurso | Configuração e comportamento |
| --- | --- |
| Movimento, Lenis, autoplay e contadores | Flags próprias; respeitam movimento reduzido e mantêm o conteúdo utilizável quando desligadas. |
| YouTube | Flag de embed e IDs validados. Thumbnail preferencialmente local; player somente após interação e consentimento quando exigido pela configuração. Link direto como alternativa. |
| Analytics e gerenciador de tags | Adaptadores previstos para IDs de fornecedores aprovados, por exemplo GA4/GTM. Desligados na configuração inicial. Evitar duplicação de tags. |
| Outros fornecedores | Novo adaptador registrado em código, com contexto, dependências e campos permitidos. |

O painel administra habilitação, identificadores e opções aceitas; não oferece caixa irrestrita de `<script>`, iframe ou código PHP. Flags são avaliadas no servidor. Recurso desligado não emite tags, preconnects, pixels ou requisições ao fornecedor. Não carregar o mesmo serviço pelo tema e por outro plugin.

Uma ferramenta de consentimento, quando necessária, controla a autorização do usuário. A flag administrativa não equivale a consentimento. Sem o mecanismo acordado, integrações dependentes dele permanecem desligadas. Testar consentimento negado, concedido e revogado. Se houver banner/widget, seu posicionamento deve ser compatível com os requisitos de CLS e navegação.

## Cache e atualização editorial

Preservar o requisito de atualização em até 10 segundos para novas requisições após publicação concluída, com os serviços operacionais e sob a carga acordada. A aba já aberta não precisa atualizar automaticamente nesta versão.

Definir a integração de cache depois de identificar o plugin/proxy/CDN real. Cobrir gravação de campos, mudanças de status, menus, cores, logos, SEO, opções globais e remoção de mídia. Invalidar também home, arquivos, relacionados e menus dependentes. Eventos devem ocorrer depois dos dados efetivamente gravados e funcionar em todas as instâncias.

Na ausência de integração de purge comprovada, não habilitar cache de página com validade incompatível com o prazo. Não usar cache duradouro de conteúdo no navegador. Assets com hash têm cache longo; imagens editoriais substituídas recebem uma URL nova/versionada.

Publicações agendadas dependem do agendador do WordPress. Infra deve executar tarefas de forma independente de acessos ao site. Se o prazo de 10 segundos também for exigido a partir do horário agendado, o intervalo do executor precisa ser dimensionado e medido para isso; um cron de um minuto não atende a esse caso.
