# Guia de Instalação — Orthanc Client em Laravel 12.x

## Passo a passo (branch main, repositório privado)

- No seu app Laravel, adicione o repositório Git privado do pacote nas configurações do Composer (tipo repositório “vcs”).
- Requeira o pacote usando a referência de branch de desenvolvimento “dev-main”.
- Garanta autenticação (token HTTPS ou chave SSH) para acesso ao repositório privado.
- Instale as dependências e publique a configuração do pacote.
- Configure .env e valide conexão/status com os comandos CLI.

## Instalação via Packagist (se público)

- Caso o pacote esteja publicado no Packagist:
  - composer require orthanctower/client

## Instalação via Git Privado (recomendado)

- Configure repositório “vcs” apontando para a URL do Git privado.
- Requeira o pacote pela branch de desenvolvimento “dev-main” (branch main).
- Alternativa: se houver tag estável publicada, use a versão (ex.: “^0.0.1”).

## Instalação via Caminho Local (desenvolvimento)

- Use repositório “path” para apontar para a pasta local do pacote e requerer “\*@dev” ou “^1.0” (quando houver version/alias).

## Publicar Configuração

- php artisan vendor:publish --tag=orthanc-client-config

## Variáveis de Ambiente (.env)

- ORTHANC_ENABLED=true
- ORTHANC_API_URL=https://orthanc.seu-dominio.com
- ORTHANC_API_TOKEN=token_do_servidor
- (Opcional) ORTHANC_QUEUE_ENABLED=true para uso de fila em produção
- (Opcional) ORTHANC_CLIENT_OVERRIDE_HANDLER=true para auto‑reporte de exceções sem editar Handler

Observação:
- A URL da API é a base do servidor (sem “/api”); o client já chama “/api/*” internamente.

## Validação

- php artisan orthanc:status
- php artisan orthanc:test-connection

## Boas práticas

- Use fila para evitar bloqueios em requisições.
- Configure retry com backoff+jitter e honre Retry-After.
- Ative sanitização de PII via “sanitize_fields”.
