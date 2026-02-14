# Deployment Guide — Orthanc Client (Laravel 12.x)

## Objetivo
- Publicar o pacote em produção com segurança e observabilidade: configuração, fila, monitoramento, rollback e validação.

## Pré‑deploy
- Verifique acesso ao repositório privado (HTTPS com token ou SSH).
- Ambiente com PHP 8.2+, Laravel 12.x, Redis (se usar fila) e acesso ao Orthanc server.

## Configuração
- Publicar config: vendor:publish (tag: orthanc-client-config).
- .env:
  - ORTHANC_ENABLED=true
  - ORTHANC_API_URL e ORTHANC_API_TOKEN válidos
  - ORTHANC_QUEUE_ENABLED=true (recomendado em produção)
  - Timeout/retry/jitter conforme SLA
  - Sanitização via sanitize_fields para PII

## Fila
- Inicie worker dedicado:
  - queue:work --queue=orthanc-client --tries=3 --timeout=30
- Monitorar status do worker e métricas de consumo.

## Observabilidade
- Logs de falha: Laravel logs (fallback.log=true).
- Métricas e uptime:
  - Monitore endpoint Orthanc, latência, 5xx/429, uso de fila.
  - Alerta em caso de indisponibilidade ou falhas repetidas.

## Segurança
- Evitar exposição de token em CLI; use --show-token-partial apenas quando necessário.
- Configure sanitize_fields e retenção de logs sem PII.
- Scans de dependências em CI/CD (Dependabot/Snyk).

## Performance
- Testes de carga: enviar lote de notificações por fila, medir throughput e latência.
- Ajuste timeout e cap_ms conforme resultados.

## Rollback
- Versionamento semântico:
  - Use tags (ex.: v1.0.0) para releases.
  - Em caso de problema, reverta para tag anterior via Composer.
- Checklist:
  - Parar worker, aplicar rollback, limpar cache/config, reiniciar worker.

## Staging
- Espelhar produção:
  - Mesmas versões de PHP/Laravel/Redis.
  - Orthanc endpoint de staging.
  - Executar smoke tests (orthanc:status, orthanc:test-connection, envio via fila).

## Validação Pós‑deploy
- Rodar comandos:
  - orthanc:status
  - orthanc:test-connection
- Enviar uma notificação de teste (rota ou Tinker) e verificar recebimento.
