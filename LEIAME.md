# OcoMon - versão 4.x
## Data: Janeiro de 2022
## Autor: Flávio Ribeiro (flaviorib@gmail.com)

## Licença: GPLv3


## IMPORTANTE:

Se você deseja instalar o OcoMon por conta própria, é necessário que saiba o que é um servidor WEB e esteja familiarizado com o processo genérico de instalação de sistemas WEB. 

Para instalar o OcoMon é necessário ter uma conta com permissão de criação de databases no MySQL ou MariaDB e acesso de escrita à pasta pública do seu servidor web.

Antes de iniciar o processo de instalação ou atualização **leia esse arquivo até o final.**


## REQUISITOS:

+ Servidor web com Apache(***não testado com outros servidores***), PHP e MySQL (ou MariaDB):
    
    - MySQL a partir da versão **5.6** (Ou MariaDB a partir da versão **10.2**)
    - PHP a partir da versão **7.4** com:
        - PDO
        - pdo_mysql
        - mbstring
        - openssl
        - imap
        - curl
        - iconv
        - gd
        - ldap

    - Para utilização da API diretamente ou por meio da abertura de chamados por email:
        - O Apache precisa permitir reescrita de URL (para poder direcionar as rotas da API via htaccess);
        - O módulo "mod_rewrite" precisa estar habilitado no Apache;

<br>

## INSTALAÇÃO OU ATUALIZAÇÃO EM AMBIENTE DE PRODUÇÃO:


### IMPORTANTE (em caso de atualização)

+ É fortemente **recomendado** fazer **BACKUP** da sua base de dados! **Faça isso primeiro** e evite eventuais dores de cabeça.

+ Identifique qual é a **sua versão instalada**. Após isso vá direto para a seção, neste documento, correspondente às instruções de atualização específicas para a sua versão. Para cada versão do OcoMon há **apenas UM arquivo específico** (ou nenhum) a ser importado para o seu banco de dados.

