# Especificação do tema WordPress iotnest

Data: 17 de novembro de 2026. Situação: implementação local das etapas 01–08 entregue e verificada. Etapas 07/08 mantêm aceites externos pendentes (SMTP, cache/agendador/infraestrutura de PRD e Safari nativo); a spec não está integralmente aceita. Evidências e limites no [status das etapas](etapas/README.md).

Converter o site estático em um tema WordPress clássico personalizado, preservando seu desenho e suas funções, com conteúdo administrável e renderização em PHP. O destino é a instalação em `cms`; a referência visual e funcional é o diretório `iotnest-site`, incluindo suas alterações locais atuais.

Esta especificação substitui a proposta headless para a implementação do site. Os documentos anteriores de infraestrutura descrevem aquela proposta e não devem orientar a criação de um serviço separado para o front.

## Regra obrigatória de fidelidade ao front

Orientação reforçada pela responsável em 17/11/2026: o tema convertido deve reproduzir o front atual, sem redesign nem alterações visuais ou comportamentais não solicitadas. O diretório `iotnest-site`, incluindo suas alterações locais, é a referência de leitura e não deve ser modificado durante a conversão.

Preservar layout, espaçamentos, dimensões, cores, gradientes, fontes, pesos, ícones, logos, imagens, recortes, breakpoints, ordem inicial, textos institucionais, animações, tempos de transição, rolagem e interações. Os valores iniciais do painel reproduzem essa referência; a possibilidade de personalização não autoriza entregar uma aparência diferente.

Mudanças de PHP, organização Sass/JS, componentes, carregamento de mídia, semântica e estabilidade devem manter o resultado observado. Não desativar efeitos existentes por preferência técnica. Se uma correção necessária não puder preservar o visual/comportamento, documentar o conflito e solicitar uma decisão antes de alterar esse aspecto.

Permanecem as mudanças funcionais já solicitadas: conteúdo administrável, seções e links condicionados ao conteúdo, blog/cases inicialmente vazios, formulário com envio real, Yoast e flags. A ausência inicial de blog/cases decorre dessas regras, não de mudança no desenho dos componentes. Usar fixtures isoladas para comparar os componentes com os exemplos estáticos, sem publicá-las na entrega.

Essa regra prevalece sobre sugestões de melhoria ou diferenças intencionais não solicitadas em qualquer documento ou prompt anterior. A equivalência deve ser verificada por comparação visual e de interações, conforme T24.

## Decisões confirmadas

- Campos personalizados com meta boxes nativas em PHP. Não usar ACF nem o plugin Meta Box.
- Formulário próprio, mantendo o desenho atual, com envio por `wp_mail()` e transporte configurado em um plugin SMTP.
- Importar textos institucionais do site estático. Blog e cases começam sem registros de conteúdo; não importar seus exemplos.
- Tema personalizado, com Sass, JavaScript modular, template parts e header/footer compartilhados.
- Administrador pode editar identidade visual, conteúdo, menus e integrações previstas, sem alterar código.
- Entregas pequenas, com branch, commits, testes e merge por etapa.

## Documentos de trabalho

| Documento | Uso |
| --- | --- |
| [Conteúdo e painel](01-conteudo-e-painel.md) | Tipos de conteúdo, campos, regras de exibição, menus, configurações e importação. |
| [Arquitetura e componentes](02-arquitetura-e-componentes.md) | Organização do tema e plugin próprio, Sass, JS, componentes, formulário, scripts e cache. |
| [Validação e entrega](03-validacao-e-entrega.md) | Matriz de testes, CLS, SEO, acessibilidade, publicação e critérios de conclusão. |
| [Etapas e merges](etapas/README.md) | Ordem de implementação, dependências e escopo de cada PR. |
| [Inventário da referência](04-inventario.md) | Mapeamento dos arquivos atuais e cuidados da conversão. |
| [Contrato com infraestrutura](05-infraestrutura.md) | Actions, artefato, cache, agendador, responsabilidades e dados do ambiente. |

## Requisitos e rastreabilidade

