# Insider Champions League — Frontend

Vue 3 single-page application for the league simulation. Built with the Composition API, strict TypeScript, Atomic Design component architecture, and a single Pinia store as the reactive state machine.

---

## Technology

| Package | Version | Role |
|---|---|---|
| Vue | 3.4.x | UI framework (Composition API, `<script setup>`) |
| TypeScript | 5.4+ | Static typing throughout |
| Vite | 5.4.x | Build tool and dev server |
| vue-tsc | 2.x | Type-checks `.vue` files at build time |
| Pinia | 2.x | State management |
| Vue Router | 4.x | Client-side routing with lazy loading |
| Axios | 1.7.x | HTTP client |
| Tailwind CSS | 3.4.x | Utility-first styling |

---

## Installation

```bash
# 1. Install dependencies
npm install

# 2. Environment setup
cp .env .env.local
# Set the backend URL:
# VITE_BASE_API_URL=http://localhost:8000/api/v1

# 3. Start the development server (http://localhost:5173)
npm run dev

# 4. Production build — runs vue-tsc type check before bundling
npm run build
```

> **Backend must be running first.** `leagueStore.initialize()` fires on mount and immediately calls the API.

---

## Project Structure

```
src/
├── core/                          # Framework-agnostic foundation — imported by all layers
│   ├── constants/
│   │   ├── ApiPaths.ts            # All backend endpoint strings
│   │   ├── LeagueConstants.ts     # Domain numbers (TOTAL_WEEKS, MIN_PREDICTION_WEEK, …)
│   │   └── Value.ts               # Universal primitives (TRUE, FALSE, ZERO, ONE, EMPTY_STRING)
│   └── models/
│       ├── ApiError.ts            # Normalized error shape produced by CredentialsInterceptor
│       ├── BaseMeta.ts            # { timestamp, version, pagination: PaginationMeta | null }
│       ├── BaseResponse.ts        # BaseResponse<T> — wraps every API response
│       ├── PaginationMeta.ts      # Pagination fields
│       └── ValidationErrorBag.ts  # { errors: Record<string, string[]> }
│
├── infrastructure/                # HTTP communication layer
│   ├── client/
│   │   └── DefaultClient.ts       # Single Axios instance — baseURL from VITE_BASE_API_URL
│   ├── interceptors/
│   │   └── CredentialsInterceptor.ts  # Normalizes 4xx responses to ApiError; side-effect import
│   └── services/
│       ├── IApiService.ts         # get<T> / post<T> / patch<T> interface
│       └── ApiService.ts          # IApiService implementation
│
├── application/                   # Domain-specific business layer
│   ├── models/
│   │   ├── Match.ts               # id, week, home_team, away_team, scores, is_played
│   │   ├── Prediction.ts          # team_name, championship_prediction (%)
│   │   ├── StandingRow.ts         # Full standings row including goals_for/against
│   │   └── Team.ts                # id, name, power
│   └── services/
│       ├── FixtureService.ts      # getFixtures(), generateFixtures()
│       ├── LeagueService.ts       # getLeagueTable(), getPredictions()
│       ├── SimulationService.ts   # playNextWeek(), playAll(), resetLeague(), updateMatchScore()
│       └── TeamService.ts         # getTeams()
│
└── presentation/                  # Visual layer — Atomic Design
    ├── stores/
    │   └── leagueStore.ts         # Single Pinia store — all UI state and actions
    ├── components/
    │   ├── atoms/
    │   │   ├── AppButton.vue      # variant: primary | secondary | danger; loading state
    │   │   ├── AppBadge.vue       # type: 'played' | 'pending'
    │   │   └── ScoreInput.vue     # v-model + error prop; red border on validation failure
    │   ├── molecules/
    │   │   ├── MatchRow.vue       # Display / edit mode; score error propagation
    │   │   └── StandingRow.vue    # One table row with all 10 stat columns
    │   └── organisms/
    │       ├── FixturePreview.vue  # Phase 2 only: 3×2 grid of week cards (no scores)
    │       ├── LeagueTable.vue    # Full standings table with thead
    │       ├── WeeklyFixtures.vue # Week tab navigation + MatchRow list
    │       └── PredictionPanel.vue # Visible from week 4; animated progress bars
    ├── templates/
    │   └── DashboardTemplate.vue  # Responsive 2-column grid layout (Phase 3 only)
    ├── router/
    │   └── index.ts               # Lazy-loaded routes
    └── views/
        └── HomeView.vue           # 3-phase state machine (Pre-league / Fixture review / Live sim)
```

---

## Design Methodology — Atomic Design

Components are composed strictly **bottom-up**. Each level knows only the level below it; nothing is skipped.

```
Atoms           AppButton, AppBadge, ScoreInput
  ↓
Molecules       MatchRow (composes Atoms), StandingRow
  ↓
Organisms       FixturePreview (Phase 2)
                LeagueTable, WeeklyFixtures, PredictionPanel (Phase 3)
  ↓
Templates       DashboardTemplate (Phase 3 grid layout)
  ↓
Views           HomeView (3-phase state machine)
```

**Atoms are dumb** — they receive props and emit events; they never import the store. Store access begins at the Molecule level (`MatchRow`) and above.

---

## State & Error Management

### `leagueStore.ts` as UI State Machine

The store is the single source of truth for all application state. No component holds derived data independently.

**State:**

