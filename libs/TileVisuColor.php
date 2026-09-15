<?php

declare(strict_types=1);

/**
 * Farbauflösung für TileVisu-Kacheln.
 *
 * Wird ausschließlich über die Fassade TileVisuLib aufgerufen.
 *
 * WICHTIG: Der CustomPresentation-Pfad prüft Farbwerte mit is_numeric(),
 * der VariablePresentation-Pfad mit is_int() (Ausnahme: TEMPLATE-Optionen,
 * dort immer is_numeric). Diese Asymmetrie ist beobachtbares Verhalten der
 * bisherigen Implementierung und wird über den $mode-Parameter
 * ('numeric'|'int') exakt beibehalten.
 */
final class TileVisuColor
{
    // Returns hex color without leading '#', or empty string if none
    public static function getProfileColorHex(int $id): string
    {
        if (!function_exists('IPS_VariableExists') || !IPS_VariableExists($id)) {
            return '';
        }
        $variable = IPS_GetVariable($id);
        $presColor = self::getPresentationColorHex($id);
        if ($presColor !== '') {
            return $presColor;
        }

        // Hat die Variable eine Symcon-8-Darstellung, zeigt Symcon keine Profilfarben -
        // ein daneben eingetragenes Profil (z.B. ~Switch) zählt dann nicht.
        $effective = self::getEffectivePresentation($id, $variable);
        if (!empty($effective) && (string)($effective['PROFILE'] ?? '') === '') {
            return '';
        }

        $value = GetValue($id);
        $profile = $variable['VariableCustomProfile'] ?: $variable['VariableProfile'];
        if ($profile && IPS_VariableProfileExists($profile)) {
            $p = IPS_GetVariableProfile($profile);
            if (isset($p['Associations']) && is_array($p['Associations'])) {
                foreach ($p['Associations'] as $association) {
                    if (isset($association['Value'], $association['Color']) && $association['Value'] == $value) {
                        return ($association['Color'] === -1) ? '' : sprintf('%06X', (int)$association['Color']);
                    }
                }
            }
        }
        return '';
    }

    public static function getPresentationColorHex(int $id): string
    {
        if (!function_exists('IPS_VariableExists') || !IPS_VariableExists($id)) {
            return '';
        }
        $variable = IPS_GetVariable($id);
        $value = GetValue($id);
        $vt = $variable['VariableType'] ?? 0;

        if (isset($variable['VariableCustomPresentation']) && is_array($variable['VariableCustomPresentation'])) {
            $c = self::resolvePresentationColor($variable['VariableCustomPresentation'], $value, $vt, 'numeric', false);
            if ($c !== null) {
                return $c;
            }
        }
        if (isset($variable['VariablePresentation']) && is_array($variable['VariablePresentation'])) {
            $c = self::resolvePresentationColor($variable['VariablePresentation'], $value, $vt, 'int', true);
            if ($c !== null) {
                return $c;
            }
        }
        return '';
    }

    /**
     * Farben eines Ein/Aus-Buttons für true und false, so wie die Symcon-Visualisierung
     * sie zeigt: ['on' => '#RRGGBB'|'', 'off' => '#RRGGBB'|''].
     *
     * Maßgeblich ist die wirksame Darstellung der Variable. Hat sie eine
     * Symcon-8-Darstellung (z.B. Schalter), zählen nur deren Farben - ein daneben
     * noch eingetragenes Profil wie ~Switch (An = grün) bleibt unberücksichtigt.
     * Nur eine Legacy-Darstellung (mit PROFILE) oder gar keine Darstellung liest
     * die Farben aus den Profil-Assoziationen.
     */
    public static function getSwitchColors(int $id): array
    {
        $colors = ['on' => '', 'off' => ''];
        if (!function_exists('IPS_VariableExists') || !IPS_VariableExists($id)) {
            return $colors;
        }
        $variable = IPS_GetVariable($id);
        if (($variable['VariableType'] ?? null) !== 0) {
            return $colors;
        }

        $pres = self::getEffectivePresentation($id, $variable);
        $profile = (string)($pres['PROFILE'] ?? '');
        if (!empty($pres) && $profile === '') {
            foreach (['on' => true, 'off' => false] as $key => $value) {
                $c = self::resolvePresentationColor($pres, $value, 0, 'numeric', true);
                if ($c !== null && $c !== '') {
                    $colors[$key] = '#' . $c;
                }
            }
            return $colors;
        }

        if ($profile === '') {
            $profile = (string)(($variable['VariableCustomProfile'] ?? '') ?: ($variable['VariableProfile'] ?? ''));
        }
        if ($profile !== '' && IPS_VariableProfileExists($profile)) {
            $p = IPS_GetVariableProfile($profile);
            foreach (($p['Associations'] ?? []) as $a) {
                if (!is_array($a) || !isset($a['Value'], $a['Color']) || (int)$a['Color'] === -1) {
                    continue;
                }
                $hex = '#' . sprintf('%06X', (int)$a['Color']);
                if ($a['Value'] == 1 || $a['Value'] === true) {
                    $colors['on'] = $hex;
                } elseif ($a['Value'] == 0 || $a['Value'] === false) {
                    $colors['off'] = $hex;
                }
            }
        }
        return $colors;
    }

