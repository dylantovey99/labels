# Album Pipeline

Automated photo album sequencing and layout pipeline for Brilliant Prints / BPro.

The complete build specification lives in [`BUILD_SPEC.md`](./BUILD_SPEC.md). It
is the authoritative document for every architectural choice. Read it before
contributing.

## Quick start

Requires Python 3.11+.

```bash
pip install -e ".[dev]"
album-pipeline --help
```

To install platform-sensitive extras (HEIC reader, PDF/IDML lab adapters):

```bash
pip install -e ".[heif,pdf,idml,dev]"
```

`pillow-heif` requires the `libheif` system library; `pikepdf` requires
`libqpdf`. Skip these extras until you reach the phase that needs them
(Phase 1 for HEIC inputs, Phase 4 for PDF/IDML output).

## What's built

| Phase | Status | Deliverable |
|-------|--------|-------------|
| 0 | ✓ | Scaffolding: package, config loader, logging, CLI stubs |
| 1 | – | Pass 1 vision classifier |
| 2 | – | Pass 2 sequencer |
| 3 | – | Pass 3 renderer (HTML review) |
| 4 | – | Pass 3 lab JPEG output |
| 5 | – | Web app shell (manual orchestration) |
| 6 | – | Stage transition automation |
| 7 | – | Photographer email integration |
| 8 | – | Notifications / SLA tracking |
| 9 | – | Intake automation |

## Layout

```
config/                  configuration files (see BUILD_SPEC §5)
src/album_pipeline/      Python package
data/eval/               hand-labelled evaluation set
data/cache/              SQLite cache (Pass 1)
templates/email/         email templates (Phase 7)
tests/                   unit + integration tests
```

Per-shoot folders live outside the repo at `cowork-config.albums_root`.

## Development

```bash
pytest                   # tests
ruff check .             # lint
ruff format .            # format
```
