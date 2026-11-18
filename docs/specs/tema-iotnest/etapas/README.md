# Etapas, commits e merges

As etapas **01, 02, 03 e 04 estão implementadas e validadas localmente** até 18/11/2026, nos respectivos recortes; 05–08 permanecem planejadas. A especificação foi integrada à `main` no merge documental `2e2014e`. Cada etapa registra commits e evidências; nenhuma publicação remota foi feita.

## Sequência de PRs

| Etapa | Documento | Depende de | Resultado revisável |
| --- | --- | --- | --- |
| 01 | [Fundação](01-fundacao.md) | Spec | Validada: tema/plugin instaláveis, T01/T22 e build reproduzível. |
| 02 | [Campos e painel](02-campos-e-painel.md) | 01 | Validada: campos, revisões, preview, autosave e permissões. |
| 03 | [Identidade e navegação](03-identidade-e-navegacao.md) | 02 | Validada localmente: header/footer, menus, identidade e T24 de navegação. |
| 04 | [Home e importação](04-home-e-importacao.md) | 03 | Conteúdo institucional em PHP, com importação repetível. |
| 05 | [Blog, cases e páginas](05-blog-cases-e-paginas.md) | 04 | Publicações e rotas completas, respeitando estados. |
| 06 | [Interações](06-interacoes.md) | 05 | Comportamentos modulares, acessíveis e estáveis. |
| 07 | [Formulário e integrações](07-formulario-e-integracoes.md) | 06 | Contato com SMTP e scripts controlados por flags. |
| 08 | [Qualidade e publicação](08-qualidade-e-publicacao.md) | 07 | SEO, cache, evidências e pacote pronto para produção. |

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
