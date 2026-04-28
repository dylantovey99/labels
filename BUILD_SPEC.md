# Album Auto-Sequencer — Build Spec for Claude Code

This is a build specification for an automated photo album layout system. It is structured for sequential implementation: each phase produces shippable, testable software. Phases 0–4 build the core CLI pipeline. Phase 5 puts a web app around it. Phases 6–9 progressively automate the operational workflow.

**Read this whole document before starting Phase 0.** Several decisions in later phases constrain choices in earlier phases (database vs filesystem, web framework, output format). The "Critical decisions — do not undo" section at the end captures load-bearing choices that should not be revisited without explicit reason.

---

## 1. Project overview

**What this builds:** A pipeline that takes a photographer's curated set of 60–150 selected images and produces a sequenced, laid-out photo album in print-ready format, plus a soft-proofed HTML review for the photographer to approve, plus an operator dashboard for the production staff who shepherd shoots through the workflow.

**Who uses it:** Production operators at a premium photo printing business (initially Brilliant Prints / BPro). Photographers receive review URLs and emails but never log in.

**What makes this different from off-the-shelf album software:** The system models photographic narrative — heroes paced as anchor points, scenes as clusters, breath as deliberate whitespace, B&W and colour mixed deliberately, facing pages composed across the gutter. It treats sequencing as a craft problem solved by a senior album designer's reasoning, not as image-tiling.

**Scope discipline:** The system sequences and lays out delivered selects. It does not cull raw shoots, does not design covers, does not generate text overlays, does not do its own colour management beyond honouring lab profiles. These boundaries exist to keep v1 shippable.

---

## 2. Architecture

Three sequential AI/processing passes plus an operational layer:

**Pass 1 — Vision classification (per image).** A vision-capable Claude model is called once per image. Returns a structured JSON record characterising shot type, subject, composition, tonality, monochrome status, crop tolerances, and narrative role. Pixel-level work happens only here.

**Pass 2 — Whole-album sequencer (one call).** A reasoning Claude call is given the metadata for *every* image at once and produces a spread-by-spread layout plan. Sees no pixels — reasons over Pass 1's metadata. This is where the photography craft lives.

**Pass 3 — Deterministic renderer.** Pure code. Takes Pass 2's layout JSON, the source images, and an album physical spec; produces processed JPEGs in lab-template format plus an HTML review interface with soft proofing. No AI in this stage.

**Operational layer (Cowork-style orchestration).** A single FastAPI web app that serves both the operator dashboard and the photographer review HTML. Wraps the three passes, manages folder-based shoot state, handles photographer email correspondence, tracks SLAs.

The two-pass split (vision per image, then sequencing on metadata only) is the architectural insight that makes this work better than commodity album software. Don't merge them.

---

## 3. Repo layout

Single repository, single Python package. No microservices.

```
album-pipeline/
├── pyproject.toml
├── README.md
├── BUILD_SPEC.md                  this document
├── .env.example
├── config/
│   ├── cowork-config.yaml         operator + photographer config (see §5.1)
│   ├── album-skus/                one YAML per SKU (see §5.2)
│   ├── lab-templates/             one YAML per supported lab (see §5.3)
│   └── prompts/
│       ├── pass1-system.md        verbatim from §7
│       ├── pass2-system.md        verbatim from §8
│       └── intent-classifier-system.md
├── src/album_pipeline/
│   ├── __init__.py
│   ├── pass1/                     vision classifier
│   ├── pass2/                     sequencer
│   ├── pass3/                     renderer
│   │   ├── geometry/              data-driven layout engine
│   │   ├── image/                 processing pipeline
│   │   ├── renderers/             review_html, lab_jpeg, idml, pdf
│   │   └── soft_proof/
│   ├── orchestration/             folder state, journal, locks
│   ├── webapp/                    FastAPI: dashboard + review
│   │   ├── api/
│   │   ├── templates/             Jinja2
│   │   └── static/
│   ├── email/                     Gmail/Missive integration, templates
│   ├── intent/                    reply intent classifier
│   ├── shared/                    types, utils, config loader
│   └── cli/                       Typer CLI entry points
├── data/
│   ├── eval/                      hand-labelled evaluation set
│   │   ├── images/
│   │   └── ground_truth/
│   └── cache/                     SQLite cache (Pass 1 results)
├── templates/email/               Markdown email templates
└── tests/
    ├── fixtures/
    │   ├── shoots/                small synthetic shoots for end-to-end tests
    │   └── images/                small public-domain images
    └── ...
```

The on-disk shoot folder structure is separate from the repo — it lives at a path configured in `cowork-config.yaml`, typically `~/BrilliantPrints/Albums/`. Shoot folder layout is documented in §6 Phase 5.

---

## 4. Tech stack

**Language:** Python 3.11+. One language, one dependency tree.

**Core dependencies:**
- `anthropic` — Claude API client. Use structured outputs / tool use for Pass 1 and Pass 2 if available for the chosen model; falls back to JSON-mode parsing.
- `pillow`, `pillow-heif` — image loading including HEIC.
- `imagehash` — perceptual hashing for near-duplicate detection.
- `littlecms` (Python bindings: `littlecms-py` or invoke `lcms2` via subprocess) — ICC profile conversion. PIL's built-in colour management is not lab-grade.
- `pyyaml` — config loading.
- `pydantic` v2 — schema validation throughout.
- `fastapi`, `uvicorn` — web app.
- `jinja2` — HTML templates.
- `typer` — CLIs.
- `simple-idml` (or generate IDML XML directly) — optional adapter.
- `pikepdf` — optional PDF adapter.
- `httpx` — for any external HTTP not via SDKs.
- `pytest`, `pytest-asyncio` — tests.
- `ruff` — linting + formatting.
- `python-frontmatter` — for parsing email template Markdown with metadata.

**Frontend in the web app:** Jinja2 server-rendered HTML + Tailwind (CDN-loaded for v1; can pre-build later) + `htmx` for interactivity. No SPA framework. The dashboard and review interface are content-heavy, action-light; SPAs are the wrong tool. Total JS in v1 should be under 200 lines.

**Storage:**
- Shoot state: filesystem (folder location = state).
- Configuration: YAML files.
- Pass 1 cache: SQLite at `data/cache/pass1.db`.
- Audit log: `journal.jsonl` per shoot, append-only.
- No app database. Folder structure plus SQLite cache is sufficient for years of operation.

**External APIs:**
- Anthropic API — required.
- Gmail API or Missive API — for email integration in Phase 7.
- No print lab APIs in v1.

---

## 5. Configuration schemas

These are contracts used across phases. Get them right early; changing them later cascades.

### 5.1 `cowork-config.yaml`

