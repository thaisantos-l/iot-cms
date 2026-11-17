# Etapa 03 — evidências locais

Data: 17/11/2026. Branch `feat/03-identidade-navegacao`, base `5f819e6`. Artefato validado: `iotnest-dc2be362ce56bf2daae2331c09ae098590af9166.zip`. PHP 8.2.4, WordPress 7.1.4, MySQL descartável, Chromium 159.0.8154.6. Nenhum deploy, push ou escrita no CMS editorial.

## Resultado

Header/footer únicos, menus nativos condicionais, identidade configurável no HTML inicial, Inter local licenciada, Sass por componente e navegação por teclado. O Lenis 1.1.20 mantém os parâmetros originais, com flags e movimento reduzido. Commit de apresentação `7a03b57`, menus `10b50ff`, JS `d29907b`, testes `aca4a82`, organização responsiva/métricas `5ec5cce`, isolamento do atualizador em QA `982878a`, referência atual `dc2be36`.

- PHP lint/WPCS, ESLint/Stylelint, build e cinco testes Node aprovados.
- Integração PHP aprovada: destinos tipados, disponibilidade das coleções e âncoras, remoção de pais vazios, nonce/capacidades, URLs incompletas, mídia inválida, ordem vazia e home privada. Mantidos os testes de fundação, campos, revisões, autosave e permissões.
- Playwright **15/15 aprovados**, sem diagnósticos PHP/HTTP: menu por teclado, Escape/foco, links internos, ausência de JS, movimento reduzido, logos largas/altas/arquivo ausente, paleta sem build, Lenis e testes administrativos anteriores.
- Comparação T24 em 360, 390, 768, 1024 e 1440 px: header, menu aberto/dropdown e footer. Quinze comparações com **zero pixels divergentes**, tolerância de apenas 2 unidades por canal para rasterização. [Capturas e relatórios](etapa-03/1440/comparison.json) versionados em `etapa-03/{largura}/`.
- Teste equivalente de layout a 200%: largura CSS 720 e DPR 2 em canvas de 1440 pixels; não foi um comando manual de zoom na interface do navegador. Sem overflow horizontal no menu. Verificação do zoom pela interface e outros navegadores permanece no aceite integrado da etapa 08.
- [CLS da composição de navegação](etapa-03/navigation-cls.json): **0 nas 12 amostras** — 390 e 1440 × 900, três repetições por viewport com cache frio/quente, latência 150 ms, download 1,6 Mbps, upload 0,75 Mbps, CPU 4×. Janela de sessão de 1 s/5 s e exclusão de entradas recentes; inclui carga/troca de fonte e operação dos menus. Header preservou 73 px. Este resultado cobre somente header/footer, não a futura home completa.

## Método e limites

A comparação isola os componentes no mesmo fundo e posição, sem conteúdo transitório sob as superfícies translúcidas. Ambas usam os mesmos arquivos oficiais Inter v20; a resposta remota da fonte da referência é interceptada somente no navegador de QA. Os placeholders sociais `#` são omitidos conforme o contrato. A referência atual ocultou Cases durante a execução: a fixture visual usa zero cases públicos pela regra real, sem remover artificialmente o link do CMS. [Registro da alteração](referencia-alteracoes.md); captura inicial preservada.

As media queries foram movidas para mixins nos respectivos componentes, mantendo a ordem da cascata; o CSS compilado foi comparado byte a byte antes/depois e permaneceu idêntico. O fallback Arial usa avanço medido no Chromium, com `size-adjust` 105,0021% (400–500) e 100,8698% (600–900), ascent/descent ajustados ao Inter. A troca foi coberta pelas medições acima.

A suíte usa um mu-plugin exclusivo do ambiente descartável para impedir consultas do atualizador a WordPress.org. Ele não entra no artefato e não altera o core ou a política de atualização do produto. Antes desse isolamento uma execução aprovou os 15 testes de navegador, mas foi rejeitada por warning de conexão TLS do atualizador; a execução final terminou com código zero e sem diagnósticos.

Reprodução: `npm run lint`, `composer lint`, `npm run build`, `npm run test:js`, `npm run package`, `PHP_BIN=/Applications/XAMPP/xamppfiles/bin/php npm run test:integration -- artifacts/iotnest-COMMIT.zip`, `npm run reference:check`. O ZIP validado contém somente runtime, sem fontes/Node no destino.

T24 da home/interações, CLS completo, testes de outros navegadores, SMTP real e prazo de atualização com cache ainda não estão aprovados. Haverá somente PRD; nenhum acesso/publicação nesse ambiente foi realizado nesta execução.
