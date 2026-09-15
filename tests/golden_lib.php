<?php

declare(strict_types=1);

/**
 * Golden-Master-Test für libs/TileVisuLib.php.
 *
 * Basiert auf den offiziellen SymconStubs (https://github.com/symcon/SymconStubs,
 * Snapshot f5a55625da66d7ac152a6400ad978bae664cc2be, gevendort unter tests/stubs/).
 *
 * Führt die public statics der Lib über eine Fixture-Matrix aus und gibt
 * deterministisches JSON auf stdout aus (Ergebnisse über stabile Labels gekeyt,
 * Objekt-IDs sind durch mt_srand fixiert). Verwendung:
 *   php tests/golden_lib.php > tests/golden_master.json          # Master erzeugen
 *   php tests/golden_lib.php | diff tests/golden_master.json -   # Regression prüfen
 *
 * Nicht abgedeckt: TileVisuLib::migrateV2() (braucht eine voll geladene
 * Modul-Instanz inkl. ApplyChanges-Kette) und der IPS_GetVariableVisualization-
 * Fallback in getIconAdvanced (Funktion existiert in den Stubs nicht; der
 * function_exists-Guard überspringt den Zweig vor wie nach dem Refactoring).
 */

require_once __DIR__ . '/stubs/autoload.php';
require_once __DIR__ . '/../libs/TileVisuLib.php';

mt_srand(42); // ObjectManager shuffled die ID-Vergabe → deterministisch machen
IPS\Kernel::reset();

$VARS = [];

function makeVar(string $label, int $type, $value): int
{
    global $VARS;
    $id = IPS_CreateVariable($type);
    SetValue($id, $value);
    $VARS[$label] = $id;
    return $id;
}

function setPresentationPool(array $presentations): void
{
    // PresentationPool hat keinen öffentlichen Setter
    $rp = new ReflectionProperty(IPS\PresentationPool::class, 'presentations');
    $rp->setValue(null, $presentations);
}

// ---------------------------------------------------------------------------
// Profile
// ---------------------------------------------------------------------------

IPS_CreateVariableProfile('TV.Test.Int', 1);
IPS_SetVariableProfileIcon('TV.Test.Int', 'Gauge');
IPS_SetVariableProfileAssociation('TV.Test.Int', 1, 'Eins', 'ArrowUp', 0x111111);
IPS_SetVariableProfileAssociation('TV.Test.Int', 2, 'Zwei', '', 0x123123);
IPS_SetVariableProfileAssociation('TV.Test.Int', 3, 'Drei', 'Cross', -1);

// ---------------------------------------------------------------------------
// Templates & Presentations (für TEMPLATE-/PRESENTATION-Auflösung)
// ---------------------------------------------------------------------------

IPS\TemplateManager::createTemplateEx([
    'TemplateID'     => '{7F9F0001-0000-4000-8000-000000000001}',
    'PresentationID' => '',
    'DisplayName'    => 'tpl-custom',
    'Values'         => ['OPTIONS' => json_encode([['Value' => 3, 'Color' => 0x224466]])],
    'IsReadOnly'     => false,
], false);
IPS\TemplateManager::createTemplateEx([
    'TemplateID'     => '{7F9F0002-0000-4000-8000-000000000002}',
    'PresentationID' => '',
    'DisplayName'    => 'tpl-std',
    'Values'         => ['COLOR_TRUE' => '1193046'], // 0x123456 als numerischer String
    'IsReadOnly'     => false,
], false);

setPresentationPool([
    '{AAAA0001-0000-4000-8000-000000000001}' => [
        'presentationParameters' => [
            'OPTIONS'     => json_encode([['Value' => 9, 'Color' => 0x334455]]),
            'COLOR_TRUE'  => 0x00AA00,
            'COLOR_FALSE' => 0xAA0000,
        ],
    ],
    '{AAAA0002-0000-4000-8000-000000000002}' => [
        'presentationParameters' => ['COLOR' => 0x456789],
    ],
    '{AAAA0003-0000-4000-8000-000000000003}' => [
        'presentationParameters' => ['COLOR' => '4548489'], // String → is_int schlägt fehl
    ],
]);

// ---------------------------------------------------------------------------
// Farb-Fixtures (getPresentationColorHex / getProfileColorHex)
// ---------------------------------------------------------------------------

