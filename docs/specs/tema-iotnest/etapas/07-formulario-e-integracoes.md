# Etapa 07 — Formulário e integrações externas

Situação: planejada. Depende da etapa 06. Requisitos: R06, R07, R09. Branch sugerida: `feat/07-contato-integracoes`.

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
