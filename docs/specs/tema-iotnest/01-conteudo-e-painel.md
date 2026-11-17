# Conteúdo e painel do tema iotnest

## Modelo de dados

| Entidade | Armazenamento | Edição |
| --- | --- | --- |
| Home institucional | Página `page` selecionada como página inicial, com meta registrada por seção. | Meta boxes agrupadas por seção, com status de exibição e ordenação. |
| Artigos | Tipo nativo `post`, categorias e tags nativas. | Título, editor, resumo, imagem destacada, publicação e Yoast. |
| Cases | CPT `iot_case`, arquivo `/cases/` e taxonomia `iot_case_sector`. | Editor, resumo, imagem destacada e meta box do case. |
| Páginas institucionais e legais | Tipo nativo `page`. | Editor de conteúdo; template legal e campos auxiliares quando aplicável. |
| Identidade do tema | Customizer e `theme_mods`. | Logo principal, logo do footer, paleta e opções visuais previstas. |
| Contatos e integrações | Opções registradas do plugin próprio. | Tela de configurações, acessível por capacidade administrativa. |
| Menus | Menus nativos do WordPress. | Localizações principal, footer e institucional/legal. |

Prefixar dados próprios com `iotnest_` e metadados protegidos com `_iotnest_`. Usar uma chave registrada por seção, por exemplo `_iotnest_home_hero`, e chaves separadas para `_iotnest_home_order` e dados específicos do case. Cada seção possui objeto com schema explícito; itens repetidos são arrays de objetos tipados. Versionar o schema e suas migrações no plugin próprio.

Configurações de SMTP e credenciais permanecem no plugin SMTP, não nos campos do tema. Não armazenar submissões do formulário no banco por padrão; se isso for necessário no futuro, definir retenção, acesso e exportação antes de implementar.

## Contratos comuns dos campos

| Tipo | Contrato |
| --- | --- |
| Texto simples | Sem HTML, sanitizado e escapado na saída; orientação de tamanho para preservar o desenho. |
| Texto rico | Apenas marcação editorial permitida, com `wp_kses_post` ou allowlist menor por campo. |
| Título com destaque | Partes textuais ou marcação inline restrita; não permitir classes, scripts ou estilos arbitrários. |
| Imagem | ID de attachment, alt na biblioteca e tamanho de renderização definido pelo componente. |
| Vídeo próprio | Attachment e poster, com validação do tipo de mídia. |
| Vídeo externo | Provedor permitido e identificador/URL validado; não aceitar iframe arbitrário. |
| CTA | Rótulo + destino válido; escolher página, seção conhecida ou URL permitida; alvo externo explícito. |
| Ícone | Identificador em uma biblioteca local de ícones auditados. |
| Cor | Valor hexadecimal validado e associado a uma variável semântica. |
| Repetidor | Itens com ID estável, inclusão, exclusão, ordem e campos tipados; excluir linhas incompletas da renderização. |
| Número | Tipo e intervalo explícitos; zero é válido quando fizer sentido. |

Texto significativo desconsidera espaços, espaços não separáveis, caracteres invisíveis e tags sem conteúdo. `"0"` não deve ser tratado automaticamente como vazio. Limpar um campo remove seu valor editorial; o tema não pode ressuscitar o texto estático por um fallback de renderização. Defaults de conteúdo são aplicados uma única vez na configuração/importação.

Campos ausentes de uma submissão parcial não significam apagar dados. Exclusão intencional deve ser distinguida de autosave, edição rápida, request incompleto ou falha de validação. Valores não reconhecidos pelo schema são rejeitados ou descartados de forma controlada.

## Seções da home

Cada seção tem `enabled`, posição na ordem e os campos abaixo. `title` é o campo principal, salvo quando a tabela indica outro. O título deve ter texto para a seção aparecer; dependências adicionais se somam a essa condição. A ordem inicial segue o front, com a seção blog prevista entre depoimentos e vídeos.

