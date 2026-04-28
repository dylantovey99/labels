"""Typer entry point.

Subcommands are stubs in Phase 0; each phase fills one in. The web app's
orchestration layer (Phase 5+) will invoke these same commands internally —
do not duplicate the logic.
"""

from __future__ import annotations

from pathlib import Path

import typer

from album_pipeline import __version__
from album_pipeline.shared import logging as app_logging

app = typer.Typer(
    name="album-pipeline",
    help="Automated photo album sequencing and layout pipeline.",
    no_args_is_help=True,
    add_completion=False,
)


def _not_yet(name: str) -> None:
    typer.echo(f"{name}: not yet implemented")
    raise typer.Exit(code=1)


@app.callback()
def _root(
    log_level: str = typer.Option("INFO", "--log-level", help="DEBUG|INFO|WARNING|ERROR"),
) -> None:
    app_logging.configure(level=log_level)


@app.command()
def version() -> None:
    """Print the package version."""
    typer.echo(__version__)


@app.command()
def classify(
    shoot_path: Path = typer.Argument(..., exists=False, help="Path to shoot folder"),
    no_cache: bool = typer.Option(False, "--no-cache"),
    max_workers: int = typer.Option(6, "--max-workers", min=1, max=32),
) -> None:
    """Pass 1 — vision classifier. Per-image metadata extraction."""
    _ = (shoot_path, no_cache, max_workers)
    _not_yet("classify")


@app.command()
def sequence(
    shoot_path: Path = typer.Argument(..., exists=False),
    allow_omission: bool = typer.Option(False, "--allow-omission"),
    revision_notes: Path | None = typer.Option(None, "--revision-notes"),
) -> None:
    """Pass 2 — whole-album sequencer. One Claude call against Pass 1 metadata."""
    _ = (shoot_path, allow_omission, revision_notes)
    _not_yet("sequence")


@app.command()
def render(
    shoot_path: Path = typer.Argument(..., exists=False),
    formats: str = typer.Option(
        "review",
        "--formats",
        help="Comma-separated: review,lab_jpeg,cover,idml,pdf",
    ),
) -> None:
    """Pass 3 — deterministic renderer. HTML review and lab output."""
    _ = (shoot_path, formats)
    _not_yet("render")


@app.command()
def dashboard(
    host: str = typer.Option("127.0.0.1", "--host"),
    port: int = typer.Option(8088, "--port"),
) -> None:
    """Start the Cowork web app (operator dashboard + photographer review)."""
    _ = (host, port)
    _not_yet("dashboard")


@app.command()
def intake(
    shoot_path: Path = typer.Argument(..., exists=False),
) -> None:
    """Move a folder into 00_inbox/ and prompt for job.json fields."""
    _ = shoot_path
    _not_yet("intake")


if __name__ == "__main__":
    app()
