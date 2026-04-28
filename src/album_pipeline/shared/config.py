"""Configuration loading and validation.

Models mirror BUILD_SPEC.md §5.1 (cowork-config) and §5.2 (album SKU) and §5.3
(lab template). Every YAML file that crosses a boundary is parsed into a
Pydantic model with full validation; downstream code reads typed objects, never
raw dicts.
"""

from __future__ import annotations

from datetime import date, time
from pathlib import Path
from typing import Annotated, Literal

import yaml
from pydantic import BaseModel, ConfigDict, Field, field_validator, model_validator

# ---------- cowork-config.yaml (§5.1) ---------------------------------------


class OutOfOfficePeriod(BaseModel):
    model_config = ConfigDict(extra="forbid")

    start: date
    end: date
    backup: str

    @model_validator(mode="after")
    def _end_after_start(self) -> OutOfOfficePeriod:
        if self.end < self.start:
            raise ValueError("out_of_office.end must be on or after start")
        return self


Channel = Literal["email", "slack", "desktop"]


class Operator(BaseModel):
    model_config = ConfigDict(extra="forbid")

    email: str
    digest_time: time
    digest_channel: Channel
    realtime_channel: Channel
    out_of_office: list[OutOfOfficePeriod] = Field(default_factory=list)


class Photographer(BaseModel):
    model_config = ConfigDict(extra="forbid")

    name: str
    email: str
    default_house_style: Literal["editorial", "classic", "lifestyle", "fine_art"]
    default_album_sku: str
    default_sla_tier: str
    forward_to_client_policy: Literal["permissive", "restrictive"]


class SLATier(BaseModel):
    """SLA threshold bundle. Either *_days or *_hours/*_minutes, not both.

    The spec mixes units across tiers (standard uses days, rush uses hours,
    express adds minutes), so all duration fields are optional and we validate
    that at least the totals are present.
    """

    model_config = ConfigDict(extra="forbid")

    total_days: int | None = None
    total_hours: int | None = None
    qc_warn_hours: int | None = None
    qc_warn_minutes: int | None = None
    review_warn_days: int | None = None
    review_warn_hours: int | None = None
    review_escalate_days: int | None = None
    review_escalate_hours: int | None = None

    @model_validator(mode="after")
    def _has_total(self) -> SLATier:
        if self.total_days is None and self.total_hours is None:
            raise ValueError("SLA tier requires total_days or total_hours")
        return self


class APIConfig(BaseModel):
    model_config = ConfigDict(extra="forbid")

    anthropic_api_key_env: str = "ANTHROPIC_API_KEY"
    vision_model: str
    sequencer_model: str
    intent_classifier_model: str


class WebConfig(BaseModel):
    model_config = ConfigDict(extra="forbid")

    host: str = "127.0.0.1"
    port: int = 8088
    external_base_url: str


class PrintHandoffNotification(BaseModel):
    model_config = ConfigDict(extra="forbid")

    type: Literal["email", "slack"]
    to: str


class PrintHandoff(BaseModel):
    model_config = ConfigDict(extra="forbid")

    type: Literal["folder_move", "email", "myob"]
    destination: Path | None = None
    notification: PrintHandoffNotification


class CoworkConfig(BaseModel):
    """Top-level cowork-config.yaml — operator + photographer wiring."""

    model_config = ConfigDict(extra="forbid")

    albums_root: Path
    operators: dict[str, Operator]
    senior_reviewer: str
    intake_default_primary: str
    photographers: list[Photographer]
    sla_tiers: dict[str, SLATier]
    api: APIConfig
    web: WebConfig
    print_handoff: PrintHandoff

    @model_validator(mode="after")
    def _references_resolve(self) -> CoworkConfig:
        if self.intake_default_primary not in self.operators:
            raise ValueError(
                f"intake_default_primary '{self.intake_default_primary}' not in operators"
            )
        for ph in self.photographers:
            if ph.default_sla_tier not in self.sla_tiers:
                raise ValueError(
                    f"photographer {ph.name}: default_sla_tier "
                    f"'{ph.default_sla_tier}' not in sla_tiers"
                )
        for op_name, op in self.operators.items():
            for ooo in op.out_of_office:
                if ooo.backup not in self.operators:
                    raise ValueError(
                        f"operator {op_name}: out_of_office.backup '{ooo.backup}' not in operators"
                    )
        return self


