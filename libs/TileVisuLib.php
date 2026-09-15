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

    // Button colors for a boolean variable: ['on' => '#RRGGBB'|'', 'off' => '#RRGGBB'|'']
    public static function getSwitchColors(int $id): array
    {
        return TileVisuColor::getSwitchColors($id);
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
     * Property-Rename-Migration v2: German → English property names.
     * Returns true if migration was performed (caller should return from ApplyChanges).
     */
    public static function migrateV2(IPSModule|IPSModuleStrict $module, int $instanceId): bool
    {
        return TileVisuMigration::migrateV2($module, $instanceId);
    }
}
