# Etapa 04 — Home institucional e importação

Situação: implementada e validada localmente em 18/11/2026, no recorte desta etapa. Depende da etapa 03. Requisitos: R03, R05, R06, R08, R13. Branch sugerida: `feat/04-home-institucional`.

## Entregáveis

- Converter as seções institucionais em template parts, usando os campos e contratos definidos. Blog/cases permanecem sem conteúdo; suas consultas e templates completos entram na etapa 05.
- Preparar view models e disponibilidade antes de gerar HTML; aplicar as condições também aos CTAs, menus e assets. Omitir wrappers vazios.
- Migrar o Sass das seções, imagens responsivas, proporções, posters e headings. Renderizar conteúdo textual inicial em PHP; tabs/etapas têm alternativa legível sem o JS da etapa 06.
- Criar pacote de textos e mídia institucional no CMS e importador explícito com chaves estáveis de origem. Incluir conteúdo atualmente embutido no JS.
- Preparar páginas legais para revisão, menus e defaults de títulos/flags de blog e cases. Criar zero registros dessas coleções.
- Identificar conteúdo de exemplo da instalação antes de tratá-lo; não alterar conteúdo legítimo. Configurar home/página de posts por uma ação explícita de setup, sem substituir a home existente silenciosamente.

## Aceite e verificações

T02–T05, T11, T15 e T16: preencher/limpar todas as seções, campos opcionais, listas de zero/um/vários itens, reordenar e remover mídia. O título apagado não é reposto por fallback estático. Reexecutar importação depois de editar e limpar campos não duplica páginas/mídias nem sobrescreve edições.

Comparar home com os mesmos valores do front conforme T24, preservando ordem inicial, composição, cores, mídias, fontes e espaçamentos. Depois testar textos longos e mídia lenta. Contato pode mostrar canais válidos; não exibir botão de envio sem serviço funcional antes da etapa 07. Os controles que ainda não têm comportamento não devem parecer operacionais; registrar esses estados como provisórios de homologação, sem tratá-los como visual final aprovado.

## Commits e merge

1. `feat: renderizar home institucional com campos nativos`
2. `feat: importar textos e midias institucionais sem duplicacao`
3. `test: cobrir visibilidade e preservacao de conteudo`

Merge com home utilizável e importador verificável. Rollback não desfaz conteúdo editorial; a importação não roda na ativação nem automaticamente em cada deploy.

## Execução

Home institucional, pacote de trinta mídias, importador repetível e setup explícito implementados. Lints, cinco testes Node, todos os cenários PHP e 24 testes Chromium aprovados. Oitenta comparações T24 nas cinco larguras, com estados provisórios de controles e rasterização delimitados. [Evidências, commits e limites](../../../evidencias/etapa-04.md). Formulário/interações completos e CLS integrado pertencem às etapas seguintes; nenhum deploy ou push.
