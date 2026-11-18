# Etapa 06 — Interações e estabilidade visual

Situação: em implementação na branch `feat/06-interacoes-componentes`, a partir da etapa 05 integrada em `f267f3d`. Requisitos: R03, R04, R06. Branch sugerida: `feat/06-interacoes-componentes`.

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

Módulos de tabs/MES/etapas, carrosséis, calculadora, contadores, mídia local, reveal, parallax e rolagem Dadá conectados ao HTML do CMS. MES reserva todos os painéis via CSS grid sem trocar textos para medir. Mantidos intervalos de 2s/4s, crossfade de vídeo de 1,5s e easing da referência. Modal compartilhado preparado para adapters da etapa 07. Commits `f3ef049` e `e52a016`; sem merge enquanto as verificações estiverem abertas.

Verificações em andamento: fórmula e limites (7 testes Node incluindo os existentes), lint/build, PHP real descartável, teclado/quantidades/autoplay, comparação de 80 componentes em cinco larguras e protocolo CLS. Resultados finais e artefato serão registrados após a execução; nenhuma aprovação de CLS antecipada.

Conflitos R14 apresentados ao responsável, ainda sem resposta: (1) o erro da calculadora original desloca o botão em 62px a 390px; proposta de erro abaixo do botão, substituindo a nota somente durante o erro; (2) painéis de “Como funciona” variam de altura, e reservar o maior acrescenta 7,59375px à altura inicial a 360px. Os dois comportamentos originais permanecem até a decisão. Protótipos locais: `artifacts/revisao-estabilidade/`; não houve escrita no front. A exigência de aprovação vem de R14 e do contrato de interações na arquitetura; não é exigência de ferramenta ou skill.