+ Confira as novidades da versão em [https://ocomonphp.sourceforge.io/changelog-incremental/](https://ocomonphp.sourceforge.io/changelog-incremental/) para identificar novas possibilidades de uso e novas configurações.


### Atualização de versão:


#### Se a sua versão atual é a 4.0RC1:

Nesse caso, nenhuma ação de banco de dados é necessária. Para atualizar basta sobrescrever os scripts da aplicação OcoMon.


1. Sobrescreva os scripts da sua versão em uso pelos scripts da versão corrente; Pronto!


#### Se a sua versão atual é a 4.0Beta1:

Alguns usuários tiveram acesso à prévia da versão 4 antes do lançamento oficial. Se este for o seu caso:

1. Importe o arquivo de atualização do banco de dados "DB_UPDATE_FROM_BETA1.sql" (em install/4.x/): <br>

        Ex. via linha de comando:
        mysql -u root -p [database_name] < /caminho/para/o/ocomon-4.x/install/4.x/DB_UPDATE_FROM_BETA1.sql
        
        Onde: [database_name]: É o nome do banco de dados do OcoMon

2. Sobrescreva os scripts da sua versão pelos scripts da versão corrente (recomendado: mantenha apenas o seu arquivo de configurações "config.inc.php" e mova/remova todos os demais scripts);

3. Por questões de segurança, após a importação do SQL, remova a pasta install. Pronto! Basta ajustar as novas configurações da versão diretamente via interface administrativa.<br>


#### Se a sua versão atual é a 3.3:

1. Importe o arquivo de atualização do banco de dados "06-DB-UPDATE-FROM-3.3.sql" (em install/4.x/): <br>

        Ex. via linha de comando:
        mysql -u root -p [database_name] < /caminho/para/o/ocomon-4.x/install/4.x/06-DB-UPDATE-FROM-3.3.sql
        
        Onde: [database_name]: É o nome do banco de dados do OcoMon

2. Sobrescreva os scripts da sua versão pelos scripts da versão 4 (recomendado: mantenha apenas o seu arquivo de configurações "config.inc.php" e mova/remova todos os demais scripts);

3. Por questões de segurança, após a importação do SQL, remova a pasta install. Pronto! Basta ajustar as novas configurações da versão diretamente via interface administrativa.<br>
#### Se a sua versão atual é a 3.2 ou 3.1 ou 3.1.1:

1. Importe o arquivo de atualização do banco de dados "05-DB-UPDATE-FROM-3.2.sql" (em install/4.x/): <br>

        Ex. via linha de comando:
        mysql -u root -p [database_name] < /caminho/para/o/ocomon-4.x/install/4.x/05-DB-UPDATE-FROM-3.2.sql
        
        Onde: [database_name]: É o nome do banco de dados do OcoMon

2. Sobrescreva os scripts da sua versão pelos scripts da versão 4 (recomendado: mantenha apenas o seu arquivo de configurações "config.inc.php" e mova/remova todos os demais scripts);

3. Por questões de segurança, após a importação do SQL, remova a pasta install. Pronto! Basta ajustar as novas configurações da versão diretamente via interface administrativa.<br>


#### Se a sua versão atual é a 3.0 (release final):

1. Importe o arquivo de atualização do banco de dados "04-DB-UPDATE-FROM-3.0.sql" (em install/4.x/): <br>

        Ex. via linha de comando:
        mysql -u root -p [database_name] < /caminho/para/o/ocomon-4.x/install/4.x/04-DB-UPDATE-FROM-3.0.sql
        
        Onde: [database_name]: É o nome do banco de dados do OcoMon

2. Sobrescreva os scripts da sua versão pelos scripts da versão 4 (recomendado: mantenha apenas o seu arquivo de configurações "config.inc.php" e mova/remova todos os demais scripts);

3. Por questões de segurança, após a importação do SQL, remova a pasta install. Pronto! Basta ajustar as novas configurações da versão diretamente via interface administrativa.<br>


#### Se a sua versão atual é qualquer uma das releases candidates(rc) da versão 3.0 (rc1, rc2, rc3):

+ Sempre é recomendado realizar o **BACKUP** tanto dos scripts da versão em uso quanto do banco de dados atualmente em uso pelo sistema.

1. Importe o arquivo de atualização do banco de dados "03-DB-UPDATE-FROM-3.0rcx.sql" (em install/4.x/): <br>

        Ex. via linha de comando:
        mysql -u root -p [database_name] < /caminho/para/o/ocomon-4.x/install/4.x/03-DB-UPDATE-FROM-3.0rcx.sql
        
        Onde: [database_name]: É o nome do banco de dados do OcoMon

2. Sobrescreva os scripts da sua versão pelos scripts da versão 4 (recomendado: mantenha apenas o seu arquivo de configurações "config.inc.php" e mova/remova todos os demais scripts);

3. Por questões de segurança, após a importação do SQL, remova a pasta install. Pronto! Basta ajustar as novas configurações da versão diretamente via interface administrativa.<br>


#### Se a sua versão atual é a versão 2.0 final

+ **IMPORTANTE:** Leia com atenção o arquivo changelog-3.0.md (*em /changelog*) para conferir as principais mudanças e principalmente sobre as **funções removidas de versões anteriores** e algumas novas **configurações necessárias** bem como mudanças de retorno sobre o tempo de SLAs para chamados pré-existentes.

+ Realize o **BACKUP** tanto dos scripts da versão em uso quanto do banco de dados atualmente em uso pelo sistema.

+ O processo de atualização considera que a versão corrente é a 2.0 (**release final**), portanto, se a sua versão for a 2.0RC6, vá para a seção relacionada.

+ **IMPORTANTE:** Dependendo da configuração do seu banco de dados quanto ao "case sensitive", será necessário renomear as seguintes tabelas (caso possuam a nomenclatura com a letra "X" em caixa alta): "areaXarea_abrechamado", "equipXpieces" para: "areaxarea_abrechamado", "equipxpieces". Isso **DEVE** ser feito **ANTES** de importar o arquivo SQL de atualização.

+ Para atualizar a partir da versão 2.0 (release final), basta sobrescrever os scripts da sua pasta do OcoMon pelos scripts da nova versão (recomendado: mantenha apenas o seu arquivo de configurações "config.inc.php" e mova/remova todos os demais scripts) e importar para o MySQL o arquivo de atualização: 02-DB-UPDATE-FROM-2.0.sql (em /install/4.x/). <br><br>

        Ex via linha de comando:
        mysql -u root -p [database_name] < /caminho/para/o/ocomon-4.x/install/4.x/02-DB-UPDATE-FROM-2.0.sql
    
        Onde: [database_name]: É o nome do banco de dados do OcoMon

+ Por questões de segurança, após a importação do SQL, remova a pasta install. Pronto! Basta ajustar as novas configurações da versão diretamente via interface administrativa.<br>

<br>

#### Se a sua versão atual é a versão 2.0RC6

+ **IMPORTANTE:** Leia com atenção o arquivo changelog-3.0.md (*em /changelog*) para conferir as principais mudanças e principalmente sobre as **funções removidas de versões anteriores** e algumas novas **configurações necessárias** bem como mudanças de retorno sobre o tempo de SLAs para chamados pré-existentes.

+ Realize o **BACKUP** tanto dos scripts da versão em uso quanto do banco de dados atualmente em uso pelo sistema.

+ O processo de atualização considera que a versão corrente é a 2.0RC6 (**versão oficial**), portanto, se a sua versão possuir qualquer customização essa ação de **atualização não é recomendada**.

+ **IMPORTANTE:** Dependendo da configuração do seu banco de dados quanto ao "case sentitive", será necessário renomear as seguintes tabelas (caso possuam a nomenclatura com a letra "X" em caixa alta): "areaXarea_abrechamado", "equipXpieces" para: "areaxarea_abrechamado", "equipxpieces". Isso **DEVE** ser feito **ANTES** de importar o arquivo SQL de atualização.

+ Para atualizar a partir da versão 2.0RC6, basta sobrescrever os scripts da sua pasta do OcoMon pelos scripts da nova versão (recomendado: mantenha apenas o seu arquivo de configurações "config.inc.php" e mova/remova todos os demais scripts) e importar para o MySQL o arquivo de atualização: RC6-DB_UPDATE_FROM_RC6.sql (em /install/4.x/). <br><br>

        Ex via linha de comando:
        mysql -u root -p [database_name] < /caminho/para/o/ocomon-4.x/install/4.x/RC6-DB_UPDATE_FROM_RC6.sql
    
        Onde: [database_name]: É o nome do banco de dados do OcoMon

+ Por questões de segurança, após a importação do SQL, remova a pasta install. Pronto! Basta ajustar as novas configurações da versão diretamente via interface administrativa.<br>

<br/><br/>
## Primeira instalação:

O processo de instalação é bastante simples e pode ser realizado seguindo 3 passos:

1. **Instalar os scripts do sistema:**

    Descompacte o contéudo do pacote do OcoMon_4x no diretório público do seu servidor web (*o caminho pode variar dependendo da distribuição ou configuração, mas de modo geral costuma ser **/var/www/html/***).

    As permissões dos arquivos podem ser as padrão do seu servidor (exceto para a pasta api/ocomon_api/storage, que precisa permitir escrita pelo usuário do Apache).

2. **Criação da base de dados:**<br>

    **SISTEMA HOSPEDADO LOCALMENTE** (**localhost** - Se o sistema será instalado em um servidor externo pule para a seção [SISTEMA EM HOSPEDAGEM EXTERNA]):
    
    Para a criação de toda a base do OcoMon, você precisa importar um único arquivo de instruções SQL:
    
    O arquivo é:
    
        01-DB_OCOMON_4.x-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql (em /install/4.x/).

    Linha de comando:
        
        mysql -u root -p < /caminho/para/o/ocomon-4.x/install/4.x/01-DB_OCOMON_4.x-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql
        
    O sistema irá solicitar a senha do usuário root (ou de qualquer outro usuário que tenha sido fornecido ao invés de root no comando acima) do MySQL.

    O comando acima irá criar o usuário "ocomon_4" com a senha padrão "senha_ocomon_mysql", e a base de dados "ocomon_4" com todas as informações necessárias para a inicialização do sistema.

    **É importante alterar essa senha do usuário "ocomon_4" no MySQL logo após a instalação do sistema.**

    Você também pode realizar a importação do arquivo SQL utilizando qualquer gerenciador de banco de dados de sua preferência.


    Caso queira que a base e/ou usuario tenham outro nome (ao invés de "ocomon_4"), edite diretamente no arquivo (*identifique as entradas relacionadas ao nome do banco, usuário e senha no início do arquivo*):

        01-DB_OCOMON_4.x-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql

    antes de realizar a importação do mesmo. Utilize essas mesmas informações no arquivo de configurações do sistema (passo **3**).
    
    **Após a importação, é recomendável a exclusão da pasta "install".**<br><br>


    **SISTEMA EM HOSPEDAGEM EXTERNA:**

    Se o sistema será instalado em um servidor externo, nesse caso, em função de eventuais limitações de criação para nomenclatura de databases e usuários (geralmente o provedor estipula um prefixo para os databases e usuários), é recomendado utilizar o nome de usuário oferecido pelo próprio serviço de hosting ou então criar um usuário específico (se a sua conta de usuário permitir) diretamente pela sua interface de acesso ao banco de dados. Sendo assim:

    - **Crie** uma database específica para o OcoMon (você define o nome);
    - **Crie** um usuário específico para acesso à database do OcoMon (ou utilize seu usuário padrão);
    - **Altere** o script "01-DB_OCOMON_4.x-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql" **removendo** as seguintes linhas do início do arquivo:

            CREATE DATABASE /*!32312 IF NOT EXISTS*/`ocomon_4` /*!40100 DEFAULT CHARACTER SET utf8 */;

            CREATE USER 'ocomon_4'@'localhost' IDENTIFIED BY 'senha_ocomon_mysql';
            GRANT SELECT , INSERT , UPDATE , DELETE ON `ocomon_4` . * TO 'ocomon_4'@'localhost';
            GRANT Drop ON ocomon_4.* TO 'ocomon_4'@'localhost';
            FLUSH PRIVILEGES;

            USE `ocomon_4`;

    - Após isso basta importar o arquivo alterado e seguir com o processo de instalação.

            mysql -u root -p [database_name] < /caminho/para/o/ocomon-4.x/install/4.x/01-DB_OCOMON_4.x-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql

        Onde: [database_name] é o nome da database que foi criada manualmente.<br>



3. **Criar o arquivo de configurações:**

    Faça uma cópia do arquivo config.inc.php-dist (*/includes/*) e renomeie para config.inc.php. Nesse novo arquivo, confira e revise as informações relacionadas à conexão com o banco de dados (*servidor, base de dados, usuário e senha*).<br><br>


## VERSÃO PARA TESTES:


Caso queira testar o sistema antes de instalar, você pode rodar um container Docker com o sistema já funcionando com alguns dados já populados. Se você já possui o Docker instalado em seu ambiente, então basta executar o seguinte comando em seu terminal:

        docker run -it --name ocomon_4 -p 8000:80 flaviorib/ocomon_demo-4.0:20211021 /bin/ocomon

Em seguida basta abrir o seu navegador e acessar pelo seguinte endereço:

        localhost:8000

E pronto! Você já está com uma instalação do OcoMon prontinha para testes com os seguintes usuários cadastrados:<br>


| usuário   | Senha     | Descrição                           |
| :-------- | :-------- | :---------------------------------  |
| admin     | admin     | Nível de administração do sistema   |
| operador  | operador  | Operador padrão – nível 1           |
| operador2 | operador  | Operador padrão – nível 2           |
| abertura  | abertura  | Apenas para abertura de ocorrências |


Caso não tenha o Docker, acesse o site e instale a versão referente ao seu sistema operacional:

[https://docs.docker.com/get-docker/](https://docs.docker.com/get-docker/)<br>

Ou então assista a esse vídeo para ver como é simples testar o OcoMon sem precisar de nenhuma instalação:
[https://www.youtube.com/watch?v=Wtq-Z4M9w5M](https://www.youtube.com/watch?v=Wtq-Z4M9w5M)<br>



## PRIMEIROS PASSOS


ACESSO

    usuário: admin
    
    senha: admin (Não esqueça de alterar esse senha tão logo tenha acesso ao sistema!!)

Novos usuários podem ser criados no menu [Admin::Usuários]
<br><br>


## CONFIGURAÇÕES GERAIS DO SISTEMA


Algumas configurações precisam ser ajustadas dependendo da intenção de uso para o sistema:

- Arquivo de configuração: /includes/config.inc.php
    - nesse arquivo estão as informações de conexão com o banco, e paths padrão.

- Para possibilitar a utilização da função de fila de e-mails é necessário configurar o agendador de tarefas do servidor para executar, na periodicidade desejada, o seguinte script:

        api/ocomon_api/service/sendEmail.php (altere as permissões do arquivo para que ele fique executável)

    - Exemplo utilizando o Crontab:

            * * * * * /usr/local/bin/php /var/www/html/ocomon-4.0/api/ocomon_api/service/sendEmail.php


- Para possibilitar a utilização da função de abertura de chamados por e-mail é necessário configurar o agendador de tarefas do servidor para executar, na periodicidade desejada, o seguinte script:

        ocomon/open_tickets_by_email/service/getMailAndOpenTicket.php (altere as permissões do arquivo para que ele fique executável)

    - Exemplo utilizando o Crontab:

            * * * * * /usr/local/bin/php /var/www/html/ocomon-4.0/ocomon/open_tickets_by_email/service/getMailAndOpenTicket.php


- Para possibilitar o controle de quantidade de requisições, caso se esteja utilizando a API diretamente ou por meio da abertura de chamados por email, é necessário que o usuário do Apache tenha permissão de escrita no diretório "api/ocomon_api/storage".


- As demais configurações do sistema são todas acessíveis por meio do menu de administração diretamente na interface do sistema. 
<br><br>


## INTEGRAÇÃO:

Acesse a documentação em [https://ocomonphp.sourceforge.io/integracao/](https://ocomonphp.sourceforge.io/integracao/)

## DOCUMENTAÇÃO:


Toda a documentação do OcoMon está disponível no site do projeto e no canal no Youtube:

+ Site oficial: [https://ocomonphp.sourceforge.io/](https://ocomonphp.sourceforge.io/)

+ Changelog: [https://ocomonphp.sourceforge.io/changelog-incremental/](https://ocomonphp.sourceforge.io/changelog-incremental/)

+ Twitter: [https://twitter.com/OcomonOficial](https://twitter.com/OcomonOficial)

+ Canal no Youtube: [https://www.youtube.com/c/OcoMonOficial](https://www.youtube.com/c/OcoMonOficial)




## Doações
Se o OcoMon lhe tem sido útil, poupado seu trabalho e lhe permitido direcionar seus recursos para outros investimentos, considere contribuir para a continuidade e crescimento do projeto: [https://ocomonphp.sourceforge.io/doacoes/](https://ocomonphp.sourceforge.io/doacoes/)

<br>Tenho convicção de que o OcoMon tem potencial para ser a ferramenta que lhe será indispensável na organização e gerência de sua área de atendimento liberando seu precioso tempo para outras realizações.

Bom uso!! :)

## Entre em contato:
+ E-mail: [ocomon.oficial@gmail.com](ocomon.oficial@gmail.com)

Flávio Ribeiro
[flaviorib@gmail.com](flaviorib@gmail)

