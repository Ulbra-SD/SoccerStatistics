# SoccerStatistics
Trabalho de G2 - Sistema web distribuído para consulta à base de dados de jogos de futebol dos campeonatos europeus entre os anos de 2008 e 2016, com uso de Memcached para aumento de performance.

# Instruções
Para configurar e utilizar a aplicação siga os passos abaixo:

1 - O sistema foi desenvolvido utilizando PHP 7.2, MariaDB 10.1 e Apache. Caso não tenha estas ferrramentas instaladas, instale-as. Instale também os pacotes php-mysqli e php-memcache (apt-get install php-mysqli php-memcache).

2 - Baixe o arquivo soccerdb.sql, que contém a base de dados MySQL que é utilizada pela aplicação.

3 - Importe o dump da base de dados para o MySQL. O usuário é "root", sem senha.

4 - Altere o arquivo soccer/config/config.json e configure com os IPs e informações do seu ambiente.

5 - Baixe a pasta soccer e o arquivo .htaccess e coloque os dois em /var/www/html. Caso seu servidor já possua um arquivo .htaccess, copie o conteúdo do arquivo baixado e cole-o no início do seu arquivo (ou renomeie seu arquivo e use o .htaccess baixado para usar a aplicação).

6 - Caso o módulo rewrite não esteja habilitado no seu servidor Apache:

    - Ative com o comando a2enmod rewrite.
    
    - Edite o arquivo /etc/apache2/sites-enabled/000-default.conf:
      
      - Procure pela linha DocumentRoot /varwww/html.
      
      - Adicione o edite a diretiva <Directory "/var/www/html"> AllowOverride All </Directory>
    
    - Faça o mesmo procedimento no arquivo /etc/apache2/sites-available/000-default.conf

7 - Edite o arquivo /etc/php/7.2/apache2/php.ini e:
    
    - Descomente a linha extension=mysqli;
    
    - Adicione a linha extension=php-memcache;
    
8 - Inicie (ou reinicie) os serviços Apache e MariaDB.

9 - No navegador, utilize o seguinte padrão de requisições:
    
    /getData/<período>?playerName=<nomeDoJogador>
    
    /getData/<período>?clubName=<nomeDoClube>
    
    /getData/<período>?clubName=<nomeDoClube>&playerName=<nomeDoJogador>
