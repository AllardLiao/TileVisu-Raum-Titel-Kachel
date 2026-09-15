<?php

declare(strict_types=1);

/**
 * Smoke-Test: instanziiert RoomTile und MultiRoomTile unter den SymconStubs,
 * ruft Create/ApplyChanges (implizit), GetConfigurationForm und
 * GetVisualizationTile auf und gibt einen normalisierten, deterministischen
 * Abdruck auf stdout aus (Zeitstempel in Hook-URLs werden maskiert).
 *
 * Verwendung:
 *   php tests/smoke_modules.php > tests/smoke_master.txt          # Abdruck fixieren
 *   php tests/smoke_modules.php | diff tests/smoke_master.txt -   # Regression prüfen
 */

require_once __DIR__ . '/stubs/autoload.php';

mt_srand(7);
IPS\Kernel::reset();

require_once __DIR__ . '/../RoomTile/module.php';
require_once __DIR__ . '/../MultiRoomTile/module.php';

const MODULES = [
    'RoomTile' => [
        'ModuleID'   => '{0E33BB6A-B5A7-4D25-883A-EDF0551DB5C3}',
        'ModuleName' => 'RoomTile',
        'ModuleType' => 3,
        'Class'      => 'RoomTile',
    ],
    'MultiRoomTile' => [
        'ModuleID'   => '{5D1D3B42-2B8E-4C5E-AE9A-4E8E2C00E9F4}',
        'ModuleName' => 'MultiRoomTile',
        'ModuleType' => 3,
        'Class'      => 'MultiRoomTile',
    ],
];

function normalize(string $s): string
{
    // Hook-URLs enthalten einen Cache-Buster: früher &ts=<time()>, jetzt &v=<MediaCRC|mtime>.
    $s = preg_replace('/&ts=\d+/', '&ts=TS', $s);
    return preg_replace('/&v=[^&"]*/', '&v=V', $s);
}

function payloadOf(string $tile): string
{
    if (preg_match('/<script>handleMessage\((.*)\)<\/script>$/s', $tile, $m)) {
        return $m[1];
    }
    return 'NICHT GEFUNDEN';
}

function assertSameValue(string $label, mixed $expected, mixed $actual): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(sprintf(
            '%s failed: expected %s, got %s',
            $label,
            json_encode($expected),
            json_encode($actual)
        ));
    }

    echo $label . "=ok\n";
}

$ifaces = [];
foreach (MODULES as $name => $module) {
    $id = IPS\ObjectManager::registerObject(1 /* Instance */);
    ob_start(); // LogMessage-Ausgaben der Stubs nicht in den Abdruck mischen
    IPS\InstanceManager::createInstance($id, $module);
    $iface = IPS\InstanceManager::getInstanceInterface($id);
    $ifaces[$name] = $iface;
    $form = (string)$iface->GetConfigurationForm();
    $tile = (string)$iface->GetVisualizationTile();
    $log = ob_get_clean();

    $form = normalize($form);
    $tile = normalize($tile);
    echo "== $name ==\n";
    echo 'form.len=' . strlen($form) . ' form.sha1=' . sha1($form) . "\n";
    echo 'tile.len=' . strlen($tile) . ' tile.sha1=' . sha1($tile) . "\n";
    // Der eingebettete Full-Update-Payload ist der eigentliche PHP→HTML-Kontrakt:
    echo 'payload=' . normalize(payloadOf($tile)) . "\n";
    echo 'log=' . trim(preg_replace('/\s+/', ' ', $log)) . "\n\n";
}

// ---------------------------------------------------------------------------
// Konfiguriertes Szenario: Variablen + Raum-Konfiguration, deckt die
// GetFullUpdateMessage-/buildDynamic*-/fillInfoAndButtons-Pfade ab
// ---------------------------------------------------------------------------

$light = IPS_CreateVariable(0);
SetValue($light, true);
// ENUMERATION: einzige nicht-triviale Presentation, die der
// GetValueFormatted-Stub formatieren kann
IPS_SetVariableCustomPresentation($light, [
    'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,
    'OPTIONS'      => json_encode([
        ['Value' => true, 'Caption' => 'An', 'Color' => 0x00FF00, 'IconValue' => 'Bulb'],
        ['Value' => false, 'Caption' => 'Aus', 'Color' => 0x333333, 'IconValue' => 'Bulb'],
    ]),
]);
IPS_SetName($light, 'Licht');

