<?php

namespace App\Support;

/**
 * Generator SVG kecil untuk sparkline, grafik tren, Grafik Barber-Johnson,
 * dan donut — dipakai berulang di halaman-halaman dashboard. Semua warna
 * dilewatkan sebagai token CSS (mis. "var(--accent)") supaya ikut tema
 * terang/gelap halaman, bukan warna literal.
 */
class ChartSvg
{
    public static function sparkline(array $values, string $color, int $w = 120, int $h = 30, int $pad = 3): string
    {
        $min = min($values);
        $max = max($values);
        $range = ($max - $min) ?: 1;
        $step = count($values) > 1 ? ($w - $pad * 2) / (count($values) - 1) : 0;

        $points = [];
        foreach (array_values($values) as $i => $v) {
            $x = $pad + $i * $step;
            $y = $pad + ($h - $pad * 2) * (1 - ($v - $min) / $range);
            $points[] = [round($x, 1), round($y, 1)];
        }
        $last = end($points);

        $d = self::pathD($points);

        return <<<SVG
        <svg class="kpi-spark" viewBox="0 0 {$w} {$h}" preserveAspectRatio="none" role="img" aria-label="Tren beberapa titik terakhir">
          <path d="{$d}" fill="none" stroke="{$color}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <circle cx="{$last[0]}" cy="{$last[1]}" r="2.4" fill="{$color}"/>
        </svg>
        SVG;
    }

    /**
     * Grafik tren dengan pita target opsional (mis. Tren BOR 30 hari, target 60-85%).
     */
    public static function trend(array $values, string $color, float $min, float $max, ?array $band = null, string $lastLabel = ''): string
    {
        $w = 760;
        $h = 170;
        $padL = 34;
        $padR = 10;
        $padT = 14;
        $padB = 22;
        $innerW = $w - $padL - $padR;
        $innerH = $h - $padT - $padB;
        $n = count($values);

        $x = fn ($i) => $padL + $i * ($innerW / max(1, $n - 1));
        $y = fn ($v) => $padT + $innerH * (1 - ($v - $min) / ($max - $min));

        $points = [];
        foreach (array_values($values) as $i => $v) {
            $points[] = [round($x($i), 1), round($y($v), 1)];
        }
        $last = end($points);

        $bandRect = '';
        if ($band) {
            $bandTop = $y($band[1]);
            $bandBot = $y($band[0]);
            $bandRect = sprintf(
                '<rect x="%s" y="%s" width="%s" height="%s" fill="var(--accent-soft)" opacity="0.6"/>',
                $padL, round($bandTop, 1), $innerW, round($bandBot - $bandTop, 1)
            );
        }

        $gridVals = [];
        $step = ($max - $min) / 4;
        for ($v = $min; $v <= $max + 0.01; $v += $step) {
            $gridVals[] = round($v);
        }
        $grid = '';
        foreach ($gridVals as $gv) {
            $gy = round($y($gv), 1);
            $grid .= sprintf(
                '<line x1="%s" x2="%s" y1="%s" y2="%s" stroke="var(--border)" stroke-width="1"/><text x="%s" y="%s" text-anchor="end" font-size="9" fill="var(--muted)" font-family="IBM Plex Mono, monospace">%s</text>',
                $padL, $w - $padR, $gy, $gy, $padL - 8, $gy + 3, $gv
            );
        }

        $d = self::pathD($points);
        $labelText = $lastLabel !== '' ? htmlspecialchars($lastLabel) : (string) end($values);

        return <<<SVG
        <svg viewBox="0 0 {$w} {$h}" style="width:100%; height:auto; display:block;" role="img" aria-label="Grafik tren">
          {$bandRect}
          {$grid}
          <path d="{$d}" fill="none" stroke="{$color}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
          <circle cx="{$last[0]}" cy="{$last[1]}" r="3.4" fill="{$color}"/>
          <text x="{$last[0]}" y="{$last[1]}" dy="-10" text-anchor="end" font-size="11" font-weight="700" fill="{$color}" font-family="IBM Plex Mono, monospace">{$labelText}</text>
        </svg>
        SVG;
    }

