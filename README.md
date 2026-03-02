# ZESCO Executive Insights Dashboard Platform

A secure, executive-grade web application for ZESCO Limited that displays organization-wide performance insights across 12 directorates.

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Vue 3 + Inertia.js v2 |
| Charts | Apache ECharts 5.5 |
| Styling | Tailwind CSS 3 |
| State | Pinia |
| Auth | Azure AD OAuth + Magic Link |
| WebSocket | Laravel Reverb |
| Build | Vite 6 |
| Database | MySQL (current) → Oracle (future) |

## Architecture

```
┌─────────────────────────────────────────────────┐
│                   Vue 3 SPA                      │
│  Pages → Components → Charts → Composables       │
├─────────────────────────────────────────────────┤
│              Inertia.js Bridge                    │
├─────────────────────────────────────────────────┤
│            Laravel Controllers                    │
│     Dashboard │ DataEntry │ Admin │ Auth          │
├─────────────────────────────────────────────────┤
│              Service Layer                        │
│  DashboardService │ SimulationService │ AlertSvc  │
├─────────────────────────────────────────────────┤
│          DataSource Abstraction                   │
│  SimulationDataSource ←→ ManualInputDataSource    │
│                    ↓                              │
│           OracleDataSource (future)               │
├─────────────────────────────────────────────────┤
│           Eloquent Models + DB                    │
└─────────────────────────────────────────────────┘
```

### DataSource Pattern

The platform uses a `DataSourceInterface` abstraction that allows seamless switching between:

1. **SimulationDataSource** — Generates realistic mock data (default for development)
2. **ManualInputDataSource** — Reads from manual data entry tables
3. **OracleDataSource** — Connects to ZESCO's Oracle ERP (future implementation)

Switch via `DASHBOARD_DATA_SOURCE=simulation|manual|oracle` in `.env`.

## Quick Start

```bash
# 1. Clone and install
git clone <repo-url> zesco-dashboard
cd zesco-dashboard
composer install
npm install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Database
php artisan migrate --seed

# 4. Build frontend
npm run dev

# 5. Start server
php artisan serve
```

## Environment Configuration

Copy `.env.example` and configure:

```env
# Database
DB_CONNECTION=mysql
DB_DATABASE=zesco_dashboard

# Azure AD (for production)
AZURE_AD_CLIENT_ID=your-client-id
AZURE_AD_CLIENT_SECRET=your-client-secret
AZURE_AD_TENANT_ID=your-tenant-id

# Dashboard
DASHBOARD_DATA_SOURCE=simulation
SIMULATION_ENABLED=true
SIMULATION_INTERVAL=30
```

## Directory Structure

```
app/
├── Console/Commands/       # Artisan commands (simulate, check-alerts)
├── Http/
│   ├── Controllers/        # Dashboard, DataEntry, Admin, Auth
│   ├── Middleware/          # RoleMiddleware, SessionTimeout, HandleInertiaRequests
│   └── Requests/           # Form validation requests
├── Models/                 # Eloquent models (12 models)
└── Services/
    ├── DataSources/        # DataSourceInterface + 3 implementations
    ├── DashboardService.php
    ├── SimulationService.php
    └── AlertService.php

resources/js/
├── Pages/
│   ├── Auth/               # Login.vue
│   ├── Dashboard/          # Index.vue, DirectorateDetail.vue, Comparison.vue
│   ├── DataEntry/          # KpiEntry, FinancialEntry, ProjectEntry, RiskEntry
│   └── Admin/              # Index.vue, AuditLogs.vue
├── Components/
│   ├── Charts/             # BaseChart, Line, Bar, Pie, Gauge, Heatmap
│   ├── Dashboard/          # KpiCard
│   ├── Layout/             # AppLayout, SidebarLink
│   └── UI/                 # Card, DateRangePicker
└── Composables/            # useDarkMode, useEcho, useFormatters
```

## Directorates (12)

| Code | Name |
|------|------|
| MD | Managing Director's Office |
| GEN | Generation |
| T&S | Transmission & Systems |
| DS | Distribution & Supply |
| CS | Customer Services |
| F&S | Finance & Strategy |
| HR | Human Resources & Administration |
| ICT | Information & Communication Technology |
| L&CS | Legal & Company Secretariat |
| IA | Internal Audit |
| P&E | Projects & Engineering |
| SHE | Safety, Health & Environment |

## Roles & Permissions

| Role | View | Input Data | Admin |
|------|------|-----------|-------|
| Executive | All directorates | No | No |
| Directorate Head | Own directorate | Yes | No |
| Admin | All directorates | Yes | Yes |

## Key Features

- **Executive Dashboard** — KPI cards, trend charts, heatmaps, gauges, AI-generated text summaries
- **Directorate Drill-down** — Per-directorate KPI trends, financials, projects, risks
- **Cross-directorate Comparison** — Side-by-side metrics comparison table and charts
- **Manual Data Entry** — CRUD forms for KPIs, financials, projects, risks with audit trail
- **Simulation Engine** — Realistic data generation for demos/development
- **Dark Mode** — System-preference detection + manual toggle
- **Print-ready** — Print stylesheet for executive reports
- **Real-time Updates** — WebSocket-powered dashboard refresh via Laravel Reverb
- **Role-based Access** — Middleware-enforced permissions
- **Audit Logging** — Complete audit trail of all data changes

## Artisan Commands

```bash
# Run simulation cycle
php artisan dashboard:simulate

# Seed historical data (6 months)
php artisan dashboard:simulate --seed --months=6

# Check alert thresholds
php artisan dashboard:check-alerts
```

## Azure AD Setup

1. Register app in Azure Portal → App registrations
2. Set redirect URI: `https://your-domain.com/auth/azure/callback`
3. Add API permissions: `User.Read`, `Directory.Read.All` (for group mapping)
4. Create client secret
5. Configure `.env` with tenant ID, client ID, client secret
6. Map Azure AD groups to roles in `AzureAdController@determineRole()`

## Oracle Integration Guide

When ready to connect to ZESCO Oracle ERP:

1. Install Oracle Instant Client on the server
2. Enable the `oci8` PHP extension
3. Update `.env`:
   ```env
   DASHBOARD_DATA_SOURCE=oracle
   ORACLE_HOST=oracle-server.zesco.co.zm
   ORACLE_PORT=1521
   ORACLE_DATABASE=ZESCODB
   ORACLE_USERNAME=dashboard_reader
   ORACLE_PASSWORD=secure-password
   ```
4. Implement the TODO methods in `app/Services/DataSources/OracleDataSource.php`
5. Expected Oracle views/tables:
   - `ZESCO.VW_KPI_DATA` — KPI actuals and targets
   - `ZESCO.VW_FINANCIAL_DATA` — Budget and actuals
   - `ZESCO.VW_PROJECTS` — Project portfolio
   - `ZESCO.VW_RISKS` — Enterprise risk register

## Testing

```bash
# Unit tests
php artisan test --filter=Unit

# Feature tests
php artisan test --filter=Feature

# All tests
php artisan test
```

## Deployment

```bash
# Production build
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache

# Run migrations
php artisan migrate --force
php artisan db:seed --force
```

## License

Proprietary — ZESCO Limited. All rights reserved.
