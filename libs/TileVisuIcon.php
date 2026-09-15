<?php

declare(strict_types=1);

/**
 * Icon-Auflösung für TileVisu-Kacheln.
 *
 * Wird ausschließlich über die Fassade TileVisuLib aufgerufen.
 * Fallback-Kette von getIconAdvanced():
 *   aufgelöste Präsentation → Visualisierung (ValueMappings) → klassisches Profil.
 */
final class TileVisuIcon
{
    /**
     * Kennzeichnet in IPS die als Präsentation verpackten Legacy-Profile;
     * deren Icons werden ignoriert und stattdessen das Profil ausgewertet.
     */
    private const LEGACY_PRESENTATION_GUID = '4153A8D4-5C33-C65F-C1F3-7B61AAF99B1C';

    // Resolve icon from VariableCustomPresentation or Profile or VarIcon
    public static function getIcon(int $id, bool $varicon): string
    {
        if (!function_exists('IPS_VariableExists') || !IPS_VariableExists($id)) {
            return 'Transparent';
        }
        $variable = IPS_GetVariable($id);
        $value = GetValue($id);
        $icon = '';

        if ($varicon) {
            $obj = IPS_GetObject($id);
            if (!empty($obj['ObjectIcon'])) {
                return $obj['ObjectIcon'];
            }
            return 'Transparent';
        }

        // Try VariableCustomPresentation (if available in this IPS version)
        if (isset($variable['VariableCustomPresentation']) && is_array($variable['VariableCustomPresentation'])) {
            $pres = $variable['VariableCustomPresentation'];
            if (!empty($pres['ICON'])) {
                $icon = $pres['ICON'];
            } elseif (!empty($pres['Icon'])) {
                $icon = $pres['Icon'];
            } elseif ($variable['VariableType'] === 0 /* boolean */) {
                $useIconFalse = $pres['USE_ICON_FALSE'] ?? true;
                if ($value && !empty($pres['ICON_TRUE'])) {
                    $icon = $pres['ICON_TRUE'];
                } elseif (!$value && $useIconFalse && !empty($pres['ICON_FALSE'])) {
                    $icon = $pres['ICON_FALSE'];
                }
            }
            if ($icon !== '') {
                return $icon;
            }
        }

        // Fallback: use profile associations
        $profile = $variable['VariableCustomProfile'] ?: $variable['VariableProfile'];
        if ($profile && IPS_VariableProfileExists($profile)) {
            $p = IPS_GetVariableProfile($profile);
            if (isset($p['Associations']) && is_array($p['Associations'])) {
                foreach ($p['Associations'] as $association) {
                    if (isset($association['Value']) && $association['Value'] == $value && !empty($association['Icon'])) {
                        return $association['Icon'];
                    }
                }
            }
            if (!empty($p['Icon'])) {
                return $p['Icon'];
            }
        }
        return 'Transparent';
    }

    public static function getIconAdvanced(int $id): string
    {
        if ($id <= 0 || !@IPS_VariableExists($id)) {
            return 'Transparent';
        }
        $variable = @IPS_GetVariable($id);
        $value = null;
        try { $value = @GetValue($id); } catch (\Throwable $e) {}
        $vt = $variable['VariableType'] ?? 0;

        // 1) IPS_GetVariablePresentation (löst Vorlagen, GUIDs, Vererbung automatisch auf)
        $pres = self::resolveVariablePresentation($id);

        $icon = '';
        if (!empty($pres) && !self::isLegacyPresentation($pres)) {
            $icon = self::iconFromPresentation($pres, $value, $vt);
        }

        // 2) IPS_GetVariableVisualization Fallback (ValueMappings)
        if ($icon === '') {
            $icon = self::iconFromVisualization($id, $value);
        }

        // 3) Klassische Variablenprofile (inkl. PROFILE aus Präsentation)
        if ($icon === '') {
            $icon = self::iconFromProfile($variable, $pres, $value);
        }

        return ($icon !== '') ? $icon : 'Transparent';
    }

