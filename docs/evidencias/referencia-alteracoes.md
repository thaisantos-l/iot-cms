# Referência atualizada durante a execução

Em 17/11/2026 a verificação identificou alterações em `index.html`, `cookies.html`, `privacidade.html` e `termos.html` após a captura inicial. O Git mantém os mesmos cinco HTMLs previamente modificados e o mesmo HEAD; CSS, JS, imagens e demais arquivos mantêm os hashes originais. A conversão não escreveu no repositório do front.

Na referência atual, os links de Cases no header/footer foram comentados e a seção de cases da home foi ocultada. Os links do Blog continuam comentados. O snapshot inicial foi preservado em `referencia-front-inicial.json`; `referencia-front.json` registra a referência atual usada nas comparações seguintes. Nenhuma alteração do usuário foi restaurada ou descartada.

A fixture visual passa a usar zero cases públicos, permitindo que a mesma regra de disponibilidade já implementada produza os menus correspondentes. Os testes funcionais continuam cobrindo a exibição após publicar o primeiro case e sua retirada quando não há publicação pública.

## Atualização de 18/11/2026

Nova alteração externa somente em `index.html`: seção Depoimentos comentada, Vídeos sem `section--tight-top`, fechamento residual `-->` após Vídeos removido. Snapshot anterior preservado em `referencia-front-2026-11-17.json`. O CMS acompanha a nova aparência: depoimentos importados desabilitados (dados preservados), espaçamento de Vídeos atualizado e nenhum texto residual. Nenhum arquivo do front foi escrito.
