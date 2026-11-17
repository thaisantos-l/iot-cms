# Etapa 01 — Fundação do tema, plugin e build

Situação: planejada. Depende da especificação. Requisitos: R03, R04 e base para R01/R10. Branch sugerida: `feat/01-fundacao-wordpress`.

## Entregáveis

- Criar tema `iotnest` com identificação, bootstrap, templates mínimos, suportes nativos e hooks WordPress; criar plugin próprio `iotnest-core`, sem instalar bibliotecas de campos.
- Separar namespaces/prefixos e loaders; tratar ausência do plugin próprio sem erro fatal e com aviso administrativo.
- Configurar Dart Sass e esbuild, lockfiles, scripts de desenvolvimento/build, manifesto com hashes e pacote de tema/plugin com assets compilados.
- Configurar ferramentas PHP e lints apropriados, com versões registradas. Confirmar PHP do XAMPP e de produção, core WordPress e Node do CI antes de fixar compatibilidade.
- Ajustar `.gitignore` para permitir tooling, testes e `.github`, preservando exclusões de uploads, core, banco, segredos e dependências de desenvolvimento. Não versionar `wp-config.php`.
- Criar o CI inicial de PR para validar o código e gerar o artefato; documentar instalação e ativação em ambiente local/homologação.
- Registrar o estado da referência, incluindo alterações locais, para as comparações T24. Trabalhar somente no CMS; não modificar arquivos do front nem introduzir um desenho novo na base do tema.

## Aceite e verificações

T01: instalação sem conteúdo próprio abre sem fatal/notices e sem requisições a assets ausentes. Desativar o plugin não derruba o tema. T22: um checkout limpo instala as dependências pelos lockfiles, compila e gera pacote funcional sem Node no servidor de destino. Verificar que arquivos de ambiente e uploads continuam ignorados.

Nenhum texto institucional ou publicação é criado nesta etapa. O template mínimo é suficiente para validar a base, sem apresentar a home definitiva como concluída.

## Commits e merge

1. `feat: adicionar base do tema e plugin iotnest`
2. `build: configurar Sass JavaScript e pacote de deploy`
3. `ci: validar build e padroes do projeto`

Merge após verificações acima. Guardar o artefato identificado pelo commit. Rollback remove/reverte somente o código desta base; não altera conteúdo da instalação.