    private static function resolveVariablePresentation(int $id): array
    {
        $pres = [];
        if (function_exists('IPS_GetVariablePresentation')) {
            try {
                $resolved = @IPS_GetVariablePresentation($id);
                if (is_array($resolved) && !empty($resolved)) {
                    $pres = $resolved;
                }
            } catch (\Throwable $e) {}
        }
        return $pres;
    }

    private static function isLegacyPresentation(array $pres): bool
    {
        if (!isset($pres['PRESENTATION'])) {
            return false;
        }
        return strcasecmp(trim((string)$pres['PRESENTATION'], '{} '), self::LEGACY_PRESENTATION_GUID) === 0;
    }

    private static function iconFromPresentation(array $pres, mixed $value, int $vt): string
    {
        // Wie in der Symcon-Visualisierung: Das Icon zum aktuellen Wert (Intervall,
        // Option, An/Aus) hat Vorrang, das allgemeine Icon ist nur der Ersatz.

        // Boolean: ICON_TRUE/ICON_FALSE
        if ($vt === 0) {
            $icon = self::iconFromBoolean($pres, $value);
            if ($icon !== '') {
                return $icon;
            }
        }

        // INTERVALS für numerische Variablen (Bereichs-Icons)
        if ($vt === 1 || $vt === 2) {
            $icon = self::iconFromIntervals($pres, $value);
            if ($icon !== '') {
                return $icon;
            }
        }

        // OPTIONS aus aufgelöster Präsentation
        $icon = self::iconFromOptions($pres, $value, $vt);
        if ($icon !== '') {
            return $icon;
        }

        // Allgemeines Icon
        foreach (['ICON', 'Icon', 'icon'] as $field) {
            if (isset($pres[$field]) && (string)$pres[$field] !== '') {
                return (string)$pres[$field];
            }
        }
        return '';
    }

    private static function iconFromBoolean(array $pres, mixed $value): string
    {
        $iconTrueSet = isset($pres['ICON_TRUE']) && trim((string)$pres['ICON_TRUE']) !== '';
        $iconFalseSet = isset($pres['ICON_FALSE']) && trim((string)$pres['ICON_FALSE']) !== '';
        if ($iconTrueSet || $iconFalseSet) {
            $useFalse = $pres['USE_ICON_FALSE'] ?? true;
            $isTrue = ($value === true) || ((string)$value === '1') || ($value === 1);
            if ($iconTrueSet && $isTrue) {
                return (string)$pres['ICON_TRUE'];
            }
            if ($iconFalseSet && $useFalse && !$isTrue) {
                return (string)$pres['ICON_FALSE'];
            }
        }
        return '';
    }

    private static function iconFromIntervals(array $pres, mixed $value): string
    {
        if (!isset($pres['INTERVALS'])) {
            return '';
        }
        $intervals = is_string($pres['INTERVALS']) ? @json_decode($pres['INTERVALS'], true) : $pres['INTERVALS'];
        $intervalsActive = isset($pres['INTERVALS_ACTIVE']) ? (bool)$pres['INTERVALS_ACTIVE'] : true;
        if (!$intervalsActive || !is_array($intervals)) {
            return '';
        }
        $current = floatval($value);
        foreach ($intervals as $interval) {
            if (empty($interval['IconActive']) || empty($interval['IconValue'])) continue;
            $min = array_key_exists('IntervalMinValue', $interval) ? floatval($interval['IntervalMinValue']) : -INF;
            $max = array_key_exists('IntervalMaxValue', $interval) ? floatval($interval['IntervalMaxValue']) : INF;
            if ($current >= $min && $current <= $max) {
                return (string)$interval['IconValue'];
            }
        }
        return '';
    }

