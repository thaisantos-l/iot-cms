# Publicação na Hostinger

O repositório é `thaisantos-l/iot-cms`, branch `main`. O push atualiza o GitHub; o deploy atualiza os arquivos da hospedagem. Conteúdo e configurações do WordPress ficam no banco e precisam de uma migração inicial separada.

## O que foi ajustado no WordPress local

Em 23/11/2026, a Home recebeu os campos do HTML de `thaisantos-l/iot-front`, junto com quatro artigos e três cases de exemplo, conforme solicitado. Esses registros, seus metadados, menus e configurações estão no banco. As mídias importadas estão em `wp-content/uploads`.

O formulário foi habilitado em **Configurações → iotnest** depois que o recebimento do teste SMTP foi confirmado. O destinatário é `contato@iotnest.ai`. No WP Mail SMTP Lite, a conexão usa **Other SMTP**, host `email-smtp.us-east-1.amazonaws.com`, SSL, porta 465, autenticação habilitada e remetente `noreply@iotnest.ai`, com a opção de forçar remetente habilitada. Usuário e senha SMTP pertencem ao ambiente e não entram no Git.

O aviso cURL 28 observado no teste aconteceu na consulta de verificação do domínio. O e-mail de teste chegou posteriormente. A presença do formulário e o endpoint de token foram conferidos localmente; o envio pelo formulário e a entrega na hospedagem ainda precisam de teste.

Não houve alteração de PHP para habilitar o formulário: a correção foi a opção `iotnest_settings.contact_enabled` no banco. Instalar o plugin SMTP não liga essa opção automaticamente. Não ativar o contato em uma nova instalação antes de testar o transporte naquele ambiente.

## Primeira publicação: arquivos e banco

O destino informado é um domínio ou subdomínio novo. Este caminho leva o estado atual do ambiente local, incluindo seus metadados, sem substituir o site estático atual. A importação do banco local substitui os registros de mesmo prefixo; não serve para mesclar dois WordPress com conteúdo independente.

1. No hPanel, adicione o novo domínio/subdomínio à hospedagem, configure seu DNS e HTTPS e instale WordPress nesse destino. Confirme que ele tem diretório e banco próprios. Depois abra **Sites → Gerenciar site** no novo endereço. O projeto foi validado com PHP 8.2 e WordPress 7.1.4.
2. Em **Arquivos → Gerenciador de arquivos**, identifique a raiz do WordPress, normalmente `public_html`. Ela deve conter `wp-admin`, `wp-includes`, `wp-content` e `index.php`.
3. Envie e extraia o pacote compilado `iotnest-COMMIT.zip`. Copie os diretórios `wp-content/themes/iotnest` e `wp-content/plugins/iotnest-core` do pacote para os caminhos equivalentes do WordPress. Eles incluem os manifests e CSS/JS compilados. Não coloque uma segunda pasta `public_html` ou `iot` dentro da raiz por engano. O arquivo `release.json` pode ficar junto do pacote guardado fora da raiz pública.
4. Envie o arquivo `iotnest-uploads.zip` e extraia sua pasta `wp-content/uploads` na raiz do WordPress, preservando a estrutura interna. Remova o ZIP da raiz pública depois da extração.
5. Em **Bancos de dados → Gerenciamento**, crie ou selecione o banco exclusivo desse WordPress. Abra seu **phpMyAdmin → Importar** e selecione o SQL da migração no computador. Não envie o SQL para `public_html`.
6. No `wp-config.php` da Hostinger, confira `DB_NAME`, `DB_USER`, `DB_PASSWORD` e `DB_HOST` usando os dados do painel. Para o banco desta instalação local, o prefixo é `$table_prefix = 'page_';`. Preserve as configurações do servidor de destino. Não envie o `wp-config.php`, `.htaccess`, `.user.ini` ou o MU-plugin de configuração do Wamp.
7. Use o SQL `iotnest-conteudo-destino.sql` preparado para o endereço exato do novo domínio/subdomínio, sem o `/iot` do ambiente local. Confira o destino no relatório que acompanha o pacote. Para outro domínio, gere uma nova exportação com substituição compatível com dados serializados. Não faça substituição textual simples em um SQL com metadados serializados.
8. Acesse `/wp-admin/` com um usuário do banco importado. Ative **iotnest-core**, o tema **iotnest**, **Yoast SEO** e **WP Mail SMTP**. Os dois últimos são instalados separadamente por **Plugins → Adicionar plugin**; não estão no pacote próprio. As versões locais usadas foram Yoast 28.9 e WP Mail SMTP Lite 4.10.4.
9. Em **Configurações → iotnest**, mantenha o contato desligado durante a configuração do envio na hospedagem. Confira o SMTP, envie um teste pelo painel e confirme o recebimento. Depois habilite o formulário, confira `contato@iotnest.ai` como destinatário e faça um envio pela página de contato.
10. Salve **Configurações → Links permanentes**, confira a página inicial em **Configurações → Leitura**, menus, imagens, artigos, cases e documentos legais. A instalação local desencoraja indexação: ajuste essa opção em Leitura quando o domínio estiver pronto para publicação. Confira HTTPS e limpe os caches da hospedagem após a atualização.

