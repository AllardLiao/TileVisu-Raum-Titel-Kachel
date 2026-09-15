<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/TileVisuLib.php';
require_once __DIR__ . '/../libs/TileVisuRoomHelpers.php';

class RoomTile extends IPSModuleStrict
{
    use TileVisuRoomHelpers;

    public function GetVisualizationTile(): string
    {
        return $this->renderVisualizationTile(__DIR__);
    }

    protected function ProcessHookData(): void
    {
        $this->deliverImageFromHook(true);
    }

    public function Create(): void
    {
        parent::Create();

        // Grid globale Einstellungen
        $this->RegisterPropertyInteger('BorderRadius', 10);
        // optionale globale Defaults
        $this->RegisterPropertyInteger('Default_InfoFontSize', 14);
        $this->RegisterPropertyInteger('Default_InfoFontColor', 0xFFFFFF);
        $this->RegisterPropertyInteger('Default_InfoHeight', 24);
        $this->RegisterPropertyInteger('Default_MenuFontSize', 14);
        $this->RegisterPropertyInteger('Default_MenuFontColor', 0xFFFFFF);
        $this->RegisterPropertyFloat('Default_MenuTransparency', 30.0);
        $this->RegisterPropertyInteger('Default_MenuBackgroundColor', 0x000000);
        $this->RegisterPropertyFloat('Default_InfoTopTransparency', 30.0);
        $this->RegisterPropertyInteger('Default_InfoTopBackgroundColor', 0x000000);
        $this->RegisterPropertyInteger('Default_InfoTopBorderRadius', 50);
        // Transparency bei Statusfarben (Infoleiste)
        $this->RegisterPropertyBoolean('TransparentStatusColors', true);
        $this->RegisterPropertyInteger('Default_TileBackgroundColor', 0x000000);
        $this->RegisterPropertyInteger('Default_RoomNameFontSize', 45);
        $this->RegisterPropertyFloat('Default_ImageTransparency', 70.0);
        $this->RegisterPropertyInteger('Default_ButtonHeight', 25);
        $this->RegisterPropertyInteger('Default_ButtonBorderRadius', 10);
        $this->RegisterPropertyBoolean('UseImageColorsForButtons', false);
        $this->RegisterPropertyBoolean('TransparentMenuStatusColors', true);
        $this->RegisterPropertyBoolean('GroupMenuInfoElements', false);
        $this->RegisterPropertyBoolean('InfoTopCentered', false);
        // Dynamic lists (no migration): Info items (top) and menu items
        $this->RegisterPropertyString('InfoItems', '[]');
        $this->RegisterPropertyString('MenuItems', '[]');

        
        $this->RegisterPropertyString('RoomName', '');
        $this->RegisterPropertyInteger('Target', 0);
        $this->RegisterPropertyInteger('TargetLinkId', 0);
        $this->RegisterPropertyInteger('TargetLinkValue', 0);
        $this->RegisterPropertyInteger('BackgroundImageUrl', 0);
        $this->RegisterPropertyInteger('BackgroundImage', 0);
        $this->RegisterPropertyInteger('BackgroundImage2', 0);
        // Hintergrund-Filtersteuerung
        $this->RegisterPropertyInteger('LightStatus', 0);
        $this->RegisterPropertyInteger('DimValue', 0);
        // Bildfilter-Parameter (pro Kachel) - Standardwerte
        $this->RegisterPropertyFloat('BgFilterBrightnessMin', 0.2);
        $this->RegisterPropertyFloat('BgFilterBrightnessMax', 1.0);
        $this->RegisterPropertyFloat('BgFilterContrastMin', 0.9);
        $this->RegisterPropertyFloat('BgFilterContrastMax', 1.0);
        $this->RegisterPropertyFloat('BgFilterGrayscaleMin', 0.0);
        $this->RegisterPropertyFloat('BgFilterGrayscaleMax', 1);
        
        $this->RegisterPropertyInteger('InfoLeft', 0);
        $this->RegisterPropertyBoolean('InfoLeftNameSwitch', false);
        $this->RegisterPropertyBoolean('InfoLeftIconSwitch', false);
        $this->RegisterPropertyBoolean('InfoLeftShowValue', true);
        $this->RegisterPropertyString('InfoLeftAltName', '');
        $this->RegisterPropertyInteger('InfoLeft2', 0);
        $this->RegisterPropertyBoolean('InfoLeft2NameSwitch', false);
        $this->RegisterPropertyBoolean('InfoLeft2IconSwitch', false);
        $this->RegisterPropertyBoolean('InfoLeft2ShowValue', true);
        $this->RegisterPropertyString('InfoLeft2AltName', '');
        $this->RegisterPropertyInteger('InfoRight', 0);
        $this->RegisterPropertyBoolean('InfoRightNameSwitch', false);
        $this->RegisterPropertyBoolean('InfoRightIconSwitch', false);
        $this->RegisterPropertyBoolean('InfoRightShowValue', true);
        $this->RegisterPropertyString('InfoRightAltName', '');
        $this->RegisterPropertyInteger('InfoRight2', 0);
        $this->RegisterPropertyBoolean('InfoRight2NameSwitch', false);
        $this->RegisterPropertyBoolean('InfoRight2IconSwitch', false);
        $this->RegisterPropertyBoolean('InfoRight2ShowValue', true);
        $this->RegisterPropertyString('InfoRight2AltName', '');
        $this->RegisterPropertyInteger('InfoMiddleLeft', 0);
        $this->RegisterPropertyBoolean('InfoMiddleLeftShowName', true);
        $this->RegisterPropertyBoolean('InfoMiddleLeftShowIcon', true);
        $this->RegisterPropertyBoolean('InfoMiddleLeftShowValue', true);
        $this->RegisterPropertyInteger('InfoMiddleRight', 0);
        $this->RegisterPropertyBoolean('InfoMiddleRightShowName', true);
        $this->RegisterPropertyBoolean('InfoMiddleRightShowIcon', true);
        $this->RegisterPropertyBoolean('InfoMiddleRightShowValue', true);
        $this->RegisterPropertyInteger('InfoMiddleIconSize', 56);
        $this->RegisterPropertyInteger('InfoMiddleTextSize', 20);
        $this->RegisterPropertyInteger('InfoMiddleColor', -1);
        
        $this->RegisterPropertyBoolean('MenuSwitch', true);
        $this->RegisterPropertyString('SwitchAlignment', 'left');
        $this->RegisterPropertyBoolean('SwitchDistribute', false);
        for ($i = 1; $i <= 5; $i++) {
            $this->RegisterPropertyInteger('Switch' . $i, 0);
            $this->RegisterPropertyBoolean('Switch' . $i . 'NameSwitch', false);
            $this->RegisterPropertyBoolean('Switch' . $i . 'IconSwitch', false);
            $this->RegisterPropertyBoolean('Switch' . $i . 'ShowValue', false);
            $this->RegisterPropertyString('Switch' . $i . 'AltName', '');
            $this->RegisterPropertyInteger('Switch' . $i . 'Width', 100);
            $this->RegisterPropertyBoolean('Switch' . $i . 'FullWidth', false);
        }
        $this->RegisterPropertyFloat('ImageTransparency', 70.0);
        $this->RegisterPropertyInteger('TileBackgroundColor', -1);
        $this->RegisterPropertyInteger('RoomNameFontColor', -1);
        $this->RegisterPropertyInteger('RoomNameFontSize', 50);
        $this->RegisterPropertyInteger('InfoFontSize', -1);
        $this->RegisterPropertyInteger('InfoFontColor', -1);
        $this->RegisterPropertyInteger('MenuFontSize', -1);
        $this->RegisterPropertyInteger('MenuFontColor', -1);
        $this->RegisterPropertyFloat('MenuTransparency', -1);
        $this->RegisterPropertyInteger('MenuBackgroundColor', -1);
        $this->RegisterPropertyString('Rooms', '[]');
        $this->RegisterAttributeString('VarMap', '{}');
        $this->RegisterAttributeString('HiddenMap', '{}');
        $this->RegisterAttributeString('HookToken', '');
        $this->SetVisualizationType(1);
        $this->RegisterMessage(0, IPS_KERNELMESSAGE);
        
    }

    public function GetConfigurationForm(): string
    {
        // Ensure property migration runs before form is displayed
        TileVisuLib::migrateV2($this, $this->InstanceID);

        $form = json_decode(@file_get_contents(__DIR__ . '/form.json'), true);
        if (!is_array($form)) {
            return json_encode(['elements' => []]);
        }

        $supportsSelectObject = ((float)IPS_GetKernelVersion() > 8.1);

        if (isset($form['elements']) && is_array($form['elements'])) {
            foreach ($form['elements'] as &$element) {
                if (!is_array($element)) {
                    continue;
                }
                if (($element['type'] ?? '') === 'ExpansionPanel' && ($element['caption'] ?? '') === 'RoomName' && isset($element['items']) && is_array($element['items'])) {
                    foreach ($element['items'] as &$row) {
                        if (!is_array($row) || ($row['type'] ?? '') !== 'RowLayout' || !isset($row['items']) || !is_array($row['items'])) {
                            continue;
                        }
                        foreach ($row['items'] as $ctrl) {
                            if (is_array($ctrl) && ($ctrl['type'] ?? '') === 'SelectObject' && ($ctrl['name'] ?? '') === 'Target') {
                                $row['visible'] = $supportsSelectObject;
                                break;
                            }
                        }
                    }
                    unset($row);
                }
                if (($element['type'] ?? '') === 'ExpansionPanel' && ($element['caption'] ?? '') === 'Menu bar' && isset($element['items']) && is_array($element['items'])) {
                    foreach ($element['items'] as &$item) {
                        if (!is_array($item)) {
                            continue;
                        }
                        if (($item['type'] ?? '') === 'List' && ($item['name'] ?? '') === 'MenuItems' && isset($item['columns']) && is_array($item['columns'])) {
                            foreach ($item['columns'] as &$column) {
                                if (!is_array($column)) continue;
                                $colName = (string)($column['name'] ?? '');
                                if ($colName === 'OpenObjectId' || $colName === 'SceneControlId') {
                                    $column['visible'] = $supportsSelectObject;
                                }
                            }
                            unset($column);
                        }
                    }
                    unset($item);
                }
            }
            unset($element);
        }

        return json_encode($form);
    }

