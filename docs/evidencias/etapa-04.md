# Etapa 04 — Home e importação

Candidato local de 18/11/2026: `50b59aa3fe4d74babcbf65a678ae16bb6dfe17b8`. Branch `feat/04-home-institucional`, a partir do merge `cdd042e`. Sem publicação ou push. Aceite local do recorte da etapa 04 concluído.

## Implementação

- Pacote institucional independente do front: textos das vinte seções, trinta mídias com SHA-256, cinco páginas para revisão e três menus nativos. O estado inicial atual exibe dezessete seções: Blog/Cases sem registros e Depoimentos desabilitados conforme o front de 18/11.
- Importador explícito em Ferramentas → Importação iotnest; permissões, nonce, relatório, chaves estáveis e lock. Reexecução preserva alterações, limpezas, mídia removida, menus e opções. Nenhuma importação na ativação/deploy.
- Configuração da home separada da importação. Não substitui home existente; documentos legais continuam em rascunho. Somente o exemplo nativo intacto identificado por título, corpo e timestamps é despublicado nessa ação explícita.
- PHP prepara disponibilidade e itens válidos antes dos wrappers. Todas as etapas antes embutidas no JS estão no HTML; fallback sem JavaScript oferece os painéis restantes. Imagens usam attachments, dimensões e srcset.

Arquivos principais: `includes/importer.php`, `resources/seed/` no plugin; `inc/view-models.php`, `inc/template-tags.php` e `template-parts/sections/` no tema; `tests/php/import.php` e `tests/e2e/home.spec.mjs`.

## Comparação T24

Chromium, DPR 1, larguras 360/390/768/1024/1440, altura 1000. Mesmo conteúdo, fontes oficiais Inter carregadas e movimento reduzido; vídeos em poster para estabilizar a captura. A referência é servida somente para leitura. Imagens são comparadas com os mesmos bytes originais e densidade intrínseca; o produto conserva seu srcset WordPress.

Dezesseis seções visíveis não relacionadas ao formulário são isoladas igualmente nos dois documentos. Geometria e pixels ficam nos JSONs; capturas preservam ambos os lados. O próximo botão de “Como funciona” está desabilitado provisoriamente, conforme o contrato da etapa 04; somente seu retângulo é separado do diff. Essa exceção deve ser removida na etapa 06.

Cantos arredondados da imagem de “Como funciona”: DOMRect, imagem, raio, fundo, overflow, transform e opacity idênticos. A diferença de antialias é classificada somente na curva de raio 16px, em faixa radial de 1px, limitada a 160 pixels e delta máximo 50 por canal; não se exclui a imagem inteira. Demais pixels mantêm tolerância de 2 por canal. A diferença bruta permanece registrada.

## Limites conhecidos e próximos aceites

- Controles de tabs, carrossel, calculadora e mídia aguardam a etapa 06 e não são apresentados como comportamento final aprovado. Contato mostra canais; formulário funcional entra na etapa 07.
- A referência original também alcança scrollWidth 404 no viewport de 390px por imagens decorativas de hardware. O teste exige que texto longo não amplie esse overflow; não se redesenhou o componente.
- Capturas isoladas não comprovam CLS da home ou equivalência de animações. Medições da sessão/interações pertencem à etapa 06 e ao QA integrado; o resultado de navegação da etapa 03 não foi extrapolado.
- Textos legais e dados comerciais continuam sujeitos à revisão editorial. SMTP/cache reais permanecem pendentes para futura execução autorizada em PRD.

## Commits

`27d0bad` importador/pacote; `223edf2` renderização PHP; `87a47db` testes; `4cd132a` fallback e composição; `efa6d2c` normalização de imagens na comparação; `50b59aa` alinhamento à referência atual e classificação estrita da rasterização.

## Verificações executadas

Lints PHP/WPCS, JavaScript e Sass aprovados; cinco testes Node aprovados; todos os cenários PHP de instalação, campos, navegação e importação aprovados em WordPress 7.1.4/PHP 8.2.4 descartáveis. Vinte e quatro testes Chromium aprovados, sem diagnósticos PHP. O artefato instalado não contém core, fontes de build ou Node.

T24 institucional: oitenta comparações, geometria coincidente; nenhum pixel divergente fora do próximo botão provisório e da rasterização documentada. Cantos: 117 pixels em 1024px, 116 em 1440px; zero nas outras larguras. Capturas selecionadas e relatórios em `etapa-04/`. Regressões de navegação, fonte, identidade, teclado, preview e permissões aprovadas. Referência: 83 hashes conferidos após a atualização externa documentada.

Comando: `PHP_BIN=/Applications/XAMPP/xamppfiles/bin/php npm run test:integration -- artifacts/iotnest-50b59aa3fe4d74babcbf65a678ae16bb6dfe17b8.zip`.
