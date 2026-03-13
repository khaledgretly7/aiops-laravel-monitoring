
# AIOps Laravel Monitoring System

## Overview

This project implements an **AIOps-style observability pipeline** for a Laravel API.
It demonstrates how structured logs, metrics collection, anomaly simulation, and monitoring dashboards can be integrated into a backend system.

The system collects telemetry from a Laravel application and visualizes operational behavior using Prometheus and Grafana.

---

# Architecture

Client Traffic
↓
Laravel API
↓
Telemetry Middleware
↓
Structured Logs → `storage/logs/aiops.log`
Metrics Endpoint → `/api/metrics`
↓
Prometheus (metrics collection)
↓
Grafana Dashboard (visualization)

---

# Technologies Used

Backend:

* Laravel 12
* PHP 8.2

Monitoring Stack:

* Prometheus
* Grafana

Other Components:

* Python traffic generator
* Structured logging
* Histogram-based latency monitoring

---

# Project Features

### Structured Logging

The system records detailed logs for every request.

Log file location:

```
storage/logs/aiops.log
```

Each log entry contains:

* timestamp
* correlation_id
* HTTP method
* request path
* status code
* latency (ms)
* client IP
* user agent
* payload size
* response size
* error category

These logs are used for **post-incident analysis and anomaly tracing**.

---

# Metrics Collection

Metrics are exposed through the endpoint:

```
/api/metrics
```

Prometheus scrapes this endpoint to collect application telemetry.

### Metrics Implemented

#### Request Counter

```
aiops_http_requests_total
```

Tracks the total number of HTTP requests.

Labels:

* method
* path
* status

---

#### Error Counter

```
aiops_http_errors_total
```

Tracks categorized errors.

Labels:

* method
* path
* error_category

Error categories include:

* SYSTEM_ERROR
* DATABASE_ERROR
* VALIDATION_ERROR
* TIMEOUT_ERROR

---

#### Latency Histogram

```
aiops_http_request_duration_seconds
```

Tracks request latency distribution.

Buckets used:

```
0.05
0.1
0.25
0.5
1
2.5
5
10
```

These buckets help identify slow requests and latency anomalies.

---

# API Endpoints

Test endpoints used for anomaly simulation:

```
GET /api/normal
GET /api/slow
GET /api/error
GET /api/random
GET /api/db
POST /api/validate
```

Metrics endpoint:

```
GET /api/metrics
```

---

# Traffic Simulation

Traffic can be generated using:

```
traffic_generator.py
```

This script randomly calls API endpoints to simulate real system load.

Example run:

```
python traffic_generator.py
```

This helps produce:

* request spikes
* slow responses
* error bursts

for monitoring demonstrations.

---

# Prometheus Configuration

Metrics scraping is configured in:

```
prometheus.yml
```

Example configuration:

```
scrape_configs:
  - job_name: "laravel-aiops"
    metrics_path: /api/metrics
    static_configs:
      - targets: ["host.docker.internal:8000"]
```

---

# Grafana Dashboard

Grafana visualizes system behavior using Prometheus metrics.

The dashboard includes panels for:

* Request Rate
* Error Rate
* Latency (P95)
* Request distribution by endpoint

Dashboard JSON export is included in:

```
grafana_dashboard.json
```

---

# Running the Monitoring Stack

Using Docker:

```
docker-compose up
```

Services started:

Prometheus → port 9090
Grafana → port 3000

---

# Demonstrating Anomalies

The system intentionally includes endpoints that simulate operational issues:

### Error Spike

```
/api/error
```

Generates server errors.

---

### Latency Spike

```
/api/slow
```

Simulates slow responses.

---

### Random Behavior

```
/api/random
```

Creates unpredictable request patterns.

These anomalies appear clearly in:

* Prometheus graphs
* Grafana dashboards

---

# Deliverables Included

This repository contains:

* Laravel monitoring implementation
* Telemetry middleware
* Structured logs
* Prometheus configuration
* Grafana dashboard export
* traffic_generator.py
* ground_truth.json
* Engineering report

---

# Learning Objectives

This project demonstrates:

* Observability design
* Metrics instrumentation
* Log schema design
* Monitoring pipelines
* Controlled anomaly simulation
* AIOps-style operational analysis

---

# Author

Khaled Elgreitly

Distributed Systems / AIOps Monitoring Project

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
