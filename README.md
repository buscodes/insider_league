# Insider Champions League

A full-stack football league simulation featuring a 4-team round-robin fixture, weighted match simulation with home-advantage logic, and Monte Carlo–based championship predictions that activate from the 4th week onward.

---

## Technology Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13, PHP 8.3, SQLite |
| Frontend | Vue 3 (Composition API), TypeScript 5.4+, Vite 5 |
| State Management | Pinia |
| HTTP Client | Axios |
| Styling | Tailwind CSS 3 |
| Testing | PHPUnit (35+ unit & feature tests) |

---

## Project Structure

```
insider_champions_league/
├── backend/          # Laravel API — DDD architecture
├── frontend/         # Vue 3 SPA — Atomic Design
├── docs/             # Phase-by-phase summaries (EN + TR)
├── ARCHITECTURE.md   # Full system architecture reference
└── README.md         # ← you are here
```

---

## System Setup

> **Order matters.** The backend must be running before the frontend is started, as the Vue app depends on the Laravel API at boot.

### Step 1 — Backend

```bash
cd backend

# Install PHP dependencies
composer install

# Copy and configure environment
cp .env.example .env
php artisan key:generate

# Run migrations and seed the 4 default teams
# Use migrate:fresh (not migrate) to guarantee a clean slate
php artisan migrate:fresh --seed

# Start the development server (default: http://localhost:8000)
php artisan serve
```

### Step 2 — Frontend

```bash
cd frontend

# Install Node dependencies
npm install

# Copy and configure environment
cp .env.example .env
# VITE_BASE_API_URL=http://localhost:8000/api/v1

# Start the development server (default: http://localhost:5173)
npm run dev
```

Open `http://localhost:5173` in your browser. The app connects to the Laravel API automatically.

---

## Architectural Overview

The project enforces strict **separation of concerns** across both backend and frontend, aligned under a shared DDD philosophy.

### Backend — Domain-Driven Design

```
Http (Controllers / Requests / Resources / Middleware)
    ↓
Domains/League (Entities, Value Objects, Aggregate Roots, Services)
    ↓
Infrastructure (Eloquent Models, Repositories)
    ↓
Core (Base classes, Constants, Exceptions)
```

- Domain entities (`FootballMatch`, `Team`) are **pure PHP classes**, completely isolated from Eloquent.
- All persistence is handled by repository contracts (`ITeamRepository`, `IMatchRepository`) with Eloquent implementations injected via the service container.
- Business rules live exclusively in the domain layer — controllers are thin HTTP adapters.

### Frontend — Atomic Design + Layered Services

```
presentation (Views / Components / Stores)
    ↓
application (Services — TeamService, FixtureService, …)
    ↓
core (Models, Constants — BaseResponse, ApiPaths, Value, LeagueConstants)
```

- Components are composed bottom-up: Atoms → Molecules → Organisms → Templates → Views.
- All reactive state lives in a single Pinia store (`leagueStore`). No component mutates state directly.
- No magic literals anywhere: every endpoint string comes from `ApiPaths`, every domain number from `LeagueConstants`, every primitive from `Value`.

---

## API Endpoints

| Method | Path | Description |
|---|---|---|
| `GET` | `/api/v1/teams` | List all 4 teams |
| `GET` | `/api/v1/fixtures` | List all matches |
| `POST` | `/api/v1/fixtures/generate` | Generate the round-robin fixture |
| `GET` | `/api/v1/league-table` | Current standings |
| `GET` | `/api/v1/predictions` | Monte Carlo championship predictions |
| `POST` | `/api/v1/simulation/play-week` | Simulate the next unplayed week |
| `POST` | `/api/v1/simulation/play-all` | Simulate all remaining weeks |
| `PATCH` | `/api/v1/matches/{id}` | Manually update a match score |
| `POST` | `/api/v1/simulation/reset` | Reset the entire season |

---

## Key Domain Rules

- **4 teams, 6 weeks, 2 matches per week** — full home-and-away round-robin (12 matches total).
- **Home advantage** — the home team receives a power bonus during simulation.
- **Predictions activate at week 4** — `ChampionshipPredictionService` runs 1,000 Monte Carlo iterations with Dynamic Early Exit to determine championship percentages.
- **Score validation** — scores must be non-negative integers; 422 errors are normalized to per-field `{ errors: Record<string, string[]> }` and displayed inline on the score inputs.
- **No page reloads** — all state transitions (play, reset, score edit) flow through the Pinia store reactively.
- **3-phase UI state machine** — Phase 1 (teams overview) → Phase 2 (fixture review, no live data) → Phase 3 (live simulation dashboard). Each phase has a distinct screen; the league table and predictions are never shown before simulation starts.
