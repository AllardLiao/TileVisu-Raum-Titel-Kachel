<?php

declare(strict_types=1);

/**
 * Export eines Zimmers aus der MultiRoomTile in eine eigenständige RoomTile.
 *
 * Prüft die Zuordnung der Felder und die Auflösung der "globalen" Werte:
 * -1 bei Farben, 0 oder kleiner bei Größen, negativ bei Prozentwerten.
 *
 * Verwendung: php tests/room_export.php
 */

require_once __DIR__ . '/stubs/autoload.php';

mt_srand(11);
IPS\Kernel::reset();
IPS\ModuleLoader::loadLibrary(__DIR__ . '/../library.json');

const MULTI_ROOM_TILE = '{5D1D3B42-2B8E-4C5E-AE9A-4E8E2C00E9F4}';

function assertSameValue(string $label, mixed $expected, mixed $actual): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(sprintf('%s failed: expected %s, got %s', $label, json_encode($expected), json_encode($actual)));
    }
    echo $label . "=ok\n";
}

ob_start();
$mrtId = IPS_CreateInstance(MULTI_ROOM_TILE);
$mrt = IPS\InstanceManager::getInstanceInterface($mrtId);

$variable = IPS_CreateVariable(0);
IPS_SetName($variable, 'Licht');

$menuItems = [[
    'Id' => 'm1', 'VariableId' => $variable, 'OpenObjectId' => 0, 'SceneControlId' => 0,
    'ShowName' => true, 'ShowIcon' => true, 'ShowValue' => false,
    'UseVarColor' => false, 'ColorTrue' => -1, 'ColorFalse' => -1,
    'AltName' => '', 'Width' => 100, 'FullWidth' => false,
]];

$rooms = [[
    'RoomName'            => 'Bad',
    'TargetCategoryId'    => 12345,
    'Switch1'             => $variable,
    'MenuItems'           => $menuItems,
    'InfoItems'           => [],
    // "globale Einstellung verwenden"
    'MenuFontSize'        => 0,
    'TileBackgroundColor' => -1,
    'MenuTransparency'    => -1.0,
    // eigene Werte des Zimmers
    'InfoFontSize'        => 18,
    'RoomNameFontColor'   => 0xAABBCC,
]];

// Globale Einstellungen der Multi-Kachel
IPS_SetProperty($mrtId, 'Default_MenuFontSize', 22);
IPS_SetProperty($mrtId, 'Default_TileBackgroundColor', 0x123456);
IPS_SetProperty($mrtId, 'Default_MenuTransparency', 55.0);
IPS_SetProperty($mrtId, 'Default_InfoHeight', 31);
IPS_SetProperty($mrtId, 'BorderRadius', 17);
IPS_SetProperty($mrtId, 'Rooms', json_encode($rooms));
IPS_ApplyChanges($mrtId);

$targetId = $mrt->ExportRoom(0);
ob_end_clean();

echo "== Zimmer-Export ==\n";
assertSameValue('instance_created', true, IPS_InstanceExists($targetId));
assertSameValue('created_under_root', 0, IPS_GetObject($targetId)['ParentID']);
assertSameValue('instance_named_after_room', 'Bad', IPS_GetName($targetId));
assertSameValue('room_name_copied', 'Bad', IPS_GetProperty($targetId, 'RoomName'));
assertSameValue('switch_copied', $variable, IPS_GetProperty($targetId, 'Switch1'));
assertSameValue('menu_items_copied_as_json', json_encode($menuItems), IPS_GetProperty($targetId, 'MenuItems'));

// -1 / 0 / negativ werden durch die globalen Werte ersetzt
assertSameValue('size_uses_global', 22, IPS_GetProperty($targetId, 'MenuFontSize'));
assertSameValue('color_uses_global', 0x123456, IPS_GetProperty($targetId, 'TileBackgroundColor'));
assertSameValue('percent_uses_global', 55.0, IPS_GetProperty($targetId, 'MenuTransparency'));

// eigene Werte des Zimmers bleiben erhalten
assertSameValue('own_size_kept', 18, IPS_GetProperty($targetId, 'InfoFontSize'));
assertSameValue('own_color_kept', 0xAABBCC, IPS_GetProperty($targetId, 'RoomNameFontColor'));

// globale Einstellungen der Kachel werden mitgenommen
assertSameValue('global_default_copied', 31, IPS_GetProperty($targetId, 'Default_InfoHeight'));
assertSameValue('global_setting_copied', 17, IPS_GetProperty($targetId, 'BorderRadius'));

// Zwei Exporte desselben Zimmers ergeben zwei Instanzen
ob_start();
$secondId = $mrt->ExportRoom(0);
ob_end_clean();
assertSameValue('export_creates_new_instance', true, $secondId !== $targetId && IPS_InstanceExists($secondId));

$failed = false;
try {
    $mrt->ExportRoom(5);
} catch (Throwable $e) {
    $failed = true;
}
assertSameValue('unknown_index_rejected', true, $failed);

// Die Auswahl im Formular kennt die vorhandenen Zimmer
$form = json_decode((string)IPS_GetConfigurationForm($mrtId), true);
$options = null;
foreach ($form['elements'] as $element) {
    if (($element['caption'] ?? '') !== 'Export') continue;
    foreach ($element['items'] as $item) {
        if (($item['name'] ?? '') === 'ExportRoomIndex') {
            $options = $item['options'];
        }
    }
}
assertSameValue('form_lists_rooms', [['caption' => 'Bad', 'value' => 0]], $options);