    /**
     * Wirksame Darstellung: IPS_GetVariablePresentation (löst eigene Darstellung
     * und Vorlagen auf), sonst die eigene bzw. die Standard-Darstellung der Variable.
     */
    private static function getEffectivePresentation(int $id, array $variable): array
    {
        if (function_exists('IPS_GetVariablePresentation')) {
            try {
                $resolved = @IPS_GetVariablePresentation($id);
                if (is_array($resolved) && !empty($resolved)) {
                    return $resolved;
                }
            } catch (\Throwable $e) {
            }
        }
        foreach (['VariableCustomPresentation', 'VariablePresentation'] as $key) {
            if (!empty($variable[$key]) && is_array($variable[$key])) {
                return $variable[$key];
            }
        }
        return [];
    }

    // Convert RGB int + alpha to css rgba()
    public static function rgbaFromHexAlpha(int $hexcolor, float $alpha): string
    {
        if ($hexcolor === -1) {
            return '';
        }
        $hexColor = sprintf('%06X', $hexcolor);
        if (strlen($hexColor) === 6) {
            $r = hexdec(substr($hexColor, 0, 2));
            $g = hexdec(substr($hexColor, 2, 2));
            $b = hexdec(substr($hexColor, 4, 2));
            return "rgba($r, $g, $b, $alpha)";
        }
        return $hexColor;
    }

