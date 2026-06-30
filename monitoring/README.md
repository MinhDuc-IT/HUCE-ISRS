# Monitoring Stack

Centralized logging for the HUCE-ISRS project.

## Services

- Loki on `http://localhost:3100`
- Grafana on `http://localhost:3000`
- Promtail scraping Docker container logs from each EC2 host and pushing to Loki

## Start

```bash
docker compose -f monitoring/docker-compose.yml up -d
```

## Grafana login

- Username: `admin`
- Password: `admin`

## Log flow

- Laravel apps log to `stderr`
- Docker captures container stdout/stderr
- Promtail runs on each app EC2 and scrapes local Docker logs
- Loki stores logs centrally
- Grafana queries Loki