    private static function iconFromOptions(array $pres, mixed $value, int $vt): string
    {
        if (!isset($pres['OPTIONS'])) {
            return '';
        }
        $opts = is_string($pres['OPTIONS']) ? @json_decode($pres['OPTIONS'], true) : $pres['OPTIONS'];
        if (!is_array($opts)) {
            return '';
        }
        $isNumeric = ($vt === 1 || $vt === 2);
        $current = $isNumeric ? floatval($value) : null;
        foreach ($opts as $opt) {
            if (!is_array($opt)) continue;
            $optIcon = $opt['IconValue'] ?? ($opt['Icon'] ?? '');
            if (empty($optIcon)) continue;
            $hasRange = isset($opt['Min']) || isset($opt['Max']) || isset($opt['MinValue']) || isset($opt['MaxValue']);
            if ($hasRange && $current !== null) {
                $min = $opt['Min'] ?? ($opt['MinValue'] ?? -INF);
                $max = $opt['Max'] ?? ($opt['MaxValue'] ?? INF);
                if ($current >= floatval($min) && $current <= floatval($max)) {
                    return (string)$optIcon;
                }
            } elseif (array_key_exists('Value', $opt)) {
                if ($current !== null && is_numeric($opt['Value'])) {
                    if (abs(floatval($opt['Value']) - $current) < 1e-9) {
                        return (string)$optIcon;
                    }
                } elseif ((string)($opt['Value'] ?? '') === (string)$value) {
                    return (string)$optIcon;
                }
            }
        }
        return '';
    }

    private static function iconFromVisualization(int $id, mixed $value): string
    {
        if (!function_exists('IPS_GetVariableVisualization')) {
            return '';
        }
        try {
            $vis = @IPS_GetVariableVisualization($id);
            if (!is_array($vis) || !isset($vis['ValueMappings'])) {
                return '';
            }
            foreach ($vis['ValueMappings'] as $mapping) {
                if (!isset($mapping['Icon']) || $mapping['Icon'] === '') continue;
                $v = floatval($value);
                $hasRange = isset($mapping['MinValue']) || isset($mapping['MaxValue']) || isset($mapping['Minimum']) || isset($mapping['Maximum']) || isset($mapping['Min']) || isset($mapping['Max']);
                if ($hasRange) {
                    $min = $mapping['MinValue'] ?? ($mapping['Minimum'] ?? ($mapping['Min'] ?? -INF));
                    $max = $mapping['MaxValue'] ?? ($mapping['Maximum'] ?? ($mapping['Max'] ?? INF));
                    if ($v >= floatval($min) && $v <= floatval($max)) {
                        return (string)$mapping['Icon'];
                    }
                } elseif (isset($mapping['Value']) && $mapping['Value'] == $value) {
                    return (string)$mapping['Icon'];
                }
            }
            if (isset($vis['Icon']) && $vis['Icon'] !== '') {
                return (string)$vis['Icon'];
            }
        } catch (\Throwable $e) {}
        return '';
    }

    private static function iconFromProfile(array $variable, array $pres, mixed $value): string
    {
        $profile = $variable['VariableCustomProfile'] ?: ($variable['VariableProfile'] ?? '');
        if (empty($profile) && isset($pres['PROFILE']) && !empty($pres['PROFILE'])) {
            $profile = (string)$pres['PROFILE'];
        }
        if (!empty($profile) && @IPS_VariableProfileExists($profile)) {
            $p = @IPS_GetVariableProfile($profile);
            if (isset($p['Associations']) && is_array($p['Associations'])) {
                foreach ($p['Associations'] as $assoc) {
                    if (isset($assoc['Value'], $assoc['Icon']) && $assoc['Icon'] !== '' && $assoc['Value'] == $value) {
                        return (string)$assoc['Icon'];
                    }
                }
            }
            if (isset($p['Icon']) && $p['Icon'] !== '') {
                return (string)$p['Icon'];
            }
        }
        return '';
    }
}
