# Etapas, commits e merges

As etapas **01–06 estão implementadas e validadas localmente** até 18/11/2026; as duas decisões R14 da etapa 06 foram respondidas, preservando os comportamentos originais. As etapas 07 e 08 têm implementação e testes locais aprovados, com aceites externos pendentes. A spec permanece sem aceite integral de SMTP, cache/agendador/infraestrutura de PRD e Safari nativo. A especificação foi integrada à `main` no merge documental `2e2014e`. Cada etapa registra commits e evidências; nenhuma publicação remota foi feita.

## Sequência de PRs

| Etapa | Documento | Depende de | Resultado revisável |
| --- | --- | --- | --- |
| 01 | [Fundação](01-fundacao.md) | Spec | Validada: tema/plugin instaláveis, T01/T22 e build reproduzível. |
| 02 | [Campos e painel](02-campos-e-painel.md) | 01 | Validada: campos, revisões, preview, autosave e permissões. |
| 03 | [Identidade e navegação](03-identidade-e-navegacao.md) | 02 | Validada localmente: header/footer, menus, identidade e T24 de navegação. |
| 04 | [Home e importação](04-home-e-importacao.md) | 03 | Conteúdo institucional em PHP, com importação repetível. |
| 05 | [Blog, cases e páginas](05-blog-cases-e-paginas.md) | 04 | Publicações e rotas completas, respeitando estados. |
| 06 | [Interações](06-interacoes.md) | 05 | Validada localmente, com as duas exceções R14 expressamente aprovadas. |
| 07 | [Formulário e integrações](07-formulario-e-integracoes.md) | 06 | Código/QA aprovados; recebimento SMTP real pendente. |
| 08 | [Qualidade e publicação](08-qualidade-e-publicacao.md) | 07 | SEO, contratos de cache, pacote e QA local aprovados; aceites externos pendentes. |

Os IDs R01–R14 estão na [spec principal](../README.md). Os cenários T01–T24 estão na [matriz de validação](../03-validacao-e-entrega.md). Cada etapa lista seu recorte; a etapa final valida o conjunto integrado. R14 é transversal: preservar o front e sua aparência/comportamento, sem redesign, em todas as etapas.

## Fluxo de trabalho

1. Versionar esta spec em uma entrega documental, sugerida como `docs/spec-tema-iotnest`, antes do código.
2. Criar a branch de cada etapa a partir da `main` atualizada após o merge anterior. Não acumular toda a conversão em um único PR.
3. Fazer commits pequenos e coerentes. Os nomes em cada documento são sugestões, não commits já executados.
4. Abrir PR com problema/resultado, requisitos atendidos, verificações, evidências visuais quando cabíveis e efeitos nos dados.
5. Integrar após passar nos checks aplicáveis; homologar o artefato e só então iniciar a etapa dependente.

As primeiras etapas não compõem um site completo. Foi confirmado que haverá **somente PRD**. Durante esta execução, merges são locais e o QA usa instalação descartável. Não há deploy autorizado; a publicação inicial e os testes reais de SMTP/cache ficam para uma execução futura autorizada. Nenhum merge de etapa parcial deve publicar automaticamente.

Após a primeira entrega, o time pode manter promoção por release ou adotar deploy em produção a cada merge aprovado na `main`. A política escolhida deve ser documentada, com o mesmo artefato validado e rollback disponível. Os detalhes estão no [contrato com infraestrutura](../05-infraestrutura.md).

## Contrato de cada PR

- Escopo limitado à etapa e dependências declaradas.
- Sem sobrescrever alterações locais da referência ou conteúdo editorial existente.
- Lint, build e testes pertinentes executados; falhas e limitações declaradas, sem apresentar check não executado como aprovado.
- Dados compatíveis com a etapa anterior; qualquer migração tem versão e plano de retorno.
- Segurança, acessibilidade e dimensões reservadas implementadas junto com o componente.
- Comparação visual e funcional T24 nas etapas que alteram apresentação/interações, sem aceitar mudanças não solicitadas sob o rótulo de melhoria.
- Instruções suficientes para reproduzir a verificação em outro checkout.

Rollback reverte o código para um artefato compatível. Não restaurar automaticamente um banco antigo, pois isso apagaria publicações recentes. Importação institucional e alterações editoriais não devem ocorrer como efeito colateral de todo deploy.

## Histórico local de integração

| Etapa | Branch | Merge / evidência |
| --- | --- | --- |
| Spec | `docs/spec-tema-iotnest` | `2e2014e` |
| 01 | `feat/01-fundacao-wordpress` | `7bb858b` — [evidência](../../../evidencias/etapa-01.md) |
| 02 | `feat/02-campos-nativos` | `5f819e6` — [evidência](../../../evidencias/etapa-02.md) |
| 03 | `feat/03-identidade-navegacao` | `cdd042e` — [evidência](../../../evidencias/etapa-03.md) |
| 04 | `feat/04-home-institucional` | `a9474d2` — [evidência](../../../evidencias/etapa-04.md) |
| 05 | `feat/05-blog-cases-paginas` | `f267f3d` — [evidência](../../../evidencias/etapa-05.md) |
| 06 | `feat/06-interacoes-componentes` | `396434e` — [evidência](../../../evidencias/etapa-06.md) |
| 07 | `feat/07-contato-integracoes` | `eebfe5f` — [evidência](../../../evidencias/etapa-07.md) |
| 08 | `feat/08-qualidade-publicacao` | Integração local na main após QA; commit identificável no histórico Git — [evidência e matriz final](../../../evidencias/etapa-08.md). |

[Manual de edição](../../../edicao.md) · [Desenvolvimento](../../../desenvolvimento.md) · [Operação, instalação e PRD](../../../operacao.md).