    // Generic associations reader
    public static function getAssociations(int $id): array
    {
        $out = [];
        if (!function_exists('IPS_VariableExists') || !IPS_VariableExists($id)) {
            return $out;
        }
        $variable = IPS_GetVariable($id);
        // Prefer associations from VariableCustomPresentation if present
        if (isset($variable['VariableCustomPresentation']) && is_array($variable['VariableCustomPresentation'])) {
            $pres = $variable['VariableCustomPresentation'];
            $assocKeys = [];
            if (isset($pres['ASSOCIATIONS']) && is_array($pres['ASSOCIATIONS'])) {
                $assocKeys[] = 'ASSOCIATIONS';
            }
            if (isset($pres['Associations']) && is_array($pres['Associations'])) {
                $assocKeys[] = 'Associations';
            }
            foreach ($assocKeys as $k) {
                foreach ($pres[$k] as $a) {
                    $item = [
                        'value' => $a['Value'] ?? ($a['value'] ?? ''),
                        'label' => $a['Name'] ?? ($a['Label'] ?? ($a['label'] ?? '')),
                    ];
                    $iconVal = $a['Icon'] ?? ($a['ICON'] ?? null);
                    if (!empty($iconVal)) {
                        $item['icon'] = $iconVal;
                    }
                    $colVal = $a['Color'] ?? ($a['COLOR'] ?? null);
                    if (isset($colVal) && $colVal !== -1) {
                        $item['color'] = '#' . sprintf('%06X', (int)$colVal);
                    }
                    $out[] = $item;
                }
            }
            // Support OPTIONS from VariableCustomPresentation (stringified JSON or array)
            if (empty($out)) {
                $optRaw = null;
                if (isset($pres['OPTIONS'])) {
                    $optRaw = $pres['OPTIONS'];
                } elseif (isset($pres['Options'])) {
                    $optRaw = $pres['Options'];
                }
                if ($optRaw !== null) {
                    $opts = [];
                    if (is_string($optRaw)) {
                        $decoded = @json_decode($optRaw, true);
                        if (is_array($decoded)) {
                            $opts = $decoded;
                        }
                    } elseif (is_array($optRaw)) {
                        $opts = $optRaw;
                    }
                    foreach ($opts as $a) {
                        if (!is_array($a)) continue;
                        $item = [
                            'value' => $a['Value'] ?? ($a['value'] ?? ''),
                            'label' => $a['Caption'] ?? ($a['Name'] ?? ($a['Label'] ?? ($a['label'] ?? ''))),
                        ];
                        $iconVal = $a['IconValue'] ?? ($a['Icon'] ?? null);
                        if (!empty($iconVal)) {
                            $item['icon'] = $iconVal;
                        }
                        if (isset($a['Color'])) {
                            $colVal = (int)$a['Color'];
                            if ($colVal !== -1) {
                                $item['color'] = '#' . sprintf('%06X', $colVal);
                            }
                        }
                        $out[] = $item;
                    }
                }
            }
            if (!empty($out)) {
                return $out;
            }
        }
        $profile = $variable['VariableCustomProfile'] ?: $variable['VariableProfile'];
        if ($profile && IPS_VariableProfileExists($profile)) {
            $p = IPS_GetVariableProfile($profile);
            if (isset($p['Associations']) && is_array($p['Associations'])) {
                foreach ($p['Associations'] as $a) {
                    $item = [
                        'value' => $a['Value'] ?? '',
                        'label' => $a['Name'] ?? '',
                    ];
                    if (!empty($a['Icon'])) {
                        $item['icon'] = $a['Icon'];
                    }
                    if (isset($a['Color']) && $a['Color'] !== -1) {
                        $item['color'] = '#' . sprintf('%06X', (int)$a['Color']);
                    }
                    $out[] = $item;
                }
            }
        }
        return $out;
    }

    /**
     * Durchläuft eine Präsentation in der Original-Reihenfolge:
     * Associations → OPTIONS → TEMPLATE → PRESENTATION-GUID → genereller Fallback.
     * Rückgabe: Hex-String (auch '' = "Farbe explizit keine") oder null = weiterfallen.
     */
    private static function resolvePresentationColor(array $pres, mixed $value, int $vt, string $mode, bool $templateGeneralFallback): ?string
    {
        $valNorm = self::normValue($value, $vt);

        foreach (['ASSOCIATIONS', 'Associations'] as $k) {
            if (isset($pres[$k]) && is_array($pres[$k])) {
                $c = self::colorFromAssociationList($pres[$k], $valNorm, $vt, $mode);
                if ($c !== null) {
                    return $c;
                }
            }
        }

        $optRaw = $pres['OPTIONS'] ?? ($pres['Options'] ?? null);
        if ($optRaw !== null) {
            $c = self::colorFromOptionList(self::decodeOptions($optRaw), $valNorm, $vt, $mode);
            if ($c !== null) {
                return $c;
            }
        }

        $c = self::colorFromIntervals($pres, $value, $vt, $mode);
        if ($c !== null) {
            return $c;
        }

        $c = self::colorFromTemplate($pres, $value, $valNorm, $vt, $templateGeneralFallback);
        if ($c !== null) {
            return $c;
        }

        $c = self::colorFromPresentationGuid($pres, $value, $valNorm, $vt, $mode);
        if ($c !== null) {
            return $c;
        }

        return self::generalFallbackColor($pres, $value, $vt);
    }

    // Bool-Werte werden auf '1'/'0' normalisiert, alle anderen per String-Cast
    private static function normValue(mixed $v, int $vt): string
    {
        if ($vt === 0) {
            return (($v === true) || ((string)$v === '1') || ($v === 1)) ? '1' : '0';
        }
        return (string)$v;
    }

