# Quickstart de Instalação — VCS (branch main)

## Escolha o método de acesso

- HTTPS + token (recomendado) ou SSH + chave com acesso ao repositório privado.

## Passos (HTTPS + token)

- Configure token do provedor para o Composer (escopo leitura do repo).
- Adicione o repositório privado ao Composer do seu app (tipo VCS, URL HTTPS).
- Instale o pacote apontando para a branch main como “dev-main”.
- Publique a configuração e ajuste o .env; valide com os comandos CLI.

## Passos (SSH)

- Garanta chave SSH e acesso ao repositório.
- Adicione o repositório VCS com URL SSH.
- Instale “dev-main”; publique config, ajuste .env e valide.

## Observações

- Em minimum-stability: stable, apontar explicitamente para “dev-main” (ou “^1.0@dev”) permite instalar versão de desenvolvimento.
- Se publicar uma tag estável (ex.: v1.0.0), pode instalar com “^1.0” sem “dev”.
- Para auto‑reporte de exceções sem editar `app/Exceptions/Handler.php`, habilite no `.env`:
  - `ORTHANC_CLIENT_OVERRIDE_HANDLER=true`
- A URL da API deve ser a base do servidor (sem “/api”); o client já chama “/api/\*” internamente.