Se aparecer uma página padrão da hospedagem, confira se um `index.html` ainda tem prioridade na raiz do novo domínio. Não altere os arquivos do domínio estático atual.

O SQL contém dados da instalação, inclusive usuários e configurações de plugins. Ele fica fora do repositório, assim como os uploads e os arquivos de configuração local. Após a migração inicial, edições feitas no WordPress publicado não devem ser substituídas por novas importações do banco local.

Referência: [transferência manual de arquivos e banco na Hostinger](https://www.hostinger.com/support/1583289-how-to-manually-transfer-a-website-to-hostinger/).

## Atualizações de código pelo painel

O checkout da `main` contém fontes. `assets/dist` não é versionado: apenas conectar este repositório ao Git do hPanel não produz um site completo nem migra os dados acima.

Para atualizar pelo Gerenciador de arquivos:

1. Obtenha o pacote compilado do commit desejado em **GitHub → Actions → iotnest CI → execução concluída → Artifacts**. Dentro do download está `iotnest-COMMIT.zip` com seu checksum. O pacote só é publicado pelo CI se suas verificações anteriores passarem. Também é possível gerar localmente com `npm ci --ignore-scripts`, `npm run build` e `npm run package`, em checkout limpo e com as versões de ferramentas do projeto.
2. Faça backup dos dois diretórios próprios, coloque o site em manutenção e atualize tema e plugin juntos com o mesmo pacote. Não substitua `wp-content` inteiro: banco, uploads e plugins de terceiros pertencem à instalação.
3. Limpe os caches, confira o site e saia da manutenção. Atualização de código não precisa reimportar banco ou uploads.

Para uma infraestrutura já provisionada com SSH e os links de release, há também o fluxo manual de GitHub Actions descrito em [operação](operacao.md). O envio à `main` executa o CI, mas não dispara esse deploy de produção.

## Onde fica a integração Git do hPanel

Na interface atual da Hostinger: **Sites → Gerenciar site → Avançado → Git → Connect with GitHub**. Autorize o acesso ao repositório, selecione `thaisantos-l/iot-cms`, branch `main`, diretório de destino e **Deploy**. Atualizações manuais usam **Redeploy**; o menu de auto-deployment controla atualizações a cada push. A integração atual usa OAuth.

Esses são os controles do painel, mas este projeto precisa do pacote compilado e do WordPress instalado. Não aponte o checkout bruto da `main` para a instalação pública esperando que o painel instale WordPress, execute o build ou migre o banco. Para este repositório, use a publicação por pacote acima ou provisione o fluxo de releases existente antes de automatizar.

Referência: [integração Git atual da Hostinger](https://www.hostinger.com/support/1583302-how-to-deploy-a-git-repository-in-hostinger/).

## Exportar URLs sem alterar o banco local

O WP-CLI permite escrever um SQL com as URLs convertidas sem modificar a instalação de origem. Exemplo, executado na raiz local e com o arquivo de saída fora da raiz pública:

```sh
wp search-replace 'http://localhost/iot' 'https://SEU-NOVO-DOMINIO' --all-tables-with-prefix --skip-columns=guid --precise --export=/CAMINHO-PRIVADO/iotnest-conteudo-destino.sql
```

Substitua `https://SEU-NOVO-DOMINIO` pelo endereço real antes de executar. Esse arquivo só serve para o domínio indicado. Verifique também referências em opções de diretórios de upload e configurações de plugins. Consulte o [comando oficial e tratamento de dados serializados](https://developer.wordpress.org/cli/commands/search-replace/).