```yaml
albums_root: /Users/dylan/BrilliantPrints/Albums

operators:
  meagan:
    email: meagan@brilliantprints.com.au
    digest_time: "09:00"
    digest_channel: email          # email | slack
    realtime_channel: desktop      # desktop | slack | email
    out_of_office: []              # list of {start, end, backup}
  juli:
    email: juli@brilliantprints.com.au
    digest_time: "07:30"
    digest_channel: slack
    realtime_channel: slack
    out_of_office: []

senior_reviewer: dylan
intake_default_primary: meagan

photographers:
  - name: jane-doe-photography
    email: jane@example.com
    default_house_style: editorial
    default_album_sku: 12x12_lay_flat_lustre
    default_sla_tier: standard
    forward_to_client_policy: permissive   # permissive | restrictive

sla_tiers:
  standard:
    total_days: 7
    qc_warn_hours: 24
    review_warn_days: 3
    review_escalate_days: 7
  rush:
    total_hours: 48
    qc_warn_hours: 4
    review_warn_hours: 24
    review_escalate_hours: 72
  express:
    total_hours: 24
    qc_warn_minutes: 60
    review_warn_hours: 4
    review_escalate_hours: 24

api:
  anthropic_api_key_env: ANTHROPIC_API_KEY
  vision_model: claude-sonnet-4-7
  sequencer_model: claude-sonnet-4-7
  intent_classifier_model: claude-haiku-4-5

web:
  host: 127.0.0.1
  port: 8088
  external_base_url: https://albums.brilliantprints.com.au   # for review URLs

print_handoff:
  type: folder_move                # folder_move | email | myob
  destination: /Volumes/Production/PrintQueue/
  notification:
    type: email
    to: production@brilliantprints.com.au
```

### 5.2 Album SKU spec — `config/album-skus/12x12_lay_flat_lustre.yaml`

```yaml
sku_id: 12x12_lay_flat_lustre
display_name: "12×12 Lay-Flat Lustre"
page_width_mm: 305
page_height_mm: 305
bleed_mm: 3
binding_type: lay_flat             # lay_flat | traditional
hinge_zone_mm: 8                   # only used if lay_flat
outer_safe_margin_mm: 12
inner_safe_margin_mm: 12
paper_type: lustre                 # lustre | glossy | matte | layflat_hd | press_matte
output_dpi: 300
output_color_space: sRGB
target_spread_count: 30            # default; overridable per shoot
lab_template: brilliant_internal
```

### 5.3 Lab template — `config/lab-templates/brilliant_internal.yaml`

```yaml
template_id: brilliant_internal
display_name: "Brilliant Prints Internal"

output_format: jpeg                # jpeg | idml | pdf
jpeg_quality: 92
color_space: sRGB
icc_profile_path: null             # path to .icc for soft proofing; null skips

folder_structure: per_spread       # per_spread | flat
file_naming: "spread_{spread:03d}/page_{side}.jpg"
                                   # tokens: {spread}, {side}, {slot}, {image_id}
include_bleed: true                # true: bled JPEGs; false: trimmed to safe area
manifest_format: json              # json | xml | none
manifest_filename: album_manifest.json

cover_handling: manual             # manual | hero_export
cover_export_path: cover/cover_hero.jpg
```

### 5.4 Per-shoot `job.json`

```json
{
  "shoot_id": "2026-04-smith-wedding",
  "photographer": {
    "name": "jane-doe-photography",
    "email": "jane@example.com"
  },
  "album_sku": "12x12_lay_flat_lustre",
  "target_spread_count": 30,
  "house_style": "editorial",
  "sla_tier": "standard",
  "due_date": "2026-05-05",
  "primary_operator": "meagan",
  "backup_operator": "juli",
  "manual_mode": false,
  "allow_omission": false,
  "intake_notes": "Photographer noted preference for B&W on ceremony images.",
  "created_at": "2026-04-28T10:14:00+10:00"
}
```

### 5.5 Pass 1 output schema (per image)

See full schema in §7. Pydantic model lives in `src/album_pipeline/pass1/schema.py`.

### 5.6 Pass 2 output schema

See full schema in §8. Pydantic model lives in `src/album_pipeline/pass2/schema.py`.

---

## 6. Build phases

Each phase produces software you can stop at and have working capability. **Do not attempt to build phases out of order.** The earlier phases include the test fixtures and evaluation harness that later phases depend on.

### Phase 0 — Project scaffolding

**Deliverable:** A working Python package with config loading, logging, and an empty CLI.

**Build:**
- Initialise repo: `pyproject.toml`, dependency manifest, `ruff` config, `pytest` config.
- `src/album_pipeline/shared/config.py` — load and validate `cowork-config.yaml` and album SKU files into Pydantic models.
- `src/album_pipeline/shared/logging.py` — structured logging to stdout + per-shoot log file.
- `src/album_pipeline/cli/main.py` — Typer entry point with subcommands stubbed: `classify`, `sequence`, `render`, `dashboard`, `intake`.
- `tests/test_config.py` — round-trip config loading.
- `data/eval/` skeleton — placeholder for evaluation set.
- `.env.example` listing required env vars (`ANTHROPIC_API_KEY` minimum).

**Acceptance criteria:**
- `pip install -e .` succeeds.
- `album-pipeline --help` lists subcommands.
- `pytest` passes (config tests only).
- `ruff check .` passes.

### Phase 1 — Pass 1 CLI (vision classifier)

**Deliverable:** A CLI that takes a folder of images and produces `pass1/manifest.json` with one classified record per image, perceptual-hash-based near-duplicate groups, and EXIF stitched in.

**Build:**
- `src/album_pipeline/pass1/schema.py` — Pydantic model for the classification record (matches the JSON schema in §7).
- `src/album_pipeline/pass1/classifier.py` — single-image classification function:
  - Loads image, resizes to 1024px on long edge for the API call (saves cost, no quality loss for this task).
  - Calls Claude vision API with the system prompt from `config/prompts/pass1-system.md`.
  - Uses structured outputs / tool use if available; otherwise JSON-mode parsing with one retry.
  - Returns validated Pydantic record or raises a typed exception.
- `src/album_pipeline/pass1/cache.py` — SQLite-backed cache. Key: `sha256(image_bytes) + prompt_version + model_version`. **The version components are critical** — without them, prompt iteration silently returns stale results.
- `src/album_pipeline/pass1/exif.py` — extract `DateTimeOriginal`, lens, focal length, aperture using PIL. Tolerant of missing EXIF.
- `src/album_pipeline/pass1/duplicates.py` — perceptual hashing across the full set; cluster images with phash distance < 6 into `near_duplicate_group_id` groups.
- `src/album_pipeline/pass1/runner.py` — orchestrate classification across a folder with parallel workers (4–8). Write `pass1/manifest.json` containing all records.
- CLI: `album-pipeline classify <shoot_path> [--no-cache] [--max-workers N]`
- Eval harness: `src/album_pipeline/pass1/eval.py` runs against `data/eval/` and reports score against hand-labels (start with simple categorical agreement; expand later).