$assocBool = ['ASSOCIATIONS' => [
    ['Value' => true, 'Color' => 0xFF0000],
    ['Value' => false, 'Color' => 0x00FF00],
]];
IPS_SetVariableCustomPresentation(makeVar('custom_assoc_bool_true', 0, true), $assocBool);
IPS_SetVariableCustomPresentation(makeVar('custom_assoc_bool_false', 0, false), $assocBool);

$ctf = ['COLOR_TRUE' => 0x112233, 'COLOR_FALSE' => 0x445566];
IPS_SetVariableCustomPresentation(makeVar('custom_colortrue_bool_true', 0, true), $ctf);
IPS_SetVariableCustomPresentation(makeVar('custom_colorfalse_bool_false', 0, false), $ctf);

IPS_SetVariableCustomPresentation(makeVar('custom_usecolorfalse_off', 0, false),
    ['USE_COLOR_FALSE' => false, 'COLOR_FALSE' => 0x445566, 'COLOR' => 0x777777]);

IPS_SetVariableCustomPresentation(makeVar('custom_options_string_int', 1, 1),
    ['OPTIONS' => json_encode([
        ['Value' => 0, 'Color' => 0x000001],
        ['Value' => 1, 'Color' => 123456],
    ])]);

IPS_SetVariableCustomPresentation(makeVar('custom_options_coloractive_false', 1, 2),
    ['OPTIONS' => [['Value' => 2, 'Color' => 0x999999, 'ColorActive' => false]]]);

IPS_SetVariableCustomPresentation(makeVar('custom_assoc_string', 3, 'abc'),
    ['Associations' => [['Value' => 'abc', 'Color' => 0xABCDEF]]]);

// Mode-Split: Color als numerischer String — CustomPresentation (is_numeric) vs.
// VariablePresentation (is_int). Beobachtbares Verhalten, muss erhalten bleiben.
IPS_SetVariableCustomPresentation(makeVar('custom_assoc_color_numeric_string', 1, 5),
    ['ASSOCIATIONS' => [['Value' => 5, 'Color' => '255']]]);
IPS\VariableManager::setVariablePresentation(makeVar('std_assoc_color_numeric_string', 1, 5),
    ['ASSOCIATIONS' => [['Value' => 5, 'Color' => '255']]]);
IPS\VariableManager::setVariablePresentation(makeVar('std_assoc_color_int', 1, 5),
    ['ASSOCIATIONS' => [['Value' => 5, 'Color' => 255]]]);

IPS_SetVariableCustomPresentation(makeVar('custom_template', 1, 3),
    ['TEMPLATE' => '{7F9F0001-0000-4000-8000-000000000001}']);

IPS_SetVariableCustomPresentation(makeVar('custom_presentation_guid_bool', 0, true),
    ['PRESENTATION' => 'AAAA0001-0000-4000-8000-000000000001']); // ohne Klammern → Lib ergänzt sie

IPS\VariableManager::setVariablePresentation(makeVar('std_template_colortrue_string', 0, true),
    ['TEMPLATE' => '{7F9F0002-0000-4000-8000-000000000002}']);
IPS\VariableManager::setVariablePresentation(makeVar('std_presentation_guid_color_int', 1, 0),
    ['PRESENTATION' => '{AAAA0002-0000-4000-8000-000000000002}']);
IPS\VariableManager::setVariablePresentation(makeVar('std_presentation_guid_color_string', 1, 0),
    ['PRESENTATION' => '{AAAA0003-0000-4000-8000-000000000003}']);

// Klassisches Profil (setVariableCustomProfile erzeugt zusätzlich die
// Legacy-Presentation — wie in echtem IPS 7/8)
IPS_SetVariableCustomProfile(makeVar('profile_assoc_color', 1, 2), 'TV.Test.Int');
IPS_SetVariableCustomProfile(makeVar('profile_assoc_color_minus1', 1, 3), 'TV.Test.Int');

IPS_SetVariableCustomPresentation(makeVar('custom_color_minus1', 1, 0), ['COLOR' => -1]);

// ---------------------------------------------------------------------------
// Icon-Fixtures (getIcon / getIconAdvanced)
// ---------------------------------------------------------------------------

IPS_SetIcon(makeVar('obj_icon', 0, true), 'Bulb');

IPS_SetVariableCustomPresentation(makeVar('custom_icon_direct', 1, 0), ['ICON' => 'Light']);

