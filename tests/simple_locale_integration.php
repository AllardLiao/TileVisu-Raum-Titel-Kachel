<?php

declare(strict_types=1);

/**
 * Integrationstest für die optionale Anbindung an Simple Locale.
 *
 * Simple Locale wird durch ein minimales Ersatzmodul nachgebildet, das
 * SLOC_IsResponsibleFor() und SLOC_TranslateExternalTexts() bereitstellt.
 * Geprüft wird, dass Raumnamen und alternative Namen in der Kachel übersetzt
 * ankommen, ein Sprachwechsel (IM_CHANGESETTINGS) neu rendert, bei mehreren
 * zuständigen Instanzen nicht übersetzt wird und die Konfiguration selbst
 * unverändert bleibt.
 *
 * Verwendung:
 *   php tests/simple_locale_integration.php
 */

require_once __DIR__ . '/stubs/autoload.php';

mt_srand(7);
IPS\Kernel::reset();

require_once __DIR__ . '/../RoomTile/module.php';
require_once __DIR__ . '/../MultiRoomTile/module.php';

class FakeSimpleLocale extends IPSModuleStrict
{
    public static array $responsibleFor = [];
    public static array $translations = [];

    public function IsResponsibleFor(int $ObjectID): bool
    {
        return in_array($ObjectID, self::$responsibleFor[$this->InstanceID] ?? [], true);
    }

    public function TranslateExternalTexts(array $Texts, string $SourceLanguage): array
    {
        return array_map(static fn ($text) => self::$translations[$text] ?? $text, $Texts);
    }
}

function SLOC_IsResponsibleFor(int $InstanceID, int $ObjectID): bool
{
    return IPS\InstanceManager::getInstanceInterface($InstanceID)->IsResponsibleFor($ObjectID);
}

// Wie in Symcon: generierte Modulfunktionen haben keine optionalen Parameter.
function SLOC_TranslateExternalTexts(int $InstanceID, array $Texts, string $SourceLanguage): array
{
    return IPS\InstanceManager::getInstanceInterface($InstanceID)->TranslateExternalTexts($Texts, $SourceLanguage);
}

function assertSameValue(string $label, mixed $expected, mixed $actual): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, sprintf("%s failed: expected %s, got %s\n", $label, json_encode($expected), json_encode($actual)));
        exit(1);
    }
    echo $label . "=ok\n";
}

function createInstance(array $module): int
{
    $id = IPS\ObjectManager::registerObject(1 /* Instance */);
    ob_start();
    IPS\InstanceManager::createInstance($id, $module);
    ob_end_clean();

    return $id;
}

function tilePayload(object $iface): array
{
    ob_start();
    $tile = (string)$iface->GetVisualizationTile();
    ob_end_clean();
    preg_match('/<script>handleMessage\((.*)\)<\/script>$/s', $tile, $m);

    return json_decode($m[1] ?? '{}', true) ?: [];
}

function postedUpdates(object $iface): array
{
    $moduleProp = new ReflectionProperty(IPSModuleStrict::class, 'module');
    $moduleProp->setAccessible(true);
    $inner = $moduleProp->getValue($iface);
    $messages = new ReflectionProperty(IPSModule::class, 'messages');
    $messages->setAccessible(true);
    $updates = array_values(array_filter($messages->getValue($inner), static fn ($m) => ($m['Message'] ?? 0) === 10541));
    $messages->setValue($inner, []);

    return $updates;
}

const SIMPLE_LOCALE = ['ModuleID' => '{1A2E3892-FE35-9E4E-A3A8-B983B0C41F64}', 'ModuleName' => 'SimpleLocale', 'ModuleType' => 3, 'Class' => 'FakeSimpleLocale'];

$temp = IPS_CreateVariable(2);
IPS_SetName($temp, 'Temperatur');
$infoItems = [['Id' => 'i1', 'Area' => 'left', 'VariableId' => $temp, 'ShowName' => true, 'ShowIcon' => false, 'ShowValue' => false, 'UseVarColor' => false, 'AltName' => 'Innen']];

$apartmentId = createInstance(SIMPLE_LOCALE);
$adminId = createInstance(SIMPLE_LOCALE);
FakeSimpleLocale::$translations = ['Wohnzimmer' => 'Living room', 'Innen' => 'Inside'];