$dim = IPS_CreateVariable(1);
SetValue($dim, 75);
IPS_SetName($dim, 'Dimmer');

$temp = IPS_CreateVariable(2);
SetValue($temp, 21.5);
IPS_SetName($temp, 'Temperatur');

$infoItems = [[
    'Id' => 'i1', 'Area' => 'left', 'VariableId' => $temp,
    'ShowName' => true, 'ShowIcon' => true, 'ShowValue' => true,
    'UseVarColor' => false, 'AltName' => '',
]];
$menuItems = [[
    'Id' => 'm1', 'VariableId' => $light, 'OpenObjectId' => 0, 'SceneControlId' => 0,
    'ShowName' => true, 'ShowIcon' => true, 'ShowValue' => false,
    'UseVarColor' => true, 'ColorTrue' => -1, 'ColorFalse' => -1,
    'AltName' => '', 'Width' => 100, 'FullWidth' => false,
]];

ob_start();
$rt = $ifaces['RoomTile'];
$rt->SetProperty('RoomName', 'Wohnzimmer');
$rt->SetProperty('LightStatus', $light);
$rt->SetProperty('DimValue', $dim);
$rt->SetProperty('Switch1', $light);
$rt->SetProperty('InfoLeft', $temp);
$rt->SetProperty('InfoItems', json_encode($infoItems));
$rt->SetProperty('MenuItems', json_encode($menuItems));
$rt->ApplyChanges();

$mr = $ifaces['MultiRoomTile'];
$multiRooms = [[
    'RoomName'  => 'Küche',
    'Switch1'   => $light,
    'InfoItems' => $infoItems,
    'MenuItems' => $menuItems,
]];
$mr->SetProperty('Rooms', json_encode($multiRooms));
$mr->ApplyChanges();
ob_end_clean();

foreach (['RoomTile' => $rt, 'MultiRoomTile' => $mr] as $name => $iface) {
    ob_start();
    $tile = (string)$iface->GetVisualizationTile();
    ob_end_clean();
    echo "== $name (konfiguriert) ==\n";
    echo 'payload=' . normalize(payloadOf($tile)) . "\n\n";
}

// ---------------------------------------------------------------------------
// Link-Object Aktion: Nur konfigurierte Link/Ziel-Paare dürfen den Link
// umhängen.
// ---------------------------------------------------------------------------

$roomLink = IPS_CreateLink();
$otherRoomLink = IPS_CreateLink();
ob_start();
$rt->SetProperty('TargetLinkId', $roomLink);
$rt->SetProperty('TargetLinkValue', $temp);
$rt->ApplyChanges();
$rt->RequestAction('setlink', json_encode(['linkId' => $roomLink, 'targetId' => $temp]));
$rt->RequestAction('setlink', json_encode(['linkId' => $roomLink, 'targetId' => $dim]));
$rt->RequestAction('setlink', json_encode(['linkId' => $otherRoomLink, 'targetId' => $temp]));
ob_end_clean();

$multiLink = IPS_CreateLink();
$otherMultiLink = IPS_CreateLink();
$multiRooms[0]['TargetLinkId'] = $multiLink;
$multiRooms[0]['TargetLinkValue'] = $dim;
ob_start();
$mr->SetProperty('Rooms', json_encode($multiRooms));
$mr->ApplyChanges();
$mr->RequestAction('setlink', json_encode(['linkId' => $multiLink, 'targetId' => $dim]));
$mr->RequestAction('setlink', json_encode(['linkId' => $multiLink, 'targetId' => $temp]));
$mr->RequestAction('setlink', json_encode(['linkId' => $otherMultiLink, 'targetId' => $dim]));
ob_end_clean();

echo "== Link-Object Aktion ==\n";
assertSameValue('roomtile_configured_pair', $temp, IPS_GetLink($roomLink)['TargetID']);
assertSameValue('roomtile_rejects_foreign_link', 0, IPS_GetLink($otherRoomLink)['TargetID']);
assertSameValue('multiroom_configured_pair', $dim, IPS_GetLink($multiLink)['TargetID']);
assertSameValue('multiroom_rejects_foreign_link', 0, IPS_GetLink($otherMultiLink)['TargetID']);