IPS_SetVariableCustomPresentation(makeVar('custom_icontrue_usefalse_off', 0, false),
    ['ICON_TRUE' => 'Ok', 'ICON_FALSE' => 'Cross', 'USE_ICON_FALSE' => false]);

$legacyIconVar = makeVar('adv_legacy_guid_profile_assoc', 1, 1);
IPS_SetVariableCustomProfile($legacyIconVar, 'TV.Test.Int');
IPS_SetVariableCustomPresentation($legacyIconVar,
    ['PRESENTATION' => VARIABLE_PRESENTATION_LEGACY, 'PROFILE' => 'TV.Test.Int', 'ICON' => 'ShouldBeIgnored']);

IPS_SetVariableCustomPresentation(makeVar('adv_intervals_float', 2, 42.5),
    ['INTERVALS' => json_encode([
        ['IntervalMinValue' => 0, 'IntervalMaxValue' => 20, 'IconActive' => true, 'IconValue' => 'BatteryEmpty'],
        ['IntervalMinValue' => 21, 'IntervalMaxValue' => 60, 'IconActive' => true, 'IconValue' => 'BatteryHalf'],
    ])]);

IPS_SetVariableCustomPresentation(makeVar('adv_options_range', 1, 15),
    ['OPTIONS' => [
        ['Min' => 0, 'Max' => 10, 'IconValue' => 'ArrowDown'],
        ['Min' => 11, 'Max' => 20, 'IconValue' => 'ArrowUp'],
    ]]);

// Werte-Darstellung mit allgemeinem Icon und Intervallen (z.B. Helligkeit):
// Das Intervall zum Wert hat Vorrang, außerhalb gilt das allgemeine Icon.
$luxPresentation = [
    'ICON' => 'Sun', 'COLOR' => -1, 'INTERVALS_ACTIVE' => true,
    'INTERVALS' => json_encode([
        ['IntervalMinValue' => 0, 'IntervalMaxValue' => 50, 'IconActive' => true, 'IconValue' => 'moon', 'ColorActive' => true, 'ColorValue' => 0],
        ['IntervalMinValue' => 751, 'IntervalMaxValue' => 9999, 'IconActive' => true, 'IconValue' => 'cloud-sun', 'ColorActive' => true, 'ColorValue' => 16185239],
        ['IntervalMinValue' => 10000, 'IntervalMaxValue' => 200000, 'IconActive' => false, 'IconValue' => 'sun-bright', 'ColorActive' => false, 'ColorValue' => 16514816],
    ]),
];
IPS_SetVariableCustomPresentation(makeVar('adv_intervals_over_icon', 1, 800), $luxPresentation);
IPS_SetVariableCustomPresentation(makeVar('adv_intervals_dark_black', 1, 20), $luxPresentation);
IPS_SetVariableCustomPresentation(makeVar('adv_intervals_inactive_fallback', 1, 20000), $luxPresentation);
IPS_SetVariableCustomPresentation(makeVar('adv_intervals_no_match', 1, 500), $luxPresentation);
IPS_SetVariableCustomPresentation(makeVar('adv_intervals_disabled', 1, 800), ['INTERVALS_ACTIVE' => false] + $luxPresentation);

// Bool-Werte-Darstellung mit allgemeinem Icon und Optionen: Option hat Vorrang
IPS_SetVariableCustomPresentation(makeVar('adv_options_over_icon', 0, true),
    ['ICON' => 'Motion', 'OPTIONS' => json_encode([
        ['Value' => false, 'IconValue' => 'person-circle-xmark'],
        ['Value' => true, 'IconValue' => 'person-walking'],
    ])]);

IPS_SetVariableCustomProfile(makeVar('adv_profile_icon_fallback', 1, 99), 'TV.Test.Int');
IPS_SetVariableCustomProfile(makeVar('profile_assoc_icon', 1, 1), 'TV.Test.Int');

// ---------------------------------------------------------------------------
// Associations- / Hidden-Fixtures
// ---------------------------------------------------------------------------

IPS_SetVariableCustomPresentation(makeVar('assoc_custom', 1, 0),
    ['ASSOCIATIONS' => [
        ['Value' => 0, 'Name' => 'Aus', 'Icon' => 'Power', 'Color' => 0x808080],
        ['Value' => 1, 'Name' => 'An', 'Icon' => '', 'Color' => -1],
    ]]);

