#!/usr/bin/env python3
"""
Builds a minimal single-glyph webfont "RufiyaaSign". Outputs TTF + WOFF2.

The glyph artwork is traced from resources/fonts/source/rufiyaa-reference.png
(the official-looking Maldivian Rufiyaa sign supplied as a reference) via
resources/fonts/source/rufiyaa-glyph-trace.svg, a clean vector trace of that
reference produced with potrace:

    convert rufiyaa-reference.png -alpha remove -alpha off -colorspace Gray \
        -threshold 50% rufiyaa-reference.pbm
    potrace rufiyaa-reference.pbm -s -o rufiyaa-glyph-trace.svg --flat

This script loads that SVG, applies its <g transform> (fontTools' SVGPath
does not apply group transforms itself, only path-level ones, so it's
applied manually below), and rescales/repositions the traced outline to sit
on the font baseline at a fixed cap height — instead of hand-drawing the
glyph with bezier calls, so the artwork always matches the reference trace
file exactly. Re-run this script after replacing the trace file.

IMPORTANT — codepoint choice: this glyph lives at a Private Use Area (PUA)
codepoint (U+E900 by default), NOT at U+20C2. An earlier version of this
project used U+20C2 on the assumption it was "MALDIVIAN RUFIYAA SIGN", but
that codepoint is in fact unassigned/reserved in the real Unicode standard
— the Currency Symbols block (U+20A0-U+20CF) currently ends its real
assignments at U+20C0 (SOM SIGN, Unicode 9.0). There is no dedicated
Unicode codepoint for the Maldivian rufiyaa sign yet; it has never been
submitted to/accepted by the Unicode Consortium.

Browsers treat a genuinely unassigned codepoint specially: they refuse to
let ANY font substitute a glyph for it and show their own "invalid
codepoint" box instead, regardless of what a custom font's cmap claims —
this happens at the Unicode-validity layer, before font glyph lookup, so
no amount of font-rebuilding fixes it. (Verified empirically: the exact
same font-building code renders perfectly at a PUA codepoint and fails
identically at U+20C2, even for a trivial placeholder glyph.)

The Private Use Area (U+E000-U+F8FF) is the Unicode-sanctioned mechanism
for exactly this situation — "a symbol with local meaning and no assigned
codepoint" — and is how every icon font (Font Awesome, etc.) works. If
Unicode ever assigns a real codepoint for the rufiyaa sign, update
RUFIYAA_CODEPOINT below (and config/shop.php's 'sign' value, and the
unicode-range in resources/css/app.css) to match.

This is dev tooling, not part of the running app — re-run it only if you
want to redraw the glyph (e.g. to swap in a new trace, or to change the
codepoint). It writes next to this script by default.

Usage:
    pip install fonttools brotli
    python3 build_rufiyaa_font.py

Then copy the outputs into place:
    cp RufiyaaSign-Regular.woff2 ../../public/fonts/RufiyaaSign-Regular.woff2
    cp RufiyaaSign-Regular.ttf   ./RufiyaaSign-Regular.ttf   # kept here as source of truth
"""
import os

from fontTools.fontBuilder import FontBuilder
from fontTools.pens.boundsPen import BoundsPen
from fontTools.pens.cu2quPen import Cu2QuPen
from fontTools.pens.recordingPen import RecordingPen
from fontTools.pens.transformPen import TransformPen
from fontTools.pens.ttGlyphPen import TTGlyphPen
from fontTools.svgLib.path import SVGPath

RUFIYAA_CODEPOINT = 0xE900  # Private Use Area — see module docstring.
RUFIYAA_GLYPH_NAME = f"uni{RUFIYAA_CODEPOINT:04X}"

UPM = 1000
TARGET_HEIGHT = 600  # glyph height in font units, same cap-height feel as body text figures
SIDE_BEARING = 40

OUT_DIR = os.path.dirname(os.path.abspath(__file__))
TRACE_SVG = os.path.join(OUT_DIR, "source", "rufiyaa-glyph-trace.svg")

