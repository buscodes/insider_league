# Insider Champions League — Backend

Laravel 13 REST API powering the league simulation. Built on Domain-Driven Design principles with pure domain entities fully isolated from the persistence layer.

---

## Technology

| Package | Version | Role |
|---|---|---|
| PHP | 8.3+ | Runtime |
| Laravel | 13.x | HTTP framework, DI container, Eloquent |
| SQLite | — | Development database (zero-config) |
| PHPUnit | 11.x | Unit and feature testing |

---

## Installation

```bash
# 1. Install dependencies
composer install

# 2. Environment setup
cp .env.example .env
php artisan key:generate

# 3. Database — SQLite file is auto-created; seed inserts the 4 default teams
# Always use migrate:fresh to guarantee a clean slate (running migrate --seed
# multiple times duplicates team rows and breaks the 4-team fixture guard)
php artisan migrate:fresh --seed

# 4. Start the development server
php artisan serve
```

The API is available at `http://localhost:8000/api/v1`.

> **SQLite note:** `DB_CONNECTION=sqlite` is the default. No database server is required. The `.env.example` already sets this; no changes needed for local development.

---

## Architecture

The codebase is organized into four distinct layers. Each layer has a single responsibility and depends only on layers below it.

```
app/
├── Http/                        # Presentation layer — HTTP only
│   ├── Controllers/             # Thin adapters; delegate to domain services
│   ├── Requests/                # Laravel Form Requests — input validation
│   ├── Resources/               # API transformers (snake_case JSON output)
│   └── Middleware/              # EnsureFixtureExists — guards simulation routes
│
├── Domains/League/              # Domain layer — all business logic lives here
│   ├── Entities/                # FootballMatch, Team — pure PHP, no Eloquent
│   ├── ValueObjects/            # Score, Week — immutable, self-validating
│   ├── AggregateRoots/          # LeagueAggregate — coordinates entities
│   ├── Services/                # Domain services (simulation, standings, prediction)
│   └── Contracts/               # Repository interfaces (IMatchRepository, ITeamRepository)
│
├── Infrastructure/              # Persistence layer
│   ├── Models/                  # Eloquent models (MatchModel, TeamModel)
│   └── Repositories/            # Eloquent implementations of domain contracts
│
└── Core/                        # Shared foundation
    ├── Bases/                   # BaseResource, BaseController
    ├── Constants/               # LeagueConstants (TOTAL_TEAMS, TOTAL_WEEKS, …)
    └── Exceptions/              # Domain exceptions
```

### Dependency Rule

```
Http  →  Domains  →  Infrastructure  →  Core
```

Domain entities never import Eloquent. Repositories translate between Eloquent models and domain entities at the boundary. Controllers receive domain objects from services via constructor-injected repository implementations.

---

## Core Algorithms

### `WeightedRandomSimulator`

Simulates a single match outcome based on team power ratings with a home-advantage multiplier applied to the home team's effective power.

```
home_effective_power = home_team.power * HOME_ADVANTAGE_MULTIPLIER
away_effective_power = away_team.power

# Random pick weighted by effective power
total = home_effective_power + away_effective_power
home_win_probability = home_effective_power / total
```

Goals are generated independently using a Poisson-like distribution seeded by the winning team's effective power. A draw is possible when the random draw falls in the neutral zone between the two probabilities.

### `ChampionshipPredictionService`

Calculates championship win probabilities using a **1,000-iteration Monte Carlo simulation** with **Dynamic Early Exit** optimization.

```
For each iteration (1 … 1000):
    1. Clone the current league state (played matches kept, unplayed matches re-simulated)
    2. Simulate all remaining unplayed matches using WeightedRandomSimulator
    3. Compute final standings
    4. Record the championship winner

    Dynamic Early Exit: if one team has won > 95% of completed iterations
    and at least 100 have run, exit early — further iterations cannot change
    the meaningful outcome.

Result: championship_prediction[team] = wins / total_iterations * 100
```

Predictions are only computed and returned when at least 4 weeks have been played (`MIN_PREDICTION_WEEK = 4`). Before that, the `/predictions` endpoint returns `data: null`.

---

## API Endpoints

| Method | Path | Middleware | Description |
|---|---|---|---|
| `GET` | `/api/v1/teams` | — | List all 4 teams |
| `GET` | `/api/v1/fixtures` | — | List all matches |
| `POST` | `/api/v1/fixtures/generate` | — | Generate round-robin fixture |
| `GET` | `/api/v1/league-table` | — | Current standings |
| `GET` | `/api/v1/predictions` | — | Monte Carlo predictions (null before week 4) |
| `POST` | `/api/v1/simulation/play-week` | `EnsureFixtureExists` | Simulate next unplayed week |
| `POST` | `/api/v1/simulation/play-all` | `EnsureFixtureExists` | Simulate all remaining weeks |
| `PATCH` | `/api/v1/matches/{id}` | `EnsureFixtureExists` | Manually update a match score |
| `POST` | `/api/v1/simulation/reset` | — | Reset entire season |

### Response Envelope

Every response — success or error — is wrapped in a consistent JSON envelope:

```json
{
  "success": true,
  "message": "Fixtures generated successfully.",
  "data": [ ... ],
  "meta": {
    "timestamp": "2026-06-21T18:00:00Z",
    "version": "1.0.0",
    "pagination": null
  }
}
```

Validation errors (422) follow the same envelope with `success: false` and `data.errors` containing per-field messages:

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "data": {
    "errors": {
      "home_score": ["The home score must be at least 0."]
    }
  }
}
```

---

## Testing

```bash
php artisan test
```

| Test Suite | File | Cases |
|---|---|---|
| Unit | `FootballMatchTest` | 5 |
| Unit | `FixtureGeneratorServiceTest` | 8 |
| Unit | `ChampionshipPredictionServiceTest` | 7 |
| Unit | `WeightedRandomSimulatorTest` | 5 |
| Unit | `StandingsCalculationServiceTest` | 9 |
| Feature | `ExampleTest` | 1 |

**35 test methods total.** All tests run against an in-memory SQLite database; no external services required.

### What is tested

- `FootballMatch` entity invariants (score assignment, played state transitions)
- `FixtureGeneratorService` — correct number of matches, home/away symmetry, week distribution
- `WeightedRandomSimulator` — output is always a valid played match, power differential affects win rates
- `StandingsCalculationService` — points calculation, goal difference ordering, tie-breaking
- `ChampionshipPredictionService` — probabilities sum to 100%, dominant team approaches 100% in deterministic scenarios, Dynamic Early Exit triggers correctly

---

## Domain Constants

Defined in `app/Core/Constants/LeagueConstants.php`:

```php
// League structure
const TOTAL_TEAMS          = 4;
const TOTAL_WEEKS          = 6;
const MATCHES_PER_WEEK     = 2;
const TOTAL_MATCHES        = 12;

// Prediction
const MIN_PREDICTION_WEEK    = 4;
const MONTE_CARLO_ITERATIONS = 1000;

// Match simulator
const HOME_ADVANTAGE  = 10;   // additive bonus applied to home team power
const DRAW_THRESHOLD  = 0.15; // max win-probability gap for a draw to be possible
const DRAW_CHANCE     = 0.25; // probability of draw when teams are evenly matched
```

No magic numbers appear anywhere in the service or entity code.