**Tests:**
- Unit tests for cache key generation, EXIF parsing, perceptual hashing, schema validation.
- Integration test: classify a fixture shoot of 5 small images; verify manifest structure.
- The Anthropic API call is mocked in tests using a recorded response. No real API calls in CI.

**Acceptance criteria:**
- `album-pipeline classify tests/fixtures/shoots/tiny/` produces a valid `pass1/manifest.json`.
- Re-running with cache produces identical output without making API calls.
- Changing the prompt version invalidates cache entries.
- Eval harness runs and reports a score.

**Calibration check (manual, not automated):** After implementation, run on one real curated select set of 80–120 images. Audit hero_score distribution: expect ~10–20% scoring 8+, ~30–40% scoring 6–7. If hero scores are skewed (>25% scoring 8+, or <5%), the prompt needs calibration tuning before Phase 2 is useful.

### Phase 2 — Pass 2 CLI (sequencer)

**Deliverable:** A CLI that takes a Pass 1 manifest plus a job spec and produces a `layout-v1.json` describing every spread.

**Build:**
- `src/album_pipeline/pass2/schema.py` — Pydantic model for layout JSON (album_summary, spreads[], omitted[]).
- `src/album_pipeline/pass2/sequencer.py` — single Claude API call:
  - Loads system prompt from `config/prompts/pass2-system.md`.
  - Builds user message JSON payload from manifest + job spec.
  - Uses structured outputs if available; otherwise JSON parse with one retry on parse failure (retry appends a constraint reminder to the system prompt).
  - Validates the response against the Pydantic schema.
- `src/album_pipeline/pass2/validation.py` — post-response checks:
  - Every `image_id` in spreads exists in the input manifest.
  - No `image_id` used more than once (warn if violated).
  - Spread count within target ±2 (warn if violated).
  - Hero spacing rule honoured (warn if heroes adjacent).
  - `moment_essential` images all placed.
  - If `allow_omission=false`, no images in the omitted array.
- `src/album_pipeline/pass2/versioning.py` — write `layout-v{N}.json` and update `layout-current.json` symlink. Initialise as v1 on first run; bump on revision re-runs.
- CLI: `album-pipeline sequence <shoot_path> [--allow-omission] [--revision-notes NOTES_FILE]`
- The `--revision-notes` flag appends notes to the system prompt for re-runs after photographer revision requests.

**Tests:**
- Unit tests for validation rules.
- Integration test using a recorded API response: feed a fixture manifest, verify layout structure and validation results.

**Acceptance criteria:**
- `album-pipeline sequence tests/fixtures/shoots/tiny/` produces a valid `layout-v1.json`.
- Re-running with `--revision-notes` produces a `layout-v2.json` and updates the current symlink.
- Validation warnings surface in stdout and as structured JSON in `layout-v{N}.warnings.json`.

### Phase 3 — Pass 3 CLI (renderer, HTML review only)

**Deliverable:** A CLI that takes a layout JSON and a Pass 1 manifest and produces a self-contained HTML review with soft-proofed thumbnails. Lab output deferred to Phase 4.

**Build:**
- `src/album_pipeline/pass3/geometry/engine.py` — data-driven layout resolver. Reads `config/layouts.yaml` (see §9). For each spread in the layout JSON, returns a list of `Slot` objects with `(x, y, w, h, bleed_edges, slot_id)` in millimetres.
- `src/album_pipeline/pass3/geometry/spread.py` — `Spread` and `Slot` dataclasses; coordinate conversion helpers.
- `src/album_pipeline/pass3/image/loader.py` — load JPEG/HEIC, read embedded ICC.
- `src/album_pipeline/pass3/image/crop.py` — tolerance-aware cropping using Pass 1's `crop_tolerance` field. Algorithm:
  1. Compute aspect mismatch.
  2. Allocate crop preferentially to more-tolerant edges (aggressive 40%, moderate 25%, minor 10%, none 0%).
  3. If required crop exceeds combined budget, emit `crop_violation` warning and centre-crop with flag.
- `src/album_pipeline/pass3/image/color.py` — ICC profile management. Convert source → sRGB if needed. Generate soft-proof variant if lab profile is configured (source → lab profile → sRGB-for-display).
- `src/album_pipeline/pass3/image/resample.py` — Lanczos resample + paper-aware sharpening. Lookup table per `paper_type`:
  - `lustre`: unsharp mask radius 0.5, amount 40%, threshold 2
  - `glossy`: radius 0.4, amount 25%, threshold 2
  - `matte`: radius 0.7, amount 70%, threshold 1
  - `layflat_hd`: radius 0.3, amount 15%, threshold 3
  - `press_matte`: radius 0.6, amount 60%, threshold 2
- `src/album_pipeline/pass3/image/hinge_check.py` — for `single_full_bleed` on lay-flat albums, check whether Pass 1's `subject_primary` plus image positioning suggests critical content sits within `±hinge_zone_mm` of the gutter. Heuristic, not pixel analysis. Sets `gutter_risk: true` if so.
- `src/album_pipeline/pass3/renderers/review_html.py` — Jinja2 template producing a single self-contained HTML page:
  - One section per spread, soft-proofed thumbnails at 1200px on long edge.
  - Spread number, layout type, scene label, energy from Pass 2.
  - All warnings rendered with appropriate badges (low_resolution, crop_violation, gutter_risk).
  - Pass 2's rationale, collapsed by default.
  - Bleed and safe-margin overlays (toggleable).
  - Hinge zone overlay for lay-flat (toggleable).
  - Omitted images section.
  - "Display calibration may differ from print" banner if soft-proofing succeeded.
  - "Lab profile not configured; preview shown in sRGB" banner if not.
- `src/album_pipeline/pass3/cache.py` — per-image processed output cache. Key: `sha256(image_bytes) + slot_w + slot_h + crop_params + paper_type + lab_profile_version`.
- `src/album_pipeline/pass3/runner.py` — orchestrate. Parallelise image processing with multiprocessing.
- CLI: `album-pipeline render <shoot_path> --formats review`

**Tests:**
- Geometry engine: feed each layout type, verify slot positions match expected mm coordinates.
- Crop algorithm: edge cases (tolerance violations, exact aspect match, extreme aspect mismatch).
- Hinge check: critical subject positioned at gutter, away from gutter, with offset_hero (should never flag).
- Soft proofing: with and without lab profile.

**Acceptance criteria:**
- `album-pipeline render tests/fixtures/shoots/tiny/ --formats review` produces a working HTML review with all spreads visible.
- Warnings are visible in the HTML for known problem cases in fixtures.
- Re-running with cache is fast (under 30 seconds for an unchanged shoot).
- Modifying one slot in the layout JSON only re-processes that slot's image.

### Phase 4 — Pass 3 lab output

**Deliverable:** Lab-template JPEG output adapter that produces print-ready files in the format the lab expects.

