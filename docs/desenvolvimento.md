# Desenvolvimento do iotnest

O CMS contém o tema PHP `wp-content/themes/iotnest` e o plugin próprio `wp-content/plugins/iotnest-core`. `../iotnest-site` é referência somente de leitura. O site final é renderizado pelo WordPress; não há um segundo front para publicar.

Contrato confirmado: PHP **8.2**, WordPress **7.1**, somente PRD. O QA local usa PHP XAMPP 8.2.4 e WordPress 7.1.4 em instalação descartável. Node 22.17.0, npm 10.9.2 e Composer 2.8.9 são ferramentas de desenvolvimento/CI; o destino não precisa de Node ou Composer. Outras séries de PHP/WordPress não estão homologadas. Não houve push, execução de Actions ou deploy remoto nesta entrega.

## Instalar, compilar e empacotar

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

Neste XAMPP, executar Composer com `/Applications/XAMPP/xamppfiles/bin/php /usr/local/bin/composer` e usar `PHP_BIN=/Applications/XAMPP/xamppfiles/bin/php` para integração. Os lockfiles são parte do contrato do build.

`npm run dev` recompila as fontes do CMS. Sass usa `@use`, preservando a cascata visual da referência. Esbuild gera bundles comum, home e administrativo. Os módulos de navegação, tabs/MES, etapas, carrosséis, calculadora, mídia, movimento, contato e consentimento inicializam por elemento e aceitam componentes ausentes. Textos e configurações editoriais vêm do HTML/JSON produzido pelo PHP.

Assets recebem hash no nome e manifesto por pacote. Os dois diretórios `assets/dist` são versionados junto com as fontes, permitindo atualizar tema/plugin por `git pull` sem Node ou ZIP na hospedagem. Ao alterar Sass/JS, execute `npm run build` e inclua os arquivos gerados e os manifestos no mesmo commit. O CI recompila e recusa divergências entre fontes e assets versionados. `.gitattributes` mantém LF para preservar os hashes também no Windows. O loader não emite URLs quando o manifesto/arquivo está ausente e mostra aviso ao administrador. WordPress, banco e uploads continuam sendo instalados separadamente.

O build define `0755` nos diretórios `assets/dist` e `0644` nos manifestos e bundles, inclusive com uma umask restritiva. Isso permite que Apache/PHP leia os arquivos quando roda com outro usuário, sem conceder escrita pública. Não remover essa normalização ao publicar o diretório temporário criado por `mkdtemp` (`0700`).

`npm run package` exige checkout limpo e gera `artifacts/iotnest-COMMIT.zip` e `.zip.sha256`. `--allow-dirty` identifica explicitamente um pacote de desenvolvimento e não é aceito na promoção. O formato 2 contém somente runtime de tema/plugin, mídias institucionais próprias para importação explícita, assets compilados e `release.json` com hashes, commit, schema 1 e contrato PHP/WordPress. O empacotador gera a identificação de release dos dois componentes. Datas ZIP fixas permitem reprodução. Fontes Sass/JS, tooling, core, plugins de terceiros, banco, credenciais e uploads não são entregues.

## Validar o ZIP em WordPress isolado

```sh
npx playwright install chromium
iotnest_sha=$(git rev-parse HEAD)
PHP_BIN=/Applications/XAMPP/xamppfiles/bin/php npm run test:integration -- "artifacts/iotnest-${iotnest_sha}.zip"
npm run reference:check
```

O runner exige Docker. Cria MySQL com imagem fixada por digest, credenciais aleatórias e porta restrita a 127.0.0.1; copia somente o core local para um diretório temporário e instala o **ZIP**, sem fontes ou Node nesse destino. Não copia `wp-config.php`, banco, uploads ou plugins da instalação editorial. Sem core local, obtém WordPress 7.1.4 do distribuidor oficial; `IOTNEST_WP_SOURCE` pode indicar outro diretório de core para QA. O Yoast 28.9 oficial é obtido separadamente, com SHA-256 fixado no runner, para exercitar a integração real.

