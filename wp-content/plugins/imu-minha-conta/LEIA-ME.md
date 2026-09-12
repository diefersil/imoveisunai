# IMU — Minha Conta 1.0.0

Plugin WordPress para o site Imóveis Unaí. Reutiliza o post type `imoveis`, o JetEngine e o tema ativo. Não cria outro WordPress nem importa o CSV.

## Instalação

1. Em Plugins → Adicionar plugin → Enviar plugin, envie `imu-minha-conta.zip` e ative.
2. Crie a página **Minha conta**, slug `minha-conta`, e insira `[imu_minha_conta]` em um bloco Shortcode ou widget Shortcode do Elementor.
3. Em **Configurações → IMU Minha Conta**, selecione essa página.
4. Confira no JetEngine o formato de valor de `galeria`: IDs, URLs ou array de ID/URL. Selecione o correspondente nas configurações do plugin. O modo IDs usa uma string de IDs separados por vírgula; URLs usa uma string separada por vírgula; array usa itens `id`/`url`. O leitor aceita também arrays de IDs e URLs. Verifique a compatibilidade com a versão instalada do JetEngine usando um imóvel de teste.
5. Confira se `data_expiracao` salva timestamp. Selecione timestamp ou data YYYY-MM-DD conforme o campo existente.
6. Confira o identificador real de Tags Imóveis: o print apresenta `tags-imoveis` e `tags-imoveus`. Informe o identificador registrado no campo de configuração. O plugin verifica que todas as taxonomias pertencem a `imoveis`.
7. Configure a validade, inicialmente 30 dias.
8. Exclua `/minha-conta/` e suas variações de parâmetros do cache de página/CDN. O plugin também envia cabeçalhos de não cache.

O cadastro fica indisponível até os formatos e as taxonomias estarem configurados. Meu perfil funciona independentemente dessas opções.

## Entrar com Google

1. Instale e ative **Nextend Social Login and Register**.
2. Nas configurações do Nextend, abra Google e siga as instruções do provedor para criar um cliente OAuth do tipo aplicativo Web no Google Cloud. Use EXATAMENTE a URI de redirecionamento que o Nextend fornecer.
3. Configure a tela de consentimento, Client ID e Client Secret no Nextend. Faça o teste de autenticação e habilite o provedor. Enquanto o aplicativo estiver em teste, observe os usuários de teste permitidos pelo Google; configure para produção antes de liberar ao público.
4. Permita novos cadastros conforme as configurações do WordPress/Nextend e selecione **Anunciante IMU** como função padrão em Configurações → Geral. Nunca escolha Administrador como função padrão de cadastro.
5. A página usa `[nextend_social_login provider="google"]` e retorna para o painel. Não expõe formulário de senha ao anunciante. O login administrativo normal do WordPress permanece disponível.
6. Para uma imobiliária que já existe, entre primeiro na conta WordPress correta e use a vinculação de conta do Nextend. Confira o usuário e a autoria antes de permitir novos cadastros com outro e-mail. O plugin não mescla contas nem transfere imóveis automaticamente.

## Dados

Perfil: `display_name` e e-mail nativo (somente leitura), `user_whatsapp`, `user_fone`, `user_endereco` em user meta.

Imóvel: título e conteúdo nativos, `preco`, `area`, `endereco`, `galeria`, imagem destacada e `data_expiracao`.

Taxonomias: `categoria`, `negociacao`, `estado`, `cidade` e Tags Imóveis configurável. Usa termos existentes; tags permite múltiplos termos. Planos e Etiquetas não aparecem. Estado e cidade são listas independentes, pois não foi fornecida uma relação entre seus termos.

O autor é sempre a conta autenticada. Consultas e gravações restringem imóveis ao próprio autor, inclusive para administradores que usem este painel. O administrador continua gerenciando todos no wp-admin.

## Publicação e expiração

- Novos anúncios ficam pendentes de revisão.
- Editar anúncio publicado muda seu status para pendente, retirando-o do site até nova aprovação. Esse comportamento é informado antes de salvar.
- A validade começa no cadastro. O anunciante vê a expiração, mas não a altera. O administrador pode alterar `data_expiracao` no WordPress.
- A rotina horária usa WP-Cron e transforma anúncios expirados em rascunho. WP-Cron depende de visitas ou de cron do servidor; não há garantia de execução no minuto exato.
- O plugin marca somente anúncios que ele cria para gerenciar a expiração. Ele não inicia expiração automática de imóveis antigos/importados. Verifique se outro plugin já controla esse campo antes de usar duas rotinas simultaneamente.
- Remover foto da galeria apenas desfaz sua referência; não apaga o arquivo da biblioteca. Substituir a capa preserva o arquivo antigo.
- Imagens novas: JPG, PNG, WebP, máximo de 8 MB por arquivo e limites do servidor. Até 30 fotos na galeria. Formulários usam upload multipart normal, sujeito a `post_max_size`, `upload_max_filesize` e `max_file_uploads` do PHP.
- Não há cobrança, planos, exclusão de anúncios ou renovação automática nesta versão.

## Verificação antes de usar em produção

O pacote foi revisado estruturalmente neste ambiente. Não havia PHP nem uma instalação WordPress disponíveis para executar o plugin; a integração com WordPress, JetEngine, Nextend e Google precisa ser validada na instalação de teste.

1. Com dois anunciantes A e B, cadastre um imóvel em cada conta. Verifique que A não consegue abrir nem gravar a edição do ID de B e vice-versa.
2. Envie título, descrição, preço com vírgula, categoria, negociação, estado, cidade, capa e duas fotos. Aprove pelo wp-admin e confira o single e os filtros existentes.
3. Edite o imóvel, remova uma foto, adicione outra e confirme que a galeria mantém as restantes e o anúncio volta para revisão.
4. Salve o perfil e confira os valores em `user_whatsapp`, `user_fone`, `user_endereco`, inclusive o botão de contato no single.
5. Teste uma imagem inválida e um arquivo acima do limite. Os dados do post não devem ser alterados nessa tentativa.
6. Confira expiração de um anúncio criado pelo painel e a preservação de imóveis importados.
7. Faça login Google com conta nova e vincule uma conta já existente em ambiente de teste. Verifique o autor dos anúncios.

Desativar o plugin remove seu agendamento e deixa os imóveis, usuários e dados existentes intactos. A página exibirá o shortcode até você removê-lo ou reativar o plugin.