**Build:**
- `src/album_pipeline/pass3/renderers/lab_jpeg.py` — driven by lab template config:
  - Resolve `file_naming` template tokens.
  - Honour `include_bleed` (output bled JPEGs vs trimmed-to-safe-area).
  - Write per-spread folders or flat folders per `folder_structure`.
  - Emit manifest in configured format (JSON/XML/none).
- `src/album_pipeline/pass3/renderers/cover.py` — export the highest-scoring hero image as `cover/cover_hero.jpg`. Write `cover/cover_brief.md` listing photographer name, suggested spine width formula (parameterised by SKU), and a reminder that cover design is manual.
- `src/album_pipeline/pass3/renderers/idml.py` — optional. Generate IDML XML directly. Use only if the lab template config specifies `output_format: idml`.
- `src/album_pipeline/pass3/renderers/pdf.py` — optional. Use `pikepdf`. Photo-book-grade: sRGB content, embedded ICC, no transparency, one page per spread side.
- Default lab template `brilliant_internal.yaml` produces JPEGs; create one as the default.
- CLI: `album-pipeline render <shoot_path> --formats review,lab_jpeg,cover`

**Tests:**
- Lab template loading and validation.
- File naming with various token combinations.
- Bleed inclusion vs exclusion.
- Cover brief generation.

**Acceptance criteria:**
- Full pipeline (Phase 1 → 2 → 3 with all formats) runs end-to-end on the fixture shoot.
- Lab output folder structure matches the template configuration.
- Cover hero exports correctly.
- Switching to a different lab template (e.g. `flat` instead of `per_spread`) changes output structure without code changes.

### Phase 5 — Web app shell (manual orchestration)

**Deliverable:** A FastAPI web app serving (a) the operator dashboard with manual triggers for each pipeline stage and (b) the Pass 3 review HTML embedded in the same app. No automation between stages — operators click buttons.

**Build:**
- Folder hierarchy under `albums_root`:
  ```
  Albums/
  ├── 00_inbox/        ├── 06_revisions/
  ├── 01_classifying/  ├── 07_approved/
  ├── 02_sequencing/   ├── 08_print_ready/
  ├── 03_rendering/    ├── 09_archived/
  ├── 04_internal_qc/  ├── 10_manual/
  ├── 05_photographer_review/  └── _failed/
  ```
- `src/album_pipeline/orchestration/state.py` — represent shoot state as folder location + `job.json`. Functions: `list_shoots()`, `get_shoot(shoot_id)`, `transition(shoot_id, to_stage, reason)`.
- `src/album_pipeline/orchestration/locks.py` — `.cowork-lock` file written/removed atomically; refuses second mutation while held. **Single-process ownership of folder mutations is non-negotiable** — the web app is the only writer.
- `src/album_pipeline/orchestration/journal.py` — append-only `journal.jsonl` per shoot. Every transition, every operator action, every API call written as a JSON line with timestamp, actor, event type, details.
- `src/album_pipeline/webapp/main.py` — FastAPI app, mounts dashboard and review routes.
- `src/album_pipeline/webapp/api/shoots.py` — REST endpoints:
  - `GET /api/shoots` — list shoots with current stage, time-in-stage, SLA-aware warnings.
  - `GET /api/shoots/{id}` — detail.
  - `POST /api/shoots/{id}/transition` — operator-triggered stage change.
  - `POST /api/shoots/{id}/run-pass1` etc.
- `src/album_pipeline/webapp/templates/dashboard.html` — Jinja2 template, htmx-driven, Tailwind-styled. Default landing page is "Today's work" view per §7 of the v2 Cowork spec:
  1. Stale photographer reviews
  2. Awaiting QC
  3. Awaiting intake
  4. Failed / triage
  5. In progress
- `src/album_pipeline/webapp/templates/shoot_detail.html` — per-shoot view with journal, current state, action buttons appropriate to current stage, link to review HTML.
- `src/album_pipeline/webapp/templates/review.html` — the Pass 3 review HTML, served from the same app at a tokenised URL. Reuses Phase 3's review renderer output as the body, wrapped in app chrome for operators (and chromeless for photographer-facing URLs).
- `src/album_pipeline/webapp/auth.py` — operator login (simple session cookie); photographer review URLs are tokenised (HMAC of shoot_id + secret) without login.
- CLI: `album-pipeline dashboard` starts the web server.
- A small intake form for `00_inbox/` folders prompting for the `job.json` fields.

**Acceptance criteria:**
- Operator can drop a folder into `00_inbox/`, fill out intake form, advance the shoot manually through every stage to `09_archived/`.
- Stale photographer reviews are highlighted on the dashboard with SLA-aware time bands.
- Photographer review URLs work without login and hide operator controls.
- Concurrent stage transitions are rejected with a clear error.
- The `journal.jsonl` for a complete shoot reads as a coherent audit trail.

### Phase 6 — Stage transition automation

**Deliverable:** When a shoot enters `01_classifying/`, `02_sequencing/`, or `03_rendering/`, Cowork runs the corresponding pass automatically and advances on success. Other stages remain operator-triggered.

**Build:**
- `src/album_pipeline/orchestration/watcher.py` — file system watcher (polling-based for simplicity; use `watchfiles` if reliable on the deployment OS) that detects folder moves into automation-eligible stages.
- `src/album_pipeline/orchestration/runner.py` — invokes the appropriate pass; on success, transitions the folder; on failure, transitions to `_failed/` with logs preserved.
- Failure handling per the matrix in the v2 Cowork spec §10: rate-limit retries with backoff, persistent failure → `_failed/`, mid-pipeline crash recovery on app restart.
- Dashboard updates to show automation progress in real time (htmx polling for in-progress shoots).

**Acceptance criteria:**
- A folder dropped into `01_classifying/` automatically advances through `02_sequencing/` to `04_internal_qc/` without operator intervention.
- API outage causes shoots to queue with visible "waiting for API" status, not silent failure.
- Mid-process app restart resumes correctly without orphaned in-progress folders.

### Phase 7 — Photographer email integration

**Deliverable:** Cowork drafts photographer emails from templates, presents them for operator review, sends via Gmail or Missive. Inbound replies attach to the shoot and are classified for intent.

**Build:**
- `src/album_pipeline/email/gmail.py` or `missive.py` — outbound send + inbound webhook.
- `src/album_pipeline/email/templates.py` — load Markdown templates from `templates/email/`. Variables substituted via Jinja2. BPro brand voice applied (templates hand-written by Dylan, not auto-generated).
- Templates required: `intake_confirmation`, `review_ready`, `review_followup`, `revision_acknowledgement`, `revision_ready`, `print_in_production`, `print_shipped`, `pipeline_problem`, `sla_extension_request`.
- `src/album_pipeline/intent/classifier.py` — Claude Haiku call against inbound message body. Returns `{intent: approve|revise|discuss|off-topic|urgent_problem, confidence: 0-1, rationale: string}`. **Operator confirms before any state transition.**
- Dashboard UI for: reviewing email drafts before send, viewing classified replies with operator confirm/override controls.