| Field | Type | Purpose |
|---|---|---|
| `teams` | `Team[]` | Team roster |
| `fixtures` | `Match[]` | All season matches |
| `standings` | `StandingRow[]` | Current league table |
| `predictions` | `Prediction[]` | Monte Carlo percentages |
| `currentWeek` | `number` | Completed weeks (drives `hasPredictions`) |
| `isLoading` | `boolean` | Disables all action buttons during async operations |
| `fixtureGenerated` | `boolean` | `true` → Phase 2 (fixture review) |
| `isSimulationStarted` | `boolean` | `true` → Phase 3 (live simulation dashboard) |
| `scoreErrors` | `Record<number, Record<string, string[]>>` | Per-match validation errors |

**3-Phase State Machine:**

| `fixtureGenerated` | `isSimulationStarted` | Phase | Screen |
|---|---|---|---|
| `false` | `false` | **1 — Pre-league** | Tournament Teams table + Generate Fixtures |
| `true` | `false` | **2 — Fixture review** | 6-week grid cards + Start Simulation |
| `true` | `true` | **3 — Live simulation** | Full dashboard + sticky action header |

`resetLeague()` sets both flags to `false` → returns to Phase 1.

**Computed:**

```typescript
hasPredictions  // currentWeek >= LeagueConstants.MIN_PREDICTION_WEEK
allPlayed       // every fixture.is_played === true
activeWeek      // week of the first unplayed match (ring highlight in WeeklyFixtures)
fixturesByWeek  // Match[] grouped by week number
```

### 422 Validation Error Flow

When `PATCH /matches/{id}` returns a 422, the error travels through four layers:

```
CredentialsInterceptor
    normalizes AxiosError → ApiError { message, status, errors }
        ↓
leagueStore.updateMatchScore()
    catches ApiError, writes errors to scoreErrors[matchId]
    returns false  →  MatchRow stays in edit mode
        ↓
MatchRow.vue
    homeError = computed(() => store.scoreErrors[matchId]?.home_score?.[0] ?? null)
    awayError = computed(() => store.scoreErrors[matchId]?.away_score?.[0] ?? null)
        ↓
ScoreInput.vue
    receives error prop, renders red border + message below input
```

Errors are cleared reactively when the user starts typing (via `watch` on score refs in `MatchRow`) — no re-submission required to dismiss the error state.

### `updateMatchScore` Return Contract

```typescript
updateMatchScore(matchId, homeScore, awayScore): Promise<boolean>
// true  → success; MatchRow closes edit mode
// false → ApiError caught; MatchRow stays open, errors visible in ScoreInput
```

---

## Dependency Direction

```
┌─────────────────────────────────────────────────────────┐
│  presentation  (Views / Components / Stores)            │
│                         imports ↓                       │
├─────────────────────────────────────────────────────────┤
│  application   (Services / Models)                      │
│                         imports ↓                       │
├─────────────────────────────────────────────────────────┤
│  core          (Models / Constants)                     │
└─────────────────────────────────────────────────────────┘
```

**Rules:**
- `core` imports nothing — it is the foundation.
- `application` imports only from `core`.
- `presentation` imports from both `application` and `core`.
- `infrastructure` imports only from `core`; it is never imported by `application` or `presentation` directly (services call `DefaultClient` and `ApiPaths`).
- Reverse imports (e.g. `application → presentation`) are strictly forbidden.

---

## No Magic Literals

Three constants files eliminate every hard-coded string and number:

```typescript
// Value.ts — universal primitives
Value.TRUE   Value.FALSE   Value.ZERO   Value.ONE   Value.EMPTY_STRING

// LeagueConstants.ts — domain numbers
LeagueConstants.TOTAL_TEAMS          // 4
LeagueConstants.TOTAL_WEEKS          // 6
LeagueConstants.MATCHES_PER_WEEK     // 2
LeagueConstants.TOTAL_MATCHES        // 12
LeagueConstants.MIN_PREDICTION_WEEK  // 4

// ApiPaths.ts — endpoint strings
ApiPaths.teams              // '/teams'
ApiPaths.fixtures           // '/fixtures'
ApiPaths.fixturesGenerate   // '/fixtures/generate'
ApiPaths.leagueTable        // '/league-table'
ApiPaths.predictions        // '/predictions'
ApiPaths.simulationPlayWeek // '/simulation/play-week'
ApiPaths.simulationPlayAll  // '/simulation/play-all'
ApiPaths.simulationReset    // '/simulation/reset'
ApiPaths.match(id)          // '/matches/:id'
```

---

## TypeScript Configuration

Key `tsconfig.json` decisions:

```json
{
  "moduleResolution": "bundler",
  "allowImportingTsExtensions": true,
  "strict": true,
  "noUnusedLocals": true,
  "noUnusedParameters": true,
  "paths": { "@/*": ["./src/*"] }
}
```

- `strict: true` — enables `strictNullChecks` and all strict checks; `null` must be handled explicitly
- `noUnusedLocals` + `noUnusedParameters` — dead code caught at compile time
- `@/` path alias — no relative `../../` import chains anywhere in the codebase
- `any` is prohibited — every value has an explicit type

---

## Build Verification

```bash
npm run build
# Runs vue-tsc --noEmit first, then vite build

# Expected output:
# ✓ 112 modules transformed
# dist/assets/HomeView-*.js   ~13 kB   ← lazy chunk (route-level code splitting)
# dist/assets/index-*.js     ~140 kB   ← vendor bundle
# ✓ built in ~600ms
```

`HomeView` is emitted as a separate lazy chunk — confirmed by the `() => import(...)` syntax in the router.
