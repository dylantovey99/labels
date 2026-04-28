"""Phase 0 acceptance: round-trip every config schema."""

from __future__ import annotations

from datetime import date, time
from pathlib import Path

import pytest
import yaml
from pydantic import ValidationError

from album_pipeline.shared.config import (
    AlbumSKU,
    CoworkConfig,
    LabTemplate,
    load_album_sku,
    load_album_skus_dir,
    load_cowork_config,
    load_lab_template,
    load_lab_templates_dir,
)

FIXTURES = Path(__file__).parent / "fixtures" / "configs"
REPO_CONFIG = Path(__file__).parent.parent / "config"


# ---------- cowork-config ---------------------------------------------------


def test_cowork_config_loads_from_fixture():
    cfg = load_cowork_config(FIXTURES / "cowork-config.yaml")
    assert cfg.albums_root == Path("/tmp/test_albums")
    assert set(cfg.operators) == {"alice", "bob"}
    assert cfg.operators["alice"].digest_time == time(8, 0)
    assert cfg.operators["alice"].out_of_office[0].backup == "bob"
    assert cfg.operators["alice"].out_of_office[0].start == date(2026, 6, 1)
    assert cfg.intake_default_primary == "alice"
    assert cfg.sla_tiers["standard"].total_days == 7
    assert cfg.sla_tiers["rush"].total_hours == 48
    assert cfg.api.vision_model.startswith("claude-")


def test_cowork_config_loads_real_repo_config():
    """The shipped config/cowork-config.yaml must always validate."""
    cfg = load_cowork_config(REPO_CONFIG / "cowork-config.yaml")
    assert "meagan" in cfg.operators
    assert cfg.senior_reviewer == "dylan"


def test_cowork_config_round_trip(tmp_path: Path):
    cfg = load_cowork_config(FIXTURES / "cowork-config.yaml")
    dumped = cfg.model_dump(mode="json")
    out = tmp_path / "round.yaml"
    out.write_text(yaml.safe_dump(dumped))
    again = load_cowork_config(out)
    assert again.model_dump(mode="json") == dumped


def test_cowork_config_rejects_unknown_intake_primary(tmp_path: Path):
    raw = yaml.safe_load((FIXTURES / "cowork-config.yaml").read_text())
    raw["intake_default_primary"] = "nonexistent"
    bad = tmp_path / "bad.yaml"
    bad.write_text(yaml.safe_dump(raw))
    with pytest.raises(ValidationError, match="intake_default_primary"):
        load_cowork_config(bad)


def test_cowork_config_rejects_unknown_sla_tier(tmp_path: Path):
    raw = yaml.safe_load((FIXTURES / "cowork-config.yaml").read_text())
    raw["photographers"][0]["default_sla_tier"] = "platinum"
    bad = tmp_path / "bad.yaml"
    bad.write_text(yaml.safe_dump(raw))
    with pytest.raises(ValidationError, match="platinum"):
        load_cowork_config(bad)


def test_cowork_config_rejects_unknown_ooo_backup(tmp_path: Path):
    raw = yaml.safe_load((FIXTURES / "cowork-config.yaml").read_text())
    raw["operators"]["alice"]["out_of_office"][0]["backup"] = "ghost"
    bad = tmp_path / "bad.yaml"
    bad.write_text(yaml.safe_dump(raw))
    with pytest.raises(ValidationError, match="ghost"):
        load_cowork_config(bad)


def test_cowork_config_rejects_extra_top_level_keys(tmp_path: Path):
    raw = yaml.safe_load((FIXTURES / "cowork-config.yaml").read_text())
    raw["mystery_field"] = "uh oh"
    bad = tmp_path / "bad.yaml"
    bad.write_text(yaml.safe_dump(raw))
    with pytest.raises(ValidationError):
        load_cowork_config(bad)


def test_cowork_config_missing_file():
    with pytest.raises(FileNotFoundError):
        load_cowork_config("/nonexistent/cowork.yaml")


# ---------- album SKU -------------------------------------------------------


