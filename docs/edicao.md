# Edição do site iotnest

O conteúdo fica no WordPress. Publicar uma edição não executa build nem GitHub Actions. O tema apresenta os dados; o plugin `iotnest-core` mantém campos e funcionalidades mesmo quando o tema é trocado. Não editar PHP, Sass ou arquivos do front para trocar textos, imagens, cores ou conteúdo institucional.

## Home e seções institucionais

Abra a página inicial em **Páginas**. O painel iotnest reúne conteúdo, ordem e habilitação das seções. Abra cada grupo para editar seus campos. Campos de texto rico aceitam a marcação permitida pelo WordPress; HTML perigoso, scripts, protocolos e IDs de fornecedores inválidos são rejeitados/normalizados no servidor.

Uma seção só aparece quando está habilitada e tem os campos principais válidos. Apagar seu título remove também seus links condicionais. Apagar um campo opcional remove somente esse elemento. Espaços, tags vazias e caracteres invisíveis não contam como conteúdo; zero numérico válido continua válido. O painel informa o motivo quando uma seção está indisponível.

Nos repetidores, use **Adicionar item**, remover e os botões de ordenação, inclusive por teclado. A identificação interna de cada linha permanece estável quando ela é movida. Linhas incompletas podem continuar salvas para edição, mas não criam cards/controles vazios no site. O limite aparece no próprio grupo: listas normalmente aceitam até 50 itens e FAQ até 100. O formulário editorial completo tem limite de 1 MiB; reduzir o conteúdo antes de salvar se esse limite for atingido. Uma submissão inválida preserva os dados anteriores.

Escolha mídia pela biblioteca. Campos de imagem/vídeo aceitam o tipo correspondente, sem digitar caminhos de servidor. Remover o arquivo da biblioteca também o torna indisponível no site; a seção ou elemento segue sua regra de ausência. Imagens são emitidas com dimensões e fontes responsivas. Hero, Dadá, módulos e cards conservam os espaços previstos no desenho.

A calculadora mantém a fórmula de perda mensal: valor da hora × horas/dia × dias/mês × (1 − OEE/100). OEE é de 0 a 100; meta não pode ser inferior ao atual; horas até 24; dias inteiros de 1 a 31; valores precisam ser finitos e a hora deve ser positiva. Textos, rótulos, mensagens e CTA são editáveis nos campos previstos. O cálculo é local ao navegador, sem enviar ou guardar dados da operação.

## Revisões, preview e publicação

**Pré-visualizar** mostra a versão de trabalho autorizada sem alterar a página pública. Revisões incluem os campos próprios; a restauração recupera o conjunto correspondente. O autosave conserva alterações desses campos mesmo quando o título/corpo nativos não mudaram. Publicar/atualizar confirma a versão pública. Se a gravação for recusada por permissão, nonce, tamanho ou estrutura inválida, corrigir o problema sem assumir que a alteração foi publicada.

Rascunhos, pendentes, agendados, privados e posts protegidos por senha não entram nas coleções públicas, nem quando o visitante está autenticado como administrador. Conteúdo agendado só aparece depois da transição efetiva para `publish`. A precisão do agendador e a atualização em até dez segundos dependem dos aceites operacionais descritos no runbook.

## Blog, cases e documentos

O blog usa **Posts**, categorias, imagem destacada, resumo e conteúdo nativos. Cases usam o menu próprio, setor, imagem e campos de empresa/solução/destaques/CTA. As listas, paginação, relacionados e tempo estimado de leitura se atualizam no servidor. Os dois começam sem exemplos importados.

Ao publicar o primeiro item elegível, a coleção aparece se sua seção estiver habilitada e titulada. Retirar o último item também retira os links condicionais e os grupos que ficariam vazios. Senha impede divulgação de cards/resumos/campos sensíveis; o acesso individual usa o formulário nativo de senha.

Páginas legais usam o template **Documento legal**. Edite os títulos e o corpo; headings H2 geram o sumário, com IDs únicos e links reais. Os textos importados precisam refletir as integrações que a organização efetivamente utiliza. Não transformar exemplos de artigos/cases do antigo front em publicações reais.

## Menus, identidade e configurações

Em **Aparência → Menus**, use as posições Principal, Mapa do site e Políticas. Os campos de destino iotnest permitem apontar para seção, coleção, página ou URL autorizada. Esses destinos acompanham disponibilidade e publicação, sem manter links para seções vazias. Um grupo sem filhos válidos desaparece quando não tem destino próprio válido.

Em **Personalizar → Identidade iotnest**, altere logo, logo do rodapé e paleta. Todos os valores iniciais conservam a referência. Logos ficam contidos na área reservada. As cores são impressas no HTML inicial, sem recompilar CSS. Conferir legibilidade e contraste após qualquer mudança; os valores originais não devem ser tratados como certificação automática de WCAG AA.

**Configurações → iotnest** contém dados globais, arquivos, CTAs, WhatsApp, destinatário/assunto de contato, flags de movimento/autoplay/Lenis/Dadá e integrações permitidas. Campos administrativos exigem `manage_options`; identidade exige `edit_theme_options`. Editores de conteúdo só gravam posts/páginas para os quais têm capacidade nativa. URLs, ícones, cores e IDs têm validação própria; não existe campo de script livre.

O formulário só aparece quando habilitado, com destinatário e títulos válidos. Configure o plugin SMTP e valide recebimento antes de habilitar. Falha conserva os campos do visitante. A confirmação significa encaminhamento ao serviço de envio. Credenciais ficam no plugin SMTP, nunca nestes campos.

YouTube mantém thumbnail local e carrega no clique. Sem flag ou bridge de consentimento requerido, não ativa o fornecedor. GA4/GTM exigem bridge e consentimento; ambos começam desligados. Se ambos forem habilitados, GTM é a fonte única. A CMP é responsabilidade do ambiente e precisa seguir [o contrato de operação](operacao.md).

## Yoast e suporte

Com Yoast instalado, use seus campos de título, descrição, canonical e indexação. O tema não duplica suas tags ou schema. A análise editorial recebe os textos dos campos próprios habilitados e acompanha a edição. Sua pontuação é uma ferramenta editorial, não uma prova do HTML público ou de posicionamento em busca. Sem Yoast, o título nativo e o site continuam funcionais.

Para falhas de envio/cache/publicação futura, consultar o responsável pelo ambiente e o [runbook](operacao.md). Não reinstalar o importador, restaurar banco antigo ou fazer deploy para resolver uma simples edição que ainda não apareceu. O importador é repetível e preserva conteúdo editado/apagado, mas não substitui backup. Não apagar diretamente seus registros operacionais para tentar recriar conteúdo.