// ---------------------------------------------------------------------------
// Schalter-Buttons auf Zahlenvariablen: mit Wertebereich umschalten
// (Minimum <-> Maximum), ohne Wertebereich den übergebenen Wert setzen.
// ---------------------------------------------------------------------------

$setValueScript = IPS_CreateScript(0);
IPS_SetScriptContent($setValueScript, 'SetValue($_IPS[\'VARIABLE\'], $_IPS[\'VALUE\']);');

// Dimmer 0-100 % über ein Profil
IPS_CreateVariableProfile('TileVisuTest.Dimmer', 1);
IPS_SetVariableProfileValues('TileVisuTest.Dimmer', 0, 100, 1);
$dimmer = IPS_CreateVariable(1);
IPS_SetVariableCustomProfile($dimmer, 'TileVisuTest.Dimmer');
IPS_SetVariableCustomAction($dimmer, $setValueScript);
SetValue($dimmer, 40);

// Dimmer als Float mit Schieberegler-Darstellung 0-1
$floatDimmer = IPS_CreateVariable(2);
IPS_SetVariableCustomPresentation($floatDimmer, ['PRESENTATION' => VARIABLE_PRESENTATION_SLIDER, 'MIN' => 0, 'MAX' => 1]);
IPS_SetVariableCustomAction($floatDimmer, $setValueScript);
SetValue($floatDimmer, 0.0);

// Auslöser ohne Wertebereich
$trigger = IPS_CreateVariable(1);
IPS_SetVariableCustomAction($trigger, $setValueScript);
SetValue($trigger, 0);

$numericMenuItems = [];
foreach (['md' => $dimmer, 'mf' => $floatDimmer, 'mt' => $trigger] as $itemId => $varId) {
    $numericMenuItems[] = [
        'Id' => $itemId, 'VariableId' => $varId, 'OpenObjectId' => 0, 'SceneControlId' => 0,
        'ShowName' => true, 'ShowIcon' => true, 'ShowValue' => false,
        'UseVarColor' => false, 'ColorTrue' => -1, 'ColorFalse' => -1,
        'AltName' => '', 'Width' => 100, 'FullWidth' => false,
    ];
}

ob_start();
$rt->SetProperty('MenuItems', json_encode($numericMenuItems));
$rt->SetProperty('Switch1', $dimmer);
$rt->ApplyChanges();
$rt->RequestAction('menuitem:md', 1);
$dimmerAfterFirstClick = GetValue($dimmer);
$rt->RequestAction('menuitem:md', 1);
$dimmerAfterSecondClick = GetValue($dimmer);
$rt->RequestAction('menuitem:mf', 1);
$floatAfterClick = GetValue($floatDimmer);
$rt->RequestAction('menuitem:mt', 1);
$triggerAfterClick = GetValue($trigger);
$rt->RequestAction('room:0:Switch1', 1);
$roomSwitchAfterClick = GetValue($dimmer);

$mr->SetProperty('Rooms', json_encode([['RoomName' => 'Bad', 'Switch1' => $dimmer, 'MenuItems' => $numericMenuItems]]));
$mr->ApplyChanges();
$mr->RequestAction('menuitem:md', 1);
$multiDimmerAfterClick = GetValue($dimmer);
$mr->RequestAction('room:0:Switch1', 1);
$multiRoomSwitchAfterClick = GetValue($dimmer);
ob_end_clean();

echo "\n== Zahlen-Schalter ==\n";
assertSameValue('roomtile_dimmer_switches_off', 0, $dimmerAfterFirstClick);
assertSameValue('roomtile_dimmer_switches_on_to_max', 100, $dimmerAfterSecondClick);
assertSameValue('roomtile_float_slider_switches_on_to_max', 1.0, $floatAfterClick);
assertSameValue('roomtile_trigger_keeps_sent_value', 1, $triggerAfterClick);
assertSameValue('roomtile_room_switch_toggles_dimmer', 0, $roomSwitchAfterClick);
assertSameValue('multiroom_dimmer_switches_on_to_max', 100, $multiDimmerAfterClick);
assertSameValue('multiroom_room_switch_toggles_dimmer', 0, $multiRoomSwitchAfterClick);