    private static function decodeOptions(mixed $optRaw): array
    {
        if (is_string($optRaw)) {
            $decoded = @json_decode($optRaw, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($optRaw) ? $optRaw : [];
    }

    // Associations akzeptieren 'Value' und 'value'; null-Werte werden übersprungen
    private static function colorFromAssociationList(array $list, string $valNorm, int $vt, string $mode): ?string
    {
        foreach ($list as $a) {
            if (!is_array($a)) continue;
            $av = $a['Value'] ?? ($a['value'] ?? null);
            if ($av === null) continue;
            if (self::normValue($av, $vt) === $valNorm) {
                $c = self::colorFromEntry($a, $mode);
                if ($c !== null) {
                    return $c;
                }
            }
        }
        return null;
    }

    // OPTIONS verlangen einen exakten 'Value'-Schlüssel (array_key_exists)
    private static function colorFromOptionList(array $opts, string $valNorm, int $vt, string $mode): ?string
    {
        foreach ($opts as $a) {
            if (!is_array($a) || !array_key_exists('Value', $a)) continue;
            if (self::normValue($a['Value'], $vt) === $valNorm) {
                $c = self::colorFromEntry($a, $mode);
                if ($c !== null) {
                    return $c;
                }
            }
        }
        return null;
    }

    // INTERVALS (Werte-Darstellung): Farbe des Intervalls, in dem der Wert liegt
    private static function colorFromIntervals(array $pres, mixed $value, int $vt, string $mode): ?string
    {
        if (($vt !== 1 && $vt !== 2) || !isset($pres['INTERVALS'])) {
            return null;
        }
        if (isset($pres['INTERVALS_ACTIVE']) && !$pres['INTERVALS_ACTIVE']) {
            return null;
        }
        $intervals = self::decodeOptions($pres['INTERVALS']);
        $current = (float)$value;
        foreach ($intervals as $interval) {
            if (!is_array($interval)) continue;
            $min = array_key_exists('IntervalMinValue', $interval) ? (float)$interval['IntervalMinValue'] : -INF;
            $max = array_key_exists('IntervalMaxValue', $interval) ? (float)$interval['IntervalMaxValue'] : INF;
            if ($current < $min || $current > $max) continue;
            if (array_key_exists('ColorActive', $interval) && !$interval['ColorActive']) {
                return null;
            }
            return self::colorFromEntry($interval, $mode);
        }
        return null;
    }

    /**
     * Liest die Farbe eines Treffers aus Color/COLOR/ColorValue/ColorDisplay.
     * ColorActive === false unterdrückt die Farbe; -1 gilt als "keine Farbe" —
     * in beiden Fällen läuft die Suche in der Liste weiter (null).
     */
    private static function colorFromEntry(array $a, string $mode): ?string
    {
        if (isset($a['ColorActive']) && $a['ColorActive'] === false) {
            return null;
        }
        foreach (['Color', 'COLOR', 'ColorValue', 'ColorDisplay'] as $key) {
            if (!isset($a[$key])) continue;
            $c = $a[$key];
            $valid = ($mode === 'int') ? is_int($c) : is_numeric($c);
            if ($valid && (int)$c !== -1) {
                return sprintf('%06X', (int)$c);
            }
        }
        return null;
    }

    private static function colorFromTemplate(array $pres, mixed $value, string $valNorm, int $vt, bool $withGeneralFallback): ?string
    {
        if (!isset($pres['TEMPLATE']) || !function_exists('IPS_GetTemplate')) {
            return null;
        }
        try {
            $tpl = @IPS_GetTemplate($pres['TEMPLATE']);
            if (!is_array($tpl) || !isset($tpl['Values']) || !is_array($tpl['Values'])) {
                return null;
            }
            $vals = $tpl['Values'];
            $optRaw = $vals['OPTIONS'] ?? ($vals['Options'] ?? null);
            if ($optRaw !== null) {
                // Template-Optionen werden in BEIDEN Pfaden mit is_numeric geprüft
                $c = self::colorFromOptionList(self::decodeOptions($optRaw), $valNorm, $vt, 'numeric');
                if ($c !== null) {
                    return $c;
                }
            }
            if ($withGeneralFallback) {
                // Nur der VariablePresentation-Pfad kennt diesen Template-Fallback
                $c = self::boolTrueFalseColor($vals, $value, $vt, 'numeric');
                if ($c !== null) {
                    return $c;
                }
                return self::plainColor($vals, 'numeric');
            }
        } catch (\Throwable $e) {
        }
        return null;
    }

    private static function colorFromPresentationGuid(array $pres, mixed $value, string $valNorm, int $vt, string $mode): ?string
    {
        if (!isset($pres['PRESENTATION']) || !function_exists('IPS_GetPresentation')) {
            return null;
        }
        try {
            $guidRaw = (string)$pres['PRESENTATION'];
            $guid = (strpos($guidRaw, '{') === false) ? ('{' . $guidRaw . '}') : $guidRaw;
            $pdata = @IPS_GetPresentation($guid);
            if (is_string($pdata)) {
                $decoded = @json_decode($pdata, true);
                if (is_array($decoded)) {
                    $pdata = $decoded;
                }
            }
            if (!is_array($pdata)) {
                return null;
            }
            $pp = isset($pdata['presentationParameters']) && is_array($pdata['presentationParameters']) ? $pdata['presentationParameters'] : [];
            if (empty($pp)) {
                return null;
            }
            $optRaw = $pp['OPTIONS'] ?? ($pp['Options'] ?? null);
            if ($optRaw !== null) {
                $c = self::colorFromOptionList(self::decodeOptions($optRaw), $valNorm, $vt, $mode);
                if ($c !== null) {
                    return $c;
                }
            }
            $c = self::boolTrueFalseColor($pp, $value, $vt, $mode);
            if ($c !== null) {
                return $c;
            }
            return self::plainColor($pp, $mode);
        } catch (\Throwable $e) {
        }
        return null;
    }

    // COLOR_TRUE/COLOR_FALSE für Bool-Variablen (sequentiell, kein elseif)
    private static function boolTrueFalseColor(array $src, mixed $value, int $vt, string $mode): ?string
    {
        if ($vt !== 0) {
            return null;
        }
        $useFalse = $src['USE_COLOR_FALSE'] ?? true;
        if (isset($src['COLOR_TRUE'])) {
            $c = $src['COLOR_TRUE'];
            $valid = ($mode === 'int') ? is_int($c) : is_numeric($c);
            if ($valid && (int)$c !== -1 && ($value === true || (string)$value === '1' || $value === 1)) {
                return sprintf('%06X', (int)$c);
            }
        }
        if ($useFalse && isset($src['COLOR_FALSE'])) {
            $c = $src['COLOR_FALSE'];
            $valid = ($mode === 'int') ? is_int($c) : is_numeric($c);
            if ($valid && (int)$c !== -1 && ($value === false || (string)$value === '0' || (string)$value === '' || $value === 0)) {
                return sprintf('%06X', (int)$c);
            }
        }
        return null;
    }

    // COLOR, dann Color — beide mit -1-Guard
    private static function plainColor(array $src, string $mode): ?string
    {
        foreach (['COLOR', 'Color'] as $key) {
            if (!isset($src[$key])) continue;
            $c = $src[$key];
            $valid = ($mode === 'int') ? is_int($c) : is_numeric($c);
            if ($valid && (int)$c !== -1) {
                return sprintf('%06X', (int)$c);
            }
        }
        return null;
    }

    /**
     * Genereller Fallback am Ende eines Pfads. Liefert '' bei explizitem -1
     * (Aufrufer gibt dann '' zurück) bzw. null, wenn gar keine numerische
     * Farbe gesetzt ist (Aufrufer fällt zum nächsten Pfad weiter).
     */
    private static function generalFallbackColor(array $pres, mixed $value, int $vt): ?string
    {
        $color = null;
        if ($vt === 0) {
            $useColorFalse = $pres['USE_COLOR_FALSE'] ?? true;
            $valBool = ($value === true || (string)$value === '1' || $value === 1);
            if ($valBool && isset($pres['COLOR_TRUE'])) {
                $color = $pres['COLOR_TRUE'];
            } elseif (!$valBool && $useColorFalse && isset($pres['COLOR_FALSE'])) {
                $color = $pres['COLOR_FALSE'];
            }
        }
        if ($color === null) {
            if (isset($pres['COLOR'])) {
                $color = $pres['COLOR'];
            } elseif (isset($pres['Color'])) {
                $color = $pres['Color'];
            }
        }
        if (is_numeric($color)) {
            $ci = (int)$color;
            return ($ci === -1) ? '' : sprintf('%06X', $ci);
        }
        return null;
    }
}
