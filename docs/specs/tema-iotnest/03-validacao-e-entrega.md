# Validação e entrega do tema iotnest

## Objetivo de estabilidade visual

CLS mede deslocamentos inesperados; não é uma propriedade aplicada ao componente. A meta de campo é CLS menor ou igual a 0,1 no percentil 75, acompanhada separadamente em mobile e desktop quando houver dados suficientes. Para a homologação, adotar como orçamento interno CLS menor ou igual a 0,05 em cada execução do roteiro de laboratório. Esse orçamento é uma escolha do projeto, não um resultado já medido. [Definição e avaliação de CLS](https://web.dev/articles/cls).

Registrar versão do código e navegador, viewport, perfil de rede/CPU, conteúdo usado e cache frio/quente. Executar pelo menos três repetições dos cenários principais. Medir carregamento e interações posteriores; um Lighthouse de navegação isolado não cobre toda a sessão. Usar a métrica por janelas de sessão fornecida pela ferramenta, sem somar indiscriminadamente todos os eventos de layout.

## Contratos dos componentes para CLS

| Componente | Contrato de estabilidade |
| --- | --- |
| Header e logos | Dimensões/proporção reservadas antes da imagem; logo extrema contida na área prevista; menu mobile em camada própria; considerar barra administrativa. |
| Imagens editoriais e capas | `width`, `height`, `srcset` e `sizes` adequados; proporção por componente; imagem ausente tratada no servidor. |
| Hero | Área prevista por breakpoint, poster inicial e vídeo dentro do mesmo espaço. Não lazy-load da mídia crítica sem avaliação. |
| Vídeos e embeds | Slot com proporção conhecida antes do player; poster e player usam o mesmo espaço. |
| Cards | Imagem com proporção uniforme e conteúdo flexível; textos longos não sobrepõem controles. |
| Tabs, MES e etapas | Espaço suficiente para os painéis antes da primeira troca; não depender de medir/reescrever textos depois do paint para corrigir altura. |
| Contadores | Espaço compatível com valor final e numerais tabulares, inclusive ao cruzar quantidade de dígitos. |
| Calculadora e formulário | Regiões de resultado/status previstas; mensagens não movem o botão ou o campo que o usuário está acionando. |
| Fontes | Arquivos locais, pesos limitados e fallback com métricas ajustadas; medir a troca de fonte. |
| Seções condicionais | Decisão no servidor; não reservar espaço para uma seção vazia nem removê-la após a carga. |
| Efeitos e integrações | Preferir opacity/transform para animações; evitar inserções acima do conteúdo e alterações de geometria sem espaço reservado. |

Alturas fixas que cortem o texto não são uma solução aceitável. Testar textos maiores, mudanças de cor/logo, mídia lenta e zoom. Para imagens da biblioteca, usar as APIs de attachments do WordPress. [Imagens responsivas do WordPress](https://developer.wordpress.org/reference/functions/wp_get_attachment_image/).

## Matriz funcional obrigatória

| ID | Cenário | Resultado esperado |
| --- | --- | --- |
| T01 | Ativar tema/plugin e abrir home ainda sem campos | Sem fatal, notices, JS quebrado ou seções vazias. |
| T02 | Limpar o campo principal de cada seção | Seção removida do HTML; seus links condicionais também. |
| T03 | Limpar um campo opcional | Apenas o elemento correspondente é omitido. |
| T04 | Texto com espaços, tags vazias ou caracteres invisíveis | Tratado como ausência; número zero válido é preservado. |
| T05 | Listas com zero, um e vários itens válidos, incluindo linhas incompletas | Sem controles vazios, duplicação ou dependência de uma contagem fixa. |
| T06 | Blog/cases com apenas rascunhos, pendentes ou agendados | Seções ocultas para visitantes e na navegação pública do administrador. |
| T07 | Publicar primeiro artigo/case | Seção aparece se habilitada e titulada, sem deploy. |
| T08 | Publicar mais itens e paginar | Ordem, limite, paginação e relacionados corretos. |
| T09 | Despublicar, privatizar, proteger por senha ou excluir o último item | Seção, cards e links condicionais desaparecem. |
| T10 | Chegar o horário de uma publicação agendada | Não vazar antes de `publish`; aparecer após a transição e invalidação. |
| T11 | Reordenar/desabilitar seções, menus e repetidores | Ordem persistente, IDs estáveis e links válidos em páginas internas. |
| T12 | Alterar logos e todas as cores previstas | Refletir no HTML/CSS inicial, sem build e sem quebra de proporções. |
| T13 | Desativar JS e solicitar movimento reduzido | Conteúdo principal acessível; navegação/formulário com alternativa funcional. |
| T14 | Revisão, restauração e preview de campos complexos | Dados coerentes com a versão escolhida; preview não altera o público. |
| T15 | Reexecutar importador após editar e limpar campos | Sem duplicar mídia/páginas nem sobrescrever alterações. Blog/cases continuam vazios. |
| T16 | Falta de imagem, URL inválida ou arquivo removido | Não gerar requisições vazias, layout quebrado ou mensagens técnicas ao visitante. |
| T17 | Usuário sem capacidade, nonce inválido e conteúdo malicioso | Não alterar dados; saída escapada e campos validados. |
| T18 | Formulário sem JS, com JS, SMTP válido, falha e clique repetido | Envio coerente, diagnóstico e feedback acessível; validar recebimento real. |
| T19 | Flag externa desligada e consentimento negado/revogado | Ausência de requests e execução não autorizada do fornecedor. |
| T20 | Yoast ativo e desativado; página inexistente | HTML válido, sem duplicação de SEO; 404 real e fallback básico de título. |
| T21 | Publicação/edição com cache previamente preenchido | Nova requisição atualizada em até 10 segundos no ambiente acordado. |
| T22 | Build em checkout limpo e instalação do artefato | CSS/JS compilados presentes e site funcional sem Node no servidor. |
| T23 | Rollback de código com conteúdo editorial recente | Versão anterior compatível funciona sem restaurar banco antigo. |
| T24 | Comparar front atual e tema com o mesmo conteúdo, estado e viewport | Mesma aparência e comportamento; nenhuma mudança não solicitada. Referência permanece intacta. |

## Roteiro visual e acessível

Comparar o tema com a referência nas larguras de 360, 390, 768, 1024 e 1440 pixels, além de zoom de 200%. Usar o mesmo navegador, escala, fontes carregadas, conteúdo, posição de rolagem e estado dos componentes. Registrar a referência a partir dos arquivos atuais, sem descartar suas alterações locais. Capturar screenshots por página e componentes, incluindo menus abertos, tabs e resultados, e comparar lado a lado ou por sobreposição/diff. Separar pequenas diferenças de rasterização do navegador de mudanças reais de geometria, cor, tipografia ou mídia. Não adotar tolerância ampla que esconda regressões.

Diferenças funcionais admitidas são somente as já solicitadas: seções/links condicionais, coleções inicialmente vazias e alterações deliberadas posteriores pelo painel. Personalização disponível não justifica trocar logo, cores, conteúdo ou ordem inicial. Para comparar cards e templates vazios na entrega, usar fixtures exclusivamente no ambiente de teste. Não redesenhar componentes para melhorar uma métrica.

Testar também animações, transições, autoplay, rolagem Lenis/Dadá, tabs, modal e calculadora contra a referência. Screenshots podem estabilizar animações para comparação, mas os efeitos reais precisam de verificação separada, inclusive em mobile e movimento reduzido. Registrar qualquer divergência e sua resolução antes do aceite.

Percorrer menu mobile, dropdown, âncoras, tabs, carrosséis, vídeos, FAQ, calculadora e formulário por teclado. Confirmar foco visível, rótulos, Escape, retorno de foco de modal, controles de pausa e ausência de foco em painéis inativos. Manter alternativa funcional quando APIs como dialog ou observers não estiverem disponíveis nos navegadores suportados.

Testar Chrome, Firefox e Safari atuais nas versões acordadas, incluindo Safari mobile quando disponível. Configurações de cores devem ser verificadas quanto a contraste e os estados não podem depender apenas de cor.

## SEO e rotas

O Yoast produz os metadados e sitemap do WordPress por meio dos hooks normais do tema. Não copiar as tags estáticas da Vercel nem duplicar title, canonical, Open Graph e JSON-LD. Testar também análise editorial: conteúdos guardados em meta boxes podem exigir um adaptador de análise próprio para o Yoast; a pontuação do painel não comprova, por si, o HTML público. Esse adaptador deve usar os campos relevantes, sem ACF. [Extensão da análise de conteúdo do Yoast](https://developer.yoast.com/customization/yoast-seo/adding-custom-data-analysis/).

Usar uma fonte de breadcrumbs; quando Yoast estiver ativo, integrar sua saída, evitando uma segunda árvore de schema conflitante. Páginas legais usam títulos/URLs reais e sumário de headings com IDs únicos. Políticas fornecidas precisam acompanhar as integrações efetivamente habilitadas; a implementação não cria aprovação jurídica automática.

Preparar o mapa das URLs antigas `.html` para as novas rotas. Artigos e cases de exemplo não ganham redirecionamento para conteúdo inexistente. Aplicar 301 somente quando existir destino equivalente e aprovado; URLs sem equivalente devem ter tratamento definido, sem soft 404. Blog usa página de posts; cases usa arquivo do CPT. Busca e paginação respeitam a política de conteúdo público.

Em produção, remover os bloqueios indevidos de indexação das páginas públicas e definir o domínio correto. Homologação permanece protegida. O `vercel.json` da referência não controla o servidor WordPress.

## Dados e desempenho dos testes

As fixtures de blog e cases existem apenas nos testes e devem ser removidas do ambiente de entrega vazio. Incluir fixtures com rascunhos, datas futuras, proteção por senha, caracteres especiais, textos longos e campos incompletos. Não depender de IDs de um banco particular.

Automatizar as regras de visibilidade, normalização, consultas, fórmula da calculadora, permissões e handler de contato. Usar testes de navegador para os fluxos de estado e interação, e ferramentas de performance para CLS. Nenhuma pontuação de performance foi medida como parte da escrita desta spec.

## Critério de merge e conclusão

Cada PR deve identificar requisitos/etapa, explicar o comportamento entregue, anexar as verificações relevantes e descrever configurações ou migrações. Deve ser revisável isoladamente, com lint/build/testes aplicáveis passando e sem introduzir placeholders no site público. Imagens e conteúdo inseridos apenas para teste não entram como publicações de produção.

A conclusão exige as oito etapas integradas, importação institucional validada, blog/cases sem posts de exemplo publicados, fidelidade visual e comportamental demonstrada em T24, acessibilidade dos componentes principais, evidências de CLS, integração real de SMTP, SEO correto e atualização editorial medida com cache.

O deploy publica somente o tema, o plugin próprio e seus assets compilados. Preserva banco, uploads, SMTP e demais configurações do ambiente. Migrações de dados são versionadas e compatíveis com o rollback acordado; a ativação normal não redefine conteúdo. Infra opera backups de banco/mídia, agendador e cache; desenvolvimento entrega os comandos, hooks e testes.
