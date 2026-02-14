# Changelog

## Unreleased
- Retry com backoff+jitter e suporte a Retry-After.
- Sanitização de contexto com `sanitize_fields` e flags `include_email/include_name`.
- Segurança no CLI: máscara de token por padrão; `--show-token-partial` opcional.
- Centralização de classificação/canais de exceções em config.
- Contrato público `OrthancClientContract`.
- Testes unit/feature (retry, Retry-After, sanitização, comandos).