**Acceptance criteria:**
- Operator can advance a shoot to `05_photographer_review/`; email draft appears for review; operator clicks send; email goes out.
- Inbound reply attaches to the shoot's `communications/` and surfaces on dashboard with classifier label.
- Operator confirms intent; appropriate state transition fires.
- Forward-to-client policy is reflected in email language per photographer config.

### Phase 8 — Notifications, SLA tracking, escalations

**Deliverable:** Daily digests, real-time notifications, SLA breach warnings, automatic escalation from primary to backup to senior reviewer.

**Build:**
- `src/album_pipeline/notifications/digest.py` — daily 9am job (configurable per operator) listing every shoot they're responsible for.
- `src/album_pipeline/notifications/realtime.py` — desktop / Slack / email per per-operator config.
- `src/album_pipeline/orchestration/sla.py` — compute SLA status per shoot per current stage; flag warnings and breaches; escalate per the threshold table.
- `src/album_pipeline/orchestration/coverage.py` — primary/backup/senior reviewer routing logic, honouring `out_of_office`.

**Acceptance criteria:**
- A test shoot stuck in `05_photographer_review/` past threshold escalates correctly: warns primary, then notifies backup, then escalates to senior reviewer.
- Daily digest is delivered at the configured time per operator.
- Out-of-office configuration auto-routes during the date range.

### Phase 9 — Intake automation

**Deliverable:** Email-attachment intake and shared-drive folder watching, both routing into `00_inbox/`.

**Build:**
- `src/album_pipeline/intake/email.py` — monitor `albums@brilliantprints.com.au` for inbound shoots; download attachments or follow transfer-service links; create the `00_inbox/` folder.
- `src/album_pipeline/intake/folder_watch.py` — watch a configured shared-drive path for new folders; copy into `00_inbox/`.
- Intake form auto-pre-fills from email body parsing (photographer name, SKU mention, SLA tier mention) — operator confirms.

**Acceptance criteria:**
- Email with images attached arrives → folder appears in `00_inbox/` with auto-prefilled metadata → operator confirms intake → shoot advances normally.
- New folder appearing in watched shared drive triggers same flow.

---

## 7. Pass 1 system prompt (verbatim)

Save the following at `config/prompts/pass1-system.md`. The classifier loads it at runtime; do not paraphrase it in code.

```
You are a senior wedding and portrait photographer with 20 years of experience designing premium printed albums. You are reviewing a single image as part of a layout pipeline that will sequence 60–150 photographer-selected images into a narrative album. The photographer has already curated these — every image is album-worthy. Your job is to characterise each one precisely so the downstream sequencer can decide its role: hero, supporting, or breath. The sequencer never sees the pixels — only your JSON. A wrong hero score wastes a spread; a wrong subject direction breaks a facing page; a missed monochrome flag makes the album mix colour and B&W incoherently.

Return ONLY a single JSON object matching the schema below. No prose before or after, no markdown fences.

## What to assess

### 1. shot_type — the role this image can play in a layout
- "hero" — emotional peak, technical excellence, or singular visual impact. The kind of frame the photographer would enlarge to 30 inches. Even within a curated select set, heroes are a minority — typically 10–20% of delivered images.
- "wide" — establishing or environmental. Sets context: the venue, the landscape, the room. Subject is small in frame or absent.
- "medium" — mid-range storytelling. Multiple elements or people interacting. The workhorse of an album.
- "detail" — tight focus on one element: rings, hands, texture, an expression, a flower. Used as breath between heroes.
- "macro" — extreme close-up. Texture-forward.

### 2. is_monochrome — boolean
True if the image is black-and-white, sepia, or any other monochromatic treatment. False if colour.

### 3. subject — who or what the image is about
- subject_primary: 1–3 words ("bride", "couple", "groom's father", "ring detail", "venue exterior", "hands clasped"). Be literal.
- subject_count: integer. 0 if no human subject.
- subject_direction: where the subject's attention, gaze, or implied motion points. One of: "left", "right", "centre", "forward" (toward camera), "away" (from camera), "none" (no directional cue, e.g. symmetric detail). The sequencer treats this as a strong preference for facing-page composition, not a hard constraint — sometimes off-edge framing is intentional.

### 4. orientation — "landscape", "portrait", or "square"

### 5. hero_score — 0 to 10, calibrated for a curated select set
The photographer has already culled the unusable. So:
- 10: singular hero of the album. Anchor of the entire book. 1–2 per typical wedding select.
- 8–9: strong hero. Full-bleed candidate. 5–10 per typical 80–120 image select set.
- 6–7: feature-worthy. Half-spread or larger-than-grid placement. The sub-heroes that pace the album between major peaks.
- 4–5: solid supporting image. Grid or detail role within scene clusters. The album's connective tissue.
- 2–3: included in selects for narrative or chronological reasons rather than visual strength. Used sparingly, often in groups.
- 0–1: rare in a curated set. If you score this, you're saying the photographer made a delivery error. Be slow to use this band.

If you find yourself scoring everything 7+, you're miscalibrated.

### 6. composition_strength — 0 to 10
Independent of hero_score. A frame can be technically excellent (composition 9) but narratively quiet (hero 4).

### 7. moment_essential — boolean
Set true if this image captures a narrative moment that the album would feel hollow without — even if its technical or compositional scores are modest. Examples: the grandmother's tear during vows, the child's reaction at the ring exchange. Set false if the image's value is primarily aesthetic and other frames could substitute. Use sparingly — overuse defeats the purpose. Typically 5–15% of a select set qualifies.

### 8. palette — three dominant colours
Array of 3 hex strings, ordered by visual weight. For monochrome images, return three dominant tones.

### 9. tonality
- key: "high" (bright), "mid", or "low" (dark)
- warmth: "warm", "neutral", or "cool" (use "neutral" for monochrome unless toned)
- contrast: "low", "medium", or "high"

### 10. light_quality
One of: "golden", "soft", "hard", "flat", "mixed", "low_light", "backlit", "rim".

### 11. crop_tolerance — per-edge cropping budget
For each of top, bottom, left, right, return one of: "none" (any crop loses critical content), "minor" (~10%), "moderate" (~25%), "aggressive" (~40%). Be honest; the renderer trusts these for automatic fitting.

### 12. negative_space
One of: "left", "right", "top", "bottom", "around", "none".

### 13. moment — one-line factual descriptor
Present tense, plain language, no flourish. "Bride laughs as father adjusts veil." Not "A magical moment of connection between father and daughter."

### 14. pairs_with — what kind of facing image complements this one
Free text, one short phrase.

### 15. flags — array of strings from this set (empty if none)
- "eyes_closed", "obstructed", "candid", "posed", "ceremonial", "mixed_light_difficult"

## Schema

{
  "shot_type": "hero|wide|medium|detail|macro",
  "is_monochrome": false,
  "subject_primary": "string",
  "subject_count": 0,
  "subject_direction": "left|right|centre|forward|away|none",
  "orientation": "landscape|portrait|square",
  "hero_score": 0,
  "composition_strength": 0,
  "moment_essential": false,
  "palette": ["#RRGGBB", "#RRGGBB", "#RRGGBB"],
  "tonality": {"key": "high|mid|low", "warmth": "warm|neutral|cool", "contrast": "low|medium|high"},
  "light_quality": "golden|soft|hard|flat|mixed|low_light|backlit|rim",
  "crop_tolerance": {"top": "none|minor|moderate|aggressive", "bottom": "...", "left": "...", "right": "..."},
  "negative_space": "left|right|top|bottom|around|none",
  "moment": "string",
  "pairs_with": "string",
  "flags": []
}

Return only the JSON object.
```