def test_album_sku_loads():
    sku = load_album_sku(FIXTURES / "sku_12x12.yaml")
    assert sku.sku_id == "12x12_lay_flat_lustre"
    assert sku.page_width_mm == 305
    assert sku.binding_type == "lay_flat"
    assert sku.hinge_zone_mm == 8


def test_album_sku_real_repo_sku():
    sku = load_album_sku(REPO_CONFIG / "album-skus" / "12x12_lay_flat_lustre.yaml")
    assert sku.sku_id == "12x12_lay_flat_lustre"


def test_album_sku_round_trip(tmp_path: Path):
    sku = load_album_sku(FIXTURES / "sku_12x12.yaml")
    out = tmp_path / "round.yaml"
    out.write_text(yaml.safe_dump(sku.model_dump(mode="json")))
    again = load_album_sku(out)
    assert again == sku


def test_album_sku_layflat_requires_hinge_zone(tmp_path: Path):
    """Spec §12.7 — lay-flat hinge zone is non-negotiable."""
    raw = yaml.safe_load((FIXTURES / "sku_12x12.yaml").read_text())
    raw["hinge_zone_mm"] = 0
    bad = tmp_path / "bad.yaml"
    bad.write_text(yaml.safe_dump(raw))
    with pytest.raises(ValidationError, match="hinge_zone_mm"):
        load_album_sku(bad)


def test_album_sku_rejects_zero_dimensions(tmp_path: Path):
    raw = yaml.safe_load((FIXTURES / "sku_12x12.yaml").read_text())
    raw["page_width_mm"] = 0
    bad = tmp_path / "bad.yaml"
    bad.write_text(yaml.safe_dump(raw))
    with pytest.raises(ValidationError):
        load_album_sku(bad)


def test_load_album_skus_dir():
    skus = load_album_skus_dir(REPO_CONFIG / "album-skus")
    assert "12x12_lay_flat_lustre" in skus
    assert isinstance(skus["12x12_lay_flat_lustre"], AlbumSKU)


# ---------- lab template ----------------------------------------------------


def test_lab_template_loads():
    tpl = load_lab_template(FIXTURES / "lab_brilliant_internal.yaml")
    assert tpl.template_id == "brilliant_internal"
    assert tpl.output_format == "jpeg"
    assert tpl.jpeg_quality == 92
    assert tpl.include_bleed is True


def test_lab_template_real_repo_template():
    tpl = load_lab_template(REPO_CONFIG / "lab-templates" / "brilliant_internal.yaml")
    assert tpl.template_id == "brilliant_internal"


def test_lab_template_rejects_naming_without_token(tmp_path: Path):
    raw = yaml.safe_load((FIXTURES / "lab_brilliant_internal.yaml").read_text())
    raw["file_naming"] = "page.jpg"
    bad = tmp_path / "bad.yaml"
    bad.write_text(yaml.safe_dump(raw))
    with pytest.raises(ValidationError, match="file_naming"):
        load_lab_template(bad)


def test_load_lab_templates_dir():
    tpls = load_lab_templates_dir(REPO_CONFIG / "lab-templates")
    assert "brilliant_internal" in tpls
    assert isinstance(tpls["brilliant_internal"], LabTemplate)


# ---------- cross-config sanity --------------------------------------------


def test_album_sku_lab_template_reference_resolves():
    """Sanity — every SKU's lab_template should match a real lab template file."""
    skus = load_album_skus_dir(REPO_CONFIG / "album-skus")
    tpls = load_lab_templates_dir(REPO_CONFIG / "lab-templates")
    for sku in skus.values():
        assert sku.lab_template in tpls, (
            f"SKU {sku.sku_id} references lab_template '{sku.lab_template}' "
            f"which is not in config/lab-templates/"
        )


def test_pydantic_models_are_strict():
    """All boundary models forbid extra keys — typo protection."""
    for model in (CoworkConfig, AlbumSKU, LabTemplate):
        assert model.model_config.get("extra") == "forbid"
