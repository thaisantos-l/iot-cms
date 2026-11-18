# Etapa 08 — SEO, qualidade e entrega

Execução local em 18/11/2026, na branch `feat/08-qualidade-publicacao`, criada da main `eebfe5f`. Implementação e QA local concluídos: rodada integrada 81/81 e revisão focada aprovadas. O aceite integral permanece aberto para verificações que exigem PRD e Safari nativo. Somente PRD foi informado e nenhuma publicação foi autorizada nesta execução.

## Código entregue

- `511d6a1`: Yoast, breadcrumbs com uma fonte, análise editorial dos campos próprios, sitemap/robots, saúde de release e purge após a gravação completa.
- `3ca1447`: paleta completa editável, preservando todos os valores iniciais; modal respeita a flag de movimento.
- `81c899c`: workflows manuais, verificação do artefato, promoção atômica coordenada e rollback sem restaurar banco.
- `372d076`: limite do corpo HTTP comporta campos Unicode válidos, inclusive expansão de `form-urlencoded`.
- `1c385ca`: testes reais de Yoast/paleta/cache/contraste e contrato de release.
- `a781efb`: SEO institucional inicial somente para páginas novas, com reimportação preservando campos apagados.
- `c785bb7`: manual editorial.
- `f59d71d`: nome acessível do MES com um único painel e teste de zoom nativo a 200%.

Arquivos principais: `wp-content/plugins/iotnest-core/includes/{seo,cache,health,importer}.php`, `wp-content/plugins/iotnest-core/assets/src/js/yoast.js`, `wp-content/themes/iotnest/inc/{breadcrumbs,customizer}.php`, `ops/release.php`, `ops/promote.mjs`, `.github/workflows/`, `tests/` e manuais em `docs/`.

O Yoast 28.9 oficial é instalado separadamente no ambiente, nunca incluído no ZIP. SHA-256 do download utilizado: `1f1af9d3dff70288b37e6edc04e27cff4979b258f62960c0b91c092614bf2301`. A análise testada é a do plugin real, incluindo atualização antes de salvar e exclusão de seções desabilitadas. Não foi substituída por um mock.

Cache: alterações são agrupadas até o fim da requisição, incluindo metadados, taxonomias, menus, opções e status. O adapter deve confirmar purge de todas as camadas. Sem configuração e evidência do motor real, HTML continua `no-store`; preview/admin/contato também permanecem privados. Essa política local não comprova o prazo de dez segundos em PRD.

Entrega: ZIP determinístico de runtime, schema 1, PHP 8.2/WP 7.1, checksums e identificação carregada pelos dois componentes. Um único symlink troca tema/plugin em conjunto; hashes antigos ficam em pool persistente. Promoção verifica CI/commit/checksum e saúde; rejeita pacote corrompido e reverte falha. O ensaio T23 usou o ZIP real A e versões B/C sintéticas para verificar troca/erro, retenção de assets, edição recente e upload preservados. Nenhum SSH/rsync remoto foi executado.

## Verificações

A bateria integrada instalou o ZIP limpo `c785bb78cc242eb67de12bdae8ebafe4fcf49361`, com PHP 8.2.4, WordPress 7.1.4 e Chromium 159.0.8154.6, em WordPress descartável. **81/81 testes de navegador aprovados, nenhum pulado**, além de 201 asserções PHP. Não houve diagnósticos PHP/WordPress. Duração do navegador: 17,1 minutos. [Índice com hashes e resultados](etapa-08/integrada.json).

CLS: 12 amostras da home, 12 de contato e 12 de navegação, **todas iguais a 0**, com três repetições frio/quente a 390/1440px, rede 150ms/1,6Mbps/0,75Mbps e CPU 4×. Home inclui carga, rolagem e interações; contato inclui resposta atrasada além da janela de entrada recente. São medições por janelas de sessão em laboratório, sem alegação de dado de campo. T24 da rodada integrada aprovou 80 componentes da home, 80 estados interativos, 15 componentes de navegação, 25 páginas internas e cinco formulários, além de movimento real/Lenis/vídeo/Dadá.

Checkout novo no commit `81cd560`: instalação por lockfiles, Composer validate/lint, ESLint/Stylelint, build e sete testes Node aprovados. O ZIP recompilado coincide **byte a byte** com o original. Seis testes focados passaram (fundação, MES 0/1/7, T24 interativo a 360px e T23). O sétimo, zoom nativo, inicialmente revelou conflito da emulação de viewport CDP com o zoom de aba; foi corrigido somente no harness e passou no **mesmo ZIP**. Não houve correção de CSS ou layout para ajustar a asserção. [Registro do checkout](etapa-08/checkout-limpo.json).

Zoom real a 200% acionado por `chrome.tabs.setZoom`: ambas as implementações apresentaram viewport CSS 720×406, DPR 2 e título da calculadora de 28,56px; menu por teclado, retorno de foco e cálculo operáveis. A captura usa CDP sem recorte de coordenadas emuladas. [Medições](etapa-08/zoom.json), [WordPress](etapa-08/wordpress-200.png), [referência](etapa-08/reference-200.png).

Os três workflows passaram em actionlint 1.7.12. Auditorias npm completa/runtime e Composer de 18/11 não reportaram vulnerabilidades. Os resultados estão nos JSONs desta pasta; GitHub Actions não foi executado remotamente.

Rodadas específicas anteriores: SEO público com Yoast ativo/inativo aprovado; cinco testes de qualidade/análise editorial/Unicode aprovados no artefato `1c385ca`; T23 aprovado no `81c899c`. Chrome 158.0.8127.37, Firefox 159.0 e WebKit 27.2 tiveram sete testes cada, incluindo contato com/sem JS, modal, teclado, zero/um/sete itens e T24 de interações a 360px. [Versões, recortes e resultados](etapa-08/navegadores.json).