| ID | Requisito | Etapas principais |
| --- | --- | --- |
| R01 | Meta boxes nativas, registro dos dados, validação, permissões, revisões e preview. | 02, 08 |
| R02 | Blog e cases exibem apenas conteúdo público publicado; comportamento correto com zero, um e vários itens. | 05, 08 |
| R03 | Sass organizado em módulos e compilação reproduzível. | 01 e todas as etapas visuais |
| R04 | JS modular, sem textos editoriais fixos e tolerante a componentes ausentes. | 01, 03, 06 |
| R05 | Template parts e seções condicionadas ao campo principal e às dependências necessárias. | 02, 03, 04, 05 |
| R06 | Estabilidade visual desde o HTML inicial e medição de CLS. | 03, 04, 06, 07, 08 |
| R07 | Formulário próprio integrado ao plugin SMTP. | 07, 08 |
| R08 | Logos, cores e textos editáveis no painel. | 02, 03, 04 |
| R09 | Flags e configuração administrativa de integrações externas. | 02, 07, 08 |
| R10 | Header, footer e menus compartilhados e configuráveis. | 03 |
| R11 | Yoast, URLs, sitemap, templates internos, páginas legais, 404 e busca. | 05, 08 |
| R12 | Conteúdo atualizado sem deploy; validar o objetivo de até 10 segundos para novas requisições. | 08 |
| R13 | Importação institucional repetível, sem duplicação ou sobrescrita de edições. | 04, 08 |
| R14 | Fidelidade visual e comportamental ao front atual, preservando o repositório de referência. | Todas; comparação nas etapas 03–08 |

## Arquitetura definida para a implementação

O tema `iotnest` cuida da apresentação. Um plugin próprio, `iotnest-core`, guarda tipos de conteúdo, meta boxes, configurações operacionais, envio do formulário e importador. Esse plugin é código do projeto, não uma biblioteca de campos. A separação permite trocar o tema sem perder o acesso administrativo aos cases e seus campos.

Ambos ficam no repositório `iot-cms`, em `wp-content/themes/iotnest` e `wp-content/plugins/iotnest-core`. O repositório `iot-front` permanece como referência da conversão. Não duplicar o desenvolvimento do site final nos dois repositórios.

O tema renderiza conteúdo e condições no servidor. Não buscar o conteúdo principal por JavaScript após carregar a página. Node é ferramenta de desenvolvimento e CI para Sass/JS; não será necessário um serviço Node em produção.

## Liberdade editorial e limites da primeira versão

O painel permite preencher, limpar, habilitar, desabilitar e ordenar as seções previstas; adicionar, remover e ordenar itens repetidos; escolher imagens; editar menus; ajustar a paleta e as logos. Os componentes mantêm contratos de conteúdo e proporções visuais para funcionar em diferentes telas.

Não haverá construtor genérico de layouts nem editor de PHP/JavaScript pelo painel. Novos tipos de seção ou novos fornecedores de scripts exigem código e passam pelo fluxo de PR. Artigos e páginas textuais usam o editor nativo do WordPress com estilos compatíveis com o tema.

São dependências operacionais da entrega: plugin SMTP e credenciais válidas, Yoast, domínio final, mecanismo real de cache e versões PHP/WordPress de produção. A escolha do plugin SMTP não altera a API `wp_mail()` usada pelo projeto. Uma ferramenta de consentimento deve ser definida antes de habilitar integrações que dependam de consentimento.

## Exibição de conteúdo

Uma seção editorial aparece quando está habilitada, seu campo principal tem texto significativo e suas dependências estão completas. Campos opcionais vazios removem apenas o elemento correspondente. Uma lista sem itens válidos remove a seção completa. A avaliação acontece em PHP antes de gerar o HTML.

Blog e cases também exigem ao menos um registro com `post_status=publish`, público e sem proteção por senha. Rascunhos, pendentes, agendados, privados e itens na lixeira não contam, inclusive para administradores olhando o site público. O primeiro item publicado faz a seção aparecer automaticamente se ela estiver habilitada e tiver seu título configurado.

Não deixar containers, títulos, setas, pontos, CTAs ou espaçamentos vazios. Os menus e links para seções usam a mesma regra de disponibilidade. O detalhamento e as exceções estão no documento de conteúdo.

## Sequência de entregas

1. Fundação do tema, plugin próprio e build.
2. Modelo de conteúdo, meta boxes e configurações administrativas.
3. Identidade visual, header, footer, menus e componentes comuns.
4. Home institucional e importação inicial.
5. Blog, cases, páginas internas e estados de publicação.
6. Interações JavaScript e estabilidade dos componentes.
7. Formulário e integrações controladas por flags.
8. SEO, cache, QA, documentação de operação e pacote final.

Cada etapa deve funcionar com os dados que já existem e com os recursos das etapas anteriores. A etapa final valida o conjunto; segurança, acessibilidade e CLS não ficam adiados até ela.

## Pontos de atenção já incorporados

A seção de blog da home está comentada no HTML. Os cases de exemplo incluem texto provisório e não serão importados. A home tem conteúdo fixo dentro do JS, o formulário usa `mailto:`, há links `#` sem destino e o HTML contém bloqueios de indexação de homologação. Esses comportamentos serão tratados nas etapas correspondentes.

Os cinco HTMLs atualmente modificados no repositório de referência são parte da fonte de migração e devem ser preservados. A especificação e suas evidências estão versionadas no repositório CMS. O `.gitignore` permite código próprio, tooling e documentação, preservando as exclusões de core, credenciais, banco, uploads e arquivos de ambiente.
