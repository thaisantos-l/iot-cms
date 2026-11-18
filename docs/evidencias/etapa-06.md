# Etapa 06 — Interações e fidelidade

Verificação local em 18/11/2026, PHP 8.2.4/WordPress 7.1.4 e Chromium 159.0.8154.6. Artefato limpo `1b679cec83080e3c498686e753dd1e14a1a4f20b`, instalado em WordPress descartável sem fontes ou Node no destino. Branch originada na main `f267f3d`.

## Código e verificações

Commits `f3ef049`, `e52a016`, `0da6b19` e `1b679ce`: módulos reutilizáveis de tabs, MES, etapas, carrosséis, cálculo, contadores, movimento, mídia local e modal. Dados vêm do HTML/JSON do WordPress; inicialização por elemento, pausas por foco/visibilidade, redução de movimento e fallbacks sem observers. MES reserva todos os textos em grid; arredondamento de altura reproduz o front, inclusive em engines sem `calc-size()`.

Lint JavaScript/Sass, sintaxe PHP/WPCS, build e sete testes Node aprovados. Os cenários PHP de conteúdo, permissões, revisões, importação, consultas e estados foram executados no WordPress real descartável. A rodada integrada teve 61/62 testes de navegador aprovados; a única falha era igualdade absoluta de `47` com `47.000244140625` em `getBoundingClientRect()` de uma imagem transformada. A asserção foi ajustada para precisão de centésimo de pixel; o teste de logos extremos, arquivo removido e escala 200% passou novamente no mesmo artefato, junto com toda a bateria PHP. Não houve alteração de CSS para corrigir o teste.

T24: 80 comparações da home, 80 estados de interação, 15 componentes de navegação e 25 páginas internas nas larguras 360/390/768/1024/1440. Os estados incluem três tabs IA, cinco MES, três posições de hardware, quatro etapas e resultado da calculadora. Geometria e pixels coincidem; o recorte de texto de “Como funciona” evita apenas a rasterização já classificada da borda arredondada da imagem, cuja geometria também é verificada. Nenhuma máscara ampla de pixels. JSONs e capturas selecionadas em [etapa-06](etapa-06/).

Movimento executado nas duas implementações: Lenis ativo, zoom conforme scroll, vídeos reais de 30,042s com crossfade de 1,5s, reprodução/pausa persistente da Dadá. Os servidores de QA receberam suporte HTTP Range para que o seek reproduza a situação de um servidor de mídia real. Essa correção existe somente no harness de testes, sem escrita na referência.

CLS da home: 12 amostras, três repetições frio/quente a 390 e 1440px, latência 150ms, download 1,6Mbps, upload 0,75Mbps, CPU 4×. Carregamento e percurso real por hardware, IA, MES, etapas, calculadora e contadores. **Todas as amostras tiveram CLS 0**, por janelas de sessão e excluindo entradas recentes conforme a definição da métrica. É evidência de laboratório, não dado de campo. [Dados completos](etapa-06/home-cls.json).

## Decisões de fidelidade e limites

Em 18/11/2026, a responsável respondeu **“Manter erro na posição original”** e **“Preservar alturas originais”**. O código já preservava os dois comportamentos. Ficam aprovadas as exceções ao contrato de estabilidade: erro antes do botão da calculadora, com deslocamento original de 62px a 390px, e alturas variáveis de “Como funciona”. A reserva do maior painel acrescentaria 7,59375px à altura inicial a 360px e não foi aplicada. Não resta decisão pendente nesses pontos; o aceite local da etapa 06 está concluído com essas exceções expressas.

Merge local `396434e`. Não houve deploy nem alteração no front: `npm run reference:check` conferiu 83 arquivos e o estado Git. O CLS medido continua sendo evidência de laboratório; os movimentos em resposta imediata à interação não podem ser apresentados como eliminados só porque ficam fora da métrica.