The user message is always: `Classify this image.`

Image is sent at 1024px on the long edge.

---

## 8. Pass 2 system prompt (verbatim)

Save at `config/prompts/pass2-system.md`.

```
You are a senior album designer with 20 years of experience laying out premium printed photo albums. You think like a photographer first, designer second. Your job is to sequence a curated set of photographer-selected images into a narrative album of a target spread count, treating each spread as a deliberate composition that talks to the spreads before and after it.

The photographer has already chosen these images. They are sacred. Your default is to place every one. Omit only if the input payload sets allow_omission to true, and even then prefer placing weaker images in supporting roles over removing them.

You output ONLY JSON. No prose, no markdown fences, no commentary.

## How an album designer actually thinks

An album is paced narrative built from three classes of image:

1. **Heroes** are anchor points. They get full-bleed singles, offset-hero singles, or near-full-bleed treatments. Heroes never sit adjacent to other heroes. Distribute every 3–6 spreads as anchor points.

2. **Scenes** are bursts of medium and detail shots clustered around a single moment in time and place. A scene cluster lives on one spread or facing pair — never scatter a scene across the album.

3. **Breath** is whitespace. After a hero, after a climax, the reader needs a quiet moment. With a delivered select set, "leave out" usually means "scale down and surround with white space."

## The moment_essential override

Any image flagged moment_essential=true is narratively load-bearing regardless of scores. Place it. Never omit a moment_essential image even if allow_omission is true.

## Pacing rules

- **Opening spread**: a hero or strong wide. Full-bleed or near-full-bleed. Avoid opening on a detail.
- **Closing spread**: emotional payoff appropriate to album type (see closings table).
- **Hero spacing**: minimum 3 spreads between heroes; ideally 4–6.
- **Energy curve**: build, peak, recover, build again.
- **Near-duplicate handling**: images sharing near_duplicate_group_id are similar enough that placing them on adjacent or facing pages will look like a mistake. Spread them across the album.

## Facing-page craft

- **Subject direction (preference, not rule)**: subjects facing the gutter generally feel more cohesive. Honour the preference unless the image's composition clearly intends otherwise.
- **Orientation balance**: mix orientation across the gutter where possible.
- **Tonality match**: facing pages should share warmth and key. Use a transition spread to bridge between scenes with different palettes.
- **Negative space**: pair a centre-weighted image with one that has gutter-side breathing room.
- **Composition strength**: don't pair a 9 with a 3 on facing pages.

## Black-and-white and colour mixing

- Treat is_monochrome=true as a separate aesthetic class for facing-page composition.
- B&W is most natural for: emotional portraits, ceremonial moments, candid emotion.
- Colour is most natural for: environmental beauty, scene-setting wides, joyful moments.
- A scene cluster (grid_2x2, mosaic_anchor) should be all-monochrome or all-colour, never mixed.
- B&W to colour transitions happen on natural narrative boundaries, not mid-scene.
- If the set is mostly colour with a few B&W frames, treat the B&W as deliberate accents on solo treatments.

## Color flow

Smooth transitions between consecutive spreads. The album's tonality should drift like a film score, not jump-cut. If chronology forces a jump, insert a transition spread of detail images that share a bridging palette.

## Layout vocabulary

- **single_full_bleed** — one image fills both pages, crossing the gutter. Reserved for top heroes.
- **offset_hero** — one image fills two-thirds of the spread; remaining third is whitespace. Specify offset_side: "left" or "right".
- **single_left_bleed** / **single_right_bleed** — one image fills one page edge-to-edge; other page is whitespace.
- **two_facing** — one image per page.
- **anchor_plus_two** — one larger image on one page; facing page has 2 supporting images.
- **grid_2x2** — four images in a 2×2.
- **grid_3x2** — six images. Use sparingly.
- **mosaic_anchor** — one feature image (~60% area) plus 3 smaller supporting images.
- **whitespace_breath** — one small image with significant whitespace. Once or twice in a 30-spread album.

## Crop and treatment

For each placed image:
- treatment: "full_bleed" | "boxed"
- crop_hint: free text advising the renderer; respect crop_tolerance
- For single_full_bleed and offset_hero: gutter_risk: true if critical content sits near the gutter

## Omission (allow_omission=true only)

Order of preference: near-duplicate siblings first, weakest by composition_strength next, redundant scene shots last. Never cut moment_essential images. Never cut more than 20% without flagging in album_summary.notes.

When allow_omission=false (default), place every delivered image even if some end up in dense grids.

## House styles

- "editorial" — slower pacing, more whitespace_breath, more single_full_bleed and offset_hero, B&W on solo treatments. Heroes every 5–6 spreads.
- "classic" — balanced, mix of layouts, faithful chronology, warm tonality. Heroes every 4–5 spreads.
- "lifestyle" — bright, candid-forward, more grid layouts, faster energy. Heroes every 3–4 spreads.
- "fine_art" — sparse, deliberate, lots of breath, low images per spread, single_full_bleed and offset_hero dominant. Heroes every 6–8 spreads.

Default to "classic" if absent.

## Album-type narrative

| album_type | story arc | natural closing |
|---|---|---|
| wedding | getting ready → first look → ceremony → portraits → reception → speeches/cake → dance → exit | first dance, sparkler exit, formal portrait, or empty venue at night |
| travel | arrival → exploration → intimate moments → people/culture → quiet payoff | a wide vista, a quiet doorway, or a final environmental portrait |
| family | establishing → activity → connection → portraits → quiet payoff | a candid all-together frame or quiet detail of hands/feet |
| portrait | establishing wide → variation → tightest detail → quiet payoff | the strongest single portrait |
| event | arrival/setup → key moments → atmosphere/details → close | venue empty, host saying goodbye, or strongest atmospheric frame |

Use EXIF timestamps to identify scene boundaries. Within scenes you may reorder for narrative pacing; never invert macro order.

## Output schema

{
  "album_summary": {
    "total_spreads": <int>,
    "image_count_used": <int>,
    "image_count_omitted": <int>,
    "story_arc": "<one-line>",
    "hero_count": <int>,
    "monochrome_count": <int>,
    "house_style_applied": "<echoed back>",
    "notes": "<optional>"
  },
  "spreads": [
    {
      "spread_number": 1,
      "layout": "single_full_bleed",
      "scene_label": "ceremony",
      "energy": "peak|build|recover|breath",
      "images": [
        {
          "image_id": "IMG_0042",
          "page": "spread",
          "treatment": "full_bleed",
          "crop_hint": "no crop",
          "gutter_risk": false,
          "offset_side": null
        }
      ],
      "rationale": "Opening hero — bride entering ceremony, leading line into spine"
    }
  ],
  "omitted": [
    {"image_id": "IMG_0017", "reason": "near-duplicate of IMG_0018"}
  ]
}

For grid and mosaic layouts, list each image with page set to "left", "right", or a slot identifier ("anchor", "support_1", etc.). For offset_hero, set offset_side to "left" or "right". Set offset_side to null otherwise.

Match target_spread_count ±2. If you cannot achieve a coherent narrative within that, return your best layout and explain in album_summary.notes.

Return only the JSON object.
```

