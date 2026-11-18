# Etapa 07 — Contato e integrações

Implementação local em 18/11/2026, branch `feat/07-contato-integracoes` a partir de `396434e`. Commits `6212fa3`, `0fb15eb` e `0738886`. Artefato limpo `073888633b6c5bd5d7e431e74653b380564ef652`, PHP 8.2.4/WordPress 7.1.4 e Chromium 159.0.8154.6.

O plugin próprio concentra validação, destinatário autorizado, `Reply-To`, nonce vinculado à operação, renovação sem cache, honeypot, frequência e idempotência. HTML e fetch usam a mesma regra. Submissões não são persistidas; apenas estado operacional sem campos pessoais. Falhas mantêm os campos; timeout consulta a mesma operação, sem repetir o transporte. Diagnóstico público genérico e retorno positivo descrevem aceitação pelo serviço, não entrega na caixa postal.

YouTube só é incorporado sob demanda, com thumbnail local e o modal original. Flags e bridge de consentimento controlam solicitações. GTM prevalece sobre GA4 direto; revogação persiste a negativa antes de recarregar e eliminar código já executado. Sem dialog, o link autorizado continua disponível. Flags desligadas ou bridge necessário ausente impedem ativação do fornecedor.

## Verificações executadas

Lint PHP/WPCS, JS/Sass e build aprovados. Bateria PHP real validou visitante/autenticado, tipos, tamanhos, nonce, honeypot, cabeçalhos, destinatário, frequência, idempotência, falha, ausência de corpo no banco e configuração inválida.

**12/12 testes Chromium aprovados**, com transporte de e-mail interceptado exclusivamente no WordPress descartável. HTML sem JS, nonce vencido, fetch, falha, timeout após operação aceita, clique repetido, teclado/Escape/foco do modal, flags, CMP negada/revogada, ausência de dialog e não duplicação de tags. Fornecedores externos simulados; nenhuma mensagem real foi enviada.

T24 contato: cinco larguras 360/390/768/1024/1440, mesmos textos/fontes/mídias, **zero pixels divergentes**. Mensagens preservam a posição de layout do botão. O teste usa `offsetTop` para separar deslocamento de layout do `translateY(-1px)` de hover que já existe no front; o hover foi preservado.

CLS contato: 12 amostras, três repetições frio/quente em mobile e desktop, rede 150ms/1,6Mbps/0,75Mbps e CPU 4×. Resposta de erro retardada para além da janela de entrada recente. **CLS 0 em todas as amostras**. [Dados e capturas](etapa-07/). Evidência de laboratório, sem alegação de CLS de campo.

## Pendências reais

Recebimento SMTP, autenticação do domínio/remetente, destinatário autorizado, integração da CMP escolhida e comportamento sob cache real não receberam aceite de PRD. O responsável informou que só haverá PRD e proibiu deploy nesta execução. O checkpoint local não encerra esses aceites; o formulário nasce desligado até configurar e validar o transporte. Credenciais e plugin SMTP nunca entram no pacote próprio.
