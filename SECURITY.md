# Security Policy

## Supported Versions
- Laravel 12.x / PHP 8.2+.

## Reporting a Vulnerability
- Abra uma issue com etiqueta "security" no repositório (ou envie por contato privado da organização).
- Evite incluir PII; forneça passos de reprodução, versão e logs mínimos.

## Data Protection
- Configure `sanitize_fields` no `config/orthanc-client.php`.
- Evite exibir tokens em CLI; use `--show-token-partial` apenas quando necessário.
