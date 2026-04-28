"""Structured logging.

Stdout JSON logger plus an optional per-shoot file handler. Keep the format
boring and grep-friendly — operators read these in tail/less, not Splunk.
"""

from __future__ import annotations

import json
import logging
import sys
from datetime import UTC, datetime
from pathlib import Path
from typing import Any

_CONFIGURED = False


class _JsonFormatter(logging.Formatter):
    def format(self, record: logging.LogRecord) -> str:
        payload: dict[str, Any] = {
            "ts": datetime.now(UTC).isoformat(timespec="milliseconds").replace("+00:00", "Z"),
            "level": record.levelname,
            "logger": record.name,
            "msg": record.getMessage(),
        }
        if record.exc_info:
            payload["exc"] = self.formatException(record.exc_info)
        # Anything attached via `logger.info("...", extra={"shoot_id": ...})`
        # rides through to the JSON line.
        for k, v in record.__dict__.items():
            if k in _STDLIB_LOGRECORD_KEYS:
                continue
            try:
                json.dumps(v)
            except (TypeError, ValueError):
                v = repr(v)
            payload[k] = v
        return json.dumps(payload, ensure_ascii=False)


_STDLIB_LOGRECORD_KEYS = {
    "name",
    "msg",
    "args",
    "levelname",
    "levelno",
    "pathname",
    "filename",
    "module",
    "exc_info",
    "exc_text",
    "stack_info",
    "lineno",
    "funcName",
    "created",
    "msecs",
    "relativeCreated",
    "thread",
    "threadName",
    "processName",
    "process",
    "taskName",
    "message",
}


def configure(level: str = "INFO") -> None:
    """Install the stdout JSON handler. Idempotent."""
    global _CONFIGURED
    if _CONFIGURED:
        return
    root = logging.getLogger()
    root.setLevel(level.upper())
    handler = logging.StreamHandler(sys.stdout)
    handler.setFormatter(_JsonFormatter())
    root.handlers.clear()
    root.addHandler(handler)
    _CONFIGURED = True


def attach_shoot_log(shoot_path: Path) -> logging.Handler:
    """Add a per-shoot file handler. Caller is responsible for removing it."""
    log_path = shoot_path / "pipeline.log"
    handler = logging.FileHandler(log_path, encoding="utf-8")
    handler.setFormatter(_JsonFormatter())
    logging.getLogger().addHandler(handler)
    return handler


def get_logger(name: str) -> logging.Logger:
    return logging.getLogger(name)
