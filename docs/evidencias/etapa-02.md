# Etapa 02 — evidências locais

Data: 17/11/2026. Branch: `feat/02-campos-nativos`; base integrada da etapa 01: `7bb858b`. Código de painel/preview: `65ee982`, schemas: `bcdecb4`. Runtime de QA: PHP 8.2.4, WordPress 7.1.4, MySQL isolado e Chromium 159.0.8154.6. O CMS editorial e a referência não receberam gravações.

## Executado

- Sintaxe PHP, WordPress Coding Standards, ESLint e Stylelint aprovados.
- Build Sass/esbuild e pacote de runtime aprovados. Instalação do ZIP em WordPress descartável, sem fontes/Node no destino, com logs PHP sem warnings/notices/fatals.
- Suíte Node: 5 testes aprovados, cobrindo inicialização 0/1/vários, idempotência e integridade/exclusões do pacote.
- Integração PHP: criação/reativação sem alterações editoriais; registro do CPT/taxonomia; texto vazio/invisível versus zero; campos parciais e limpeza; ordem, IDs e linhas incompletas; nonce/capacidades; JSON inválido, limite de repetidor, payload >1 MiB e submissão truncada; edição rápida; revisão e restauração; autosave e resolução de arrays no preview; HTML/URL maliciosos; flags/dependências; opções protegidas por `manage_options`; REST parcial/autorização/privacidade; publicações privadas, futuras, pendentes, rascunhos e protegidas por senha fora das coleções públicas.
- Playwright: **5/5 aprovados**. Dois testes administrativos novos: edição/repetidor/ordenação por teclado, foco, gravação/reload, autosave de campos pelo heartbeat, dois previews consecutivos sem modificar a publicação, limpeza intencional; configurações válidas e rejeição de ID de integração inválido preservando o estado anterior. Mantidos os três testes da fundação.
- Referência: 83 arquivos e estado Git iguais à captura inicial, incluindo os cinco HTMLs locais modificados.

Reprodução: `composer lint`, `npm run lint`, `npm run build`, `npm run test:js`, `npm run package`, `PHP_BIN=/Applications/XAMPP/xamppfiles/bin/php npm run test:integration -- artifacts/iotnest-COMMIT.zip`, `npm run reference:check`. Usar checkout limpo para release. Durante desenvolvimento, `--allow-dirty` identifica o ZIP como `-dirty`; não é um artefato de promoção.

## Detalhes e limites

O core local retorna arrays de metadata de preview com um envelope incompatível com o desembrulhamento da API de metadata. A correção do plugin restringe-se às próprias chaves, usuário autorizado e filtro nativo de preview ativo. O autosave clássico também compara apenas título/conteúdo/resumo; o plugin grava seus campos na revisão do autor pelo heartbeat e preserva o preview quando o core descarta uma revisão de texto idêntico. Os dois caminhos foram exercitados pelo navegador. Nenhum arquivo do core foi editado.

Repetidores têm IDs persistentes e limites explícitos. Campos visuais são serializados em um único JSON para evitar truncamento por `max_input_vars`. O servidor valida nonce, capacidade, tamanho, marcador e schema antes de escrever qualquer seção; campos não enviados são preservados. O editor pode limpar títulos e manter linhas incompletas; a camada de disponibilidade impede sua exibição posterior.

T04/T11/T14/T17 estão aprovados no recorte de campos desta etapa. T11 de menus e T17 do formulário serão executados nas respectivas etapas. O painel registra identidade, mas sua aplicação visual é etapa 03. A apresentação final, T24 visual/comportamental e CLS ainda não foram aprovados. SMTP e atualização com cache real permanecem dependentes de execução futura autorizada em PRD, único ambiente confirmado. Não houve push ou deploy.
