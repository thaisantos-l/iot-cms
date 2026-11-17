# Etapa 03 — Identidade, header, footer e navegação

Situação: implementada e validada localmente em 17/11/2026, no recorte de navegação desta etapa. Depende da etapa 02. Requisitos: R03, R04, R05, R06, R08, R10. Branch sugerida: `feat/03-identidade-navegacao`.

## Entregáveis

- Extrair header/footer únicos, com hooks nativos e dados globais; registrar menus `primary`, `footer` e `legal`.
- Permitir logos e cores pelo painel, gerar CSS custom properties no servidor e substituir cores fixas dos componentes migrados por variáveis com os mesmos valores iniciais. Preparar fontes locais com licença, preservando família, pesos e resultado visual.
- Criar componentes de título, CTA, mídia, mensagens e containers. Implementar links por destino tipado e helpers de escape/URLs.
- Associar itens de menu a seções/coleções por metadados, aplicando a mesma disponibilidade da home; tratar pais sem filhos, submenu e âncoras em páginas internas.
- Implementar módulo de navegação com teclado, foco, Escape, menu mobile e fallback sem JS. Reservar espaço de logos/header, inclusive com barra administrativa.
- Organizar Sass base/layout/componentes, mantendo proporções e comportamento responsivo do desenho de referência.

## Aceite e verificações

T11–T13 e T16: editar menu/logo/cores sem build, navegar nas páginas internas, omitir URL incompleta, testar mídia ausente e movimento reduzido. Testar largura de 360 até 1440 px, zoom 200%, logo larga/alta e navegação por teclado. Menus não apontam para seções indisponíveis e a ausência de um elemento não causa erro JS.

Não renderizar duas cópias divergentes do header ou footer. Registrar comparação T24 com a identidade original, inclusive logo, tipografia, espaçamentos e menu, além da verificação de deslocamento do header; os testes de todas as seções vêm com a home.

## Commits e merge

1. `feat: migrar identidade visual e componentes comuns`
2. `feat: integrar menus condicionais header e footer`
3. `refactor: modularizar navegacao e estilos compartilhados`

Merge com componentes navegáveis e configurações funcionando. Rollback usa o artefato anterior e preserva menus/opções; chaves não reconhecidas pela versão anterior não devem produzir fatal.

## Execução e evidências

Branch criada a partir da `main` integrada da etapa 02 (`5f819e6`). Implementados header/footer, logos originais, Inter local, paleta no HTML inicial, navegação nativa condicional e teclado. Sass por componente preserva a cascata; Lenis conserva versão e parâmetros originais.

Lint/build, testes PHP, cinco testes Node e 15 testes de navegador aprovados. T24 de navegação: quinze comparações nas cinco larguras sem pixels divergentes. CLS da composição header/footer: zero nas doze amostras, sem extrapolar para a home. O teste de layout equivalente a zoom 200% usa viewport CSS/DPR; zoom pela interface e outros navegadores permanecem no aceite integrado. Detalhes, commits, capturas, perfil e limitações em [evidências da etapa 03](../../../evidencias/etapa-03.md).

Artefato local validado: `iotnest-dc2be362ce56bf2daae2331c09ae098590af9166.zip`. Nenhum deploy ou push. SMTP/cache real seguem pendentes para PRD autorizado.