# ---------- album SKU (§5.2) ------------------------------------------------


PaperType = Literal["lustre", "glossy", "matte", "layflat_hd", "press_matte"]
BindingType = Literal["lay_flat", "traditional"]


class AlbumSKU(BaseModel):
    model_config = ConfigDict(extra="forbid")

    sku_id: str
    display_name: str
    page_width_mm: Annotated[float, Field(gt=0)]
    page_height_mm: Annotated[float, Field(gt=0)]
    bleed_mm: Annotated[float, Field(ge=0)]
    binding_type: BindingType
    hinge_zone_mm: Annotated[float, Field(ge=0)] = 0
    outer_safe_margin_mm: Annotated[float, Field(ge=0)]
    inner_safe_margin_mm: Annotated[float, Field(ge=0)]
    paper_type: PaperType
    output_dpi: Annotated[int, Field(gt=0)] = 300
    output_color_space: Literal["sRGB", "AdobeRGB", "ProPhoto"] = "sRGB"
    target_spread_count: Annotated[int, Field(gt=0)] = 30
    lab_template: str

    @model_validator(mode="after")
    def _hinge_only_for_layflat(self) -> AlbumSKU:
        # Critical decision §12.7: lay-flat hinge zone is 8mm; traditional binding ignores it.
        if self.binding_type == "lay_flat" and self.hinge_zone_mm <= 0:
            raise ValueError("lay_flat binding requires hinge_zone_mm > 0 (spec §12.7)")
        return self


# ---------- lab template (§5.3) ---------------------------------------------


OutputFormat = Literal["jpeg", "idml", "pdf"]


class LabTemplate(BaseModel):
    model_config = ConfigDict(extra="forbid")

    template_id: str
    display_name: str
    output_format: OutputFormat
    jpeg_quality: Annotated[int, Field(ge=1, le=100)] = 92
    color_space: Literal["sRGB", "AdobeRGB", "ProPhoto"] = "sRGB"
    icc_profile_path: Path | None = None
    folder_structure: Literal["per_spread", "flat"] = "per_spread"
    file_naming: str
    include_bleed: bool = True
    manifest_format: Literal["json", "xml", "none"] = "json"
    manifest_filename: str | None = "album_manifest.json"
    cover_handling: Literal["manual", "hero_export"] = "manual"
    cover_export_path: str | None = None

    @field_validator("file_naming")
    @classmethod
    def _has_required_tokens(cls, v: str) -> str:
        # We don't enforce a specific token set; lab templates own their conventions.
        # But empty / template-less strings are rejected so each output isn't overwritten.
        if "{" not in v or "}" not in v:
            raise ValueError("file_naming must contain at least one {token}")
        return v


# ---------- loaders ---------------------------------------------------------


def _read_yaml(path: Path) -> dict:
    if not path.exists():
        raise FileNotFoundError(f"config file not found: {path}")
    with path.open("r", encoding="utf-8") as f:
        data = yaml.safe_load(f)
    if not isinstance(data, dict):
        raise ValueError(f"{path}: top-level YAML must be a mapping")
    return data


def load_cowork_config(path: str | Path) -> CoworkConfig:
    return CoworkConfig.model_validate(_read_yaml(Path(path)))


def load_album_sku(path: str | Path) -> AlbumSKU:
    return AlbumSKU.model_validate(_read_yaml(Path(path)))


def load_lab_template(path: str | Path) -> LabTemplate:
    return LabTemplate.model_validate(_read_yaml(Path(path)))


def load_album_skus_dir(dir_path: str | Path) -> dict[str, AlbumSKU]:
    """Load every *.yaml in dir_path, keyed by sku_id."""
    result: dict[str, AlbumSKU] = {}
    for p in sorted(Path(dir_path).glob("*.yaml")):
        sku = load_album_sku(p)
        if sku.sku_id in result:
            raise ValueError(f"duplicate sku_id {sku.sku_id} (second seen at {p})")
        result[sku.sku_id] = sku
    return result


def load_lab_templates_dir(dir_path: str | Path) -> dict[str, LabTemplate]:
    result: dict[str, LabTemplate] = {}
    for p in sorted(Path(dir_path).glob("*.yaml")):
        tpl = load_lab_template(p)
        if tpl.template_id in result:
            raise ValueError(f"duplicate template_id {tpl.template_id} (second seen at {p})")
        result[tpl.template_id] = tpl
    return result