    private function buildDynamicMenu(array $list, array &$outRoom): array
    {
        $items = [];
        $idx = 0;
        foreach ($list as $row) {
            if (!is_array($row)) continue;
            $id = (string)($row['Id'] ?? '');
            $varId = (int)($row['VariableId'] ?? 0);
            $openObjectId = (int)($row['OpenObjectId'] ?? 0);
            $sceneControlId = (int)($row['SceneControlId'] ?? 0);
            $showName = isset($row['ShowName']) ? (bool)$row['ShowName'] : false;
            $showIcon = isset($row['ShowIcon']) ? (bool)$row['ShowIcon'] : false;
            $showValue = isset($row['ShowValue']) ? (bool)$row['ShowValue'] : false;
            $useVarColor = isset($row['UseVarColor']) ? (bool)$row['UseVarColor'] : false;
            $colorTrue = isset($row['ColorTrue']) ? (int)$row['ColorTrue'] : -1;
            $colorFalse = isset($row['ColorFalse']) ? (int)$row['ColorFalse'] : -1;
            $altName = (string)($row['AltName'] ?? '');
            $width = (int)($row['Width'] ?? 100);
            $fullWidth = isset($row['FullWidth']) ? (bool)$row['FullWidth'] : false;
            // order follows current list order (drag&drop)
            $order = $idx++;
            if ($id === '') {
                // Stable auto id independent of order (based on content)
                $seed = 'M|' . (string)$varId . '|' . trim($altName);
                $id = 'm' . substr(sha1($seed), 0, 10);
            }
            $key = 'menuitem-' . $id;

            // Meta/values (optional, for later dynamic rendering)
            $valueFormatted = '';
            $icon = '';
            $hasVar = ($varId > 0) && @IPS_VariableExists($varId);
            $hasObject = ($openObjectId > 0) && @IPS_ObjectExists($openObjectId);
            $hasScene = ($sceneControlId > 0) && @IPS_InstanceExists($sceneControlId);
            if ($hasVar && TileVisuLib::isObjectHidden($varId)) {
                $hasVar = false;
            }
            if ($hasObject && TileVisuLib::isObjectHidden($openObjectId)) {
                $hasObject = false;
            }
            if ($hasScene && TileVisuLib::isObjectHidden($sceneControlId)) {
                $hasScene = false;
            }
            if (!$hasVar && !$hasObject && !$hasScene) {
                continue;
            }
            $typeVal = null;
            $hasValidAction = false;
            $actionType = 'none';
            $options = [];
            $rawValue = null;
            $color = '';
            $colorOn = '';
            $colorOff = '';
            $statusBgColor = '';
            $objectIcon = '';
            $objectName = '';
            if ($hasVar) {
                try {
                    $vi = @IPS_GetVariable($varId);
                    if ($vi && is_array($vi)) {
                        $typeVal = $vi['VariableType'] ?? null;
                        $actionId = 0;
                        if (isset($vi['VariableCustomAction']) && $vi['VariableCustomAction'] > 0) { $actionId = $vi['VariableCustomAction']; }
                        elseif (isset($vi['VariableAction']) && $vi['VariableAction'] > 0) { $actionId = $vi['VariableAction']; }
                        $hasValidAction = ($actionId > 0) && (@IPS_InstanceExists($actionId) || @IPS_ScriptExists($actionId));
                        if ($hasValidAction) {
                            $actionType = 'variable';
                        }
                    }
                } catch (Throwable $e) {}
                try { $valueFormatted = (string)@GetValueFormatted($varId); } catch (Throwable $e) {}
                try { $rawValue = @GetValue($varId); } catch (Throwable $e) {}
                // Associations for multi options
                try {
                    if ($typeVal === 1) { // INTEGER
                        $opts = TileVisuLib::getIntegerAssociations($varId);
                        if (is_array($opts) && !empty($opts)) { $options = $opts; }
                    } elseif ($typeVal === 3) { // STRING
                        $opts = TileVisuLib::getStringAssociations($varId);
                        if (is_array($opts) && !empty($opts)) { $options = $opts; }
                    }
                } catch (Throwable $e) {}
                if ($showIcon) { try { $icon = (string)$this->GetIconAdvanced($varId); } catch (Throwable $e) {} }
                // Colors for boolean
                if ($typeVal === 0) {
                    $btnColors = $this->GetButtonColors($varId);
                    if (!empty($btnColors['on'])) { $colorOn = $btnColors['on']; }
                    if (!empty($btnColors['off'])) { $colorOff = $btnColors['off']; }
                }
                // Status background color for no-action items - same logic as Info Badges
                if ($useVarColor) {
                    // First try GetColor (works for all variable types including Bool)
                    $c = $this->GetColor($varId);
                    if ($c !== '') {
                        $statusBgColor = '#' . $c;
                        $color = '#' . $c;
                    }
                    // Explicit override for Bool using ColorTrue/ColorFalse when set (not Transparent/-1)
                    if ($typeVal === 0 && ($colorTrue !== -1 || $colorFalse !== -1)) {
                        $isOn = false;
                        try { $isOn = (bool)@GetValue($varId); } catch (Throwable $e) {}
                        if ($isOn && $colorTrue !== -1) {
                            $statusBgColor = '#' . sprintf('%06X', $colorTrue);
                        } elseif (!$isOn && $colorFalse !== -1) {
                            $statusBgColor = '#' . sprintf('%06X', $colorFalse);
                        }
                    }
                }
            } elseif ($hasScene) {
                // SceneControl: build options from child variables Scene1..SceneN, value from ActiveScene
                $hasValidAction = true;
                $actionType = 'scenecontrol';
                $rawValue = '';
                $labelToIndex = [];
                try {
                    foreach ((array)@IPS_GetChildrenIDs($sceneControlId) as $cid) {
                        if (!@IPS_VariableExists($cid)) continue;
                        $o = @IPS_GetObject($cid);
                        $ident = (string)($o['ObjectIdent'] ?? '');
                        if (preg_match('/^Scene(\d+)$/i', $ident, $m)) {
                            $idxScene = (int)$m[1];
                            $label = (string)($o['ObjectName'] ?? $ident);
                            $iconName = (string)($o['ObjectIcon'] ?? '');
                            $opt = ['value' => $idxScene, 'label' => $label];
                            if ($iconName !== '') { $opt['icon'] = $iconName; }
                            $options[] = $opt;
                            $labelToIndex[$label] = $idxScene;
                        }
                    }
                    if (!empty($options)) {
                        usort($options, function($a,$b){ return ((int)($a['value']??0)) <=> ((int)($b['value']??0)); });
                    }
                } catch (Throwable $e) {}
                try {
                    $active = null;
                    if (function_exists('SZS_GetActiveScene')) {
                        $active = @SZS_GetActiveScene($sceneControlId);
                    } else {
                        $activeVar = 0;
                        foreach ((array)@IPS_GetChildrenIDs($sceneControlId) as $cid) {
                            if (@IPS_VariableExists($cid)) {
                                $o = @IPS_GetObject($cid);
                                $ident = (string)($o['ObjectIdent'] ?? '');
                                if ($ident === 'ActiveScene') { $activeVar = $cid; break; }
                            }
                        }
                        if ($activeVar > 0) { $active = @GetValue($activeVar); }
                    }
                    if (is_numeric($active)) { $rawValue = (int)$active; }
                    elseif (is_string($active) && isset($labelToIndex[$active])) { $rawValue = (int)$labelToIndex[$active]; }
                } catch (Throwable $e) {}
            }
            if (!$hasValidAction && $hasObject) {
                $hasValidAction = true;
                $actionType = 'object';
            }
            if ($hasObject) {
                try {
                    $obj = @IPS_GetObject($openObjectId);
                    if (is_array($obj)) {
                        $objectName = (string)$obj['ObjectName'] ?? '';
                        $objectIcon = (string)$obj['ObjectIcon'] ?? '';
                    }
                } catch (Throwable $e) {}
                // Force visibility for object buttons
                $showName = true;
                $showIcon = true;
                $showValue = false;
            }
            if ($altName !== '' || $showName || $hasObject) {
                $derivedName = $altName !== '' ? $this->TranslateLabel($altName) : ($hasVar ? (string)@IPS_GetName($varId) : ($hasObject ? $objectName : ''));
                if ($derivedName !== '') { $outRoom[$key . 'name'] = $derivedName; }
            }
            if ($valueFormatted !== '' && $showValue) { $outRoom[$key] = $valueFormatted; $outRoom[$key . 'asso'] = $valueFormatted; }
            $outRoom[$key . 'showvalue'] = $showValue;
            $outRoom[$key . 'showicon'] = $showIcon || $hasObject;
            $finalIcon = $icon;
            if (($finalIcon === '' || $finalIcon === 'Transparent') && $hasObject) {
                $finalIcon = $objectIcon;
            }
            if ($finalIcon !== '' && $finalIcon !== 'Transparent') { $outRoom[$key . 'icon'] = $finalIcon; }
            if ($hasObject) {
                $outRoom[$key . 'openobject'] = $openObjectId;
            }

            $items[] = [
                'id' => $id,
                'key' => $key,
                'order' => $order,
                'variableId' => $varId,
                'openObjectId' => $hasObject ? $openObjectId : 0,
                'type' => $typeVal,
                'hasaction' => $hasValidAction,
                'action' => $actionType,
                'width' => $width,
                'fullWidth' => $fullWidth,
                'showName' => $showName,
                'showValue' => $showValue,
                'showIcon' => $showIcon,
                'options' => $options,
                'value' => $rawValue,
                'color' => $color,
                'colorOn' => $colorOn,
                'colorOff' => $colorOff,
                'useVarColor' => $useVarColor,
                'statusBgColor' => $statusBgColor,
            ];
            // Wertebereich für Dimmer-Buttons: Streichen über den Button stellt einen Wert ein.
            // Nur mitgeschickt, wenn es einen gibt.
            if ($hasValidAction && ($typeVal === 1 || $typeVal === 2) && empty($options)) {
                $range = TileVisuLib::getNumericRange($varId);
                if ($range !== null) {
                    $items[count($items) - 1]['range'] = $range;
                }
            }
        }
        usort($items, function ($a, $b) { return ($a['order'] <=> $b['order']); });
        return $items;
    }
    
    private function NormalizeDynamicLists(): bool
    {
        $changed = false;
        // InfoItems
        $info = @json_decode($this->ReadPropertyString('InfoItems'), true);
        if (is_array($info)) {
            $seen = [];
            foreach ($info as &$row) {
                if (!is_array($row)) { $row = []; $changed = true; }
                $id = isset($row['Id']) ? (string)$row['Id'] : '';
                if ($id === '' || isset($seen[$id])) {
                    try { $id = 'i' . bin2hex(random_bytes(6)); } catch (Throwable $e) { $id = 'i' . substr(sha1(uniqid('', true)), 0, 12); }
                    $row['Id'] = $id;
                    $changed = true;
                }
                $seen[$id] = true;
            }
            unset($row);
            if ($changed) {
                IPS_SetProperty($this->InstanceID, 'InfoItems', json_encode($info));
            }
        }
        // MenuItems
        $menu = @json_decode($this->ReadPropertyString('MenuItems'), true);
        if (is_array($menu)) {
            $seen = [];
            foreach ($menu as &$row) {
                if (!is_array($row)) { $row = []; $changed = true; }
                $id = isset($row['Id']) ? (string)$row['Id'] : '';
                if ($id === '' || isset($seen[$id])) {
                    try { $id = 'm' . bin2hex(random_bytes(6)); } catch (Throwable $e) { $id = 'm' . substr(sha1(uniqid('', true)), 0, 12); }
                    $row['Id'] = $id;
                    $changed = true;
                }
                $seen[$id] = true;
            }
            unset($row);
            if ($changed) {
                IPS_SetProperty($this->InstanceID, 'MenuItems', json_encode($menu));
            }
        }
        if ($changed) {
            // Apply normalized properties and stop current ApplyChanges run
            IPS_ApplyChanges($this->InstanceID);
            return true;
        }
        return false;
    }