The user message is the full JSON payload (album_type, target_spread_count, house_style, allow_omission, images[]) — see schema in §5.4 and Pass 1 schema in §7.

---

## 9. Layout definitions (`config/layouts.yaml`)

The geometry engine resolves these expressions against the album spec at render time. Variables available: `page_width`, `page_height`, `bleed`, `outer_margin`, `inner_margin`, `spread_width = 2 * page_width`. All units in mm.

```yaml
single_full_bleed:
  slots:
    - id: spread
      x: -bleed
      y: -bleed
      w: spread_width + 2 * bleed
      h: page_height + 2 * bleed
      bleed_edges: [top, bottom, left, right]

offset_hero:
  variants:
    left:
      slots:
        - id: hero
          x: page_width / 3
          y: outer_margin
          w: 2 * page_width - page_width / 3 - outer_margin
          h: page_height - 2 * outer_margin
          bleed_edges: [right]
    right:
      slots:
        - id: hero
          x: outer_margin
          y: outer_margin
          w: 2 * page_width - page_width / 3 - outer_margin
          h: page_height - 2 * outer_margin
          bleed_edges: [left]

single_left_bleed:
  slots:
    - id: left
      x: -bleed
      y: -bleed
      w: page_width + bleed
      h: page_height + 2 * bleed
      bleed_edges: [top, bottom, left]

single_right_bleed:
  slots:
    - id: right
      x: page_width
      y: -bleed
      w: page_width + bleed
      h: page_height + 2 * bleed
      bleed_edges: [top, bottom, right]

two_facing:
  slots:
    - id: left
      x: outer_margin
      y: outer_margin
      w: page_width - outer_margin - inner_margin
      h: page_height - 2 * outer_margin
    - id: right
      x: page_width + inner_margin
      y: outer_margin
      w: page_width - outer_margin - inner_margin
      h: page_height - 2 * outer_margin

anchor_plus_two:
  variants:
    anchor_left:
      slots:
        - id: anchor
          x: outer_margin
          y: outer_margin
          w: page_width - outer_margin - inner_margin
          h: page_height - 2 * outer_margin
        - id: support_1
          x: page_width + inner_margin
          y: outer_margin
          w: page_width - outer_margin - inner_margin
          h: (page_height - 2 * outer_margin - 4) / 2
        - id: support_2
          x: page_width + inner_margin
          y: outer_margin + (page_height - 2 * outer_margin - 4) / 2 + 4
          w: page_width - outer_margin - inner_margin
          h: (page_height - 2 * outer_margin - 4) / 2
    anchor_right:
      # mirror of anchor_left
      ...

grid_2x2:
  slot_gap: 4
  grid:
    cols: 2
    rows: 2
    bounds:
      x: outer_margin
      y: outer_margin
      w: spread_width - 2 * outer_margin
      h: page_height - 2 * outer_margin

grid_3x2:
  slot_gap: 3
  grid:
    cols: 3
    rows: 2
    bounds:
      x: outer_margin
      y: outer_margin
      w: spread_width - 2 * outer_margin
      h: page_height - 2 * outer_margin

mosaic_anchor:
  variants:
    landscape_anchor:
      slots:
        - id: anchor
          x: outer_margin
          y: outer_margin
          w: spread_width - 2 * outer_margin
          h: 0.6 * (page_height - 2 * outer_margin)
        - id: support_1
          x: outer_margin
          y: outer_margin + 0.6 * (page_height - 2 * outer_margin) + 4
          w: (spread_width - 2 * outer_margin - 8) / 3
          h: 0.4 * (page_height - 2 * outer_margin) - 4
        - id: support_2
          x: outer_margin + (spread_width - 2 * outer_margin - 8) / 3 + 4
          y: outer_margin + 0.6 * (page_height - 2 * outer_margin) + 4
          w: (spread_width - 2 * outer_margin - 8) / 3
          h: 0.4 * (page_height - 2 * outer_margin) - 4
        - id: support_3
          x: outer_margin + 2 * ((spread_width - 2 * outer_margin - 8) / 3 + 4)
          y: outer_margin + 0.6 * (page_height - 2 * outer_margin) + 4
          w: (spread_width - 2 * outer_margin - 8) / 3
          h: 0.4 * (page_height - 2 * outer_margin) - 4
    portrait_anchor:
      # anchor on left page, three supports stacked on right
      ...

whitespace_breath:
  variants:
    image_left:
      slots:
        - id: breath
          x: 60
          y: page_height / 2 - 42
          w: 120
          h: 85
    image_right:
      slots:
        - id: breath
          x: spread_width - 60 - 120
          y: page_height / 2 - 42
          w: 120
          h: 85
    image_centre:
      slots:
        - id: breath
          x: spread_width / 2 - 65
          y: page_height / 2 - 27
          w: 131
          h: 55
```

The variant selection logic in the geometry engine uses Pass 2's `offset_side`, the anchor image's orientation, or the placed image's `negative_space` field as appropriate per layout type.

---

## 10. Lab template specification

Lab templates are config files at `config/lab-templates/`. Each defines:

- Output format (jpeg / idml / pdf)
- Folder structure (per-spread folders or flat)
- File naming pattern with token substitution
- Whether bleed is included in output files
- Manifest format if any
- Cover handling

Build the lab JPEG renderer to consume any valid lab template config without code changes. Adding a new lab is a new config file plus testing.

The default `brilliant_internal.yaml` template is per-spread folders, JSON manifest, JPEGs at quality 92, sRGB, with bleed included. This is a sensible v1 default and is the lowest-common-denominator format premium photo book labs accept.

