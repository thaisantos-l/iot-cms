# Etapa 07 — Formulário e integrações externas

Situação: implementação e testes locais aprovados; aceite de envio real pendente. SMTP real permanece pendente de execução futura autorizada em PRD, único ambiente informado. Depende da etapa 06. Requisitos: R06, R07, R09. Branch sugerida: `feat/07-contato-integracoes`.

## Entregáveis

- Criar handler próprio para visitantes e autenticados, mantendo layout e envio HTML sem JS; integrar `fetch` como melhoria progressiva da mesma regra de negócio.
- Validar nome/e-mail, limites de tamanho, nonce, honeypot, frequência e cabeçalhos. Destinatário vem das configurações autorizadas; visitante só pode compor `Reply-To` validado.
- Integrar `wp_mail()` ao plugin SMTP definido no ambiente. Não guardar credenciais no código e não registrar submissões no banco por padrão.
- Criar estados acessíveis, área de mensagens reservada, preservação de campos em falha e prevenção de duplicação. Não informar entrega na caixa postal apenas porque `wp_mail()` retornou sucesso.
- Implementar adapters de fornecedores permitidos, IDs validados e flags. Novas integrações de analytics ficam desligadas por padrão; os recursos existentes preservam a apresentação e o comportamento da referência, respeitando o contrato de consentimento. YouTube mantém a mesma thumbnail/apresentação, com arquivo local e carregamento sob demanda; modal controla foco e reprodução sem redesign.
- Integrar consentimento quando exigido para os fornecedores habilitados, evitando tags duplicadas. Não criar editor livre de `<script>` no painel.

## Aceite e verificações

T17–T19: envio válido com/sem JS, recebimento SMTP real, falha, timeout, nonce vencido, preenchimento inválido e clique repetido. Validar o formulário também com cache preenchido: páginas antigas não podem deixá-lo permanentemente inválido por nonce vencido; definir renovação segura ou política de cache compatível.

Flag desligada/consentimento negado não emite tags nem requisições ao fornecedor. Revogação impede novas ativações. Testar teclado, fechamento e retorno de foco do modal; resultados e mensagens respeitam o orçamento de estabilidade.

## Commits e merge

1. `feat: enviar contato com wp_mail e validacao no servidor`
2. `feat: administrar integracoes por flags e provedores`
3. `test: validar SMTP consentimento e falhas de envio`

Merge com serviço verificado em homologação. Se credenciais ainda faltarem, registrar a etapa como pendente de aceite de envio. Rollback conserva configurações e pode desligar adapters; não altera SMTP ou banco editorial automaticamente.

## Execução local em 18/11/2026

Branch criada da main `396434e`. Commits `6212fa3` (servidor/adapters), `0fb15eb` (formulário/mídia) e `0738886` (testes). Artefato limpo do último commit instalado em WordPress descartável: bateria PHP aprovada, 12/12 testes Chromium, cinco diffs de contato com zero pixels divergentes e 12 amostras CLS iguais a zero. Lint PHP/WPCS/JS/Sass e build aprovados. [Evidências](../../../evidencias/etapa-07.md).

Merge local `eebfe5f`. O checkpoint permitiu concluir os contratos independentes da etapa 08. Não representa aceite de SMTP: plugin/provedor, remetente, credenciais e destinatário de teste precisam ser definidos e o recebimento medido em PRD numa execução futura autorizada. Formulário permanece desligado por padrão até essa configuração. Não houve envio real ou publicação remota.