    public function ApplyChanges(): void
    {
        parent::ApplyChanges();
        $this->SendDebug('ApplyChanges', 'triggered', 0);

        // Heavy Work erst bei bereitem Kernel: registerImageHook ruft IPS_ApplyChanges()
        // auf der WebHook-Control auf. Vor KR_READY (z.B. beim Symcon-Start) greift das
        // nicht zuverlässig -> der Bild-Hook bleibt unregistriert und Media-Hintergründe
        // werden nicht ausgeliefert. MessageSink ruft ApplyChanges() bei KR_READY erneut.
        if (IPS_GetKernelRunlevel() !== KR_READY) {
            return;
        }

        // One-time migration: German → English property names (v2)
        if (TileVisuLib::migrateV2($this, $this->InstanceID)) {
            return;
        }

        // Ensure hidden IDs are auto-assigned once
        if ($this->NormalizeDynamicLists()) {
            return;
        }

        // WebHook für Bildauslieferung registrieren
        $this->registerImageHook('/hook/roomgridimages/' . $this->InstanceID);

        // Referenzen säubern
        foreach ($this->GetReferenceList() as $ref) {
            $this->UnregisterReference($ref);
        }

        // Nachrichten säubern
        foreach ($this->GetMessageList() as $senderID => $messageIDs) {
            foreach ($messageIDs as $messageID) {
                $this->UnregisterMessage($senderID, $messageID);
            }
        }

        $rooms = $this->getRooms();
        $varMap = [];
        $watchProps = ['InfoLeft','InfoLeft2','InfoRight','InfoRight2','InfoMiddleLeft','InfoMiddleRight','Switch1','Switch2','Switch3','Switch4','Switch5','LightStatus','DimValue'];

        // Register dynamic menu items for variable updates
        $menuList = @json_decode($this->ReadPropertyString('MenuItems'), true);
        if (is_array($menuList)) {
            foreach ($menuList as $row) {
                if (!is_array($row)) continue;
                $varId = (int)($row['VariableId'] ?? 0);
                $itemId = (string)($row['Id'] ?? '');
                if ($varId > 0 && $itemId !== '' && @IPS_VariableExists($varId)) {
                    $this->RegisterReference($varId);
                    $this->RegisterMessage($varId, VM_UPDATE);
                    $this->RegisterMessage($varId, OM_CHANGEHIDDEN);
                    $this->RegisterMessage($varId, OM_CHANGENAME);
                    if (!isset($varMap[$varId]) || !is_array($varMap[$varId])) {
                        $varMap[$varId] = [];
                    }
                    $varMap[$varId][] = ['idx' => 0, 'prop' => 'menuitem:' . $itemId];
                }
                $openObjectId = (int)($row['OpenObjectId'] ?? 0);
                if ($openObjectId > 0 && @IPS_ObjectExists($openObjectId)) {
                    $this->RegisterReference($openObjectId);
                    $this->RegisterMessage($openObjectId, OM_CHANGEHIDDEN);
                    $this->RegisterMessage($openObjectId, OM_CHANGENAME);
                }
                // SceneControl ActiveScene variable tracking
                $sceneControlId = (int)($row['SceneControlId'] ?? 0);
                if ($sceneControlId > 0 && $itemId !== '' && @IPS_InstanceExists($sceneControlId)) {
                    $this->RegisterReference($sceneControlId);
                    $this->RegisterMessage($sceneControlId, OM_CHANGEHIDDEN);
                    $this->RegisterMessage($sceneControlId, OM_CHANGENAME);
                    $activeVar = 0;
                    foreach ((array)@IPS_GetChildrenIDs($sceneControlId) as $cid) {
                        if (@IPS_VariableExists($cid)) {
                            $o = @IPS_GetObject($cid);
                            if (($o['ObjectIdent'] ?? '') === 'ActiveScene') { $activeVar = $cid; break; }
                        }
                    }
                    if ($activeVar > 0) {
                        $this->RegisterReference($activeVar);
                        $this->RegisterMessage($activeVar, VM_UPDATE);
                        $this->RegisterMessage($activeVar, OM_CHANGEHIDDEN);
                        $this->RegisterMessage($activeVar, OM_CHANGENAME);
                        if (!isset($varMap[$activeVar]) || !is_array($varMap[$activeVar])) {
                            $varMap[$activeVar] = [];
                        }
                        $varMap[$activeVar][] = ['idx' => 0, 'prop' => 'menuitem:' . $itemId];
                    }
                }
            }
        }

        // Register dynamic info items for variable updates
        $infoList = @json_decode($this->ReadPropertyString('InfoItems'), true);
        if (is_array($infoList)) {
            foreach ($infoList as $row) {
                if (!is_array($row)) continue;
                $varId = (int)($row['VariableId'] ?? 0);
                $itemId = (string)($row['Id'] ?? '');
                if ($varId > 0 && $itemId !== '' && @IPS_VariableExists($varId)) {
                    $this->RegisterReference($varId);
                    $this->RegisterMessage($varId, VM_UPDATE);
                    $this->RegisterMessage($varId, OM_CHANGEHIDDEN);
                    $this->RegisterMessage($varId, OM_CHANGENAME);
                    if (!isset($varMap[$varId]) || !is_array($varMap[$varId])) {
                        $varMap[$varId] = [];
                    }
                    $varMap[$varId][] = ['idx' => 0, 'prop' => 'infoitem:' . $itemId];
                }
            }
        }

        foreach ($rooms as $idx => $room) {
            foreach ($watchProps as $prop) {
                if (!isset($room[$prop])) {
                    continue;
                }
                $id = (int)$room[$prop];
                if ($id > 0 && IPS_VariableExists($id)) {
                    $this->RegisterReference($id);
                    $this->RegisterMessage($id, VM_UPDATE);
                    $this->RegisterMessage($id, OM_CHANGEHIDDEN);
                    $this->RegisterMessage($id, OM_CHANGENAME);
                    if (!isset($varMap[$id]) || !is_array($varMap[$id])) {
                        $varMap[$id] = [];
                    }
                    $varMap[$id][] = ['idx' => $idx, 'prop' => $prop];
                }
            }
            // BackgroundImage ist Media, nicht Variable -> keine Message
        }

        // TargetLinkId reference
        $linkId = (int)$this->ReadPropertyInteger('TargetLinkId');
        if ($linkId > 0 && @IPS_LinkExists($linkId)) {
            $this->RegisterReference($linkId);
        }
        $linkTarget = (int)$this->ReadPropertyInteger('TargetLinkValue');
        if ($linkTarget > 0 && @IPS_ObjectExists($linkTarget)) {
            $this->RegisterReference($linkTarget);
        }

        // Dynamic background image URL variable
        $bgUrlVarId = (int)$this->ReadPropertyInteger('BackgroundImageUrl');
        if ($bgUrlVarId > 0 && @IPS_VariableExists($bgUrlVarId)) {
            $this->RegisterReference($bgUrlVarId);
            $this->RegisterMessage($bgUrlVarId, VM_UPDATE);
        }

        $this->WriteAttributeString('VarMap', json_encode($varMap));

        // Simple Locale (optional): Sprachwechsel mitbekommen, Beschriftungen übersetzen
        $this->RegisterSimpleLocaleMessages();
        $this->RefreshSimpleLocaleLabels();

        // Initiales Full Update
        $this->UpdateVisualizationValue(json_encode($this->GetFullUpdateMessage()));
    }

