<?php

declare(strict_types=1);

require_once __DIR__ . '/TileVisuColor.php';
require_once __DIR__ . '/TileVisuIcon.php';
require_once __DIR__ . '/TileVisuMigration.php';

/**
 * Fassade für die TileVisu-Hilfsfunktionen.
 *
 * Die öffentliche API (Signaturen und Fallback-Werte: '' für "keine Farbe",
 * 'Transparent' für "kein Icon", false bei Fehlern) ist eingefroren —
 * RoomTile und MultiRoomTile rufen ausschließlich diese Klasse auf.
 * Die Implementierung liegt in TileVisuColor, TileVisuIcon und TileVisuMigration.
 */
class TileVisuLib
{
    // Returns hex color without leading '#', or empty string if none
    public static function getProfileColorHex(int $id): string
    {
        return TileVisuColor::getProfileColorHex($id);
    }

    public static function getPresentationColorHex(int $id): string
    {
        return TileVisuColor::getPresentationColorHex($id);
    }

    // Convert RGB int + alpha to css rgba()
    public static function rgbaFromHexAlpha(int $hexcolor, float $alpha): string
    {
        return TileVisuColor::rgbaFromHexAlpha($hexcolor, $alpha);
    }

    // Resolve icon from VariableCustomPresentation or Profile or VarIcon
    public static function getIcon(int $id, bool $varicon): string
    {
        return TileVisuIcon::getIcon($id, $varicon);
    }

    public static function getIconAdvanced(int $id): string
    {
        return TileVisuIcon::getIconAdvanced($id);
    }

    public static function isObjectHidden(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        if (!function_exists('IPS_ObjectExists') || !@IPS_ObjectExists($id)) {
            return false;
        }
        try {
            $o = @IPS_GetObject($id);
            return (bool)($o['ObjectIsHidden'] ?? false);
        } catch (\Throwable $e) {
            return false;
        }
    }

    // Generic associations reader
    public static function getAssociations(int $id): array
    {
        return TileVisuColor::getAssociations($id);
    }

    public static function getIntegerAssociations(int $id): array
    {
        return self::getAssociations($id);
    }

    public static function getStringAssociations(int $id): array
    {
        return self::getAssociations($id);
    }

    /**
     * Wertebereich einer Zahlenvariable als ['min' => float, 'max' => float],
     * oder null, wenn es keinen gibt.
     *
     * Einen Bereich hat eine Variable mit Schieberegler-Darstellung oder mit einem
     * Profil, dessen Minimum kleiner als das Maximum ist (z.B. ein Dimmer 0-100 %).
     * Variablen mit Auswahloptionen gelten bewusst als "ohne Bereich" - für sie
     * schickt die Kachel den gewählten Wert selbst.
     */
    public static function getNumericRange(int $id): ?array
    {
        if (!function_exists('IPS_VariableExists') || !@IPS_VariableExists($id)) {
            return null;
        }
        if (!empty(self::getAssociations($id))) {
            return null;
        }

        $variable = @IPS_GetVariable($id);
        $presentation = [];
        if (function_exists('IPS_GetVariablePresentation')) {
            try {
                $resolved = @IPS_GetVariablePresentation($id);
                if (is_array($resolved)) {
                    $presentation = $resolved;
                }
            } catch (\Throwable $e) {}
        }
        if (empty($presentation) && is_array($variable['VariableCustomPresentation'] ?? null)) {
            $presentation = $variable['VariableCustomPresentation'];
        }

        $sliderGuid = defined('VARIABLE_PRESENTATION_SLIDER') ? VARIABLE_PRESENTATION_SLIDER : '{6B9CAEEC-5958-C223-30F7-BD36569FC57A}';
        $presentationGuid = trim((string)($presentation['PRESENTATION'] ?? ''), '{} ');
        if ($presentationGuid !== '' && strcasecmp($presentationGuid, trim($sliderGuid, '{} ')) === 0) {
            $min = (float)($presentation['MIN'] ?? 0);
            $max = (float)($presentation['MAX'] ?? 100);
            return $min < $max ? ['min' => $min, 'max' => $max] : null;
        }

        $profile = (string)($presentation['PROFILE'] ?? '');
        if ($profile === '') {
            $profile = (string)(($variable['VariableCustomProfile'] ?? '') ?: ($variable['VariableProfile'] ?? ''));
        }
        if ($profile !== '' && @IPS_VariableProfileExists($profile)) {
            $p = @IPS_GetVariableProfile($profile);
            $min = (float)($p['MinValue'] ?? 0);
            $max = (float)($p['MaxValue'] ?? 0);
            if ($min < $max) {
                return ['min' => $min, 'max' => $max];
            }
        }

        return null;
    }

    /**
     * Property-Rename-Migration v2: German → English property names.
     * Returns true if migration was performed (caller should return from ApplyChanges).
     */
    public static function migrateV2(IPSModule|IPSModuleStrict $module, int $instanceId): bool
    {
        return TileVisuMigration::migrateV2($module, $instanceId);
    }
}
