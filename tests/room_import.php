<?php

declare(strict_types=1);

/**
 * Import einer eigenständigen RoomTile als weiteres Zimmer der MultiRoomTile.
 *
 * Prüft die Übernahme der Felder, die beiden Optionen (individuelle
 * Einstellungen, Infocenter) und die Auswahlliste im Formular.
 *
 * Verwendung: php tests/room_import.php
 */

require_once __DIR__ . '/stubs/autoload.php';

mt_srand(13);
IPS\Kernel::reset();
IPS\ModuleLoader::loadLibrary(__DIR__ . '/../library.json');

const MULTI_ROOM_TILE = '{5D1D3B42-2B8E-4C5E-AE9A-4E8E2C00E9F4}';
const ROOM_TILE = '{0E33BB6A-B5A7-4D25-883A-EDF0551DB5C3}';

function assertSameValue(string $label, mixed $expected, mixed $actual): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(sprintf('%s failed: expected %s, got %s', $label, json_encode($expected), json_encode($actual)));
    }
    echo $label . "=ok\n";
}

function roomsOf(int $instanceId): array
{
    return (array)json_decode((string)IPS_GetProperty($instanceId, 'Rooms'), true);
}

/** Unterlisten liegen nach der Normalisierung als JSON-Text im Zimmer */
function listOf(array $room, string $key): array
{
    $value = $room[$key] ?? [];
    if (is_string($value)) {
        $value = json_decode($value, true);
    }
    return is_array($value) ? $value : [];
}

ob_start();
$category = IPS_CreateCategory();
IPS_SetName($category, 'Kacheln');

$light = IPS_CreateVariable(0);
IPS_SetName($light, 'Licht');
$temperature = IPS_CreateVariable(2);
IPS_SetName($temperature, 'Temperatur');
$humidity = IPS_CreateVariable(2);
IPS_SetName($humidity, 'Luftfeuchte');

$menuItems = [[
    'Id' => 'm1', 'VariableId' => $light, 'OpenObjectId' => 0, 'SceneControlId' => 0,
    'ShowName' => true, 'ShowIcon' => true, 'ShowValue' => false,
    'UseVarColor' => false, 'ColorTrue' => -1, 'ColorFalse' => -1,
    'AltName' => '', 'Width' => 100, 'FullWidth' => false,
]];

$roomTileId = IPS_CreateInstance(ROOM_TILE);
IPS_SetParent($roomTileId, $category);
IPS_SetName($roomTileId, 'Bad');
IPS_SetProperty($roomTileId, 'RoomName', 'Badezimmer');
IPS_SetProperty($roomTileId, 'MenuItems', json_encode($menuItems));
IPS_SetProperty($roomTileId, 'MenuFontSize', 19);
IPS_SetProperty($roomTileId, 'TileBackgroundColor', 0x445566);
IPS_SetProperty($roomTileId, 'MenuTransparency', 42.0);
IPS_SetProperty($roomTileId, 'InfoMiddleLeft', $temperature);
IPS_SetProperty($roomTileId, 'InfoMiddleLeftShowName', true);
IPS_SetProperty($roomTileId, 'InfoMiddleLeftShowIcon', false);
IPS_SetProperty($roomTileId, 'InfoMiddleLeftShowValue', true);
IPS_SetProperty($roomTileId, 'InfoMiddleRight', $humidity);
IPS_ApplyChanges($roomTileId);

$mrtId = IPS_CreateInstance(MULTI_ROOM_TILE);
$mrt = IPS\InstanceManager::getInstanceInterface($mrtId);
IPS_ApplyChanges($mrtId);

// 1) Ohne individuelle Einstellungen, mit Infocenter
$mrt->ImportRoom($roomTileId, false, true);
$rooms = roomsOf($mrtId);
$room = $rooms[0] ?? [];
ob_end_clean();

echo "== Zimmer-Import ==\n";
assertSameValue('room_added', 1, count($rooms));
assertSameValue('room_name_from_instance', 'Badezimmer', $room['RoomName'] ?? null);
assertSameValue('menu_items_imported', $menuItems, listOf($room, 'MenuItems'));
assertSameValue('target_category_reset', 0, $room['TargetCategoryId'] ?? null);

// Ohne individuelle Einstellungen gilt die globale Einstellung der Kachel
assertSameValue('global_size_placeholder', -1, $room['MenuFontSize'] ?? null);
assertSameValue('room_name_size_placeholder', -1, $room['RoomNameFontSize'] ?? null);
assertSameValue('show_room_name_enabled', true, $room['ShowRoomName'] ?? null);
assertSameValue('global_color_placeholder', -1, $room['TileBackgroundColor'] ?? null);
assertSameValue('global_percent_placeholder', -1.0, (float)($room['MenuTransparency'] ?? 0));

// Infocenter wird zu Einträgen der Infoleiste, links bleibt links
$infoItems = listOf($room, 'InfoItems');
assertSameValue('info_center_moved', 2, count($infoItems));
assertSameValue('info_center_left_area', 'left', $infoItems[0]['Area'] ?? null);
assertSameValue('info_center_left_variable', $temperature, $infoItems[0]['VariableId'] ?? null);
assertSameValue('info_center_flags_kept', [true, false, true], [
    $infoItems[0]['ShowName'] ?? null, $infoItems[0]['ShowIcon'] ?? null, $infoItems[0]['ShowValue'] ?? null,
]);
assertSameValue('info_center_right_area', 'right', $infoItems[1]['Area'] ?? null);
assertSameValue('info_center_ids_unique', true, ($infoItems[0]['Id'] ?? '') !== ($infoItems[1]['Id'] ?? ''));

// 2) Mit individuellen Einstellungen, ohne Infocenter
ob_start();
$mrt->ImportRoom($roomTileId, true, false);
$rooms = roomsOf($mrtId);
ob_end_clean();
$second = $rooms[1] ?? [];
assertSameValue('second_room_added', 2, count($rooms));
assertSameValue('individual_size_kept', 19, $second['MenuFontSize'] ?? null);
assertSameValue('show_room_name_also_enabled', true, $second['ShowRoomName'] ?? null);
assertSameValue('individual_color_kept', 0x445566, $second['TileBackgroundColor'] ?? null);
assertSameValue('individual_percent_kept', 42.0, (float)($second['MenuTransparency'] ?? 0));
assertSameValue('info_center_not_moved', 0, count(listOf($second, 'InfoItems')));

// 3) Auswahlliste im Formular: Name und Pfad
ob_start();
$options = json_decode($mrt->RefreshImportInstances(), true);
ob_end_clean();
assertSameValue('instance_list_built', [['caption' => 'Bad (Kacheln)', 'value' => $roomTileId]], $options);

// 4) Fehlerfälle
$rejected = 0;
foreach ([0, $light] as $wrongId) {
    try {
        ob_start();
        $mrt->ImportRoom((int)$wrongId, false, true);
        ob_end_clean();
    } catch (Throwable $e) {
        ob_end_clean();
        $rejected++;
    }
}
assertSameValue('invalid_instances_rejected', 2, $rejected);