IPS_SetVariableCustomPresentation(makeVar('assoc_options', 1, 0),
    ['OPTIONS' => json_encode([
        ['Value' => 0, 'Caption' => 'Null', 'IconValue' => 'Hollow', 'Color' => 0x010203],
        ['Value' => 1, 'Caption' => 'Eins', 'Color' => -1],
    ])]);

IPS_SetVariableCustomProfile(makeVar('assoc_profile', 1, 0), 'TV.Test.Int');

IPS_SetHidden(makeVar('hidden_true', 0, false), true);
makeVar('hidden_false', 0, false);

// ---------------------------------------------------------------------------
// Testlauf
// ---------------------------------------------------------------------------

const MISSING_ID = 99999;

$result = [];

$colorLabels = ['custom_assoc_bool_true', 'custom_assoc_bool_false', 'custom_colortrue_bool_true',
    'custom_colorfalse_bool_false', 'custom_usecolorfalse_off', 'custom_options_string_int',
    'custom_options_coloractive_false', 'custom_assoc_string', 'custom_assoc_color_numeric_string',
    'std_assoc_color_numeric_string', 'std_assoc_color_int', 'custom_template',
    'custom_presentation_guid_bool', 'std_template_colortrue_string', 'std_presentation_guid_color_int',
    'std_presentation_guid_color_string', 'profile_assoc_color', 'profile_assoc_color_minus1',
    'custom_color_minus1', 'adv_intervals_over_icon', 'adv_intervals_dark_black',
    'adv_intervals_inactive_fallback', 'adv_intervals_no_match', 'adv_intervals_disabled'];
foreach ($colorLabels as $label) {
    $result['getPresentationColorHex'][$label] = TileVisuLib::getPresentationColorHex($VARS[$label]);
    $result['getProfileColorHex'][$label] = TileVisuLib::getProfileColorHex($VARS[$label]);
}
$result['getPresentationColorHex']['missing'] = TileVisuLib::getPresentationColorHex(MISSING_ID);
$result['getProfileColorHex']['missing'] = TileVisuLib::getProfileColorHex(MISSING_ID);

foreach (['obj_icon', 'custom_icon_direct', 'custom_icontrue_usefalse_off', 'profile_assoc_icon'] as $label) {
    $result['getIcon_varicon'][$label] = TileVisuLib::getIcon($VARS[$label], true);
    $result['getIcon'][$label] = TileVisuLib::getIcon($VARS[$label], false);
}

$advLabels = ['custom_icon_direct', 'custom_icontrue_usefalse_off', 'adv_legacy_guid_profile_assoc',
    'adv_intervals_float', 'adv_options_range', 'adv_profile_icon_fallback', 'profile_assoc_icon',
    'adv_intervals_over_icon', 'adv_intervals_dark_black', 'adv_intervals_inactive_fallback',
    'adv_intervals_no_match', 'adv_intervals_disabled', 'adv_options_over_icon'];
foreach ($advLabels as $label) {
    $result['getIconAdvanced'][$label] = TileVisuLib::getIconAdvanced($VARS[$label]);
}
$result['getIconAdvanced']['zero'] = TileVisuLib::getIconAdvanced(0);
$result['getIconAdvanced']['missing'] = TileVisuLib::getIconAdvanced(MISSING_ID);

foreach (['assoc_custom', 'assoc_options', 'assoc_profile'] as $label) {
    $result['getAssociations'][$label] = TileVisuLib::getAssociations($VARS[$label]);
}
$result['getAssociations']['missing'] = TileVisuLib::getAssociations(MISSING_ID);
$result['getIntegerAssociations']['assoc_custom'] = TileVisuLib::getIntegerAssociations($VARS['assoc_custom']);
$result['getStringAssociations']['assoc_options'] = TileVisuLib::getStringAssociations($VARS['assoc_options']);

$result['isObjectHidden'] = [
    'hidden_true'  => TileVisuLib::isObjectHidden($VARS['hidden_true']),
    'hidden_false' => TileVisuLib::isObjectHidden($VARS['hidden_false']),
    'zero'         => TileVisuLib::isObjectHidden(0),
    'missing'      => TileVisuLib::isObjectHidden(MISSING_ID),
];

$result['rgbaFromHexAlpha'] = [
    'minus1' => TileVisuLib::rgbaFromHexAlpha(-1, 0.5),
    'orange' => TileVisuLib::rgbaFromHexAlpha(0xFF8000, 0.25),
    'black'  => TileVisuLib::rgbaFromHexAlpha(0, 1.0),
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), "\n";