| Chave e âncora | Conteúdo editável | Dependência adicional |
| --- | --- | --- |
| `hero` / `topo` | Título, descrição, CTAs, imagem/poster e vídeo de fundo opcional. | Mídia é opcional; sem vídeo, usar poster; sem mídia, manter composição textual estável. |
| `features` / `recursos` | Rótulo acessível `label`, lista de textos da faixa e animação. | `label` + ao menos um item com texto; não é uma faixa de logos de clientes. |
| `solution` / `solucao` | Eyebrow, título, texto, bullets, imagem, CTA. | Título; sem imagem, layout textual adaptado. |
| `hardware` / `hardware` | Título, descrição, imagens do hardware, subtítulo explicativo e grupos de sinais com ícone, título e itens. | Título; cada grupo exige título e itens; carrossel só com imagens válidas. |
| `ai` / `ia` | Título e lista de recursos com texto, ícone e imagem. | Ao menos um recurso com texto e imagem válida. |
| `assistant` / `dada` | Título, descrição opcional, vídeo e poster, opções de reprodução. | Vídeo ou poster válido; controles somente quando houver vídeo. |
| `defects` / `apontamento` | Título, texto, imagem e CTA opcional. | Título. |
| `energy` / `energia` | Título, texto, imagem e rótulos da composição visual. | Título; a composição decorativa depende da imagem. |
| `reports` / `relatorios` | Título, texto, imagem e listas de recursos. | Título; listas sem itens são omitidas. |
| `mes` / `mess` | Título editorial da seção e módulos com rótulo, título, descrição e imagem de fundo. | Ao menos um módulo válido; primeiro módulo já renderizado no servidor. |
| `calculator` / `calculadora` | Título, introdução, labels, mensagens, observação e CTA. | Título e flag da calculadora; fórmulas permanecem no código. |
| `comparison` / `comparativo` | Título, descrição, nomes das colunas, linhas de recursos, estados e nota. | Ao menos uma linha com rótulo; estados `full`, `partial`, `none`. |
| `cases` / `cases` | Título, descrição, limite de cards, texto de CTA e ordenação prevista. | Ao menos um case público publicado. |
| `how_it_works` / `como-funciona` | Título e etapas com rótulo, título, texto, imagem e modo de encaixe. | Ao menos uma etapa com título e texto; imagem é opcional. |
| `testimonials` / `depoimentos` | Título e depoimentos com citação, nome, cargo, empresa e avatar. | Ao menos uma citação com nome do autor; avatar opcional. |
| `blog` / `blog` | Título, descrição, limite de cards, filtro opcional de categoria e CTA. | Ao menos um artigo público publicado que corresponda ao filtro. |
| `videos` / `videos` | Título e itens com título, descrição, vídeo e thumbnail. | Ao menos um item com título e vídeo válido. |
| `about` / `sobre` | Título, parágrafos, imagem, bullets e indicador numérico com unidade/rótulo. | Título; contador somente com número e rótulo válidos. |
| `faq` / `faq` | Título, perguntas, respostas e CTA final. | Ao menos um par pergunta/resposta completo. |
| `contact` / `contato` | Título, descrição, imagem, canais de contato, título e mensagens do formulário. | Título; formulário exige configuração de destinatário válida. Canais válidos podem aparecer sem formulário. |

Componentes filhos também são condicionais. Uma imagem faltante não gera `<img src="">`; um CTA incompleto não gera link; um subtítulo vazio não cria um elemento com margem. A tabela de comparação aceita uma lista controlada de colunas e células, com associação por ID em vez de posição, para não trocar valores ao reordenar.

O painel deve informar o motivo de uma seção ficar oculta: título vazio, flag desativada, lista incompleta ou ausência de publicações. Permitir limpar o campo sem impedir salvar a página: esconder uma seção é uma operação válida. Mostrar recomendações de proporção e comprimento sem descartar silenciosamente textos longos.

## Blog e cases

As consultas de listagem usam explicitamente conteúdo público com `post_status=publish` e `has_password=false`. Conteúdos `draft`, `pending`, `future`, `private`, `trash` e revisões não entram. A mesma política vale para cards da home, relacionados, menus condicionais e busca pública do projeto.

| Estado da coleção | Comportamento na home |
| --- | --- |
| Zero publicados, inclusive quando existirem rascunhos/agendados | Não renderizar a seção, seus controles nem links condicionais. |
| Um publicado e configuração da seção válida | Mostrar um card, sem duplicações, setas, dots ou autoplay. |
| Vários publicados | Mostrar até o limite configurado; controles apenas se houver overflow real, sem mudar a altura da seção. |
| Último publicado volta para rascunho, fica privado ou é removido | A seção e seus links condicionais desaparecem após a invalidação de cache. |
| Agendado chega ao horário mas ainda tem status `future` | Continua ausente até o WordPress concluir a publicação. |

Ordenação inicial: data de publicação decrescente, com ID como desempate. Contagem, filtros e seleção dos cards usam critérios equivalentes. Reutilizar os resultados no request, sem executar consultas repetidas em cada card e menu. Usar paginação nas listagens completas e restaurar o contexto global após consultas secundárias.

Artigo: título, conteúdo, resumo e imagem nativos; categoria opcional; tempo de leitura calculado a partir do conteúdo, não fixo. Case: título, resumo, imagem e editor nativos; campos adicionais `company`, `solution`, `highlights[]` e CTA, além da taxonomia de segmento. O editor organiza desafio, solução e resultados sem duplicar o mesmo conteúdo em dois lugares. Campos vazios do resumo lateral são omitidos.