Contraste medido: branco sobre a cor de marca original `#02afef` tem razão 2.506:1, abaixo de AA. A cor foi preservada por R14, sem declarar conformidade AA. A comparação de reflow a 200% desta rodada usa viewport CSS equivalente; o teste adicional de zoom nativo é registrado separadamente.

Safari nativo 18.6 não foi testado: `safaridriver` recusou criar sessão porque a automação remota está desabilitada. Preferências do usuário foram preservadas. Safari mobile não estava disponível. Aprovação em WebKit não será apresentada como aprovação desses navegadores.

As decisões R14 de 18/11 estão fechadas: erro da calculadora na posição original e alturas originais de “Como funciona”. A pequena diferença de rasterização da imagem arredondada em “Como funciona” é classificada nas evidências anteriores; geometria e texto são comparados separadamente, sem tolerância ampla que esconda redesign.

## Instalação editorial local

O site local foi aberto em Chromium a 390/1440px, sem erros JS, requests falhos ou imagens carregadas quebradas; assets retornaram 200. Home/blog/cases retornaram 200 e URL inexistente retornou 404. [Registro do navegador](etapa-08/local-browser.json).

O site está em `http://localhost:8082/iotnest/cms/`, com tema próprio, Core e Yoast ativos. A importação explícita criou 30 mídias, cinco páginas, configurações e três menus; a segunda execução não criou duplicações. Home/blog foram publicados; privacidade, cookies e termos permanecem em rascunho para revisão, conforme o contrato da etapa 04. Blog e cases têm zero itens publicados. Formulário desligado até SMTP validado.

Antes de gravar, foi realizado backup das 12 tabelas WordPress fora da raiz pública, com arquivo 0600 e diretório 0700, em `~/Library/Application Support/iotnest/backups/20261118-190222-ba82e7/wordpress.sql`. SHA-256: `dc64e7e957bf9e6a639503f76c6ed9153db11467be61c8a9c6be578f0fd0a820`. Banco e credenciais não são versionados. Registros preexistentes foram preservados; somente o post nativo Hello World intacto foi retirado da publicação pela regra documentada do setup.

Ajustes locais de ambiente: links permanentes `/blog/%postname%/`, bloco nativo WordPress no `.htaccess` antes ausente e permissão de escrita do Apache na nova pasta uploads. Upload HTTP nativo foi verificado com sessão temporária; attachment e sessão de teste foram removidos. Nenhum arquivo do core foi editado. SEO inicial foi adicionado apenas às cinco páginas criadas nesta execução e apenas em chaves ausentes; nome/descrição nativos do site foram preservados.

## Matriz final de aceite

| Cenário | Evidência local | Situação |
| --- | --- | --- |
| T01 | Ativação/reativação, tema sem campos, fallback, manifesto e HTTP sem diagnósticos. | Verificado localmente. |
| T02–T04 | Normalização PHP, campos principais/opcionais vazios, zeros válidos e regras no HTML. | Verificado localmente. |
| T05 | Coleções/repetidores zero/um/vários, incompletos, reordenação e interações 0/1/7. | Verificado localmente. |
| T06–T09 | Rascunhos, pendentes, privados, senha, lixeira, primeiro/último público, paginação, relacionados e menus. | Verificado localmente. |
| T10 | Não divulgação antes de publish; transição efetiva e purge verificados. | Executor e precisão do cron em PRD pendentes. |
| T11–T12 | Ordem/IDs, menus condicionais, logos extremos e paleta no HTML inicial sem build. | Verificado localmente. |
| T13 | Sem JS, movimento reduzido, teclado e APIs ausentes; alternativa HTML do contato. | Verificado localmente. |
| T14 | Revisão, restauração, autosave de campos e preview autorizado isolado. | Verificado localmente. |
| T15 | Importação repetida, edição/limpeza/tombstones e SEO preservados; blog/cases vazios. | Verificado localmente. |
| T16–T17 | Mídia removida, URLs inválidas, nonces, capacidades, sanitização e escape. | Verificado localmente. |
| T18 | Handler/HTTP, com/sem JS, falha, timeout, duplicação, Unicode e limites. | Recebimento SMTP real pendente; transporte de QA interceptado. |
| T19 | Flags, bridge ausente, consentimento negado/revogado e ausência de requests não autorizados. | Contrato local verificado; CMP escolhida depende do ambiente. |
| T20 | Yoast real, análise editorial, metadata/schema, breadcrumb, sitemap, robots, 404 e fallback. | Verificado localmente; domínio/indexação PRD pendentes. |
| T21 | Eventos completos/coalescidos, política de HTML e diagnóstico de purge. | Dez segundos sob cache real não medidos; pendente. |
| T22 | Build/pacote limpo, runtime sem Node e checksums determinísticos. | Verificado localmente; Actions remoto não executado. |
| T23 | Promoção/retorno locais, assets retidos, corrupção/falha rejeitadas e edição/upload preservados. | Verificado em QA; infraestrutura/FPM/SSH real pendentes. |
| T24 | Cinco larguras, home, navegação, páginas internas, contato, tabs e animações reais. | Verificado nos recortes registrados; Safari nativo/mobile pendentes. |

A entrega local encerra implementação, manuais e preparação operacional. **A spec não recebe aceite integral** enquanto faltarem recebimento SMTP, atualização medida no cache real, agendador/infraestrutura e navegador nativo previsto. Políticas precisam de revisão editorial antes da publicação. Esses itens não autorizam deploy implícito; a próxima execução de PRD exige instrução específica.
