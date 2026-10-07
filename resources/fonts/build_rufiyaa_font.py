#!/usr/bin/env python3
"""
Builds a minimal single-glyph webfont "RufiyaaSign" containing only
U+20C2 (MALDIVIAN RUFIYAA SIGN), drawn as a stylised rendition of the
Thaana letter Raa (ރ) with an added horizontal stroke, per the official
MMA rufiyaa sign concept. Outputs TTF + WOFF2.

This is dev tooling, not part of the running app — re-run it only if you
want to redraw the glyph (e.g. to swap in the official MMA artwork once
available). It writes next to this script by default.

Usage:
    pip install fonttools brotli
    python3 build_rufiyaa_font.py

Then copy the outputs into place:
    cp RufiyaaSign-Regular.woff2 ../../public/fonts/RufiyaaSign-Regular.woff2
    cp RufiyaaSign-Regular.ttf   ./RufiyaaSign-Regular.ttf   # kept here as source of truth
"""
import os

from fontTools.fontBuilder import FontBuilder
from fontTools.pens.ttGlyphPen import TTGlyphPen
from fontTools.pens.cu2quPen import Cu2QuPen
from fontTools.pens.boundsPen import BoundsPen

UPM = 1000
BASELINE_SVG_Y = 800  # svg y that maps to font baseline (font_y = 0)
X_OFFSET = 40
K = 0.5522847498  # bezier circle constant

def tx(x, y):
    """Map hand-authored SVG (y-down, 0..1000 viewbox) coords to font units (y-up)."""
    return (x + X_OFFSET, BASELINE_SVG_Y - y)


def draw_stem(pen):
    pen.moveTo(tx(172, 800))
    pen.lineTo(tx(172, 235))
    pen.lineTo(tx(278, 235))
    pen.lineTo(tx(278, 800))
    pen.closePath()


def draw_hook(pen):
    pen.moveTo(tx(278, 232))
    pen.curveTo(tx(365, 226), tx(445, 256), tx(450, 322))
    pen.curveTo(tx(453, 372), tx(418, 408), tx(362, 412))
    pen.curveTo(tx(332, 414), tx(304, 405), tx(286, 390))
    pen.lineTo(tx(286, 338))
    pen.curveTo(tx(300, 352), tx(326, 360), tx(352, 357))
    pen.curveTo(tx(392, 352), tx(412, 330), tx(410, 315))
    pen.curveTo(tx(406, 278), tx(350, 262), tx(278, 268))
    pen.closePath()


def draw_crossbar(pen):
    pen.moveTo(tx(105, 561))
    pen.lineTo(tx(105, 485))
    pen.lineTo(tx(465, 485))
    pen.lineTo(tx(465, 561))
    pen.closePath()


def draw_dot(pen):
    cx, cy, r = 382, 685, 52
    rk = r * K
    right = tx(cx + r, cy)
    bottom = tx(cx, cy + r)
    left = tx(cx - r, cy)
    top = tx(cx, cy - r)
    pen.moveTo(right)
    pen.curveTo(tx(cx + r, cy + rk), tx(cx + rk, cy + r), bottom)
    pen.curveTo(tx(cx - rk, cy + r), tx(cx - r, cy + rk), left)
    pen.curveTo(tx(cx - r, cy - rk), tx(cx - rk, cy - r), top)
    pen.curveTo(tx(cx + rk, cy - r), tx(cx + r, cy - rk), right)
    pen.closePath()


def draw_glyph(pen):
    draw_stem(pen)
    draw_hook(pen)
    draw_crossbar(pen)
    draw_dot(pen)


def draw_notdef(pen):
    # Simple hollow box, standard .notdef convention.
    pen.moveTo((80, -20))
    pen.lineTo((80, 780))
    pen.lineTo((520, 780))
    pen.lineTo((520, -20))
    pen.closePath()
    pen.moveTo((130, 30))
    pen.lineTo((470, 30))
    pen.lineTo((470, 730))
    pen.lineTo((130, 730))
    pen.closePath()


# --- compute bounds for metrics -------------------------------------------------
bounds_pen = BoundsPen(None)
draw_glyph(bounds_pen)
xmin, ymin, xmax, ymax = bounds_pen.bounds
print("glyph bounds:", bounds_pen.bounds)

notdef_bounds_pen = BoundsPen(None)
draw_notdef(notdef_bounds_pen)
nd_xmin, nd_ymin, nd_xmax, nd_ymax = notdef_bounds_pen.bounds

# --- build actual TrueType outlines (quadratic) ---------------------------------
glyph_ttpen = TTGlyphPen(None)
cu2qu_pen = Cu2QuPen(glyph_ttpen, max_err=1.0, reverse_direction=False)
draw_glyph(cu2qu_pen)
glyph = glyph_ttpen.glyph()

notdef_ttpen = TTGlyphPen(None)
draw_notdef(notdef_ttpen)
notdef_glyph = notdef_ttpen.glyph()

# --- advance widths ---------------------------------------------------------------
ADVANCE = 620
NOTDEF_ADVANCE = 600

fb = FontBuilder(UPM, isTTF=True)
glyph_order = [".notdef", "uni20C2"]
fb.setupGlyphOrder(glyph_order)
fb.setupCharacterMap({0x20C2: "uni20C2"})
fb.setupGlyf({".notdef": notdef_glyph, "uni20C2": glyph})

metrics = {
    ".notdef": (NOTDEF_ADVANCE, nd_xmin),
    "uni20C2": (ADVANCE, xmin),
}
fb.setupHorizontalMetrics(metrics)
fb.setupHorizontalHeader(ascent=800, descent=-200, lineGap=0)
fb.setupNameTable({
    "familyName": "RufiyaaSign",
    "styleName": "Regular",
    "uniqueFontIdentifier": "RufiyaaSign-Regular;AureliaBoutonniere;1.000",
    "fullName": "RufiyaaSign Regular",
    "version": "Version 1.000",
    "psName": "RufiyaaSign-Regular",
}, mac=False)
fb.setupOS2(
    sTypoAscender=800, sTypoDescender=-200, sTypoLineGap=0,
    usWinAscent=800, usWinDescent=200,
    sxHeight=500, sCapHeight=700,
    achVendID="ARLB",
    fsType=0,
)
fb.setupPost()
fb.setupHead(unitsPerEm=UPM)
fb.setupMaxp()

OUT_DIR = os.path.dirname(os.path.abspath(__file__))

ttf_path = os.path.join(OUT_DIR, "RufiyaaSign-Regular.ttf")
fb.font.save(ttf_path)
print("saved", ttf_path)

# --- woff2 ---
fb.font.flavor = "woff2"
woff2_path = os.path.join(OUT_DIR, "RufiyaaSign-Regular.woff2")
fb.font.save(woff2_path)
print("saved", woff2_path)
print("Now copy the .woff2 into public/fonts/ to deploy it (see module docstring).")