Banco, usuários e fixtures existem somente nessa instalação descartável. Guardas de diretório, marcador, constante e prefixo de banco impedem executar os testes PHP contra o CMS editorial. O transporte de e-mail de QA intercepta `wp_mail()` somente nesse ambiente: testa o handler sem enviar correspondência. O runner verifica diagnósticos PHP/WordPress e remove seu container e diretório ao terminar.

A bateria cobre modelo/campos, revisões/preview/autosave, permissões, importação repetível, menus, estados públicos, consultas, contato e invalidação. O navegador cobre editor e front, zero/um/vários itens, teclado, ausência de JS/APIs, SEO real, interações, T24 e CLS. O teste de release promove o ZIP e versões sintéticas compatíveis/incompatíveis para verificar troca, retenção de hashes, rejeição de corrupção e rollback com edição recente preservada. Não é execução de produção.

Para um recorte, acrescentar argumentos Playwright depois do ZIP, por exemplo `tests/e2e/seo.spec.mjs --output artifacts/seo`. Para outros engines, instalar `firefox`/`webkit` e usar `IOTNEST_BROWSER=firefox` ou `webkit`. `IOTNEST_BROWSER_CHANNEL=chrome` seleciona o Chrome instalado. Os testes de CLS usam CDP e são executados em Chromium. WebKit automatizado não certifica Safari nativo ou iOS.

`npm run reference:check` confere commit, estado Git e SHA-256 dos 83 arquivos versionados do front contra `docs/evidencias/referencia-front.json`. Investigar divergências sem restaurar arquivos automaticamente. O snapshot conserva os cinco HTMLs modificados pela responsável. T24 compara a referência servida diretamente, nas cinco larguras exigidas; fixtures ficam no QA. Hashes não substituem comparação visual. No CI sem o repositório de referência, esses testes são explicitamente pulados; as evidências locais registram sua execução real.

CLS é medido por janelas de sessão, com três repetições de cache frio/quente, viewport/rede/CPU registrados e carregamento/interações. Relatórios e screenshots completos ficam em `artifacts/` (ignorado); evidências selecionadas ficam em `docs/evidencias/`. Não afirmar resultado de campo ou extrapolar o ensaio local para PRD.

## Instalação, edição e operação

Para editar conteúdo, consultar [o manual de edição](edicao.md). Para preparar o ambiente, importar conteúdo, configurar SMTP/CMP/cache, promover o artefato e retornar uma versão, seguir [o runbook de operação](operacao.md).

Ativar/reativar tema e plugin não importa, publica ou sobrescreve conteúdo. A importação institucional é uma ação explícita, repetível e preserva edições/limpezas/tombstones. A configuração inicial publica home/blog; documentos legais continuam em rascunho para revisão. Blog/cases não recebem os exemplos da referência. Sem o plugin próprio, o tema apresenta fallback e aviso administrativo em vez de fatal.

CI faz lint/build/testes e arquiva o ZIP. Deploy e rollback são manuais, na main e no ambiente protegido `production`, com promoção dos mesmos bytes aprovados. Não há rebuild no servidor, importação automática ou restauração de banco no rollback. Infra fornece SSH, caminhos, hook FPM/OPcache, cache, SMTP, CMP e agendador. Esses componentes exigem teste no ambiente correspondente antes de receber aceite.

## Dependências e limitações

As auditorias executadas em 18/11/2026 (`npm audit`, `npm audit --omit=dev` e `composer audit`) não reportaram vulnerabilidades nas dependências instaladas. Os JSONs datados estão nas [evidências da etapa 08](evidencias/etapa-08/). Isso é o resultado daquela consulta, não garantia permanente; manter os lockfiles e repetir a auditoria antes de atualizar/publicar. O aviso transitivo registrado na etapa 01 não aparece mais na resposta atual do registro.

A implementação local não representa aceite de recebimento SMTP, cache real em até dez segundos, agendador operacional, Safari nativo ou infraestrutura de produção. Esses limites estão registrados na matriz final; não marcar a spec integralmente aceita antes das verificações obrigatórias externas.