    public function MessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data): void
    {
        $this->SendDebug('MessageSink', 'Sender=' . $SenderID . ' Message=' . $Message . ' Data=' . @json_encode($Data), 0);
        if ($Message === IPS_KERNELMESSAGE) {
            if (isset($Data[0]) && $Data[0] === KR_READY) {
                // Volle Initialisierung wie bei manuellem Neuladen nachholen, damit der
                // Bild-Hook bei bereitem Kernel zuverlässig registriert wird.
                $this->ApplyChanges();
            }
            return;
        }
        if (IPS_GetKernelRunlevel() !== KR_READY) {
            return;
        }
        try {
        if ($Message === OM_CHANGENAME) {
            // Angezeigte Namen stammen aus IPS_GetName() - nach einer Umbenennung neu aufbauen
            $this->SendDebug('OM_CHANGENAME', 'Sender=' . $SenderID . ' -> FULL RELOAD triggered', 0);
            $this->UpdateVisualizationValue(json_encode($this->GetFullUpdateMessage()));
            return;
        }
        if ($Message === OM_CHANGEHIDDEN) {
            // Nur reloaden wenn sich der Hidden-Status tatsächlich ändert
            $isHidden = false;
            try { $o = @IPS_GetObject($SenderID); if (is_array($o)) { $isHidden = (bool)($o['ObjectIsHidden'] ?? false); } } catch (Throwable $e) {}
            $hiddenMap = @json_decode((string)@$this->ReadAttributeString('HiddenMap'), true);
            if (!is_array($hiddenMap)) { $hiddenMap = []; }
            $key = (string)$SenderID;
            $prev = array_key_exists($key, $hiddenMap) ? ($hiddenMap[$key] ? '1' : '0') : 'n/a';
            $this->SendDebug('OM_CHANGEHIDDEN', 'Sender=' . $SenderID . ' prev=' . $prev . ' now=' . ($isHidden ? '1' : '0'), 0);
            if (array_key_exists($key, $hiddenMap) && (bool)$hiddenMap[$key] === $isHidden) {
                $this->SendDebug('OM_CHANGEHIDDEN', 'no change -> skip reload', 0);
                return; // Kein echter Wechsel -> kein Reload
            }
            $hiddenMap[$key] = $isHidden;
            $this->WriteAttributeString('HiddenMap', json_encode($hiddenMap));
            $this->SendDebug('OM_CHANGEHIDDEN', 'FULL RELOAD triggered', 0);
            $this->UpdateVisualizationValue(json_encode($this->GetFullUpdateMessage()));
            return;
        }
        if ($Message === IM_CHANGESETTINGS) {
            // Simple Locale: Sprachwechsel oder neu eingelesener Baum - Beschriftungen neu übersetzen
            if ($this->IsSimpleLocaleInstance($SenderID) && $this->RefreshSimpleLocaleLabels()) {
                $this->SendDebug('SimpleLocale', 'Beschriftungen geändert -> FULL RELOAD triggered', 0);
                $this->UpdateVisualizationValue(json_encode($this->GetFullUpdateMessage()));
            }
            return;
        }
        if ($Message !== VM_UPDATE) {
            return;
        }
        // Dynamic background image URL variable changed → send new image1 + disable filter
        $bgUrlVarId = (int)@$this->ReadPropertyInteger('BackgroundImageUrl');
        if ($bgUrlVarId > 0 && $SenderID === $bgUrlVarId) {
            $url = (string)@GetValue($bgUrlVarId);
            $delta = [
                ['idx' => 0, 'key' => 'image1', 'value' => $url],
                ['idx' => 0, 'key' => 'bgfilter', 'value' => 0.0],
            ];
            $this->UpdateVisualizationValue(json_encode(['delta' => $delta]));
            return;
        }
        $mapRaw = @($this->ReadAttributeString('VarMap'));
        if (!is_string($mapRaw)) {
            return;
        }
        $map = json_decode($mapRaw, true) ?: [];
        if (!isset($map[$SenderID])) {
            return;
        }
        $mappings = $map[$SenderID];
        if (isset($mappings['idx'])) {
            $mappings = [$mappings];
        }

        $rooms = $this->getRooms();
        $delta = [];
        foreach ($mappings as $mp) {
            if (!is_array($mp) || !isset($mp['idx'], $mp['prop'])) {
                continue;
            }
            $idx = (int)$mp['idx'];
            $prop = (string)$mp['prop'];
            if (!isset($rooms[$idx])) {
                continue;
            }
            $room = $rooms[$idx];

            // Spezialfall: LightStatus/DimValue → bgfilter/bgfade (0..100)
            if ($prop === 'LightStatus' || $prop === 'DimValue') {
                $boolId = (int)($room['LightStatus'] ?? 0);
                $dimId = (int)($room['DimValue'] ?? 0);
                $pOut = $this->computeBgFilterValue($boolId, $dimId);
                // Filter deaktivieren wenn URL-Variable aktiv oder Bild 2 konfiguriert
                $bgUrlId = (int)($room['BackgroundImageUrl'] ?? 0);
                $imageID2 = (int)($room['BackgroundImage2'] ?? 0);
                if (($bgUrlId > 0 && @IPS_VariableExists($bgUrlId)) || $imageID2 > 0) { $pOut = 0.0; }
                $delta[] = [ 'idx' => $idx, 'key' => 'bgfilter', 'value' => $pOut ];
                // bgfade nur senden wenn zweites Bild konfiguriert ist
                $imageID2 = (int)($room['BackgroundImage2'] ?? 0);
                if ($imageID2 > 0) {
                    $fade = $this->computeBgFadeValue($boolId, $dimId);
                    $delta[] = [ 'idx' => $idx, 'key' => 'bgfade', 'value' => $fade ];
                }
                continue;
            }

            // Dynamic menu/info items special handling
            if (strpos($prop, 'menuitem:') === 0) {
                $itemId = substr($prop, strlen('menuitem:'));
                $varId = $SenderID; // mapped variable triggered this update
                if ($varId > 0 && @IPS_VariableExists($varId)) {
                    $value = @GetValue($varId);
                    // If this menuitem is a SceneControl entry, map label -> numeric index
                    $menuList = @json_decode($this->ReadPropertyString('MenuItems'), true);
                    $sceneControlId = 0;
                    if (is_array($menuList)) {
                        foreach ($menuList as $row) {
                            if (!is_array($row)) continue;
                            $rid = (string)($row['Id'] ?? '');
                            if ($rid === $itemId) { $sceneControlId = (int)($row['SceneControlId'] ?? 0); break; }
                        }
                    }
                    if ($sceneControlId > 0 && @IPS_InstanceExists($sceneControlId)) {
                        if (!is_numeric($value)) {
                            $mapped = 0;
                            foreach ((array)@IPS_GetChildrenIDs($sceneControlId) as $cid) {
                                if (!@IPS_VariableExists($cid)) continue;
                                $o = @IPS_GetObject($cid);
                                $ident = (string)($o['ObjectIdent'] ?? '');
                                if (preg_match('/^Scene(\d+)$/i', $ident, $m)) {
                                    $label = (string)($o['ObjectName'] ?? $ident);
                                    if ((string)$label === (string)$value) { $mapped = (int)$m[1]; break; }
                                }
                            }
                            $value = $mapped;
                        } else {
                            $value = (int)$value;
                        }
                    }

                    $delta[] = ['idx' => $idx, 'key' => 'menuitem-' . $itemId . 'value', 'value' => $value];
                    $delta[] = ['idx' => $idx, 'key' => 'menuitem-' . $itemId . 'asso', 'value' => (string)@GetValueFormatted($varId)];
                    try { $icon = (string)$this->GetIconAdvanced($varId); } catch (Throwable $e) { $icon = ''; }
                    if ($icon !== '' && $icon !== 'Transparent') {
                        $delta[] = ['idx' => $idx, 'key' => 'menuitem-' . $itemId . 'icon', 'value' => $icon];
                    }
                    $altName = ''; $showName = false; $openObjectId = 0; $objectName = '';
                    try {
                        $menuList = @json_decode($this->ReadPropertyString('MenuItems'), true);
                        if (is_array($menuList)) {
                            foreach ($menuList as $row) {
                                if (!is_array($row)) continue;
                                $rid = (string)($row['Id'] ?? '');
                                if ($rid === $itemId) {
                                    $altName = (string)($row['AltName'] ?? '');
                                    $showName = isset($row['ShowName']) ? (bool)$row['ShowName'] : false;
                                    $openObjectId = (int)($row['OpenObjectId'] ?? 0);
                                    break;
                                }
                            }
                        }
                    } catch (Throwable $e) {}
                    if ($openObjectId > 0 && @IPS_ObjectExists($openObjectId)) {
                        try { $obj = @IPS_GetObject($openObjectId); $objectName = (string)($obj['ObjectName'] ?? ''); } catch (Throwable $e) { $objectName = ''; }
                    }
                    if ($altName !== '') {
                        $delta[] = ['idx' => $idx, 'key' => 'menuitem-' . $itemId . 'name', 'value' => $this->TranslateLabel($altName)];
                    } elseif ($openObjectId > 0 && $objectName !== '') {
                        $delta[] = ['idx' => $idx, 'key' => 'menuitem-' . $itemId . 'name', 'value' => $objectName];
                    } elseif ($showName) {
                        try { $nm = (string)@IPS_GetName($varId); } catch (Throwable $e) { $nm = ''; }
                        if ($nm !== '') {
                            $delta[] = ['idx' => $idx, 'key' => 'menuitem-' . $itemId . 'name', 'value' => $nm];
                        }
                    }
                    // Status-Hintergrundfarbe (useVarColor) live nachführen — sonst nur Wert/Icon
                    $ce = $this->buildMenuStatusColorDelta($itemId, $varId);
                    if ($ce !== null) { $delta[] = $ce; }
                }
                continue;
            }
            if (strpos($prop, 'infoitem:') === 0) {
                $itemId = substr($prop, strlen('infoitem:'));
                $varId = $SenderID; // mapped variable triggered this update
                if ($varId > 0 && @IPS_VariableExists($varId)) {
                    $delta[] = ['idx' => $idx, 'key' => 'infoitem-' . $itemId . 'asso', 'value' => (string)@GetValueFormatted($varId)];
                    try { $icon = (string)$this->GetIconAdvanced($varId); } catch (Throwable $e) { $icon = ''; }
                    if ($icon !== '' && $icon !== 'Transparent') {
                        $delta[] = ['idx' => $idx, 'key' => 'infoitem-' . $itemId . 'icon', 'value' => $icon];
                    }
                    // Determine correct name: prefer AltName if set; else only use IPS name when ShowName is true
                    $useColor = false; $ct = -1; $cf = -1; $altName = ''; $showName = false;
                    try {
                        $infoList = @json_decode($this->ReadPropertyString('InfoItems'), true);
                        if (is_array($infoList)) {
                            foreach ($infoList as $row) {
                                if (!is_array($row)) continue;
                                $rid = (string)($row['Id'] ?? '');
                                if ($rid === $itemId) {
                                    $useColor = isset($row['UseVarColor']) ? (bool)$row['UseVarColor'] : false;
                                    $ct = isset($row['ColorTrue']) ? (int)$row['ColorTrue'] : -1;
                                    $cf = isset($row['ColorFalse']) ? (int)$row['ColorFalse'] : -1;
                                    $altName = (string)($row['AltName'] ?? '');
                                    $showName = isset($row['ShowName']) ? (bool)$row['ShowName'] : false;
                                    break;
                                }
                            }
                        }
                    } catch (Throwable $e) {}
                    if ($altName !== '') {
                        $delta[] = ['idx' => $idx, 'key' => 'infoitem-' . $itemId . 'name', 'value' => $this->TranslateLabel($altName)];
                    } elseif ($showName) {
                        try { $nm = (string)@IPS_GetName($varId); } catch (Throwable $e) { $nm = ''; }
                        if ($nm !== '') {
                            $delta[] = ['idx' => $idx, 'key' => 'infoitem-' . $itemId . 'name', 'value' => $nm];
                        }
                    }
                    // Color delta: Prefer explicit ColorTrue/ColorFalse for bool; otherwise UseVarColor profile color
                    $sendColor = null; $vType = null; $isOn = false;
                    try { $viTmp = @IPS_GetVariable($varId); if (is_array($viTmp)) { $vType = $viTmp['VariableType'] ?? null; } } catch (Throwable $e) {}
                    if ($vType === 0) {
                        try { $isOn = (bool)@GetValue($varId); } catch (Throwable $e) { $isOn = false; }
                        if ($isOn && $ct !== -1) {
                            $sendColor = '#' . sprintf('%06X', $ct);
                        } elseif (!$isOn && $cf !== -1) {
                            $sendColor = '#' . sprintf('%06X', $cf);
                        } elseif ($useColor) {
                            try { $c = (string)$this->GetColor($varId); $sendColor = ($c !== '' ? ('#' . $c) : ''); } catch (Throwable $e) { $sendColor = ''; }
                        }
                    } elseif ($useColor) {
                        try { $c = (string)$this->GetColor($varId); $sendColor = ($c !== '' ? ('#' . $c) : ''); } catch (Throwable $e) { $sendColor = ''; }
                    }
                    if ($sendColor !== null) {
                        $delta[] = ['idx' => $idx, 'key' => 'infoitem-' . $itemId . 'color', 'value' => $sendColor];
                    }
                }
                continue;
            }

            // Switch1-5 → 'schalter' Prefix im Frontend
            $isSwitch = (strpos($prop, 'Switch') === 0 && is_numeric(substr($prop, 6, 1)));
            $deltaKey = $isSwitch ? ('schalter' . substr($prop, 6)) : strtolower($prop);

            // Wert formatiert
            $delta[] = [
                'idx' => $idx,
                'key' => $deltaKey,
                'value' => $this->CheckAndGetValueFormattedFromId($this->ReadInt($room, $prop))
            ];

            // Farbe
            $col = $this->GetColor($this->ReadInt($room, $prop));
            $delta[] = [
                'idx' => $idx,
                'key' => $deltaKey . 'color',
                'value' => ($col !== '') ? ('#' . $col) : ''
            ];

            // Name/Icon/Asso/AltName wenn nicht BackgroundImage
            if ($prop !== 'BackgroundImage') {
                $isMiddleProp = ($prop === 'InfoMiddleLeft' || $prop === 'InfoMiddleRight');
                $nameKey = $isMiddleProp ? ($prop . 'ShowName') : ($prop . 'NameSwitch');
                if ($this->ReadBool($room, $nameKey)) {
                    $delta[] = [
                        'idx' => $idx,
                        'key' => $deltaKey . 'name',
                        'value' => IPS_GetName($this->ReadInt($room, $prop))
                    ];
                }
                $icon = $this->GetIconAdvanced($this->ReadInt($room, $prop));
                $delta[] = [
                    'idx' => $idx,
                    'key' => $deltaKey . 'icon',
                    'value' => $icon
                ];
                $delta[] = [
                    'idx' => $idx,
                    'key' => $deltaKey . 'asso',
                    'value' => $this->CheckAndGetValueFormattedFromId($this->ReadInt($room, $prop))
                ];
                if (isset($room[$prop . 'AltName'])) {
                    $delta[] = [
                        'idx' => $idx,
                        'key' => ($isSwitch ? ('switch' . substr($prop, 6)) : strtolower($prop)) . 'altname',
                        'value' => $this->TranslateLabel((string)$room[$prop . 'AltName'])
                    ];
                }
            }

            // SwitchN raw value as delta for active state
            if ($isSwitch) {
                $n = (int)substr($prop, 6);
                $delta[] = [
                    'idx' => $idx,
                    'key' => 'schalter' . $n . 'value',
                    'value' => @GetValue($this->ReadInt($room, $prop))
                ];
            }
            // InfoN Rohwert als Delta für Active-State (für interaktive Info-Buttons)
            if (strpos($prop, 'Info') === 0) {
                $n = (int)substr($prop, 4);
                $delta[] = [
                    'idx' => $idx,
                    'key' => 'info' . $n . 'value',
                    'value' => @GetValue($this->ReadInt($room, $prop))
                ];
            }
            // Dynamic menu item update
            if (strpos($prop, 'menuitem:') === 0) {
                $itemId = substr($prop, strlen('menuitem:'));
                $this->sendMenuItemDelta($itemId, $this->ReadInt($room, $prop));
                return;
            }
        }
        if (!empty($delta)) {
            $this->UpdateVisualizationValue(json_encode(['delta' => $delta]));
        }
        } catch (Throwable $e) {}
    }

    public function RequestAction(string $Ident, mixed $Value): void
    {
        // Dynamic menu item action: menuitem:<Id>
        // menuitemset:<Id> - genauen Wert setzen (Streichen über einen Dimmer-Button),
        // statt wie beim Tippen umzuschalten
        $setExactValue = strpos($Ident, 'menuitemset:') === 0;
        if ($setExactValue) {
            $Ident = 'menuitem:' . substr($Ident, strlen('menuitemset:'));
        }
        if (strpos($Ident, 'menuitem:') === 0) {
            $itemId = substr($Ident, strlen('menuitem:'));
            $menuList = @json_decode($this->ReadPropertyString('MenuItems'), true);
            if (!is_array($menuList) || empty($menuList)) {
                return;
            }
            foreach ($menuList as $row) {
                if (!is_array($row)) continue;
                $rid = (string)($row['Id'] ?? '');
                if ($rid === '' || $rid !== $itemId) continue;
                // SceneControl action
                $sceneControlId = (int)($row['SceneControlId'] ?? 0);
                if ($sceneControlId > 0 && @IPS_InstanceExists($sceneControlId)) {
                    try {
                        $targetScene = $Value;
                        if (!is_numeric($targetScene)) {
                            $targetScene = 0;
                            foreach ((array)@IPS_GetChildrenIDs($sceneControlId) as $cid) {
                                if (!@IPS_VariableExists($cid)) continue;
                                $o = @IPS_GetObject($cid);
                                $ident = (string)($o['ObjectIdent'] ?? '');
                                if (preg_match('/^Scene(\d+)$/i', $ident, $m)) {
                                    $label = (string)($o['ObjectName'] ?? $ident);
                                    if ((string)$label === (string)$Value) { $targetScene = (int)$m[1]; break; }
                                }
                            }
                        } else {
                            $targetScene = (int)$targetScene;
                        }
                        if ($targetScene > 0) {
                            @SZS_CallScene($sceneControlId, $targetScene);
                            @SZS_UpdateActive($sceneControlId);
                        }
                    } catch (Throwable $e) {}
                    return;
                }

                // Variable action fallback
                $varId = (int)($row['VariableId'] ?? 0);
                if ($varId <= 0 || !@IPS_VariableExists($varId)) return;
                $variable = @IPS_GetVariable($varId);
                if (!is_array($variable)) return;
                $vType = $variable['VariableType'] ?? 0;
                $actionId = 0;
                if (isset($variable['VariableCustomAction']) && $variable['VariableCustomAction'] > 0) { $actionId = $variable['VariableCustomAction']; }
                elseif (isset($variable['VariableAction']) && $variable['VariableAction'] > 0) { $actionId = $variable['VariableAction']; }
                $hasValidAction = ($actionId > 0) && (@IPS_InstanceExists($actionId) || @IPS_ScriptExists($actionId));
                if (!$hasValidAction) return;

                if ($vType === 0) { $newValue = ($Value === null) ? !@GetValue($varId) : (bool)$Value; }
                elseif ($vType === 1 || $vType === 2) { $newValue = $setExactValue ? $this->ResolveNumericSetValue($varId, $vType, $Value) : $this->ResolveNumericSwitchValue($varId, $vType, $Value); }
                else { $newValue = (string)$Value; }
                @RequestAction($varId, $newValue);
                $this->sendMenuItemDelta($itemId, $varId);
                return;
            }
            return;
        }
        // Link-Ziel ändern
        if ($Ident === 'setlink' && is_string($Value)) {
            $this->SendDebug('setlink', 'Value=' . $Value, 0);
            $payload = @json_decode($Value, true);
            if (is_array($payload)) {
                $linkId = (int)($payload['linkId'] ?? 0);
                $targetId = (int)($payload['targetId'] ?? 0);
                $this->SendDebug('setlink', 'linkId=' . $linkId . ' targetId=' . $targetId, 0);
                if (
                    $this->isConfiguredTargetLinkPair($linkId, $targetId)
                    && @IPS_LinkExists($linkId)
                    && @IPS_ObjectExists($targetId)
                ) {
                    @IPS_SetLinkTargetID($linkId, $targetId);
                    $this->SendDebug('setlink', 'IPS_SetLinkTargetID executed', 0);
                }
            }
            return;
        }

        // Reorder Buttons aus der Form
        if ($Ident === 'reorder' && is_string($Value)) {
            $payload = json_decode($Value, true);
            if (!is_array($payload) || !isset($payload['direction'], $payload['index'])) {
                return;
            }
            $this->ReorderRoom((int)$payload['index'], (string)$payload['direction']);
            return;
        }

        // Switch/Info from tile: room:<idx>:SwitchN | InfoN
        if (strpos($Ident, 'room:') === 0) {
            $parts = explode(':', $Ident);
            if (count($parts) === 3) {
                $idx = (int)$parts[1];
                $property = $parts[2];
                $rooms = $this->getRooms();
                if (!isset($rooms[$idx]) || !isset($rooms[$idx][$property])) {
                    return;
                }
                $varId = (int)$rooms[$idx][$property];
                if (!IPS_VariableExists($varId)) {
                    return;
                }
                // Typ-spezifisches Setzen
                $variable = IPS_GetVariable($varId);
                $vType = $variable['VariableType'];
                // Prüfe ob eine Action vorhanden ist (VariableCustomAction oder VariableAction)
                $actionId = 0;
                if (isset($variable['VariableCustomAction']) && $variable['VariableCustomAction'] > 0) {
                    $actionId = $variable['VariableCustomAction'];
                } elseif (isset($variable['VariableAction']) && $variable['VariableAction'] > 0) {
                    $actionId = $variable['VariableAction'];
                }
                $hasValidAction = ($actionId > 0) && (@IPS_InstanceExists($actionId) || @IPS_ScriptExists($actionId));

                // Zielwert je nach Variablentyp ermitteln
                if ($vType === 0) { // BOOLEAN -> toggle
                    $newValue = !@GetValue($varId);
                } elseif ($vType === 1) { // INTEGER
                    $newValue = $this->ResolveNumericSwitchValue($varId, $vType, $Value);
                } elseif ($vType === 3) { // STRING
                    $newValue = (string)$Value;
                } elseif ($vType === 2 && TileVisuLib::getNumericRange($varId) !== null) { // FLOAT mit Wertebereich
                    $newValue = $this->ResolveNumericSwitchValue($varId, $vType, $Value);
                } else {
                    // Fallback: toggle
                    $newValue = !@GetValue($varId);
                }

                if ($hasValidAction) {
                    @RequestAction($varId, $newValue);
                } else {
                    // Keine gültige Action: Interaktion ignorieren
                    return;
                }
            }
            return;
        }
    }

    private function GetFullUpdateMessage(): array
    {
        $result = [];
        $result['grid'] = $this->buildGridConfig();
        $defaults = $this->readGlobalDefaults();

        $resultRooms = [];
        foreach ($this->getRooms() as $idx => $room) {
            $resultRooms[] = $this->buildRoomPayload($room, (int)$idx, $defaults);
        }
        $result['rooms'] = $resultRooms;
        return $result;
    }

    private function buildGridConfig(): array
    {
        return [
        'borderRadius' => $this->ReadPropertyInteger('BorderRadius'),
        'buttonHeight' => $this->ReadPropertyInteger('Default_ButtonHeight'),
        'useImageColorsForButtons' => $this->ReadPropertyBoolean('UseImageColorsForButtons'),
        'transparentStatusColors' => $this->ReadPropertyBoolean('TransparentStatusColors'),
        'transparentMenuStatusColors' => $this->ReadPropertyBoolean('TransparentMenuStatusColors'),
        'groupMenuInfoElements' => $this->ReadPropertyBoolean('GroupMenuInfoElements'),
        'menuTransparency' => (float)$this->ReadPropertyFloat('Default_MenuTransparency')
        ];
    }

    private function readGlobalDefaults(): array
    {
        // Lese optionale Defaults aus den separaten Properties und mappe sie auf die Raum-Keys
        // Robust lesen: Infohöhe kann in bestehenden Instanzen fehlen
        $defInfoHeight = 0;
        try { $defInfoHeight = @($this->ReadPropertyInteger('Default_InfoHeight')); } catch (Throwable $e) {}
        if (!is_int($defInfoHeight) || $defInfoHeight <= 0) {
            // Fallback auf ältere Schreibweise mit Umlaut; unter Module Strict
            // kann der Lesezugriff auf die unregistrierte Property werfen
            $alt = 0;
            try { $alt = @($this->ReadPropertyInteger('Default_Infohöhe')); } catch (Throwable $e) {}
            if (is_int($alt) && $alt > 0) { $defInfoHeight = $alt; } else { $defInfoHeight = 0; }
        }

        $defaults = [
            'InfoFontSize'       => (int)$this->ReadPropertyInteger('Default_InfoFontSize'),
            'InfoFontColor'         => (int)$this->ReadPropertyInteger('Default_InfoFontColor'),
            'InfoHeight'                => (int)$defInfoHeight,
            'MenuFontSize'  => (int)$this->ReadPropertyInteger('Default_MenuFontSize'),
            'MenuFontColor'    => (int)$this->ReadPropertyInteger('Default_MenuFontColor'),
            'MenuTransparency'     => (float)$this->ReadPropertyFloat('Default_MenuTransparency'),
            'MenuBackgroundColor'=> (int)$this->ReadPropertyInteger('Default_MenuBackgroundColor'),
            'InfoTopTransparency'       => (float)$this->ReadPropertyFloat('Default_InfoTopTransparency'),
            'InfoTopBackgroundColor'  => (int)$this->ReadPropertyInteger('Default_InfoTopBackgroundColor'),
            'InfoTopBorderRadius'      => (int)$this->ReadPropertyInteger('Default_InfoTopBorderRadius'),
            'TileBackgroundColor'   => (int)$this->ReadPropertyInteger('Default_TileBackgroundColor'),
            'RoomNameFontSize'   => (int)$this->ReadPropertyInteger('Default_RoomNameFontSize'),
            'ImageTransparency'          => (float)$this->ReadPropertyFloat('Default_ImageTransparency')
        ];
        // Normalisiere Transparent(-1) für globale Defaults auf sinnvolle Standardwerte
        if (isset($defaults['InfoFontColor']) && (int)$defaults['InfoFontColor'] === -1) {
            $defaults['InfoFontColor'] = 0xFFFFFF; // Weiß
        }
        if (isset($defaults['MenuFontColor']) && (int)$defaults['MenuFontColor'] === -1) {
            $defaults['MenuFontColor'] = 0xFFFFFF; // Weiß
        }
        if (isset($defaults['MenuBackgroundColor']) && (int)$defaults['MenuBackgroundColor'] === -1) {
            $defaults['MenuBackgroundColor'] = 0x000000; // Schwarz
        }
        if (isset($defaults['InfoTopBackgroundColor']) && (int)$defaults['InfoTopBackgroundColor'] === -1) {
            $defaults['InfoTopBackgroundColor'] = 0x000000; // Schwarz
        }
        if (isset($defaults['TileBackgroundColor']) && (int)$defaults['TileBackgroundColor'] === -1) {
            $defaults['TileBackgroundColor'] = 0x000000; // Schwarz
        }
        return $defaults;
    }

    private function buildRoomPayload(array $room, int $idx, array $defaults): array
    {
        $r = [];
        $r['idx'] = $idx;
        $this->applyRoomStyles($r, $room, $defaults);
        $this->applyRoomImagesAndFilter($r, $room);
        $this->applyRoomItems($r, $room);
        return $r;
    }

    private function applyRoomStyles(array &$r, array $room, array $defaults): void
    {
        // Styles (kombiniere Defaults + Raum-spezifisch)
        $inf = null;
        if (array_key_exists('InfoFontSize', $room)) { $inf = (int)$room['InfoFontSize']; }
        $r['infofontsize'] = ($inf === null || $inf <= 0) ? (int)($defaults['InfoFontSize'] ?? 16) : $inf;
        // Höhe der Infoleiste (nur globaler Default)
        $r['infoheight'] = (int)($defaults['InfoHeight'] ?? 0);

        $imf = null;
        if (array_key_exists('MenuFontSize', $room)) { $imf = (int)$room['MenuFontSize']; }
        $r['menufontsize'] = ($imf === null || $imf <= 0) ? (int)($defaults['MenuFontSize'] ?? 16) : $imf;

        $kcol = null;
        if (array_key_exists('TileBackgroundColor', $room)) { $kcol = (int)$room['TileBackgroundColor']; }
        if ($kcol === null || $kcol === -1) { $kcol = (int)($defaults['TileBackgroundColor'] ?? 0x000000); }
        $r['tilebackgroundcolor'] = $this->toCssHex($kcol);

        $icol = null;
        if (array_key_exists('InfoFontColor', $room)) { $icol = (int)$room['InfoFontColor']; }
        if ($icol === null || $icol === -1) { $icol = (int)($defaults['InfoFontColor'] ?? 0xFFFFFF); }
        $r['infofontcolor'] = $this->toCssHex($icol);

        // InfoTop BackgroundColor (badges) aus Defaults (Prozent -> Alpha)
        $topCol = (int)($defaults['InfoTopBackgroundColor'] ?? 0x000000);
        $topAlphaPercent = $this->normalizePercent((float)($defaults['InfoTopTransparency'] ?? 30.0));
        $r['infotopbackgroundcolor'] = $this->cssRgba($topCol, $this->percentToAlpha($topAlphaPercent));
        $r['infotopborderradius'] = (int)($defaults['InfoTopBorderRadius'] ?? 50);
        $r['buttonborderradius'] = (int)$this->ReadPropertyInteger('Default_ButtonBorderRadius');

        $imcol = null;
        if (array_key_exists('MenuFontColor', $room)) { $imcol = (int)$room['MenuFontColor']; }
        if ($imcol === null || $imcol === -1) { $imcol = (int)($defaults['MenuFontColor'] ?? 0xFFFFFF); }
        $r['menufontcolor'] = $this->toCssHex($imcol);

        if (array_key_exists('MenuBackgroundColor', $room) && (int)$room['MenuBackgroundColor'] !== -1) {
            $bgCol = (int)$room['MenuBackgroundColor'];
            $alphaVal = null;
            if (array_key_exists('MenuTransparency', $room)) { $alphaVal = (float)$room['MenuTransparency']; }
            $bgAlphaPercent = ($alphaVal === null || $alphaVal < 0)
                ? $this->normalizePercent((float)($defaults['MenuTransparency'] ?? 30.0))
                : $this->normalizePercent($alphaVal);
        } else {
            $bgCol = (int)($defaults['MenuBackgroundColor'] ?? 0x000000);
            $bgAlphaPercent = $this->normalizePercent((float)($defaults['MenuTransparency'] ?? 30.0));
        }
        $r['menubackgroundcolor'] = $this->cssRgba((int)$bgCol, $this->percentToAlpha($bgAlphaPercent));
        $r['switchalignment'] = (string)($room['SwitchAlignment'] ?? 'left');
        $r['switchdistribute'] = (bool)($room['SwitchDistribute'] ?? false);
        $r['imagetransparency'] = $this->percentToAlpha($this->normalizePercent($this->ReadNumOrDefault($room, 'ImageTransparency', $defaults, 70.0)));
        $r['infotopcentered'] = (bool)$this->ReadPropertyBoolean('InfoTopCentered');

        // Hintergrundbild-Filter Parameter an Frontend senden
        $r['bgfilterbrightnessmin'] = (float)$this->ReadNumOrDefault($room, 'BgFilterBrightnessMin', [], 0.2);
        $r['bgfilterbrightnessmax'] = (float)$this->ReadNumOrDefault($room, 'BgFilterBrightnessMax', [], 1.0);
        $r['bgfiltercontrastmin']   = (float)$this->ReadNumOrDefault($room, 'BgFilterContrastMin', [], 0.9);
        $r['bgfiltercontrastmax']   = (float)$this->ReadNumOrDefault($room, 'BgFilterContrastMax', [], 1.0);
        $r['bgfiltergrayscalemin']  = (float)$this->ReadNumOrDefault($room, 'BgFilterGrayscaleMin', [], 0.0);
        $r['bgfiltergrayscalemax']  = (float)$this->ReadNumOrDefault($room, 'BgFilterGrayscaleMax', [], 0.5);

        $r['roomname'] = $this->TranslateLabel((string)($room['RoomName'] ?? ''));
        $r['targetlink'] = (int)($room['Target'] ?? 0);
        $r['targetlinkid'] = (int)($room['TargetLinkId'] ?? 0);
        $r['targetlinkvalue'] = (int)($room['TargetLinkValue'] ?? 0);
        $rn = null;
        if (array_key_exists('RoomNameFontSize', $room)) {
            $rn = (int)$room['RoomNameFontSize'];
        }
        if ($rn === null || $rn === -1) {
            $r['roomnamefontsize'] = (int)($defaults['RoomNameFontSize'] ?? 64);
        } else {
            $r['roomnamefontsize'] = $rn;
        }
        $rncol = null;
        if (array_key_exists('RoomNameFontColor', $room)) { $rncol = (int)$room['RoomNameFontColor']; }
        if ($rncol === null || $rncol === -1) { $rncol = 0xFFFFFF; }
        $r['roomnamefontcolor'] = $this->toCssHex($rncol);

        $midIconSize = (int)$this->ReadPropertyInteger('InfoMiddleIconSize');
        if ($midIconSize <= 0) {
            $midIconSize = 56;
        }
        $r['infomiddleiconsize'] = $midIconSize;

        $midTextSize = (int)$this->ReadPropertyInteger('InfoMiddleTextSize');
        if ($midTextSize <= 0) {
            $midTextSize = (int)($defaults['InfoFontSize'] ?? 16);
        }
        $r['infomiddletextsize'] = $midTextSize;

        $midColor = (int)$this->ReadPropertyInteger('InfoMiddleColor');
        if ($midColor === -1) {
            $r['infomiddlecolor'] = $r['infofontcolor'];
        } else {
            $r['infomiddlecolor'] = $this->toCssHex($midColor);
        }

        $r['menuswitch'] = (bool)($room['MenuSwitch'] ?? true);

        // Use image colors for buttons per room (fallback to global flag)
        $useImgCol = array_key_exists('UseImageColorsForButtons', $room)
            ? (bool)$room['UseImageColorsForButtons']
            : (bool)$this->ReadPropertyBoolean('UseImageColorsForButtons');
        $r['useimagecolors'] = $useImgCol;
    }

    private function applyRoomImagesAndFilter(array &$r, array $room): void
    {
        // Dynamic background image URL variable (overrides media images)
        $bgUrlVarId = (int)($room['BackgroundImageUrl'] ?? 0);
        $bgUrlActive = ($bgUrlVarId > 0 && @IPS_VariableExists($bgUrlVarId));
        $bgUrlValue = $bgUrlActive ? (string)@GetValue($bgUrlVarId) : '';

        // Bilder: per WebHook ausliefern (Base64 via JSON)
        $imageID = (int)($room['BackgroundImage'] ?? 0);
        $imageID2 = (int)($room['BackgroundImage2'] ?? 0);
        // Prüfe ob Media-IDs gültig sind
        if ($imageID2 > 0 && !@IPS_MediaExists($imageID2)) {
            $imageID2 = 0; // Ungültige Media-ID ignorieren
        }
        if ($bgUrlActive && $bgUrlValue !== '') {
            $r['image1'] = $bgUrlValue;
            $r['image2enabled'] = false;
        } else {
            $r['image1'] = $this->BuildImageHookUrl($imageID);
            if ($imageID2 > 0) {
                $r['image2'] = $this->BuildImageHookUrl($imageID2);
                $r['image2enabled'] = true;
            } else {
                $r['image2enabled'] = false;
            }
        }

        // Hintergrundfilter & Fade aus LightStatus/DimValue
        try {
            $boolId = (int)($room['LightStatus'] ?? 0);
            $dimId = (int)($room['DimValue'] ?? 0);
            $p = $this->computeBgFilterValue($boolId, $dimId);
            // Filter deaktivieren wenn URL-Variable aktiv oder Bild 2 konfiguriert
            if ($bgUrlActive || $imageID2 > 0) { $p = 0.0; }
            $r['bgfilter'] = $p;
            // bgfade nur senden wenn zweites Bild konfiguriert ist (und keine URL-Variable)
            if (!$bgUrlActive && $imageID2 > 0) {
                $r['bgfade'] = $this->computeBgFadeValue($boolId, $dimId);
            } else {
                $r['bgfade'] = 0.0;
            }
        } catch (Throwable $e) {}
    }

    private function applyRoomItems(array &$r, array $room): void
    {
        $useImgCol = (bool)($r['useimagecolors'] ?? false);
        // Dynamic lists
        $infoList = @json_decode($this->ReadPropertyString('InfoItems'), true);
        $menuList = @json_decode($this->ReadPropertyString('MenuItems'), true);
        $useDynamicInfo = is_array($infoList) && count($infoList) > 0;
        $useDynamicMenu = is_array($menuList) && count($menuList) > 0;

        if ($useDynamicInfo) {
            $r['infoitems'] = $this->buildDynamicInfo($infoList, $r);
        }
        if ($useDynamicMenu) {
            $r['menuitems'] = $this->buildDynamicMenu($menuList, $r);
        }

        // Fixed Info-/Schalter-Werte (werden auch bei dynamischen Listen für Info-Mitte benötigt)
        $this->fillInfoAndButtons($r, $room);

        // Schalter-Breiten, Volle Breite und Farben (fixed only)
        for ($i = 1; $i <= 5; $i++) {
            // Always send breite and vollebreite, even if no variable assigned
            $r['switch' . $i . 'width'] = (float)($room['Switch' . $i . 'Width'] ?? 100);
            $r['switch' . $i . 'fullwidth'] = (bool)($room['Switch' . $i . 'FullWidth'] ?? false);

            $sid = (int)($room['Switch' . $i] ?? 0);
            if (!$useImgCol && $sid > 0 && IPS_VariableExists($sid)) {
                $col = $this->GetColor($sid);
                if ($col !== '') {
                    $r['schalter' . $i . 'color'] = '#' . $col;
                }
            }
        }

        // Alt-Namen (fixed only)
        for ($i = 1; $i <= 5; $i++) {
            $r['switch' . $i . 'altname'] = $this->TranslateLabel((string)($room['Switch' . $i . 'AltName'] ?? ''));
            $r['info' . $i . 'altname'] = $this->TranslateLabel((string)($room['Info' . $i . 'AltName'] ?? ''));
        }
        $r['infoleftaltname'] = $this->TranslateLabel((string)($room['InfoLeftAltName'] ?? ''));
        $r['inforightaltname'] = $this->TranslateLabel((string)($room['InfoRightAltName'] ?? ''));
        $r['infoleft2altname'] = $this->TranslateLabel((string)($room['InfoLeft2AltName'] ?? ''));
        $r['inforight2altname'] = $this->TranslateLabel((string)($room['InfoRight2AltName'] ?? ''));
    }

    private function fillInfoAndButtons(array &$out, array $room): void
    {
        // InfoSeiten links/rechts + Menü-Infos
        $pairs = [
            'InfoLeft' => 'infoleft',
            'InfoLeft2' => 'infoleft2',
            'InfoRight' => 'inforight',
            'InfoRight2' => 'inforight2',
            'InfoMiddleLeft' => 'infomiddleleft',
            'InfoMiddleRight' => 'infomiddleright'
        ];
        $middleProps = ['InfoMiddleLeft', 'InfoMiddleRight'];
        foreach ($pairs as $prop => $key) {
            $id = (int)($room[$prop] ?? 0);
            if ($id > 0 && IPS_VariableExists($id) && !TileVisuLib::isObjectHidden($id)) {
                // fallback color from profile/presentation
                $col = $this->GetColor($id);
                if ($col !== '') {
                    $out[$key . 'color'] = '#' . $col;
                }
                $val = GetValueFormatted($id);
                $out[$key] = $val;
                $out[$key . 'asso'] = $val;
                $typeLocal =  null;
                try { $viTmp = isset($varInfo) ? $varInfo : IPS_GetVariable($id); $typeLocal = $viTmp['VariableType'] ?? null; } catch (Throwable $e) {}
                $isMiddle = in_array($prop, $middleProps, true);
                $defaultShowName = ($typeLocal === 0);
                if ($isMiddle) {
                    $defaultShowName = true;
                }
                // InfoMiddle* verwenden 'ShowName', alle anderen 'NameSwitch'
                $nameSuffix = $isMiddle ? 'ShowName' : 'NameSwitch';
                if ($this->ReadBoolOrDefault($room, $prop . $nameSuffix, $defaultShowName)) {
                    $out[$key . 'name'] = IPS_GetName($id);
                }
                $icon = $this->GetIconAdvanced($id);
                if ($icon !== 'Transparent' && $icon !== '') {
                    $out[$key . 'icon'] = $icon;
                }
                // ShowValue flag (default true for Infos)
                $out[$key . 'showvalue'] = $this->ReadBoolOrDefault($room, $prop . 'ShowValue', true);
                // InfoMiddle* verwenden 'ShowIcon', alle anderen 'IconSwitch'
                $defaultIcon = $isMiddle;
                $iconSuffix = $isMiddle ? 'ShowIcon' : 'IconSwitch';
                $out[$key . 'showicon'] = $this->ReadBoolOrDefault($room, $prop . $iconSuffix, $defaultIcon);
            }
        }

        for ($i = 1; $i <= 5; $i++) {
            $prop = 'Info' . $i;
            $id = (int)($room[$prop] ?? 0);
            if ($id > 0 && IPS_VariableExists($id) && !TileVisuLib::isObjectHidden($id)) {
                $key = 'info' . $i;
                // fallback color from profile/presentation
                try {
                    $col = $this->GetColor($id);
                    if ($col !== '') {
                        $out[$key . 'color'] = '#' . $col;
                    }
                } catch (Throwable $e) {}
                // Meta: konfiguriert, Typ, Action vorhanden
                try {
                    $varInfo = IPS_GetVariable($id);
                    $out[$key . 'configured'] = true;
                    if (isset($varInfo['VariableType'])) {
                        $out[$key . 'type'] = $varInfo['VariableType'];
                    }
                    $actionId = 0;
                    if (isset($varInfo['VariableCustomAction']) && $varInfo['VariableCustomAction'] > 0) {
                        $actionId = $varInfo['VariableCustomAction'];
                    } elseif (isset($varInfo['VariableAction']) && $varInfo['VariableAction'] > 0) {
                        $actionId = $varInfo['VariableAction'];
                    }
                    $hasValidAction = ($actionId > 0) && (@IPS_InstanceExists($actionId) || @IPS_ScriptExists($actionId));
                    $out[$key . 'hasaction'] = $hasValidAction;
                } catch (Throwable $e) {
                    // ignore
                }
                // aktueller Rohwert (für Active-State)
                try {
                    $out[$key . 'value'] = GetValue($id);
                } catch (Throwable $e) {
                    // ignore
                }
                // Assoziationen für Integer/String bereitstellen
                try {
                    $vi = isset($varInfo) ? $varInfo : IPS_GetVariable($id);
                    if (isset($vi['VariableType'])) {
                        if ($vi['VariableType'] === 1) { // INTEGER
                            $opts = TileVisuLib::getIntegerAssociations($id);
                            if (!empty($opts)) { $out[$key . 'options'] = $opts; }
                        } elseif ($vi['VariableType'] === 3) { // STRING
                            $opts = TileVisuLib::getStringAssociations($id);
                            if (!empty($opts)) { $out[$key . 'options'] = $opts; }
                        }
                    }
                } catch (Throwable $e) {
                    // ignore
                }
                $val = GetValueFormatted($id);
                $out[$key] = $val;
                $out[$key . 'asso'] = $val;
                if ($this->ReadBoolOrDefault($room, $prop . 'NameSwitch', false)) {
                    $out[$key . 'name'] = IPS_GetName($id);
                }
                $icon = $this->GetIconAdvanced($id);
                if ($icon !== 'Transparent' && $icon !== '') {
                    $out[$key . 'icon'] = $icon;
                }
                $out[$key . 'showvalue'] = $this->ReadBoolOrDefault($room, $prop . 'ShowValue', true);
            }
        }

        for ($i = 1; $i <= 5; $i++) {
            $prop = 'Switch' . $i;
            $id = (int)($room[$prop] ?? 0);
            if ($id > 0 && IPS_VariableExists($id) && !TileVisuLib::isObjectHidden($id)) {
                $sKey = 'schalter' . $i; // Frontend erwartet 'schalter' Prefix
                $eKey = 'switch' . $i;    // Einige Keys nutzen 'switch' Prefix
                // aktueller Rohwert (für Active-State im Frontend)
                try {
                    $out[$sKey . 'value'] = GetValue($id);
                } catch (Throwable $e) {
                    // ignore
                }
                // Markiere als konfiguriert und liefere Variablentyp
                try {
                    $varInfo = IPS_GetVariable($id);
                    $out[$sKey . 'configured'] = true;
                    if (isset($varInfo['VariableType'])) {
                        $out[$sKey . 'type'] = $varInfo['VariableType'];
                    }
                    $actionId = 0;
                    if (isset($varInfo['VariableCustomAction']) && $varInfo['VariableCustomAction'] > 0) {
                        $actionId = $varInfo['VariableCustomAction'];
                    } elseif (isset($varInfo['VariableAction']) && $varInfo['VariableAction'] > 0) {
                        $actionId = $varInfo['VariableAction'];
                    }
                    $hasValidAction = ($actionId > 0) && (@IPS_InstanceExists($actionId) || @IPS_ScriptExists($actionId));
                    $out[$sKey . 'hasaction'] = $hasValidAction;
                    if (isset($varInfo['VariableType']) && $varInfo['VariableType'] === 0) {
                        $btnColors = $this->GetButtonColors($id);
                        if ($btnColors['on'] !== '') {
                            $out[$sKey . 'colorOn'] = $btnColors['on'];
                        }
                        if ($btnColors['off'] !== '') {
                            $out[$sKey . 'colorOff'] = $btnColors['off'];
                        }
                    }
                } catch (Throwable $e) {
                    // ignore
                }
                // IconSwitch-Flag (Frontend erwartet 'switch' Prefix)
                try {
                    $out[$eKey . 'iconswitch'] = $this->ReadBoolOrDefault($room, $prop . 'IconSwitch', false);
                } catch (Throwable $e) {
                    // ignore
                }
                // ShowValue flag (Frontend erwartet 'switch' Prefix)
                try {
                    $out[$eKey . 'showvalue'] = $this->ReadBoolOrDefault($room, $prop . 'ShowValue', false);
                } catch (Throwable $e) {
                    // ignore
                }
                // ShowName flag for labels in multi-button groups
                try {
                    $out[$sKey . 'showname'] = $this->ReadBoolOrDefault($room, $prop . 'NameSwitch', false);
                } catch (Throwable $e) {
                    // ignore
                }
                // Assoziationen für Integer-/String-Variablen bereitstellen
                try {
                    $varInfo = isset($varInfo) ? $varInfo : IPS_GetVariable($id);
                    if (isset($varInfo['VariableType'])) {
                        if ($varInfo['VariableType'] === 1) { // INTEGER
                            $opts = TileVisuLib::getIntegerAssociations($id);
                            if (!empty($opts)) { $out[$sKey . 'options'] = $opts; }
                        } elseif ($varInfo['VariableType'] === 3) { // STRING
                            $opts = TileVisuLib::getStringAssociations($id);
                            if (!empty($opts)) { $out[$sKey . 'options'] = $opts; }
                        }
                    }
                } catch (Throwable $e) {
                    // ignore
                }
                $val = GetValueFormatted($id);
                $out[$sKey] = $val;
                $out[$sKey . 'asso'] = $val;
                if ($this->ReadBoolOrDefault($room, $prop . 'NameSwitch', false)) {
                    $out[$sKey . 'name'] = IPS_GetName($id);
                }
                $icon = $this->GetIconAdvanced($id);
                if ($icon !== 'Transparent' && $icon !== '') {
                    $out[$sKey . 'icon'] = $icon;
                }
                // ShowIcon flag
                $out[$sKey . 'showicon'] = $this->ReadBoolOrDefault($room, $prop . 'IconSwitch', true);
            }
        }
    }

    private function getRooms(): array
    {
        
        $room = [];
        
        $room['RoomName'] = (string)$this->ReadPropertyString('RoomName');
        $room['Target'] = (int)$this->ReadPropertyInteger('Target');
        $room['TargetLinkId'] = (int)$this->ReadPropertyInteger('TargetLinkId');
        $room['TargetLinkValue'] = (int)$this->ReadPropertyInteger('TargetLinkValue');
        $room['BackgroundImageUrl'] = (int)$this->ReadPropertyInteger('BackgroundImageUrl');
        $room['BackgroundImage'] = (int)$this->ReadPropertyInteger('BackgroundImage');
        $room['BackgroundImage2'] = (int)$this->ReadPropertyInteger('BackgroundImage2');
        // Hintergrund-Filtersteuerung
        $room['LightStatus'] = (int)$this->ReadPropertyInteger('LightStatus');
        $room['DimValue'] = (int)$this->ReadPropertyInteger('DimValue');
        // Bildfilter-Parameter
        $room['BgFilterBrightnessMin'] = (float)$this->ReadPropertyFloat('BgFilterBrightnessMin');
        $room['BgFilterBrightnessMax'] = (float)$this->ReadPropertyFloat('BgFilterBrightnessMax');
        $room['BgFilterContrastMin']   = (float)$this->ReadPropertyFloat('BgFilterContrastMin');
        $room['BgFilterContrastMax']   = (float)$this->ReadPropertyFloat('BgFilterContrastMax');
        $room['BgFilterGrayscaleMin']  = (float)$this->ReadPropertyFloat('BgFilterGrayscaleMin');
        $room['BgFilterGrayscaleMax']  = (float)$this->ReadPropertyFloat('BgFilterGrayscaleMax');
        
        $room['ImageTransparency'] = (float)$this->ReadPropertyFloat('ImageTransparency');
        $room['TileBackgroundColor'] = (int)$this->ReadPropertyInteger('TileBackgroundColor');
        $room['RoomNameFontColor'] = (int)$this->ReadPropertyInteger('RoomNameFontColor');
        $room['RoomNameFontSize'] = (int)$this->ReadPropertyInteger('RoomNameFontSize');
        $room['InfoFontSize'] = (int)$this->ReadPropertyInteger('InfoFontSize');
        $room['InfoFontColor'] = (int)$this->ReadPropertyInteger('InfoFontColor');
        $room['MenuFontSize'] = (int)$this->ReadPropertyInteger('MenuFontSize');
        $room['MenuFontColor'] = (int)$this->ReadPropertyInteger('MenuFontColor');
        $room['MenuTransparency'] = (float)$this->ReadPropertyFloat('MenuTransparency');
        $room['MenuBackgroundColor'] = (int)$this->ReadPropertyInteger('MenuBackgroundColor');
        $room['UseImageColorsForButtons'] = (bool)$this->ReadPropertyBoolean('UseImageColorsForButtons');
        
        $room['InfoLeft'] = (int)$this->ReadPropertyInteger('InfoLeft');
        $room['InfoLeftNameSwitch'] = (bool)$this->ReadPropertyBoolean('InfoLeftNameSwitch');
        $room['InfoLeftIconSwitch'] = (bool)$this->ReadPropertyBoolean('InfoLeftIconSwitch');
        $room['InfoLeftShowValue'] = (bool)$this->ReadPropertyBoolean('InfoLeftShowValue');
        $room['InfoLeftAltName'] = (string)$this->ReadPropertyString('InfoLeftAltName');
        $room['InfoLeft2'] = (int)$this->ReadPropertyInteger('InfoLeft2');
        $room['InfoLeft2NameSwitch'] = (bool)$this->ReadPropertyBoolean('InfoLeft2NameSwitch');
        $room['InfoLeft2IconSwitch'] = (bool)$this->ReadPropertyBoolean('InfoLeft2IconSwitch');
        $room['InfoLeft2ShowValue'] = (bool)$this->ReadPropertyBoolean('InfoLeft2ShowValue');
        $room['InfoLeft2AltName'] = (string)$this->ReadPropertyString('InfoLeft2AltName');
        $room['InfoRight'] = (int)$this->ReadPropertyInteger('InfoRight');
        $room['InfoRightNameSwitch'] = (bool)$this->ReadPropertyBoolean('InfoRightNameSwitch');
        $room['InfoRightIconSwitch'] = (bool)$this->ReadPropertyBoolean('InfoRightIconSwitch');
        $room['InfoRightShowValue'] = (bool)$this->ReadPropertyBoolean('InfoRightShowValue');
        $room['InfoRightAltName'] = (string)$this->ReadPropertyString('InfoRightAltName');
        $room['InfoRight2'] = (int)$this->ReadPropertyInteger('InfoRight2');
        $room['InfoRight2NameSwitch'] = (bool)$this->ReadPropertyBoolean('InfoRight2NameSwitch');
        $room['InfoRight2IconSwitch'] = (bool)$this->ReadPropertyBoolean('InfoRight2IconSwitch');
        $room['InfoRight2ShowValue'] = (bool)$this->ReadPropertyBoolean('InfoRight2ShowValue');
        $room['InfoRight2AltName'] = (string)$this->ReadPropertyString('InfoRight2AltName');
        $room['InfoMiddleLeft'] = (int)$this->ReadPropertyInteger('InfoMiddleLeft');
        $room['InfoMiddleLeftShowName'] = (bool)$this->ReadPropertyBoolean('InfoMiddleLeftShowName');
        $room['InfoMiddleLeftShowIcon'] = (bool)$this->ReadPropertyBoolean('InfoMiddleLeftShowIcon');
        $room['InfoMiddleLeftShowValue'] = (bool)$this->ReadPropertyBoolean('InfoMiddleLeftShowValue');
        $room['InfoMiddleRight'] = (int)$this->ReadPropertyInteger('InfoMiddleRight');
        $room['InfoMiddleRightShowName'] = (bool)$this->ReadPropertyBoolean('InfoMiddleRightShowName');
        $room['InfoMiddleRightShowIcon'] = (bool)$this->ReadPropertyBoolean('InfoMiddleRightShowIcon');
        $room['InfoMiddleRightShowValue'] = (bool)$this->ReadPropertyBoolean('InfoMiddleRightShowValue');

        $room['MenuSwitch'] = (bool)$this->ReadPropertyBoolean('MenuSwitch');
        $room['SwitchAlignment'] = (string)$this->ReadPropertyString('SwitchAlignment');
        $room['SwitchDistribute'] = (bool)$this->ReadPropertyBoolean('SwitchDistribute');
        for ($i = 1; $i <= 5; $i++) {
            $room['Switch' . $i] = (int)$this->ReadPropertyInteger('Switch' . $i);
            $room['Switch' . $i . 'NameSwitch'] = (bool)$this->ReadPropertyBoolean('Switch' . $i . 'NameSwitch');
            $room['Switch' . $i . 'IconSwitch'] = (bool)$this->ReadPropertyBoolean('Switch' . $i . 'IconSwitch');
            $room['Switch' . $i . 'ShowValue'] = (bool)$this->ReadPropertyBoolean('Switch' . $i . 'ShowValue');
            $room['Switch' . $i . 'AltName'] = (string)$this->ReadPropertyString('Switch' . $i . 'AltName');
            $room['Switch' . $i . 'Width'] = (int)$this->ReadPropertyInteger('Switch' . $i . 'Width');
            $room['Switch' . $i . 'FullWidth'] = (bool)$this->ReadPropertyBoolean('Switch' . $i . 'FullWidth');
        }
        return [$room];
    }

    private function isConfiguredTargetLinkPair(int $linkId, int $targetId): bool
    {
        if ($linkId <= 0 || $targetId <= 0) {
            return false;
        }

        return $linkId === (int)$this->ReadPropertyInteger('TargetLinkId')
            && $targetId === (int)$this->ReadPropertyInteger('TargetLinkValue');
    }

    private function computeBgFilterValue(int $boolId, int $dimId): float
    {
        $hasBool = $boolId > 0 && @IPS_VariableExists($boolId);
        $hasDim = $dimId > 0 && @IPS_VariableExists($dimId);
        $boolVal = false;
        $dimPercent = 0.0;
        if ($hasBool) {
            try { $boolVal = (bool)@GetValue($boolId); } catch (Throwable $e) {}
        }
        if ($hasDim) {
            try {
                $raw = (float)@GetValue($dimId);
                $min = 0.0; $max = 100.0;
                $vi = @IPS_GetVariable($dimId);
                if (is_array($vi)) {
                    $prof = '';
                    if (!empty($vi['VariableCustomProfile'])) { $prof = (string)$vi['VariableCustomProfile']; }
                    elseif (!empty($vi['VariableProfile'])) { $prof = (string)$vi['VariableProfile']; }
                    if ($prof !== '') {
                        $vp = @IPS_GetVariableProfile($prof);
                        if (is_array($vp)) {
                            if (isset($vp['MinValue'])) { $min = (float)$vp['MinValue']; }
                            if (isset($vp['MaxValue'])) { $max = (float)$vp['MaxValue']; }
                        }
                    }
                }
                if (!($max > $min)) {
                    if ($raw >= 0.0 && $raw <= 1.0) { $min = 0.0; $max = 1.0; }
                    elseif ($raw >= 0.0 && $raw <= 255.0) { $min = 0.0; $max = 255.0; }
                    elseif ($raw >= 0.0 && $raw <= 65535.0) { $min = 0.0; $max = 65535.0; }
                }
                $norm = ($max > $min) ? (($raw - $min) / ($max - $min)) : 0.0;
                if ($norm < 0.0) { $norm = 0.0; }
                if ($norm > 1.0) { $norm = 1.0; }
                $dimPercent = $norm * 100.0;
            } catch (Throwable $e) {}
        }
        $pOut = 0.0;
        if ($hasBool) {
            if (!$boolVal) { $pOut = 100.0; }
            else if ($hasDim) { $pOut = 100.0 - $dimPercent; }
        } elseif ($hasDim) {
            $pOut = 100.0 - $dimPercent;
        } else {
            // Wenn weder Bool noch Dim vorhanden: Standardfilter 50%
            $pOut = 50.0;
        }
        if ($pOut < 0.0) { $pOut = 0.0; }
        if ($pOut > 100.0) { $pOut = 100.0; }
        return $pOut;
    }

    private function computeBgFadeValue(int $boolId, int $dimId): float
    {
        $hasBool = $boolId > 0 && @IPS_VariableExists($boolId);
        $hasDim = $dimId > 0 && @IPS_VariableExists($dimId);
        $boolVal = true; // default: an
        $dimPercent = 0.0;
        if ($hasBool) {
            try { $boolVal = (bool)@GetValue($boolId); } catch (Throwable $e) {}
        }
        if ($hasDim) {
            try {
                $raw = (float)@GetValue($dimId);
                $min = 0.0; $max = 100.0;
                $vi = @IPS_GetVariable($dimId);
                if (is_array($vi)) {
                    $prof = '';
                    if (!empty($vi['VariableCustomProfile'])) { $prof = (string)$vi['VariableCustomProfile']; }
                    elseif (!empty($vi['VariableProfile'])) { $prof = (string)$vi['VariableProfile']; }
                    if ($prof !== '') {
                        $vp = @IPS_GetVariableProfile($prof);
                        if (is_array($vp)) {
                            if (isset($vp['MinValue'])) { $min = (float)$vp['MinValue']; }
                            if (isset($vp['MaxValue'])) { $max = (float)$vp['MaxValue']; }
                        }
                    }
                }
                if (!($max > $min)) {
                    if ($raw >= 0.0 && $raw <= 1.0) { $min = 0.0; $max = 1.0; }
                    elseif ($raw >= 0.0 && $raw <= 255.0) { $min = 0.0; $max = 255.0; }
                    elseif ($raw >= 0.0 && $raw <= 65535.0) { $min = 0.0; $max = 65535.0; }
                }
                $norm = ($max > $min) ? (($raw - $min) / ($max - $min)) : 0.0;
                if ($norm < 0.0) { $norm = 0.0; }
                if ($norm > 1.0) { $norm = 1.0; }
                $dimPercent = $norm * 100.0;
            } catch (Throwable $e) {}
        }
        // Fade-Logik: Bild1 wird transparenter, wenn Licht aus bzw. je nach DimValue
        if ($hasBool && $boolVal === false) {
            return 100.0;
        }
        if ($hasDim) {
            // 100% (hell) => 0% Transparency, 0% (aus) => 100% Transparency
            $fade = 100.0 - $dimPercent;
            if ($fade < 0.0) $fade = 0.0; if ($fade > 100.0) $fade = 100.0;
            return $fade;
        }
        return 0.0;
    }

    // Handle WebHook requests directly in the module (no script target)
    /**
     * Status-Hintergrundfarbe eines Menü-Items als Delta-Eintrag (oder null, wenn useVarColor
     * deaktiviert ist). Spiegelt die Voll-Render-Logik aus buildDynamicMenu: GetColor, für
     * Bool zusätzlich ColorTrue/ColorFalse-Override. Key passt zu dataset.statusbgcolor im Frontend.
     */
    private function buildMenuStatusColorDelta(string $itemId, int $varId): ?array
    {
        if ($varId <= 0 || !@IPS_VariableExists($varId)) {
            return null;
        }
        $useVarColor = false; $colorTrue = -1; $colorFalse = -1; $found = false;
        try {
            $menuList = @json_decode($this->ReadPropertyString('MenuItems'), true);
            if (is_array($menuList)) {
                foreach ($menuList as $row) {
                    if (!is_array($row) || (string)($row['Id'] ?? '') !== $itemId) {
                        continue;
                    }
                    $useVarColor = isset($row['UseVarColor']) ? (bool)$row['UseVarColor'] : false;
                    $colorTrue = isset($row['ColorTrue']) ? (int)$row['ColorTrue'] : -1;
                    $colorFalse = isset($row['ColorFalse']) ? (int)$row['ColorFalse'] : -1;
                    $found = true;
                    break;
                }
            }
        } catch (Throwable $e) {}
        if (!$found || !$useVarColor) {
            return null;
        }
        $statusBgColor = '';
        try {
            $c = (string)$this->GetColor($varId);
            if ($c !== '') {
                $statusBgColor = '#' . $c;
            }
            $vt = null;
            try { $vi = @IPS_GetVariable($varId); if (is_array($vi)) { $vt = $vi['VariableType'] ?? null; } } catch (Throwable $e) {}
            if ($vt === 0 && ($colorTrue !== -1 || $colorFalse !== -1)) {
                $isOn = false;
                try { $isOn = (bool)@GetValue($varId); } catch (Throwable $e) {}
                if ($isOn && $colorTrue !== -1) {
                    $statusBgColor = '#' . sprintf('%06X', $colorTrue);
                } elseif (!$isOn && $colorFalse !== -1) {
                    $statusBgColor = '#' . sprintf('%06X', $colorFalse);
                }
            }
        } catch (Throwable $e) {}
        return ['idx' => 0, 'key' => 'menuitem-' . $itemId . 'statusbgcolor', 'value' => $statusBgColor];
    }

    private function sendMenuItemDelta(string $itemId, int $varId): void
    {
        if ($varId <= 0 || !@IPS_VariableExists($varId)) return;
        $key = 'menuitem-' . $itemId;
        try {
            $val = @GetValue($varId);
            $delta = [];
            $delta[] = ['idx' => 0, 'key' => $key . 'value', 'value' => $val];
            $delta[] = ['idx' => 0, 'key' => $key . 'asso', 'value' => (string)@GetValueFormatted($varId)];
            // also send icon and name like in MessageSink
            try { $icon = (string)$this->GetIconAdvanced($varId); } catch (Throwable $e) { $icon = ''; }
            if ($icon !== '' && $icon !== 'Transparent') {
                $delta[] = ['idx' => 0, 'key' => $key . 'icon', 'value' => $icon];
            }
            // Name-Priorität: AltName > Objektname > Variablenname (nur wenn ShowName=true)
            $altName = ''; $showName = false; $openObjectId = 0; $objectName = '';
            try {
                $menuList = @json_decode($this->ReadPropertyString('MenuItems'), true);
                if (is_array($menuList)) {
                    foreach ($menuList as $row) {
                        if (!is_array($row)) continue;
                        $rid = (string)($row['Id'] ?? '');
                        if ($rid === $itemId) {
                            $altName = (string)($row['AltName'] ?? '');
                            $showName = isset($row['ShowName']) ? (bool)$row['ShowName'] : false;
                            $openObjectId = (int)($row['OpenObjectId'] ?? 0);
                            break;
                        }
                    }
                }
            } catch (Throwable $e) {}
            if ($openObjectId > 0 && @IPS_ObjectExists($openObjectId)) {
                try { $obj = @IPS_GetObject($openObjectId); $objectName = (string)($obj['ObjectName'] ?? ''); } catch (Throwable $e) { $objectName = ''; }
            }
            if ($altName !== '') {
                $delta[] = ['idx' => 0, 'key' => $key . 'name', 'value' => $this->TranslateLabel($altName)];
            } elseif ($openObjectId > 0 && $objectName !== '') {
                $delta[] = ['idx' => 0, 'key' => $key . 'name', 'value' => $objectName];
            } elseif ($showName) {
                try { $nm = (string)@IPS_GetName($varId); } catch (Throwable $e) { $nm = ''; }
                if ($nm !== '') {
                    $delta[] = ['idx' => 0, 'key' => $key . 'name', 'value' => $nm];
                }
            }
            // Status-Hintergrundfarbe (useVarColor) live nachführen — sonst nur Wert/Icon
            $ce = $this->buildMenuStatusColorDelta($itemId, $varId);
            if ($ce !== null) { $delta[] = $ce; }
            $this->UpdateVisualizationValue(json_encode(['delta' => $delta]));
        } catch (Throwable $e) {
            // ignore
        }
    }

}
?>
