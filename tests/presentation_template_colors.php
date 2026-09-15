<?php

declare(strict_types=1);

/**
 * Farben aus einer Darstellung mit Vorlage.
 *
 * In der eigenen Darstellung einer Variable steht bei einer Vorlage nur der
 * Verweis (TEMPLATE + PRESENTATION). Die Werte der Vorlage - z.B. Intervalle mit
 * Farben - liefert erst IPS_GetVariablePresentation. Die Stubs lösen Vorlagen
 * nicht auf, deshalb ersetzt dieser Test IPS_GetVariablePresentation durch eine
 * Fassung, die die aufgelöste Darstellung wie Symcon zurückgibt.
 *
 * Verwendung: php tests/presentation_template_colors.php
 */

// Stub-Fassung von IPS_GetVariablePresentation abschalten, eigene verwenden
define('IPS_VERSION', 7.99);

$RESOLVED_PRESENTATIONS = [];

function IPS_GetVariablePresentation(int $VariableID)
{
    global $RESOLVED_PRESENTATIONS;
    if (isset($RESOLVED_PRESENTATIONS[$VariableID])) {
        return $RESOLVED_PRESENTATIONS[$VariableID];
    }
    return IPS\VariableManager::getVariablePresentation($VariableID);
}

require_once __DIR__ . '/stubs/autoload.php';
require_once __DIR__ . '/../libs/TileVisuLib.php';

IPS\Kernel::reset();

function assertSameValue(string $label, mixed $expected, mixed $actual): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(sprintf('%s failed: expected %s, got %s', $label, json_encode($expected), json_encode($actual)));
    }
    echo $label . "=ok\n";
}

$templateId = '{8581A425-D29B-1AF1-F1B3-583643F144EB}';
$intervals = json_encode([
    ['IntervalMinValue' => 21, 'IntervalMaxValue' => 26, 'IconActive' => true, 'IconValue' => 'temperature-three-quarters', 'ColorActive' => true, 'ColorValue' => 16738893],
    ['IntervalMinValue' => 11, 'IntervalMaxValue' => 20, 'IconActive' => true, 'IconValue' => 'temperature-half', 'ColorActive' => true, 'ColorValue' => 7846721],
]);

// Temperatur wie ein Gerät sie anlegt: Standardprofil ~Temperature, darüber eine
// eigene Werte-Darstellung, die nur auf eine Vorlage verweist
$temperature = IPS_CreateVariable(2);
IPS\VariableManager::setVariableProfile($temperature, '~Temperature');
IPS_SetVariableCustomPresentation($temperature, ['TEMPLATE' => $templateId, 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION]);
SetValue($temperature, 16.9);
$RESOLVED_PRESENTATIONS[$temperature] = [
    'PRESENTATION'     => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
    'ICON'             => 'temperature-half',
    'COLOR'            => -1,
    'SUFFIX'           => ' °C',
    'INTERVALS_ACTIVE' => true,
    'INTERVALS'        => $intervals,
];

// Wert außerhalb aller Intervalle: keine Farbe
$outside = IPS_CreateVariable(2);
IPS_SetVariableCustomPresentation($outside, ['TEMPLATE' => $templateId, 'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION]);
SetValue($outside, 40.0);
$RESOLVED_PRESENTATIONS[$outside] = $RESOLVED_PRESENTATIONS[$temperature];

echo "== Farben aus Vorlage ==\n";
assertSameValue('template_interval_color', '77BB41', TileVisuLib::getPresentationColorHex($temperature));
assertSameValue('template_interval_status_color', '77BB41', TileVisuLib::getProfileColorHex($temperature));
assertSameValue('template_interval_icon', 'temperature-half', TileVisuLib::getIconAdvanced($temperature));
assertSameValue('template_outside_intervals_no_color', '', TileVisuLib::getProfileColorHex($outside));