Artigos e cases publicados não dependem de imagem para existir. Cards podem usar uma área visual neutra e estável quando não houver imagem; a página individual omite a capa sem arquivo. Relacionados excluem o item atual e desaparecem quando a consulta retornar zero.

As rotas de arquivo `/blog/` e `/cases/` continuam acessíveis com estado vazio útil, mesmo quando a seção da home estiver oculta. A página de posts usa `home.php`; `/cases/` usa o arquivo do CPT, sem criar uma página com slug conflitante. Definir os permalinks e redirecionamentos na etapa 05.

## Identidade e navegação

Customizer: logo principal com `custom-logo`, logo do footer, ícone do site pelas opções nativas e paleta semântica de marca, destaque, texto, superfícies, borda e estado de erro. Valores geram CSS custom properties no HTML inicial, sem recompilar Sass ou esperar JS. Validar as cores e fornecer orientação de contraste; não aceitar CSS livre nesses campos.

Menus: localizações `primary`, `footer` e `legal`. Links do menu podem apontar para páginas, arquivos de conteúdo e âncoras conhecidas da home. Uma âncora funciona também nas páginas internas, usando a URL real da home. Não gravar `localhost`, caminhos `.html` ou IDs fixos de páginas no tema.

Usar metadados do item de menu para associar uma seção ou coleção, sem decidir pela comparação do texto do link. Se o destino condicional estiver indisponível, omitir o item. Remover também pais puramente agrupadores quando todos os filhos desaparecerem; preservar pais com destino próprio válido. O submenu mantém operação por teclado e indicação do item atual.

Header e footer compartilham dados globais de contatos, redes sociais, CTA comercial e acesso à plataforma. URLs vazias não produzem ícones clicáveis com `href="#"`. Copyright mantém o texto/ano da referência como valor inicial editável, sem atualização silenciosa que altere esse conteúdo. A logo inicial deve ser idêntica à referência; unificar os SVGs é permitido somente se preservar exatamente desenho, posição, proporção e nitidez. Caso contrário, manter a composição auditada como default e permitir uma logo alternativa pelo painel, na mesma área reservada. Upload administrativo de SVG só será permitido com sanitização específica; PNG/WebP e os SVGs auditados do próprio tema atendem à primeira versão.

## Edição segura e revisões

O plugin registra metadados, schema, sanitização e permissões por tipo. Ao salvar, validar nonce e capacidade de editar o objeto; não processar revisões/autosaves como se fossem submissões completas. Configurações operacionais exigem `manage_options`; identidade e menus seguem capacidades nativas. Não usar nonce como substituto de autorização.

Campos editoriais devem acompanhar revisões e preview, incluindo arrays e ordem das seções. Registrar o suporte de revisões dos metadados e testar o caminho de gravação do editor escolhido. Preview autenticado de rascunho não altera listas públicas nem entra em cache compartilhado. Campos expostos para edição REST têm schema e controle de acesso; credenciais e configurações internas não são públicas.

Os repetidores precisam suportar teclado, feedback de gravação, limites documentados e proteção contra perda por truncamento de formulário. Se a quantidade de inputs exceder os limites suportados, a interface deve orientar e a gravação deve falhar de forma íntegra, sem apagar o final da lista.

Referências de implementação: [meta boxes nativas](https://developer.wordpress.org/plugins/metadata/custom-meta-boxes/) e [registro de metadados e revisões](https://developer.wordpress.org/reference/functions/register_meta/).

## Importação inicial

O importador será uma ação explícita de configuração, não uma rotina disparada a cada ativação ou deploy. Criar um pacote institucional versionado no repositório CMS; o WordPress instalado não deve depender do diretório irmão `iotnest-site` para funcionar.

Importar os textos institucionais, os campos de cabeçalho/rodapé e as mídias correspondentes. Ícones de interface ficam no tema; imagens editoriais entram na biblioteca e são referenciadas por attachment ID. Configurar títulos e flags iniciais de blog/cases, mas criar zero artigos e zero cases. Garantir também que um post de exemplo nativo da instalação não faça a seção aparecer na entrega inicial; identificá-lo e tratar sua remoção/despublicação no setup sem afetar conteúdo legítimo existente.

Depoimentos, números comerciais, contatos e textos legais são copiados como conteúdo fornecido, com revisão editorial antes de produção. Não importar lorem ipsum, capas “a definir”, links `#` ou registros fictícios de blog/cases. Páginas importadas são preparadas para revisão antes de definir/publicar a home definitiva; o importador não substitui uma home existente silenciosamente.

Mapear uma chave de origem estável para cada página/mídia importada. Reexecutar não duplica registros nem sobrescreve campos que o administrador já editou ou limpou. Exibir relatório com itens criados, reutilizados e ignorados. A opção de redefinir conteúdo, se implementada, deve ser separada da importação padrão.
