# Etapa 02 — Modelo de conteúdo e painel

Situação: planejada. Depende da etapa 01. Requisitos: R01, R05, R08, R09. Branch sugerida: `feat/02-campos-nativos`.

## Entregáveis

- Registrar CPT `iot_case`, taxonomia `iot_case_sector` e suportes de editor, resumo, imagem e revisões; manter artigos no tipo nativo `post`.
- Registrar os schemas de home e cases descritos em [conteúdo e painel](../01-conteudo-e-painel.md), com versão, tipos, validação e capacidades. Distinguir valor ausente de remoção intencional.
- Criar meta boxes nativas com campos de texto/mídia, repetidores acessíveis, ordenação e flags; mostrar por que uma seção está oculta.
- Configurar telas operacionais para contatos, formulário e integrações. Registrar a paleta e identidade no Customizer; a aplicação visual completa chega na etapa 03.
- Implementar normalização de texto significativo, itens incompletos e condições compartilhadas de disponibilidade. Definir limites dos repetidores e impedir gravações truncadas/parciais.
- Integrar revisões e preview dos campos, incluindo arrays e ordem. Manter o editor nativo para artigos/páginas; o conteúdo da home vem dos campos de seção, sem uma segunda fonte concorrente no editor.

## Aceite e verificações

Testar T04, T11, T14 e T17: espaços/HTML vazio versus zero, reordenação, revisão restaurada, preview sem alteração pública, capacidades e nonce. Testar gravação inválida, autosave, edição rápida e request incompleto sem perda dos campos não enviados. O editor da home deve deixar claro de onde vem cada conteúdo.

As regras de disponibilidade devem ter testes úteis para título vazio, seção desabilitada, lista inválida e dependências; sua aplicação no HTML será validada nas etapas 03–05.

## Commits e merge

1. `feat: registrar conteudos e schemas de metadados`
2. `feat: criar painel de campos nativos e configuracoes`
3. `test: validar revisoes permissoes e regras de conteudo`

Merge com contratos de dados estáveis e verificações aprovadas. Migrações devem ser aditivas; reverter código não apaga metadados. Não criar posts/cases de demonstração no banco de entrega.
