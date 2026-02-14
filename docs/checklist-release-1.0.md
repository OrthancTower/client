# Checklist de Release 1.0 — Orthanc Client (Laravel 12.x, PHP 8.2+)

## Sumário Executivo
- Estado geral: funcional e estável para produção, com foco em cliente HTTP, fila opcional, retry com backoff+jitter, sanitização de contexto e Exception Handler centralizado.
- Itens pendentes para 1.0: cobertura de testes ≥ 80% (mensurada em CI), configuração de CI/CD, monitoramento/alertas, segurança (scan automatizado), performance (cargas), rollback documentado, ambiente de staging espelhando produção.
- Plano priorizado com responsáveis definido abaixo.

## Checklist Detalhado
- Funcionalidades core implementadas e testadas
  - Status: OK
  - Itens:
    - Cliente HTTP com retry+jitter e Retry-After [OrthancClient.php](file:///Users/rodrigocarvalho/Dev/_projects/orthanc-tower/orthanc-client/src/OrthancClient.php)
    - Fila opcional (Job) [SendNotificationJob.php](file:///Users/rodrigocarvalho/Dev/_projects/orthanc-tower/orthanc-client/src/Jobs/SendNotificationJob.php)
    - Exception Handler com classificação/canais via config [OrthancClientExceptionHandler.php](file:///Users/rodrigocarvalho/Dev/_projects/orthanc-tower/orthanc-client/src/Exceptions/OrthancClientExceptionHandler.php), [config](file:///Users/rodrigocarvalho/Dev/_projects/orthanc-tower/orthanc-client/config/orthanc-client.php)
    - Contexto e sanitização [ContextBuilder.php](file:///Users/rodrigocarvalho/Dev/_projects/orthanc-tower/orthanc-client/src/Support/ContextBuilder.php)
    - Commands: status e teste de conexão [StatusCommand.php](file:///Users/rodrigocarvalho/Dev/_projects/orthanc-tower/orthanc-client/src/Commands/StatusCommand.php), [TestConnectionCommand.php](file:///Users/rodrigocarvalho/Dev/_projects/orthanc-tower/orthanc-client/src/Commands/TestConnectionCommand.php)
    - Facade e Provider [Orthanc.php](file:///Users/rodrigocarvalho/Dev/_projects/orthanc-tower/orthanc-client/src/Facades/Orthanc.php), [OrthancClientServiceProvider.php](file:///Users/rodrigocarvalho/Dev/_projects/orthanc-tower/orthanc-client/src/OrthancClientServiceProvider.php)

- Cobertura de testes automatizados (mínimo 80%)
  - Status: Parcial
  - Situação: testes Unit/Feature presentes; cobertura não mensurada em CI.
  - Ação: integrar cobertura (pcov/xdebug) e relatório em pipeline; ampliar testes para cenários extremos (timeouts, 5xx sequenciais, Retry-After, fila habilitada).
  - Responsável: QA/Backend

- Documentação técnica completa (README, API docs, deployment guide)
  - Status: Parcial
  - Situação: README completo (API, fila, handler, retry+jitter); faltam guia de deployment, CHANGELOG/SECURITY já incluídos.
  - Ação: adicionar “Deployment Guide” com exemplos de .env, workers e observabilidade.
  - Responsável: Backend/DevRel

- Configuração de CI/CD pipeline funcionando
  - Status: Pendente
  - Situação: não há pipeline ativo.
  - Ação: criar workflow (GitHub Actions) com: composer install, testes (vendor/bin/phpunit), cobertura, lint (phpcs/phpstan), release tags.
  - Responsável: DevOps

- Monitoramento e alertas configurados (logs, métricas, uptime)
  - Status: Pendente
  - Situação: pacote provê logs e integração com Orthanc server, mas sem métricas/uptime externos documentados.
  - Ação: integrar com observabilidade (Prometheus/Grafana/New Relic/Datadog) e alertas; configurar monitoramento de fila e endpoint Orthanc.
  - Responsável: DevOps

- Segurança verificada (vulnerabilidades scan, autenticação, autorização)
  - Status: Parcial
  - Situação: medidas internas (token masked no CLI, sanitização de PII, config de exceções); faltam scans automatizados e revisão formal.
  - Ação: rodar scans (Composer Audit, Snyk, Dependabot), revisar logs para PII e validar políticas.
  - Responsável: Segurança/DevOps

- Performance validada (load testing, benchmarks)
  - Status: Pendente
  - Situação: não há testes de carga publicados.
  - Ação: executar carga simulada de notificações (fila habilitada), avaliar throughput/latência, ajustar timeout/retry/cap_ms conforme resultados.
  - Responsável: QA/DevOps

- Rollback procedure documentado
  - Status: Pendente
  - Situação: sem documento de rollback.
  - Ação: definir rollback por versionamento semântico (tags), instruções para reverter release em Composer e pipeline de deploy; considerar idempotency-key habilitado.
  - Responsável: DevOps

- Ambiente de staging espelhando produção
  - Status: Pendente
  - Situação: não informado.
  - Ação: criar staging com mesmas versões (Laravel 12, PHP 8.2+, Redis), Orthanc endpoint dedicado, workers e observabilidade idênticos; testes de fumaça.
  - Responsável: DevOps

## Bloqueadores Identificados
- Ausência de pipeline CI/CD impede medição automática de cobertura e qualidade.
- Falta de ambiente de staging e guia de deploy atrapalha validação de produção.
- Monitoramento e alertas não integrados limitam resposta a incidentes.

## Plano de Ação Priorizado
- Alta
  - Configurar CI/CD com testes, cobertura e lint.
  - Provisionar staging espelhando produção e testes de fumaça.
  - Integrar monitoramento/alertas (fila, endpoint Orthanc, logs).
- Média
  - Ampliar suite de testes (cobrir ≥ 80%): cenários extremos de retry/timeout/fila/sanitização.
  - Adicionar Deployment Guide e exemplos avançados (idempotency-key, centralização de exceções).
- Baixa
  - Documentar rollback procedure e checklist operacional.
  - Automatizar scans de segurança e atualização de dependências (Dependabot/Snyk).

## Responsáveis
- DevOps: CI/CD, staging, monitoramento/alertas, rollback, segurança (scans).
- Backend: documentação de deploy, ajustes de config, expansão de testes de integração.
- QA: carga/benchmark, cobertura de testes e validação em staging.

## Conclusão
- Núcleo pronto para produção; para o lançamento 1.0, priorizar CI/CD, staging e observabilidade, além de elevar cobertura de testes para ≥ 80% e formalizar segurança/performance. Com esses itens concluídos, o projeto atende os requisitos de release profissional.
