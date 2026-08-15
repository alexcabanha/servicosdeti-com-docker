# Projeto HelpDesk

Projeto de HelpDesk desenvolvido como laboratório de infraestrutura, utilizando Docker, Azure, monitoramento, backup e acesso seguro via HTTPS. 
O projeto abaixo é o projeto final solicitado pelo Curso de Docker Compose no Senac MS

## Arquitetura

A aplicação é executada em uma VM Linux na Microsoft Azure.
Liberei apenas as portas 80 e 443. Para o desafio via DNS e 80 da aplicação.

```text
                         Internet
                            |
                            v
                    helpdesk.alexcabanha.com.br
                            |
                         HTTPS :443
                            |
                         Traefik
                            |
          +-----------------+-----------------+
          |                 |                 |
        Nginx             API             Grafana
          |                 |                 |
         PHP              Backend          Prometheus
                            |                 |
                         MySQL              cAdvisor
                            |                 |
                         Redis          Node Exporter

                     Portainer
                         |
                    Docker Engine
```

## Ambiente

- Microsoft Azure
- VM Linux
- Docker
- Docker Compose
- Traefik
- Nginx
- PHP
- Python / FastAPI
- MySQL 8.4
- Redis
- Prometheus
- Grafana
- cAdvisor
- Node Exporter
- Portainer

## Containers

| Serviço | Função |
|---|---|
| Traefik | Proxy reverso e HTTPS |
| Nginx | Servidor web |
| PHP | Aplicação web |
| Backend | API |
| MySQL | Banco de dados |
| Redis | Cache / dados temporários |
| Prometheus | Coleta de métricas |
| Grafana | Dashboards e alertas |
| cAdvisor | Métricas dos containers |
| Node Exporter | Métricas do sistema |
| Portainer | Gerenciamento Docker |

## Redes Docker

A infraestrutura utiliza redes separadas para reduzir a comunicação desnecessária entre os serviços.

```text
frontend_net
    |
    +-- Traefik
    +-- Nginx
    +-- PHP
    +-- Backend
    +-- Grafana
    +-- Portainer

php_net
    |
    +-- Nginx
    +-- PHP

backend_net
    |
    +-- PHP
    +-- Backend
    +-- MySQL
    +-- Redis

monitoring_net
    |
    +-- Traefik
    +-- Backend
    +-- Prometheus
    +-- Grafana
    +-- cAdvisor
    +-- Node Exporter
    +-- Portainer
```

As redes `php_net` e `backend_net` são internas.

## Domínio e HTTPS

O acesso externo utiliza o domínio:

`helpdesk.alexcabanha.com.br`

O domínio foi configurado no Registro.br apontando para o IP público da VM hospedada no Azure.

O Traefik é responsável pelo proxy reverso e pelo certificado HTTPS utilizando Let's Encrypt.

O certificado é obtido e renovado automaticamente pelo Traefik através do ACME.

A porta 80 é utilizada pelo desafio HTTP do Let's Encrypt e a porta 443 pelo acesso HTTPS.

Os dados do ACME são armazenados localmente em:

```text
./letsencrypt/acme.json
```

Esse arquivo não é versionado.

## Acesso

Aplicação:

```text
https://helpdesk.alexcabanha.com.br
```

Grafana:

```text
https://helpdesk.alexcabanha.com.br/grafana/
```

Portainer:

```text
https://helpdesk.alexcabanha.com.br/portainer/
```

A aplicação também pode ser acessada diretamente pelo IP da VM através de HTTP.

## Monitoramento

O monitoramento utiliza Prometheus e Grafana.

O Prometheus coleta métricas de:

- Sistema operacional através do Node Exporter
- Containers através do cAdvisor

O Grafana utiliza o Prometheus como fonte de dados para dashboards e alertas.

O Prometheus mantém atualmente 7 dias de retenção.

## Backup

A VM possui um segundo disco dedicado para armazenamento dos backups.

O disco é montado em:

```text
/backup
```

Estrutura utilizada:

```text
/backup
├── application
├── configs
├── logs
└── mysql
```

O backup do MySQL é realizado diariamente às 03:00 através do cron.

Agendamento:

```cron
0 3 * * * /home/supcabanha/projeto-final/scripts/backup-mysql.sh
```

O script realiza um `mysqldump` do banco `servicoti`, compacta o resultado com gzip e armazena os arquivos em:

```text
/backup/mysql
```

Os backups possuem retenção de 7 dias.

Os logs da rotina ficam em:

```text
/backup/logs/mysql-backup.log
```

O script verifica se o container MySQL está em execução e se o arquivo de backup não está vazio antes de considerar a operação concluída.
Lebrando que, a copia é efetuada algumas vezes por dia da VM e armazenada em outra região.

## Persistência

Os dados dos serviços que precisam sobreviver à recriação dos containers utilizam volumes Docker.

Principais volumes:

```text
mysql_data
redis_data
grafana_data
prometheus_data
portainer_data
```

Persistência e backup são tratados separadamente.

O volume Docker garante a persistência dos dados do container, enquanto o diretório `/backup` é utilizado para a rotina de backup.

## Segurança

Algumas informações não são armazenadas no repositório.

O arquivo `.env` está no `.gitignore` e não deve ser enviado ao GitHub.

O arquivo do certificado ACME também não é versionado:

```text
letsencrypt/acme.json
```

Esse arquivo contém informações privadas relacionadas aos certificados.

## Estrutura do projeto

```text
projeto-final/
├── backend/
│   ├── app/
│   ├── Dockerfile
│   └── requirements.txt
│
├── php/
│   ├── src/
│   └── Dockerfile
│
├── nginx/
│   └── nginx.conf
│
├── monitoring/
│   └── prometheus/
│       └── prometheus.yml
│
├── scripts/
│   └── backup-mysql.sh
│
├── logs/
├── letsencrypt/
├── docker-compose.yml
├── .env
└── .gitignore
```

## Objetivo

O projeto foi desenvolvido para demonstrar, na prática, a construção de uma aplicação containerizada com:

- Separação de serviços
- Redes Docker
- Persistência de dados
- Proxy reverso
- HTTPS automático
- Monitoramento
- Alertas
- Gerenciamento dos containers
- Backup automatizado
- Organização de infraestrutura

O objetivo principal é demonstrar não apenas a aplicação, mas também os componentes necessários para mantê-la funcionando de forma organizada e monitorada.
