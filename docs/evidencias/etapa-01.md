# Etapa 01 — evidências locais

Data: 17/11/2026. Código testado: `fab2247fe8c48802185963266866afca4b6b6108`, na branch `feat/01-fundacao-wordpress`. Escopo: fundação, T01 e T22. Não é o aceite da conversão completa.

| Verificação executada | Resultado |
| --- | --- |
| `composer validate --strict`, sintaxe PHP e WordPress Coding Standards | Aprovados. |
| `npm run lint` (ESLint e Stylelint) | Aprovado. |
| `npm run build` no workspace e em checkout limpo | Manifestos e todos os assets byte a byte idênticos. |
| `npm run test:js` | 5/5: componentes 0/1/vários, idempotência, DOM pronto, pacote determinístico, manifesto inválido e exclusões Git. |
| Integração PHP com WordPress real | 9/9 verificações: tema, ativação/reativação, conteúdo vazio, home/opções e rascunho/metadados preservados. |
| Playwright Chromium 159.0.8154.6 | 3/3: home sem campos e assets, plugin desativado/aviso administrativo, manifesto ausente. Sem erros JS, requisições falhas ou diagnósticos PHP. |
| `actionlint` 1.7.12 | Workflow inicial aprovado. GitHub Actions não executado remotamente. |
| `npm run reference:check` | 83 arquivos com SHA-256, commit e estado Git idênticos à captura inicial. |
| `npm audit --omit=dev` / Composer audit na instalação | Sem vulnerabilidades de runtime reportadas. Aviso transitivo de tooling detalhado no runbook. |

Reprodução de T22: worktree novo em `/tmp/iotnest-stage01-fab2247`, sem core, `node_modules`, `vendor` ou assets iniciais. Executados `npm ci --ignore-scripts`, `composer install`, validação, lints, build, testes Node, pacote e `npm run test:integration`. O runner baixou WordPress 7.1.4 e instalou o ZIP em outro diretório temporário com MySQL isolado. O destino recebeu apenas PHP/assets compilados do projeto. Runtime: PHP XAMPP 8.2.4; Node do build 22.17.0; npm 10.9.2; Composer 2.8.9. O PHP embutido serviu HTTP; não foi alegado teste do Apache XAMPP, que estava parado.

Pacote preservado em `artifacts/iotnest-fab2247fe8c48802185963266866afca4b6b6108.zip`, acompanhado do SHA-256 em `.zip.sha256`. Contém 19 arquivos de runtime e `release.json`. Diretório de artefatos é ignorado pelo Git. O pacote não instala core/plugins de terceiros nem altera banco/uploads.

O aviso de `ABSPATH` encontrado na primeira execução era da configuração temporária do runner; corrigido antes deste commit. A execução final verificou também o log PHP do servidor e o `debug.log` do WordPress, ambos sem diagnósticos.

Referência: commit `b3e37ec`, com mudanças locais preservadas em `index.html`, `cases.html`, `cookies.html`, `privacidade.html` e `termos.html`. Captura: [referencia-front.json](referencia-front.json). Nenhum arquivo da referência foi editado, e nenhum conteúdo editorial do CMS existente foi acessado para gravação.

R14 nesta etapa cobre a preservação da fonte e a ausência de redesign na base. T24 visual/comportamental, CLS, SMTP, atualização com cache em até 10 segundos e homologação/produção não foram medidos ou declarados aprovados. A home mínima é apenas a fundação prevista na spec.
