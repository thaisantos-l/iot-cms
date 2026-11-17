# Desenvolvimento do iotnest

O CMS contém o tema PHP `wp-content/themes/iotnest` e o plugin próprio `wp-content/plugins/iotnest-core`. `../iotnest-site` é referência somente de leitura. Não há front separado para publicar.

Contrato confirmado em 17/11/2026: PHP **8.2** e WordPress **7.1** em produção. Haverá somente PRD; QA usa uma instalação local descartável, sem autorização de deploy nesta execução. A validação local usa PHP XAMPP 8.2.4 e core 7.1.4. Headers exigem PHP ≥ 8.2 e WordPress ≥ 7.1; outras séries não estão homologadas. Node 22.17.0, npm 10.9.2 e Composer 2.8.9 são ferramentas de desenvolvimento, sem necessidade de Node/Composer no destino.

## Instalar e compilar

```sh
npm ci --ignore-scripts
composer install --no-interaction --prefer-dist
composer validate --strict
composer lint
npm run lint
npm run build
npm run test:js
npm run package
```

No XAMPP deste workspace, `php` não está no PATH. Executar Composer com `/Applications/XAMPP/xamppfiles/bin/php /usr/local/bin/composer`. Para testes HTTP, definir `PHP_BIN=/Applications/XAMPP/xamppfiles/bin/php`.

`npm run dev` recompila fontes do CMS quando mudam. Sass usa `@use`; esbuild gera os bundles comum, home e administrativo. Os módulos ainda sem comportamento nesta fundação serão preenchidos nas etapas correspondentes. A home mínima não representa o desenho final. Nenhum conteúdo institucional é criado nesta etapa.

Assets recebem SHA-256 abreviado no nome e manifesto por pacote. `assets/dist` não é versionado. O loader não gera URLs quando o manifesto/arquivo está ausente; um aviso no painel orienta instalar o pacote completo. Um checkout sem build não é um release.

`npm run package` exige checkout limpo e gera `artifacts/iotnest-COMMIT.zip` e checksum. `--allow-dirty` cria um pacote identificado como `dirty`, exclusivamente para desenvolvimento. O ZIP inclui apenas runtime do tema/plugin e `release.json` com hashes de todos os arquivos. O manifesto é validado; arquivos compilados não podem ser substituídos sem atualizar seu hash. Fontes de desenvolvimento, core, credenciais, banco e uploads ficam fora. Datas ZIP são fixas para reprodução do pacote.

## Validar o artefato

```sh
npx playwright install chromium
iotnest_sha=$(git rev-parse HEAD)
PHP_BIN=/Applications/XAMPP/xamppfiles/bin/php npm run test:integration -- "artifacts/iotnest-${iotnest_sha}.zip"
npm run reference:check
```

O runner de integração exige Docker. Cria MySQL temporário com imagem fixada por digest, credenciais aleatórias e porta restrita a 127.0.0.1, copia somente o core local para um diretório temporário e instala o **ZIP**, sem fontes, tooling ou Node nesse destino. Não lê/copía `wp-config.php`, banco, uploads ou plugins de terceiros da instalação existente. Em checkout sem core, obtém WordPress 7.1.4 do distribuidor oficial. `IOTNEST_WP_SOURCE` permite indicar uma instalação de core para QA. O runtime PHP serve HTTP em porta livre.

O banco é criado do zero. Apenas nessa instalação descartável o runner remove os exemplos nativos e cria/remove fixtures. Ativação e reativação do código de entrega não criam, atualizam ou removem conteúdo editorial. Guardas de diretório, marcador, constante e prefixo de banco impedem executar os testes PHP contra o CMS editorial. Ao terminar, o runner remove seu container e diretório temporário.

Os testes verificam ativação, preservação de rascunho/metadados existentes, ausência de importação implícita, home sem campos, assets HTTP, console, foco do skip link, fallback com plugin desativado, aviso administrativo e manifesto ausente. Erros/notices/warnings PHP fazem o runner falhar. Traces de falha ficam em `test-results/`, ignorado no Git.

`npm run reference:check` compara commit, estado Git e SHA-256 dos 83 arquivos versionados do front com `docs/evidencias/referencia-front.json`. Se houver diferença, investigar a alteração; nunca restaurar arquivos automaticamente. A captura inicial preserva os cinco HTMLs locais modificados. Screenshots e comparação de interações T24 entram nas etapas visuais; hashes não substituem T24.

## Instalação manual e retorno

Em uma cópia descartável WordPress 7.1/PHP 8.2, conferir o checksum, extrair em diretório de release e copiar **somente** `wp-content/themes/iotnest` e `wp-content/plugins/iotnest-core`. Ativar iotnest Core em Plugins e iotnest em Aparência. Não copiar `release.json` para core nem sincronizar toda a raiz/`wp-content` com exclusão. A ativação não define home, não cria posts/cases, não importa conteúdo e não faz flush global de rewrite.

Sem o plugin, o tema mantém o fallback mínimo e informa a dependência ao administrador. Para retornar, desativar o plugin e restaurar somente o código anterior ou selecionar o tema anterior. Não restaurar banco nem apagar metadados. O fluxo coordenado de deploy/rollback será finalizado na etapa 08.

O CI inicial executa lint, build, testes e instalação isolada do artefato em PR/push localmente preparado para `main`. Actions estão fixadas por SHA; permissões são de leitura. O workflow não publica, não usa secrets de produção e ainda não foi executado no GitHub nesta entrega local.

## Dependências e limites atuais

As versões transitivas estão nos lockfiles. `npm audit --omit=dev` não encontra dependências vulneráveis de runtime. A auditoria completa em 17/11/2026 aponta ReDoS em `picomatch` transitivo do tooling Sass/Stylelint, sem correção disponível no registro consultado (11 entradas afetadas por propagação). O projeto só passa globs fixos do próprio repositório a essas ferramentas; elas não são entregues ao WordPress. Acompanhar [GHSA-fc5q-39xc-vjpf](https://github.com/advisories/GHSA-fc5q-39xc-vjpf) e atualizar o lockfile quando houver correção. Não aplicar o downgrade automático sugerido pela auditoria para Stylelint 9. A dependência de empacotamento `fflate` foi fixada em 0.8.3, corrigindo o aviso da 0.8.2.

Não há validação de SMTP, cache real, prazo editorial de 10 segundos ou CLS nesta fundação. Esses aceites dependem das etapas específicas e dos ambientes correspondentes.