# The trace file's root <g> carries "translate(0,402) scale(0.1,-0.1)" (a
# potrace convention: it emits path data in its own internal 10x scale with
# y flipped, then undoes that with a group transform). fontTools' SVGPath
# parses path data but ignores the enclosing <g transform>, so it is
# applied by hand here against the raw, untransformed path coordinates.
GROUP_TRANSFORM = (0.1, 0, 0, -0.1, 0, 402)


def load_traced_glyph_pen():
    """Returns a RecordingPen of the trace file's outline, in its own
    (untransformed) raw coordinate space — i.e. before GROUP_TRANSFORM."""
    raw_pen = RecordingPen()
    SVGPath(TRACE_SVG).draw(raw_pen)
    return raw_pen


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


# --- figure out the transform that maps raw trace coordinates straight into
#     font units: baseline-sitting, TARGET_HEIGHT tall, SIDE_BEARING from
#     the left edge. --------------------------------------------------------
raw_pen = load_traced_glyph_pen()

svg_bounds_pen = BoundsPen(None)
raw_pen.replay(TransformPen(svg_bounds_pen, GROUP_TRANSFORM))
svg_xmin, svg_ymin, svg_xmax, svg_ymax = svg_bounds_pen.bounds
# svg space is y-down (0 at top of the viewBox); svg_ymax is the visual
# bottom of the shape, which is what should land on the font baseline.
svg_height = svg_ymax - svg_ymin
scale = TARGET_HEIGHT / svg_height

# Compose GROUP_TRANSFORM with the font-space remap in a single matrix so
# the traced outline only needs to be walked once:
#   font_x = (0.1*x - svg_xmin) * scale + SIDE_BEARING
#   font_y = (svg_ymax - (-0.1*y + 402)) * scale
a, b, c, d = 0.1 * scale, 0.0, 0.0, 0.1 * scale
e = SIDE_BEARING - svg_xmin * scale
f = (svg_ymax - 402) * scale
FONT_TRANSFORM = (a, b, c, d, e, f)

# --- compute bounds for metrics -------------------------------------------------
bounds_pen = BoundsPen(None)
raw_pen.replay(TransformPen(bounds_pen, FONT_TRANSFORM))
xmin, ymin, xmax, ymax = bounds_pen.bounds
print("glyph bounds (font units):", bounds_pen.bounds)

notdef_bounds_pen = BoundsPen(None)
draw_notdef(notdef_bounds_pen)
nd_xmin, nd_ymin, nd_xmax, nd_ymax = notdef_bounds_pen.bounds

# --- build actual TrueType outlines (quadratic) ---------------------------------
glyph_ttpen = TTGlyphPen(None)
cu2qu_pen = Cu2QuPen(glyph_ttpen, max_err=1.0, reverse_direction=False)
raw_pen.replay(TransformPen(cu2qu_pen, FONT_TRANSFORM))
glyph = glyph_ttpen.glyph()

notdef_ttpen = TTGlyphPen(None)
draw_notdef(notdef_ttpen)
notdef_glyph = notdef_ttpen.glyph()

# --- advance widths ---------------------------------------------------------------
ADVANCE = round(xmax + SIDE_BEARING)
NOTDEF_ADVANCE = 600

fb = FontBuilder(UPM, isTTF=True)
glyph_order = [".notdef", RUFIYAA_GLYPH_NAME]
fb.setupGlyphOrder(glyph_order)
fb.setupCharacterMap({RUFIYAA_CODEPOINT: RUFIYAA_GLYPH_NAME})
fb.setupGlyf({".notdef": notdef_glyph, RUFIYAA_GLYPH_NAME: glyph})

metrics = {
    ".notdef": (NOTDEF_ADVANCE, nd_xmin),
    RUFIYAA_GLYPH_NAME: (ADVANCE, round(xmin)),
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

ttf_path = os.path.join(OUT_DIR, "RufiyaaSign-Regular.ttf")
fb.font.save(ttf_path)
print("saved", ttf_path)

# --- woff2 ---
fb.font.flavor = "woff2"
woff2_path = os.path.join(OUT_DIR, "RufiyaaSign-Regular.woff2")
fb.font.save(woff2_path)
print("saved", woff2_path)
print("Now copy the .woff2 into public/fonts/ to deploy it (see module docstring).")