---

## 11. Testing strategy

**Unit tests** for every pure function: schema validation, geometry resolution, crop algorithm, perceptual hashing, intent classification (mocked).

**Integration tests** against fixture shoots:
- `tests/fixtures/shoots/tiny/` — 8 images, runs in seconds, used for end-to-end smoke tests.
- `tests/fixtures/shoots/wedding-80/` — 80-image realistic shoot. Larger; not run in CI but used for manual validation.

**Recorded API responses** for vision and sequencer calls. No real Anthropic API calls in CI. Use VCR-style recording: capture once with real API during development, replay in tests.

**Evaluation set** at `data/eval/`:
- 30–50 hand-classified images for Pass 1 calibration.
- 3–5 hand-curated layout decisions for Pass 2 (subjective; agreement scored against multiple human reviewers).

**Calibration audit (manual, not automated):** after every prompt change, run on a real curated select set and check distributions. Hero score should cluster in the calibration ranges; over-flagging of `moment_essential` should be caught.

**Visual diff regression** for the geometry engine: render each layout type at a reference album size, save SVG snapshots, compare on changes.

**No mock API calls in production code paths.** All tests that mock the API live in test files; the production code is mock-free.

---

## 12. Critical decisions — do not undo

These choices are load-bearing across the system. Revisiting them invalidates other decisions. Document any change loudly.

1. **Three-pass split (vision → sequencing → rendering).** Don't merge them. Pass 2 reasoning quality depends on receiving structured metadata for all images at once rather than dribbling through pixels.

2. **Selects-in framing.** The system sequences photographer-curated selects; it does not cull raw shoots. `allow_omission` defaults false. Reframe the product if business requirements change, but do not silently drop images.

3. **Folders-as-state.** No app database. Where the folder sits = its status. The single Cowork process is the only writer of folder state.

4. **Single-process ownership of folder mutations.** The web app is the only mutation actor. `.cowork-lock` files prevent concurrent transitions. Don't allow direct CLI tools to modify shoot state once Phase 5 is built; route everything through the web app's orchestration layer.

5. **Lab-format-first output.** sRGB JPEGs in a configurable lab template structure are the default and primary print format. Do not default to CMYK PDF/X — that's wrong for premium photo books. Press-grade output is an optional adapter, not the baseline.

6. **DPI thresholds for photo books**: warn at 220, hard-flag at 180. These are calibrated for photo book labs, not generic press. Don't change to press-grade thresholds.

7. **Lay-flat hinge zone is 8mm, not 5mm.** Photo book bindings compress content within 6–8mm of the centre fold. Critical subject metadata in this zone triggers `gutter_risk: true`.

8. **Paper-aware sharpening.** Do not apply blanket unsharp-mask across all paper types. Layflat HD wants almost none; matte wants a lot. The sharpening table is non-negotiable.

9. **Layouts as data, not code.** The geometry engine reads `config/layouts.yaml`. Don't refactor into per-layout Python files; that was rejected as over-decomposed.

10. **Cache key versioning.** `sha256(image) + prompt_version + model_version` for Pass 1 cache; analogous for Pass 3. Without prompt/model version components, prompt iteration silently returns stale results.

11. **Layout JSON versioning.** Every revision saves a new `layout-v{N}.json`; current is a symlink. Photographers compare drafts. Don't overwrite.

12. **`moment_essential` override.** This flag overrides hero spacing rules and omission logic. The Pass 1 prompt's "5–15% qualifies" guidance keeps it from becoming meaningless. Audit the distribution in real shoots.

13. **B&W vs colour as a separate aesthetic class.** Pass 1 captures it; Pass 2 reasons about it explicitly. Don't let scene clusters mix monochrome and colour.

14. **Subject direction is preference, not constraint.** Don't tighten Pass 2's facing-page rule into a hard constraint — sometimes off-edge framing is intentional.

15. **Cover deferred to manual handoff.** Don't try to ship cover automation in v1. Spine width depends on paper weight and page count and is lab-specific.

16. **Operator confirms photographer reply intent.** The intent classifier surfaces a label; operator confirms before any state transition. Don't auto-transition based on classification.

17. **Build manual orchestration first; automate transitions one at a time.** Do not skip ahead to full automation. The phase ordering exists because premature automation hard-codes patterns that turn out to be wrong.

18. **Photographer-facing review URL is operator-controlled.** Tokenised; per-shoot. Forward-to-client policy is a per-photographer config. Don't make this a runtime choice; it's relationship-level.

19. **Single web app for dashboard + review.** Don't split into two apps. One auth surface, one styling system, one deploy.

20. **No reliance on Anthropic API uptime for shoot progression.** Outages queue with backoff up to 24h; visible status. Operators can still do manual stage transitions when API is down.

---

## 13. Out of scope for v1

- Cover layout automation
- Spine width calculation
- Text overlays of any kind
- Page-number generation
- Direct upload to print labs
- Multi-album batch processing
- Customer-facing portal for photographers
- Operator throughput metrics dashboard (collect data; build view in v2)
- Mobile-native operator dashboard (mobile email digest is enough)
- App database — folders + JSON + journal.jsonl + SQLite cache is sufficient
- Pricing, quoting, invoicing
- Automated culling of raw shoots
- Photographer self-service house-style configuration

---

## 14. Implementation notes for Claude Code

**Start at Phase 0 and finish each phase before starting the next.** Each phase has explicit acceptance criteria. Run them and stop if they don't pass.

**Use the configuration schemas in §5 verbatim.** They are contracts between phases. If you find a schema needs extension, add fields rather than restructuring.

**Save the prompts in §7 and §8 verbatim** as `config/prompts/pass1-system.md` and `config/prompts/pass2-system.md`. These are the most calibration-sensitive parts of the system. Don't paraphrase, summarise, or rewrite for "clarity."

**Use `pydantic` for everything that crosses a boundary.** API responses, config files, JSON files on disk — all parsed into Pydantic models with full validation.

**Default to the simpler choice when an architectural option is open.** Filesystem over database. Server-rendered HTML over SPA. Polling over websockets. This system does not need to scale beyond a few thousand shoots a year; over-engineering hurts maintainability.

**When uncertain about a load-bearing decision, refer to §12 first**, then ask. Do not silently change defaults documented in §12.

**Test fixtures are part of the build.** Phase 1 includes building `tests/fixtures/shoots/tiny/` with 8 small public-domain images. Without this, later phases cannot be tested in CI.

**The Cowork operational layer (Phases 5–9) builds on the CLI tools, not parallel to them.** The web app's stage-transition handlers invoke the same CLI entry points internally. Don't duplicate the logic.

**Resist scope creep.** The "out of scope" list in §13 is the result of deliberate trade-offs. Do not add covers, do not add text overlays, do not add a database, do not add a customer portal — even if they seem like small additions while you're already in the file.