// ---------------------------------------------------------------------------
// RoomTile
// ---------------------------------------------------------------------------

$rtId = createInstance(['ModuleID' => '{0E33BB6A-B5A7-4D25-883A-EDF0551DB5C3}', 'ModuleName' => 'RoomTile', 'ModuleType' => 3, 'Class' => 'RoomTile']);
$rt = IPS\InstanceManager::getInstanceInterface($rtId);
FakeSimpleLocale::$responsibleFor = [$apartmentId => [$rtId]];
ob_start();
$rt->SetProperty('RoomName', 'Wohnzimmer');
$rt->SetProperty('InfoLeft', $temp);
$rt->SetProperty('InfoLeftAltName', 'Innen');
$rt->SetProperty('InfoItems', json_encode($infoItems));
$rt->ApplyChanges();
ob_end_clean();

echo "== RoomTile ==\n";
$room = tilePayload($rt)['rooms'][0] ?? [];
assertSameValue('roomtile_roomname_translated', 'Living room', $room['roomname'] ?? null);
assertSameValue('roomtile_fixed_altname_translated', 'Inside', $room['infoleftaltname'] ?? null);
assertSameValue('roomtile_info_item_name_translated', 'Inside', $room['infoitem-i1name'] ?? null);
assertSameValue('roomtile_configuration_untouched', 'Wohnzimmer', json_decode(IPS_GetConfiguration($rtId), true)['RoomName']);

// Sprachwechsel: Simple Locale meldet IM_CHANGESETTINGS, die Kachel rendert neu
postedUpdates($rt);
FakeSimpleLocale::$translations = ['Wohnzimmer' => 'Salón', 'Innen' => 'Dentro'];
ob_start();
$rt->MessageSink(time(), $apartmentId, IM_CHANGESETTINGS, []);
ob_end_clean();
$updates = postedUpdates($rt);
assertSameValue('roomtile_language_switch_rerenders', 1, count($updates));
assertSameValue('roomtile_language_switch_new_roomname', 'Salón', json_decode($updates[0]['Data'][0], true)['rooms'][0]['roomname'] ?? null);

// Unveränderte Übersetzungen: kein unnötiges Neurendern
ob_start();
$rt->MessageSink(time(), $apartmentId, IM_CHANGESETTINGS, []);
ob_end_clean();
assertSameValue('roomtile_unchanged_labels_no_rerender', 0, count(postedUpdates($rt)));

// Kachel in zwei Visualisierungen: nicht raten, nicht übersetzen
FakeSimpleLocale::$responsibleFor[$adminId] = [$rtId];
ob_start();
$rt->ApplyChanges();
ob_end_clean();
assertSameValue('roomtile_ambiguous_not_translated', 'Wohnzimmer', tilePayload($rt)['rooms'][0]['roomname'] ?? null);

// ---------------------------------------------------------------------------
// MultiRoomTile
// ---------------------------------------------------------------------------

$mrId = createInstance(['ModuleID' => '{5D1D3B42-2B8E-4C5E-AE9A-4E8E2C00E9F4}', 'ModuleName' => 'MultiRoomTile', 'ModuleType' => 3, 'Class' => 'MultiRoomTile']);
$mr = IPS\InstanceManager::getInstanceInterface($mrId);
FakeSimpleLocale::$responsibleFor = [$apartmentId => [$mrId]];
FakeSimpleLocale::$translations = ['Wohnzimmer' => 'Living room', 'Innen' => 'Inside'];
ob_start();
$mr->SetProperty('Rooms', json_encode([['RoomName' => 'Wohnzimmer', 'InfoItems' => $infoItems]]));
$mr->ApplyChanges();
ob_end_clean();

echo "\n== MultiRoomTile ==\n";
$room = tilePayload($mr)['rooms'][0] ?? [];
assertSameValue('multiroom_roomname_translated', 'Living room', $room['roomname'] ?? null);
assertSameValue('multiroom_info_item_name_translated', 'Inside', $room['infoitem-i1name'] ?? null);
assertSameValue('multiroom_configuration_untouched', 'Wohnzimmer', json_decode(json_decode(IPS_GetConfiguration($mrId), true)['Rooms'], true)[0]['RoomName']);