    /**
     * Grafik Barber-Johnson: memetakan titik bulanan (TOI, LOS) terhadap
     * zona ideal (BOR 70-85%, LOS 3-12 hari, TOI 1-3 hari).
     *
     * @param  array<int, array{month:string, toi:float, los:float}>  $points
     */
    public static function barberJohnson(array $points, string $color): string
    {
        $w = 340;
        $h = 258;
        $padL = 34;
        $padR = 16;
        $padT = 14;
        $padB = 36;
        $toiMax = 6;
        $losMax = 14;
        $innerW = $w - $padL - $padR;
        $innerH = $h - $padT - $padB;

        $X = fn ($toi) => $padL + ($toi / $toiMax) * $innerW;
        $Y = fn ($los) => $padT + (1 - $los / $losMax) * $innerH;

        $zx1 = $X(1);
        $zx2 = $X(3);
        $zy1 = $Y(12);
        $zy2 = $Y(3);

        $bor70End = (2.3333 * $toiMax <= $losMax) ? [$toiMax, 2.3333 * $toiMax] : [$losMax / 2.3333, $losMax];
        $bor85End = (5.6667 * $toiMax <= $losMax) ? [$toiMax, 5.6667 * $toiMax] : [$losMax / 5.6667, $losMax];

        $ticksX = [0, 1, 2, 3, 4, 5, 6];
        $ticksY = [0, 3, 6, 9, 12, 14];

        $gridX = '';
        foreach ($ticksX as $t) {
            $gridX .= sprintf('<line x1="%s" x2="%s" y1="%s" y2="%s" stroke="var(--border)" stroke-width="1"/>', round($X($t), 1), round($X($t), 1), $padT, $h - $padB);
        }
        $gridY = '';
        foreach ($ticksY as $t) {
            $gridY .= sprintf('<line x1="%s" x2="%s" y1="%s" y2="%s" stroke="var(--border)" stroke-width="1"/>', $padL, $w - $padR, round($Y($t), 1), round($Y($t), 1));
        }
        $labelsX = '';
        foreach ($ticksX as $t) {
            $labelsX .= sprintf('<text x="%s" y="%s" text-anchor="middle" font-size="8.5" fill="var(--muted)" font-family="IBM Plex Mono, monospace">%s</text>', round($X($t), 1), $h - $padB + 13, $t);
        }
        $labelsY = '';
        foreach ($ticksY as $t) {
            $labelsY .= sprintf('<text x="%s" y="%s" text-anchor="end" font-size="8.5" fill="var(--muted)" font-family="IBM Plex Mono, monospace">%s</text>', $padL - 6, round($Y($t) + 3, 1), $t);
        }

        $pathPts = array_map(fn ($p) => [round($X($p['toi']), 1), round($Y($p['los']), 1)], $points);
        $last = end($pathPts);
        $lastMonth = end($points)['month'];

        $dots = '';
        foreach (array_slice($pathPts, 0, -1) as $p) {
            $dots .= sprintf('<circle cx="%s" cy="%s" r="2.6" fill="%s" opacity=".55"/>', $p[0], $p[1], $color);
        }

        $d = self::pathD($pathPts);
        $zoneW = round($zx2 - $zx1, 1);
        $zoneH = round($zy2 - $zy1, 1);
        $x0 = round($X(0), 1);
        $y0 = round($Y(0), 1);
        $bor70X = round($X($bor70End[0]), 1);
        $bor70Y = round($Y($bor70End[1]), 1);
        $bor85X = round($X($bor85End[0]), 1);
        $bor85Y = round($Y($bor85End[1]), 1);
        $bor70LabelX = min($bor70X, $w - $padR);
        $bor70LabelY = $padT + 10;
        $bor85LabelX = $bor85X;
        $bor85LabelY = $padT + 10;
        $axisLabelX = $w - $padR;
        $axisLabelY = $h - 8;

        return <<<SVG
        <svg viewBox="0 0 {$w} {$h}" style="width:100%; height:auto; display:block;" role="img" aria-label="Grafik Barber-Johnson">
          {$gridX}{$gridY}
          <rect x="{$zx1}" y="{$zy1}" width="{$zoneW}" height="{$zoneH}" fill="var(--accent-soft)" opacity="0.6"/>
          <line x1="{$x0}" y1="{$y0}" x2="{$bor70X}" y2="{$bor70Y}" stroke="var(--muted)" stroke-width="1" stroke-dasharray="3 3"/>
          <line x1="{$x0}" y1="{$y0}" x2="{$bor85X}" y2="{$bor85Y}" stroke="var(--muted)" stroke-width="1" stroke-dasharray="3 3"/>
          <text x="{$bor70LabelX}" y="{$bor70LabelY}" text-anchor="end" font-size="8.5" fill="var(--muted)" font-family="IBM Plex Mono, monospace">BOR 70%</text>
          <text x="{$bor85LabelX}" y="{$bor85LabelY}" text-anchor="middle" font-size="8.5" fill="var(--muted)" font-family="IBM Plex Mono, monospace">BOR 85%</text>
          <path d="{$d}" fill="none" stroke="{$color}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          {$dots}
          <circle cx="{$last[0]}" cy="{$last[1]}" r="4.2" fill="{$color}"/>
          <text x="{$last[0]}" y="{$last[1]}" dx="7" dy="3.5" font-size="9.5" font-weight="700" fill="{$color}" font-family="IBM Plex Mono, monospace">{$lastMonth}</text>
          {$labelsX}{$labelsY}
          <text x="{$axisLabelX}" y="{$axisLabelY}" text-anchor="end" font-size="8.5" fill="var(--muted)">TOI (hari) &#8594;</text>
        </svg>
        SVG;
    }

    /** @param  array<int, array{label:string, val:float, color:string}>  $items */
    public static function donut(array $items, int $size = 118): string
    {
        $r = $size / 2 - 8;
        $holeR = $r - 10;
        $cx = $size / 2;
        $cy = $size / 2;
        $circ = 2 * M_PI * $r;
        $offset = 0;
        $arcs = '';

        foreach ($items as $it) {
            $len = ($it['val'] / 100) * $circ;
            $arcs .= sprintf(
                '<circle cx="%s" cy="%s" r="%s" fill="none" stroke="%s" stroke-width="15" stroke-dasharray="%s %s" stroke-dashoffset="%s" transform="rotate(-90 %s %s)"/>',
                $cx, $cy, $r, $it['color'], round($len, 1), round($circ - $len, 1), round(-$offset, 1), $cx, $cy
            );
            $offset += $len;
        }

        return <<<SVG
        <svg width="{$size}" height="{$size}" viewBox="0 0 {$size} {$size}" role="img" aria-label="Payer mix pasien">
          {$arcs}
          <circle cx="{$cx}" cy="{$cy}" r="{$holeR}" fill="var(--surface)"/>
        </svg>
        SVG;
    }

    private static function pathD(array $points): string
    {
        $d = '';
        foreach ($points as $i => $p) {
            $d .= ($i === 0 ? 'M' : 'L').$p[0].' '.$p[1].' ';
        }

        return trim($d);
    }
}
