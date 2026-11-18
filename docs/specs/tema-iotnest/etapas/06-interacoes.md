# Etapa 06 — Interações e estabilidade visual

Situação: implementação e aceite local concluídos, com duas exceções de estabilidade aprovadas pela responsável em 18/11/2026; checkpoint na branch `feat/06-interacoes-componentes`, a partir da etapa 05 integrada em `f267f3d`. Requisitos: R03, R04, R06. Branch sugerida: `feat/06-interacoes-componentes`.

## Entregáveis

- Dividir comportamentos em módulos: tabs, etapas, carrosséis, calculadora, contadores, mídia e movimento. Reutilizar a navegação da etapa 03.
- Alimentar tudo pelo HTML/JSON do CMS; remover textos editoriais fixos do bundle e suposições sobre contagem de itens.
- Guardar inicializações por elemento, evitar listeners duplicados e pausar timers fora de uso. Carrossel de um item não tem autoplay/setas/dots; controles de vários itens dependem de overflow.
- Estabilizar tabs/MES/etapas antes da primeira troca; eliminar a reescrita temporária de conteúdo para medir altura após o paint.
- Implementar calculadora com fórmula preservada, limites, formatação e espaço de resultado/status. Implementar reprodução de mídia local e preparar componente de modal; o carregamento de terceiros fica ligado às flags na etapa 07.
- Respeitar teclado, foco, redução de movimento e pausa. Preservar efeitos e defaults de Lenis, Dadá, autoplay e demais animações, incluindo tempos e condições do front. Não desativar ou substituir comportamentos durante a refatoração; flags permitem mudanças posteriores pelo administrador.

## Aceite e verificações

T05, T11, T13, T16 e T24, além dos casos matemáticos da [arquitetura](../02-arquitetura-e-componentes.md). Testar seções removidas, itens reordenados, conteúdo alterado no painel, teclado, abas ocultas e mídia que falha. Conteúdo deve continuar acessível sem JS. Comparar comportamento e aparência em interação com o front, não apenas screenshots da página parada.

Medir carregamento e interações com fontes/mídia lentas conforme o [protocolo CLS](../03-validacao-e-entrega.md). Corrigir deslocamentos sem cortar textos longos ou fixar alturas que prejudiquem zoom. Registrar três execuções dos cenários pertinentes; orçamento interno CLS ≤ 0,05.

## Commits e merge

1. `refactor: separar interacoes em modulos por componente`
2. `feat: conectar interacoes aos dados do WordPress`
3. `perf: estabilizar paineis midias e resultados`

Merge após evidências funcionais/visuais pertinentes. Rollback deve manter o HTML renderizado funcional; nenhuma interação depende de campos antigos hardcoded para recuperar conteúdo.


## Execução local em 18/11/2026

Módulos de tabs/MES/etapas, carrosséis, calculadora, contadores, mídia local, reveal, parallax e rolagem Dadá conectados ao HTML do CMS. MES reserva todos os painéis via CSS grid sem trocar textos para medir. Mantidos intervalos de 2s/4s, crossfade de vídeo de 1,5s e easing da referência. Modal compartilhado preparado para adapters da etapa 07. Commits `f3ef049`, `e52a016`, `0da6b19` e `1b679ce`. Merge local `396434e`; os comportamentos originais foram expressamente confirmados pela responsável ao responder às duas propostas de estabilidade.

Verificações executadas: 7 testes Node, lint/build/PHP/WPCS, bateria PHP descartável e 61/62 testes de navegador, com o único teste de precisão geométrica corrigido e aprovado em repetição focada no mesmo artefato. T24 inclui 80 componentes da home e 80 estados de interação. CLS da home: 12 amostras, todas 0. [Evidências e limites](../../../evidencias/etapa-06.md).

Decisões R14 recebidas em 18/11/2026: **“Manter erro na posição original”** e **“Preservar alturas originais”**. Portanto, o erro da calculadora continua antes do botão (deslocamento original de 62px a 390px), e “Como funciona” mantém as alturas variáveis. Reservar o maior painel acrescentaria 7,59375px à altura inicial a 360px e foi recusado. Essas são exceções explícitas aos contratos de estabilidade de R06; não são falhas ocultadas pela medição de CLS. Nenhuma alteração visual proposta foi aplicada e não resta decisão pendente sobre esses dois pontos. Protótipos locais permanecem em `artifacts/revisao-estabilidade/`, fora da entrega.
