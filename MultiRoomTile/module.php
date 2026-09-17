<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/TileVisuLib.php';
require_once __DIR__ . '/../libs/TileVisuRoomHelpers.php';

class MultiRoomTile extends IPSModuleStrict
{
    private const ROOM_TILE_MODULE_ID = '{0E33BB6A-B5A7-4D25-883A-EDF0551DB5C3}';

    /** Globale Einstellungen, die beide Module unter gleichem Namen kennen */
    private const EXPORT_GLOBAL_PROPERTIES = [
        'BorderRadius', 'TransparentStatusColors', 'TransparentMenuStatusColors',
        'GroupMenuInfoElements', 'UseImageColorsForButtons',
        'Default_InfoFontSize', 'Default_InfoFontColor', 'Default_InfoHeight',
        'Default_MenuFontSize', 'Default_MenuFontColor', 'Default_MenuTransparency',
        'Default_MenuBackgroundColor', 'Default_TileBackgroundColor',
        'Default_RoomNameFontSize', 'Default_RoomNameFontColor', 'Default_ImageTransparency',
        'Default_InfoTopTransparency', 'Default_InfoTopBackgroundColor',
        'Default_BgFilterBrightnessMin', 'Default_BgFilterBrightnessMax',
        'Default_BgFilterContrastMin', 'Default_BgFilterContrastMax',
        'Default_BgFilterGrayscaleMin', 'Default_BgFilterGrayscaleMax',
    ];

    /** Felder eines Zimmers, die es auch als Eigenschaft der Room-Tile gibt */
    private const ROOM_FIELDS = [
        'RoomName', 'Target', 'TargetLinkId', 'TargetLinkValue',
        'RoomNameFontColor', 'RoomNameFontSize',
        'InfoTopCentered', 'InfoFontSize', 'InfoFontColor', 'InfoItems',
        'InfoTopTransparency', 'InfoTopBackgroundColor',
        'BackgroundImage', 'BackgroundImage2', 'BackgroundImageUrl', 'ImageTransparency',
        'TileBackgroundColor', 'LightStatus', 'DimValue',
        'BgFilterBrightnessMin', 'BgFilterBrightnessMax',
        'BgFilterContrastMin', 'BgFilterContrastMax',
        'BgFilterGrayscaleMin', 'BgFilterGrayscaleMax',
        'MenuSwitch', 'MenuFontSize', 'MenuFontColor', 'MenuTransparency',
        'MenuBackgroundColor', 'UseImageColorsForButtons', 'TransparentMenuStatusColors',
        'GroupMenuInfoElements', 'SwitchAlignment', 'SwitchDistribute', 'MenuItems',
    ];

    /** Raumfelder, die in der Room-Tile anders heissen */
    private const ROOM_FIELD_PROPERTIES = [
        'InfoTopTransparency'    => 'Default_InfoTopTransparency',
        'InfoTopBackgroundColor' => 'Default_InfoTopBackgroundColor',
    ];

    /** Felder, die als JSON-Liste gespeichert sind */
    private const ROOM_LIST_FIELDS = ['InfoItems', 'MenuItems'];

    /** Info-Center der Room-Tile: Eigenschaft => Bereich in der Infoleiste */
    private const INFO_CENTER_FIELDS = [
        'InfoMiddleLeft'  => 'left',
        'InfoMiddleRight' => 'right',
    ];

    /** Nur im Verbund sinnvoll, gehoert nicht in eine eigenstaendige Kachel */
    private const EXPORT_SKIPPED_ROOM_FIELDS = ['TargetCategoryId'];

    /** "-1" heisst hier "globale Einstellung verwenden" */
    private const EXPORT_COLOR_FIELDS = [
        'TileBackgroundColor', 'InfoFontColor', 'MenuFontColor',
        'RoomNameFontColor', 'MenuBackgroundColor', 'InfoTopBackgroundColor',
    ];

    /** 0 oder kleiner heisst hier "globale Einstellung verwenden" */
    private const EXPORT_SIZE_FIELDS = ['InfoFontSize', 'MenuFontSize', 'RoomNameFontSize'];

    /** Negativ heisst hier "globale Einstellung verwenden" */
    private const EXPORT_PERCENT_FIELDS = [
        'MenuTransparency', 'ImageTransparency', 'InfoTopTransparency',
        'BgFilterBrightnessMin', 'BgFilterBrightnessMax',
        'BgFilterContrastMin', 'BgFilterContrastMax',
        'BgFilterGrayscaleMin', 'BgFilterGrayscaleMax',
    ];

    use TileVisuRoomHelpers;

    public function GetVisualizationTile(): string
    {
        return $this->renderVisualizationTile(__DIR__);
    }

    protected function ProcessHookData(): void
    {
        $this->deliverImageFromHook(false);
    }

    public function Create(): void
    {
        parent::Create();

        // Grid globale Einstellungen
        $this->RegisterPropertyInteger('MinWidth', 300);
        $this->RegisterPropertyInteger('MinHeight', 220);
        $this->RegisterPropertyInteger('Gap', 12);
        $this->RegisterPropertyInteger('BorderRadius', 10);
        $this->RegisterPropertyInteger('Columns', 0);
        $this->RegisterPropertyBoolean('UseFullTileHeight', false);
        $this->RegisterPropertyInteger('CustomMargin', -1);
        // optionale globale Defaults
        $this->RegisterPropertyInteger('Default_InfoFontSize', 14);
        $this->RegisterPropertyInteger('Default_InfoFontColor', 0xFFFFFF);
        // Höhe der Infoleiste (Row-Top) in Pixeln
        $this->RegisterPropertyInteger('Default_InfoHeight', 24);
        $this->RegisterPropertyInteger('Default_MenuFontSize', 14);
        $this->RegisterPropertyInteger('Default_MenuFontColor', 0xFFFFFF);
        $this->RegisterPropertyFloat('Default_MenuTransparency', 30.0);
        $this->RegisterPropertyInteger('Default_MenuBackgroundColor', 0x000000);
        $this->RegisterPropertyInteger('Default_TileBackgroundColor', 0x000000);
        $this->RegisterPropertyInteger('Default_RoomNameFontSize', 45);
        $this->RegisterPropertyInteger('Default_RoomNameFontColor', 0xFFFFFF);
        $this->RegisterPropertyFloat('Default_ImageTransparency', 70.0);
        // Info-Top Badge Hintergrund (einheitlich)
        $this->RegisterPropertyFloat('Default_InfoTopTransparency', 30.0);
        $this->RegisterPropertyInteger('Default_InfoTopBackgroundColor', 0x000000);
        // Transparency bei Statusfarben (Infoleiste)
        $this->RegisterPropertyBoolean('TransparentStatusColors', true);
        $this->RegisterPropertyBoolean('UseImageColorsForButtons', false);
        $this->RegisterPropertyBoolean('TransparentMenuStatusColors', true);
        $this->RegisterPropertyBoolean('GroupMenuInfoElements', false);
        // Image filter defaults (brightness/contrast/grayscale ranges)
        $this->RegisterPropertyFloat('Default_BgFilterBrightnessMin', 0.2);
        $this->RegisterPropertyFloat('Default_BgFilterBrightnessMax', 1.0);
        $this->RegisterPropertyFloat('Default_BgFilterContrastMin', 0.9);
        $this->RegisterPropertyFloat('Default_BgFilterContrastMax', 1.0);
        $this->RegisterPropertyFloat('Default_BgFilterGrayscaleMin', 0.0);
        $this->RegisterPropertyFloat('Default_BgFilterGrayscaleMax', 0.5);

        // Räume als JSON-Array
        $this->RegisterPropertyString('Rooms', '[]');

        // internes Mapping VarID -> { idx, prop }
        $this->RegisterAttributeString('VarMap', '{}');
        $this->RegisterAttributeString('HiddenMap', '{}');
        $this->RegisterAttributeString('HookToken', '');

        // HTML Kachel
        $this->SetVisualizationType(1);

        // Register for kernel ready to (re)register WebHook
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
                if (!is_array($element)) continue;
                if (($element['type'] ?? '') === 'ExpansionPanel' && ($element['caption'] ?? '') === 'Rooms') {
                    foreach ($element['items'] as &$roomsItem) {
                        if (!is_array($roomsItem)) continue;
                        if (($roomsItem['type'] ?? '') === 'List' && ($roomsItem['name'] ?? '') === 'Rooms') {
                            if (isset($roomsItem['form']) && is_array($roomsItem['form'])) {
                                foreach ($roomsItem['form'] as &$subPanel) {
                                    if (!is_array($subPanel)) continue;
                                    if (($subPanel['type'] ?? '') === 'ExpansionPanel' && ($subPanel['caption'] ?? '') === 'RoomName') {
                                        foreach ($subPanel['items'] as &$row) {
                                            if (!is_array($row)) continue;
                                            if (($row['type'] ?? '') === 'RowLayout' && isset($row['items']) && is_array($row['items'])) {
                                                foreach ($row['items'] as &$ctrl) {
                                                    if (is_array($ctrl) && ($ctrl['type'] ?? '') === 'SelectObject' && ($ctrl['name'] ?? '') === 'Target') {
                                                        $row['visible'] = $supportsSelectObject;
                                                        break;
                                                    }
                                                }
                                                unset($ctrl);
                                            }
                                        }
                                        unset($row);
                                    }
                                    if (($subPanel['type'] ?? '') === 'ExpansionPanel' && ($subPanel['caption'] ?? '') === 'Menu') {
                                        foreach ($subPanel['items'] as &$mi) {
                                            if (!is_array($mi)) continue;
                                            if (($mi['type'] ?? '') === 'List' && ($mi['name'] ?? '') === 'MenuItems' && isset($mi['columns']) && is_array($mi['columns'])) {
                                                foreach ($mi['columns'] as &$col) {
                                                    if (!is_array($col)) continue;
                                                    $n = (string)($col['name'] ?? '');
                                                    if (in_array($n, ['ShowName','ShowIcon','ShowValue','FullWidth'], true)) {
                                                        $col['visible'] = false;
                                                    }
                                                }
                                                unset($col);
                                            }
                                        }
                                        unset($mi);
                                    }
                                }
                                unset($subPanel);
                            }
                        }
                    }
                    unset($roomsItem);
                }
            }
            unset($element);
            $this->fillExportRoomOptions($form);
        }
        return json_encode($form);
    }

    /**
     * Fuellt die Auswahl im Export-Bereich mit den vorhandenen Zimmern.
     */
    private function fillExportRoomOptions(array &$form): void
    {
        $rooms = @json_decode($this->ReadPropertyString('Rooms'), true);
        $options = [];
        if (is_array($rooms)) {
            foreach ($rooms as $index => $room) {
                if (!is_array($room)) continue;
                $name = trim((string)($room['RoomName'] ?? ''));
                if ($name === '') {
                    $name = 'Raum ' . ((int)$index + 1);
                }
                $options[] = ['caption' => $name, 'value' => (int)$index];
            }
        }
        if (empty($options)) {
            $options[] = ['caption' => '-', 'value' => -1];
        }
        foreach ($form['elements'] as &$element) {
            if (!is_array($element) || ($element['type'] ?? '') !== 'ExpansionPanel' || ($element['caption'] ?? '') !== 'Export') {
                continue;
            }
            foreach ($element['items'] as &$item) {
                if (is_array($item) && ($item['name'] ?? '') === 'ExportRoomIndex') {
                    $item['options'] = $options;
                    $item['value'] = (int)$options[0]['value'];
                }
            }
            unset($item);
        }
        unset($element);
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

        // Ensure hidden IDs are auto-assigned once (per room)
        if ($this->NormalizeRoomLists()) {
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
        $watchProps = ['LightStatus','DimValue'];

        foreach ($rooms as $idx => $room) {
            // Dynamische Info-Items
            $infoList = $this->parseRoomList($room['InfoItems'] ?? []);
            $hasDynamicInfo = false;
            foreach ($infoList as $row) {
                if (!is_array($row)) continue;
                $varId = (int)($row['VariableId'] ?? 0);
                $itemId = (string)($row['Id'] ?? '');
                if ($varId > 0 && $itemId !== '' && @IPS_VariableExists($varId)) {
                    $hasDynamicInfo = true;
                    $this->RegisterReference($varId);
                    $this->RegisterMessage($varId, VM_UPDATE);
                    $this->RegisterMessage($varId, OM_CHANGEHIDDEN);
                    if (!isset($varMap[$varId]) || !is_array($varMap[$varId])) {
                        $varMap[$varId] = [];
                    }
                    $varMap[$varId][] = ['idx' => $idx, 'prop' => 'infoitem:' . $itemId];
                }
            }

            // Dynamische Menü-Items
            $menuList = $this->parseRoomList($room['MenuItems'] ?? []);
            $hasDynamicMenu = false;
            foreach ($menuList as $row) {
                if (!is_array($row)) continue;
                $varId = (int)($row['VariableId'] ?? 0);
                $itemId = (string)($row['Id'] ?? '');
                if ($varId > 0 && $itemId !== '' && @IPS_VariableExists($varId)) {
                    $hasDynamicMenu = true;
                    $this->RegisterReference($varId);
                    $this->RegisterMessage($varId, VM_UPDATE);
                    $this->RegisterMessage($varId, OM_CHANGEHIDDEN);
                    if (!isset($varMap[$varId]) || !is_array($varMap[$varId])) {
                        $varMap[$varId] = [];
                    }
                    $varMap[$varId][] = ['idx' => $idx, 'prop' => 'menuitem:' . $itemId];
                }
                $openObjectId = (int)($row['OpenObjectId'] ?? 0);
                if ($openObjectId > 0 && @IPS_ObjectExists($openObjectId)) {
                    $this->RegisterReference($openObjectId);
                    $this->RegisterMessage($openObjectId, OM_CHANGEHIDDEN);
                }
                // SceneControl ActiveScene tracking
                $sceneControlId = (int)($row['SceneControlId'] ?? 0);
                if ($sceneControlId > 0 && $itemId !== '' && @IPS_InstanceExists($sceneControlId)) {
                    $hasDynamicMenu = true;
                    $this->RegisterReference($sceneControlId);
                    $this->RegisterMessage($sceneControlId, OM_CHANGEHIDDEN);
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
                        if (!isset($varMap[$activeVar]) || !is_array($varMap[$activeVar])) {
                            $varMap[$activeVar] = [];
                        }
                        $varMap[$activeVar][] = ['idx' => $idx, 'prop' => 'menuitem:' . $itemId];
                    }
                }
            }

            if (!$hasDynamicInfo) {
                $staticInfoProps = ['InfoLeft','InfoLeft2','InfoRight','InfoRight2','Info1','Info2','Info3','Info4','Info5'];
                foreach ($staticInfoProps as $prop) {
                    if (!isset($room[$prop])) {
                        continue;
                    }
                    $id = (int)$room[$prop];
                    if ($id > 0 && IPS_VariableExists($id)) {
                        $this->RegisterReference($id);
                        $this->RegisterMessage($id, VM_UPDATE);
                        $this->RegisterMessage($id, OM_CHANGEHIDDEN);
                        if (!isset($varMap[$id]) || !is_array($varMap[$id])) {
                            $varMap[$id] = [];
                        }
                        $varMap[$id][] = ['idx' => $idx, 'prop' => $prop];
                    }
                }
            }

            if (!$hasDynamicMenu) {
                $staticSwitchProps = ['Switch1','Switch2','Switch3','Switch4','Switch5'];
                foreach ($staticSwitchProps as $prop) {
                    if (!isset($room[$prop])) {
                        continue;
                    }
                    $id = (int)$room[$prop];
                    if ($id > 0 && IPS_VariableExists($id)) {
                        $this->RegisterReference($id);
                        $this->RegisterMessage($id, VM_UPDATE);
                        $this->RegisterMessage($id, OM_CHANGEHIDDEN);
                        if (!isset($varMap[$id]) || !is_array($varMap[$id])) {
                            $varMap[$id] = [];
                        }
                        $varMap[$id][] = ['idx' => $idx, 'prop' => $prop];
                    }
                }
            }

            foreach ($watchProps as $prop) {
                if (!isset($room[$prop])) {
                    continue;
                }
                $id = (int)$room[$prop];
                if ($id > 0 && IPS_VariableExists($id)) {
                    $this->RegisterReference($id);
                    $this->RegisterMessage($id, VM_UPDATE);
                    $this->RegisterMessage($id, OM_CHANGEHIDDEN);
                    if (!isset($varMap[$id]) || !is_array($varMap[$id])) {
                        $varMap[$id] = [];
                    }
                    $varMap[$id][] = ['idx' => $idx, 'prop' => $prop];
                }
            }
            // BackgroundImage ist Media, nicht Variable -> keine Message

            // TargetLinkId reference
            $tLinkId = (int)($room['TargetLinkId'] ?? 0);
            if ($tLinkId > 0 && @IPS_LinkExists($tLinkId)) {
                $this->RegisterReference($tLinkId);
            }
            $tLinkTarget = (int)($room['TargetLinkValue'] ?? 0);
            if ($tLinkTarget > 0 && @IPS_ObjectExists($tLinkTarget)) {
                $this->RegisterReference($tLinkTarget);
            }

            // Dynamic background image URL variable
            $bgUrlVarId = (int)($room['BackgroundImageUrl'] ?? 0);
            if ($bgUrlVarId > 0 && @IPS_VariableExists($bgUrlVarId)) {
                $this->RegisterReference($bgUrlVarId);
                $this->RegisterMessage($bgUrlVarId, VM_UPDATE);
                if (!isset($varMap[$bgUrlVarId]) || !is_array($varMap[$bgUrlVarId])) {
                    $varMap[$bgUrlVarId] = [];
                }
                $varMap[$bgUrlVarId][] = ['idx' => $idx, 'prop' => 'BackgroundImageUrl'];
            }
        }

        $this->WriteAttributeString('VarMap', json_encode($varMap));

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
        if ($Message !== VM_UPDATE) {
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

            // Dynamic background image URL variable changed → send new image1 + disable filter
            if ($prop === 'BackgroundImageUrl') {
                $url = (string)@GetValue($SenderID);
                $delta[] = ['idx' => $idx, 'key' => 'image1', 'value' => $url];
                $delta[] = ['idx' => $idx, 'key' => 'bgfilter', 'value' => 0.0];
                continue;
            }

            // Spezialfall: LightStatus/DimValue → bgfilter/bgfade (0..100)
            if ($prop === 'LightStatus' || $prop === 'DimValue') {
                $boolId = (int)($room['LightStatus'] ?? 0);
                $dimId = (int)($room['DimValue'] ?? 0);
                $hasBool = $boolId > 0 && @IPS_VariableExists($boolId);
                $hasDim = $dimId > 0 && @IPS_VariableExists($dimId);
                $boolVal = false;
                $dimVal = 0.0;
                if ($hasBool) {
                    try { $boolVal = (bool)@GetValue($boolId); } catch (Throwable $e) { $boolVal = false; }
                }
                if ($hasDim) {
                    try { $dimVal = (float)@GetValue($dimId); } catch (Throwable $e) { $dimVal = 0.0; }
                    if ($dimVal < 0) $dimVal = 0.0; if ($dimVal > 100) $dimVal = 100.0;
                }
                // Gating: Bool=false => Effekt aus, sonst von DimValue abhängig (falls vorhanden)
                $pOut = 0.0;
                if ($hasBool) {
                    if ($boolVal === false) {
                        $pOut = 100.0;
                    } else {
                        if ($hasDim) {
                            $pOut = 100.0 - $dimVal;
                        }
                    }
                } elseif ($hasDim) {
                    $pOut = 100.0 - $dimVal;
                }

                if (!$hasBool && !$hasDim) {
                    // Wenn kein LightStatus/DimValue: Standardfilter nur bei einem Bild
                    $imageID2 = (int)($room['BackgroundImage2'] ?? 0);
                    // Validierung: Ungültige Media-ID ignorieren
                    if ($imageID2 > 0 && !@IPS_MediaExists($imageID2)) { $imageID2 = 0; }
                    $pOut = ($imageID2 > 0) ? 0.0 : 0;
                } else {
                    $imageID2 = (int)($room['BackgroundImage2'] ?? 0);
                    // Validierung: Ungültige Media-ID ignorieren
                    if ($imageID2 > 0 && !@IPS_MediaExists($imageID2)) { $imageID2 = 0; }
                }
                if ($pOut < 0.0) {
                    $pOut = 0.0;
                }
                // Filter deaktivieren wenn URL-Variable aktiv oder Bild 2 konfiguriert
                $bgUrlId = (int)($room['BackgroundImageUrl'] ?? 0);
                if (($bgUrlId > 0 && @IPS_VariableExists($bgUrlId)) || $imageID2 > 0) { $pOut = 0.0; }
                $delta[] = [ 'idx' => $idx, 'key' => 'bgfilter', 'value' => $pOut ];
                // bgfade nur senden wenn zweites Bild konfiguriert ist
                if ($imageID2 > 0) {
                    $fade = 0.0;
                    if ($hasBool && $boolVal === false) { $fade = 100.0; }
                    elseif ($hasDim) { $fade = 100.0 - $dimVal; }
                    if ($fade < 0.0) $fade = 0.0; if ($fade > 100.0) $fade = 100.0;
                    $delta[] = [ 'idx' => $idx, 'key' => 'bgfade', 'value' => $fade ];
                }
                continue;
            }

            if (strpos($prop, 'menuitem:') === 0) {
                $itemId = substr($prop, strlen('menuitem:'));
                $varId = $SenderID;
                if ($varId > 0 && @IPS_VariableExists($varId)) {
                    // Prüfe ob SceneControl: dann Label -> Index mappen
                    $value = @GetValue($varId);
                    $rooms = $this->getRooms();
                    $sceneId = 0;
                    $menuList = $this->parseRoomList($rooms[$idx]['MenuItems'] ?? []);
                    foreach ($menuList as $row) {
                        if (!is_array($row)) continue;
                        $rid = (string)($row['Id'] ?? '');
                        if ($rid === $itemId) { $sceneId = (int)($row['SceneControlId'] ?? 0); break; }
                    }
                    if ($sceneId > 0 && @IPS_InstanceExists($sceneId)) {
                        if (!is_numeric($value)) {
                            $mapped = 0;
                            foreach ((array)@IPS_GetChildrenIDs($sceneId) as $cid) {
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
                        $delta[] = ['idx' => $idx, 'key' => 'menuitem-' . $itemId . 'value', 'value' => $value];
                        $delta[] = ['idx' => $idx, 'key' => 'menuitem-' . $itemId . 'asso', 'value' => (string)@GetValueFormatted($varId)];
                        continue;
                    }
                    // Fallback: Standard-Delta
                    $delta = array_merge($delta, $this->buildMenuItemDelta($idx, $itemId, $varId));
                }
                continue;
            }
            if (strpos($prop, 'infoitem:') === 0) {
                $itemId = substr($prop, strlen('infoitem:'));
                $varId = $SenderID;
                if ($varId > 0 && @IPS_VariableExists($varId)) {
                    $delta = array_merge($delta, $this->buildInfoItemDelta($idx, $itemId, $varId));
                }
                continue;
            }

            if (strpos($prop, 'info:') === 0) {
                $itemId = substr($prop, strlen('info:'));
                $varId = $SenderID;
                if ($varId > 0 && @IPS_VariableExists($varId)) {
                    $delta[] = ['idx' => $idx, 'key' => 'infoitem-' . $itemId . 'asso', 'value' => (string)@GetValueFormatted($varId)];
                }
                continue;
            }

            if (strpos($prop, 'switch:') === 0) {
                $itemId = substr($prop, strlen('switch:'));
                $varId = $SenderID;
                if ($varId > 0 && @IPS_VariableExists($varId)) {
                    $delta[] = ['idx' => $idx, 'key' => 'menuitem-' . $itemId . 'value', 'value' => @GetValue($varId)];
                }
                continue;
            }
        }
        if (!empty($delta)) {
            $this->UpdateVisualizationValue(json_encode(['delta' => $delta]));
        }
        } catch (Throwable $e) {}
    }

    public function RequestAction(string $Ident, mixed $Value): void
    {
        // Reorder Buttons aus der Form
        if ($Ident === 'reorder' && is_string($Value)) {
            $payload = json_decode($Value, true);
            if (!is_array($payload) || !isset($payload['direction'], $payload['index'])) {
                return;
            }
            $this->ReorderRoom((int)$payload['index'], (string)$payload['direction']);
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

        // Menü-Item Aktionen
        if (strpos($Ident, 'menuitem:') === 0) {
            $itemId = substr($Ident, strlen('menuitem:'));
            $rooms = $this->getRooms();
            foreach ($rooms as $idx => $room) {
                $menuList = $this->parseRoomList($room['MenuItems'] ?? []);
                foreach ($menuList as $row) {
                    if (!is_array($row)) continue;
                    $rid = (string)($row['Id'] ?? '');
                    if ($rid === '' || $rid !== $itemId) continue;
                    // SceneControl Aktion
                    $sceneControlId = (int)($row['SceneControlId'] ?? 0);
                    if ($sceneControlId > 0 && @IPS_InstanceExists($sceneControlId)) {
                        try {
                            $target = $Value;
                            if (!is_numeric($target)) {
                                $target = 0;
                                foreach ((array)@IPS_GetChildrenIDs($sceneControlId) as $cid) {
                                    if (!@IPS_VariableExists($cid)) continue;
                                    $o = @IPS_GetObject($cid);
                                    $ident = (string)($o['ObjectIdent'] ?? '');
                                    if (preg_match('/^Scene(\d+)$/i', $ident, $m)) {
                                        $label = (string)($o['ObjectName'] ?? $ident);
                                        if ((string)$label === (string)$Value) { $target = (int)$m[1]; break; }
                                    }
                                }
                            } else {
                                $target = (int)$target;
                            }
                            if ($target > 0) {
                                @SZS_CallScene($sceneControlId, $target);
                                @SZS_UpdateActive($sceneControlId);
                            }
                        } catch (Throwable $e) {}
                        return;
                    }
                    $varId = (int)($row['VariableId'] ?? 0);
                    if ($varId <= 0 || !@IPS_VariableExists($varId)) return;
                    $variable = @IPS_GetVariable($varId);
                    if (!is_array($variable)) return;
                    $vType = $variable['VariableType'] ?? 0;
                    $actionId = 0;
                    if (isset($variable['VariableCustomAction']) && $variable['VariableCustomAction'] > 0) {
                        $actionId = $variable['VariableCustomAction'];
                    } elseif (isset($variable['VariableAction']) && $variable['VariableAction'] > 0) {
                        $actionId = $variable['VariableAction'];
                    }
                    $hasValidAction = ($actionId > 0) && (@IPS_InstanceExists($actionId) || @IPS_ScriptExists($actionId));
                    if (!$hasValidAction) return;

                    if ($vType === 0) {
                        $newValue = ($Value === null) ? !@GetValue($varId) : (bool)$Value;
                    } elseif ($vType === 1) {
                        $newValue = (int)$Value;
                    } elseif ($vType === 2) {
                        $newValue = (float)$Value;
                    } else {
                        $newValue = (string)$Value;
                    }
                    @RequestAction($varId, $newValue);
                    $this->sendMenuItemDelta($idx, $itemId, $varId);
                    return;
                }
            }
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
                    $newValue = (int)$Value;
                } elseif ($vType === 3) { // STRING
                    $newValue = (string)$Value;
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

    /**
     * Exportiert einen Raum aus der Zimmer-Liste in eine eigenstaendige
     * Room-Tile-Instanz unterhalb der Wurzel.
     *
     * Raumfelder mit "-1" bzw. 0 bedeuten "globale Einstellung verwenden". Sie
     * werden beim Export durch den globalen Wert dieser Instanz ersetzt, damit
     * die neue Kachel genauso aussieht wie der Raum im Verbund.
     *
     * @param int $Index Position des Raums in der Zimmer-Liste (0-basiert)
     * @return int InstanceID der neuen Room-Tile-Instanz
     */
    public function ExportRoom(int $Index): int
    {
        $rooms = @json_decode($this->ReadPropertyString('Rooms'), true);
        if (!is_array($rooms) || !isset($rooms[$Index]) || !is_array($rooms[$Index])) {
            throw new Exception('Kein Raum an Position ' . $Index . ' vorhanden');
        }
        $room = $rooms[$Index];

        $name = trim((string)($room['RoomName'] ?? ''));
        if ($name === '') {
            $name = 'Raum ' . ($Index + 1);
        }

        $targetId = IPS_CreateInstance(self::ROOM_TILE_MODULE_ID);
        IPS_SetParent($targetId, 0);
        IPS_SetName($targetId, $name);

        foreach ($this->buildRoomExportProperties($room) as $property => $value) {
            $this->CopyPropertyTo($targetId, $property, $value);
        }
        IPS_ApplyChanges($targetId);

        $this->SendDebug('ExportRoom', 'Raum "' . $name . '" nach Instanz #' . $targetId . ' exportiert', 0);
        return $targetId;
    }

    /**
     * Eigenschaften der neuen Room-Tile-Instanz: zuerst die globalen
     * Einstellungen dieser Instanz, darueber die Werte des Raums.
     */
    private function buildRoomExportProperties(array $room): array
    {
        $defaults = $this->readGlobalDefaults();
        $out = [];

        // Globale Einstellungen, die es in beiden Modulen unter gleichem Namen gibt
        foreach (self::EXPORT_GLOBAL_PROPERTIES as $property) {
            $value = $this->ReadOwnProperty($property);
            if ($value !== null) {
                $out[$property] = $value;
            }
        }

        foreach ($room as $key => $value) {
            if (in_array($key, self::EXPORT_SKIPPED_ROOM_FIELDS, true)) {
                continue;
            }
            if (is_array($value)) {
                // Unterlisten (Info-Elemente, Menue-Eintraege) liegen als JSON vor
                $out[$key] = json_encode($value);
                continue;
            }
            $out[$key] = $value;
        }

        // "-1" bzw. 0 heisst "globale Einstellung" - beim Export den globalen Wert eintragen
        foreach (self::EXPORT_COLOR_FIELDS as $key) {
            if (!isset($defaults[$key])) continue;
            if (!array_key_exists($key, $out) || (int)$out[$key] === -1) {
                $out[$key] = (int)$defaults[$key];
            }
        }
        foreach (self::EXPORT_SIZE_FIELDS as $key) {
            if (!isset($defaults[$key])) continue;
            if (!array_key_exists($key, $out) || (int)$out[$key] <= 0) {
                $out[$key] = (int)$defaults[$key];
            }
        }
        foreach (self::EXPORT_PERCENT_FIELDS as $key) {
            if (!isset($defaults[$key])) continue;
            if (!array_key_exists($key, $out) || (float)$out[$key] < 0) {
                $out[$key] = (float)$defaults[$key];
            }
        }

        return $out;
    }

    /**
     * Schreibt eine Eigenschaft in die Zielinstanz, passend zum dortigen Typ.
     * Eigenschaften, die es dort nicht gibt, werden uebersprungen.
     */
    private function CopyPropertyTo(int $targetId, string $property, mixed $value): void
    {
        try {
            $current = @IPS_GetProperty($targetId, $property);
        } catch (Throwable $e) {
            return;
        }
        if (is_bool($current)) {
            $value = (bool)$value;
        } elseif (is_int($current)) {
            $value = (int)$value;
        } elseif (is_float($current)) {
            $value = (float)$value;
        } elseif (is_string($current)) {
            $value = is_string($value) ? $value : (string)json_encode($value);
        }
        @IPS_SetProperty($targetId, $property, $value);
    }

    /**
     * Eigener Property-Wert oder null, wenn es die Eigenschaft nicht gibt.
     */
    private function ReadOwnProperty(string $property): mixed
    {
        try {
            return @IPS_GetProperty($this->InstanceID, $property);
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Fuellt die Auswahl im Import-Bereich mit allen vorhandenen
     * Room-Tile-Instanzen. Wird ueber den Knopf im Formular ausgeloest.
     *
     * @return string Die gesetzten Optionen als JSON (Name und Pfad je Instanz)
     */
    public function RefreshImportInstances(): string
    {
        $options = [];
        foreach (IPS_GetInstanceListByModuleID(self::ROOM_TILE_MODULE_ID) as $instanceId) {
            $options[] = [
                'caption' => IPS_GetName($instanceId) . ' (' . $this->GetObjectPath($instanceId) . ')',
                'value'   => (int)$instanceId,
            ];
        }
        if (empty($options)) {
            $options[] = ['caption' => '-', 'value' => 0];
        }
        $this->UpdateFormField('ImportInstanceId', 'options', json_encode($options));
        $this->UpdateFormField('ImportInstanceId', 'value', (int)$options[0]['value']);
        return json_encode($options);
    }

    /**
     * Uebernimmt eine eigenstaendige Room-Tile-Instanz als weiteres Zimmer.
     *
     * @param int  $RoomTileID         Room-Tile-Instanz
     * @param bool $IndividualSettings true: Darstellung der Instanz uebernehmen,
     *                                 false: globale Einstellungen dieser Kachel verwenden
     * @param bool $MoveInfoCenter     Info-Center der Instanz als Eintraege in die Infoleiste uebernehmen
     */
    public function ImportRoom(int $RoomTileID, bool $IndividualSettings, bool $MoveInfoCenter): void
    {
        if ($RoomTileID <= 0 || !@IPS_InstanceExists($RoomTileID)) {
            throw new Exception('Instanz #' . $RoomTileID . ' existiert nicht');
        }
        $instance = IPS_GetInstance($RoomTileID);
        if (($instance['ModuleInfo']['ModuleID'] ?? '') !== self::ROOM_TILE_MODULE_ID) {
            throw new Exception('Instanz #' . $RoomTileID . ' ist keine Room-Tile');
        }

        $room = $this->buildRoomFromInstance($RoomTileID, $IndividualSettings, $MoveInfoCenter);

        $rooms = @json_decode($this->ReadPropertyString('Rooms'), true);
        if (!is_array($rooms)) {
            $rooms = [];
        }
        $rooms[] = $room;
        IPS_SetProperty($this->InstanceID, 'Rooms', json_encode($rooms));
        IPS_ApplyChanges($this->InstanceID);

        $this->SendDebug('ImportRoom', 'Instanz #' . $RoomTileID . ' als Zimmer "' . $room['RoomName'] . '" uebernommen', 0);
        $this->ReloadForm();
    }

    /**
     * Zimmer-Zeile aus den Eigenschaften einer Room-Tile-Instanz.
     */
    private function buildRoomFromInstance(int $RoomTileID, bool $IndividualSettings, bool $MoveInfoCenter): array
    {
        $room = [];
        foreach (self::ROOM_FIELDS as $field) {
            $property = self::ROOM_FIELD_PROPERTIES[$field] ?? $field;
            try {
                $value = @IPS_GetProperty($RoomTileID, $property);
            } catch (Throwable $e) {
                continue;
            }
            if (in_array($field, self::ROOM_LIST_FIELDS, true)) {
                $decoded = is_string($value) ? @json_decode($value, true) : $value;
                $room[$field] = is_array($decoded) ? $decoded : [];
                continue;
            }
            $room[$field] = $value;
        }

        $name = trim((string)($room['RoomName'] ?? ''));
        if ($name === '') {
            $name = IPS_GetName($RoomTileID);
        }
        $room['RoomName'] = $name;
        $room['TargetCategoryId'] = 0;
        // Die Room-Tile kennt keinen Schalter dafuer; im Verbund wird der Name gezeigt
        $room['ShowRoomName'] = true;

        if ($MoveInfoCenter) {
            $room['InfoItems'] = array_merge(
                is_array($room['InfoItems'] ?? null) ? $room['InfoItems'] : [],
                $this->BuildInfoCenterItems($RoomTileID)
            );
        }

        if (!$IndividualSettings) {
            // "-1" bzw. 0 heisst in einem Zimmer: globale Einstellung dieser Kachel
            foreach (self::EXPORT_COLOR_FIELDS as $field) {
                if (array_key_exists($field, $room)) {
                    $room[$field] = -1;
                }
            }
            foreach (self::EXPORT_SIZE_FIELDS as $field) {
                if (array_key_exists($field, $room)) {
                    // -1 ist bei allen Groessenfeldern der Platzhalter fuer "global"
                    $room[$field] = -1;
                }
            }
            foreach (self::EXPORT_PERCENT_FIELDS as $field) {
                if (array_key_exists($field, $room)) {
                    $room[$field] = -1.0;
                }
            }
            if (array_key_exists('InfoTopTransparency', $room)) {
                $room['InfoTopTransparency'] = -1.0;
            }
        }

        return $room;
    }

    /**
     * Info-Center-Variablen einer Room-Tile als Eintraege fuer die Infoleiste.
     * Links bleibt links, rechts bleibt rechts.
     */
    private function BuildInfoCenterItems(int $RoomTileID): array
    {
        $items = [];
        foreach (self::INFO_CENTER_FIELDS as $property => $area) {
            try {
                $variableId = (int)@IPS_GetProperty($RoomTileID, $property);
            } catch (Throwable $e) {
                continue;
            }
            if ($variableId <= 0 || !@IPS_VariableExists($variableId)) {
                continue;
            }
            $items[] = [
                'Id'          => $this->NewListId('i'),
                'Area'        => $area,
                'VariableId'  => $variableId,
                'ShowName'    => (bool)$this->ReadForeignFlag($RoomTileID, $property . 'ShowName'),
                'ShowIcon'    => (bool)$this->ReadForeignFlag($RoomTileID, $property . 'ShowIcon'),
                'ShowValue'   => (bool)$this->ReadForeignFlag($RoomTileID, $property . 'ShowValue'),
                'UseVarColor' => false,
                'ColorTrue'   => -1,
                'ColorFalse'  => -1,
                'AltName'     => '',
            ];
        }
        return $items;
    }

    private function ReadForeignFlag(int $RoomTileID, string $property): bool
    {
        try {
            return (bool)@IPS_GetProperty($RoomTileID, $property);
        } catch (Throwable $e) {
            return false;
        }
    }

    private function NewListId(string $prefix): string
    {
        try {
            return $prefix . bin2hex(random_bytes(6));
        } catch (Throwable $e) {
            return $prefix . substr(sha1(uniqid('', true)), 0, 12);
        }
    }

    /**
     * Pfad eines Objekts ohne den eigenen Namen, z.B. "Kacheln\Erdgeschoss".
     */
    private function GetObjectPath(int $objectId): string
    {
        $parts = [];
        $parentId = (int)(IPS_GetObject($objectId)['ParentID'] ?? 0);
        while ($parentId > 0) {
            array_unshift($parts, IPS_GetName($parentId));
            $parentId = (int)(IPS_GetObject($parentId)['ParentID'] ?? 0);
        }
        return empty($parts) ? 'Wurzel' : implode(' \ ', $parts);
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
        'minWidth' => $this->ReadPropertyInteger('MinWidth'),
        'minHeight' => $this->ReadPropertyInteger('MinHeight'),
        'gap' => $this->ReadPropertyInteger('Gap'),
        'borderRadius' => $this->ReadPropertyInteger('BorderRadius'),
        'useImageColorsForButtons' => $this->ReadPropertyBoolean('UseImageColorsForButtons'),
        'transparentStatusColors' => $this->ReadPropertyBoolean('TransparentStatusColors'),
        'transparentMenuStatusColors' => $this->ReadPropertyBoolean('TransparentMenuStatusColors'),
        'groupMenuInfoElements' => $this->ReadPropertyBoolean('GroupMenuInfoElements'),
        'columns' => $this->ReadPropertyInteger('Columns'),
        'useFullTileHeight' => $this->ReadPropertyBoolean('UseFullTileHeight'),
        'customMargin' => $this->ReadPropertyInteger('CustomMargin')
        ];
    }

    private function readGlobalDefaults(): array
    {
        // Lese optionale Defaults aus den separaten Properties und mappe sie auf die Raum-Keys
        // Robust: Infohöhe kann in bestehenden Instanzen noch nicht existieren
        $defInfoHeight = 0;
        try { $defInfoHeight = @($this->ReadPropertyInteger('Default_InfoHeight')); } catch (Throwable $e) {}
        if (!is_int($defInfoHeight) || $defInfoHeight <= 0) {
            // Fallback auf evtl. ältere Schreibweise mit Umlaut; unter Module Strict
            // kann der Lesezugriff auf die unregistrierte Property werfen
            $alt = 0;
            try { $alt = @($this->ReadPropertyInteger('Default_Infohöhe')); } catch (Throwable $e) {}
            if (is_int($alt) && $alt > 0) {
                $defInfoHeight = $alt;
            } else {
                $defInfoHeight = 0;
            }
        }

        $defaults = [
            'InfoFontSize'       => (int)$this->ReadPropertyInteger('Default_InfoFontSize'),
            'InfoFontColor'         => (int)$this->ReadPropertyInteger('Default_InfoFontColor'),
            'InfoHeight'                => (int)$defInfoHeight,
            'MenuFontSize'  => (int)$this->ReadPropertyInteger('Default_MenuFontSize'),
            'MenuFontColor'    => (int)$this->ReadPropertyInteger('Default_MenuFontColor'),
            'MenuTransparency'     => (float)$this->ReadPropertyFloat('Default_MenuTransparency'),
            'MenuBackgroundColor'=> (int)$this->ReadPropertyInteger('Default_MenuBackgroundColor'),
            'TileBackgroundColor'   => (int)$this->ReadPropertyInteger('Default_TileBackgroundColor'),
            'RoomNameFontSize'   => (int)$this->ReadPropertyInteger('Default_RoomNameFontSize'),
            'RoomNameFontColor'     => (int)$this->ReadPropertyInteger('Default_RoomNameFontColor'),
            'ImageTransparency'          => (float)$this->ReadPropertyFloat('Default_ImageTransparency'),
            'InfoTopTransparency'       => (float)$this->ReadPropertyFloat('Default_InfoTopTransparency'),
            'InfoTopBackgroundColor'  => (int)$this->ReadPropertyInteger('Default_InfoTopBackgroundColor'),
            // Image filter defaults
            'BgFilterBrightnessMin'    => (float)$this->ReadPropertyFloat('Default_BgFilterBrightnessMin'),
            'BgFilterBrightnessMax'    => (float)$this->ReadPropertyFloat('Default_BgFilterBrightnessMax'),
            'BgFilterContrastMin'      => (float)$this->ReadPropertyFloat('Default_BgFilterContrastMin'),
            'BgFilterContrastMax'      => (float)$this->ReadPropertyFloat('Default_BgFilterContrastMax'),
            'BgFilterGrayscaleMin'     => (float)$this->ReadPropertyFloat('Default_BgFilterGrayscaleMin'),
            'BgFilterGrayscaleMax'     => (float)$this->ReadPropertyFloat('Default_BgFilterGrayscaleMax')
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
        if (isset($defaults['TileBackgroundColor']) && (int)$defaults['TileBackgroundColor'] === -1) {
            $defaults['TileBackgroundColor'] = 0x000000; // Schwarz
        }
        if (isset($defaults['RoomNameFontColor']) && (int)$defaults['RoomNameFontColor'] === -1) {
            $defaults['RoomNameFontColor'] = 0xFFFFFF; // Weiß
        }
        if (isset($defaults['InfoTopBackgroundColor']) && (int)$defaults['InfoTopBackgroundColor'] === -1) {
            $defaults['InfoTopBackgroundColor'] = 0x000000; // Schwarz
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

        $imcol = null;
        if (array_key_exists('MenuFontColor', $room)) { $imcol = (int)$room['MenuFontColor']; }
        if ($imcol === null || $imcol === -1) { $imcol = (int)($defaults['MenuFontColor'] ?? 0xFFFFFF); }
        $r['menufontcolor'] = $this->toCssHex($imcol);

        // Info-Top Layout: Centered in the middle?
        $r['infotopcentered'] = (bool)($room['InfoTopCentered'] ?? false);

        // Info-Top Badge Hintergrund (einheitlich für links/mitte/rechts)
        // Farbe: zuerst neues Feld, sonst alte per-Side Felder als Fallback
        if (array_key_exists('InfoTopBackgroundColor', $room) && (int)$room['InfoTopBackgroundColor'] !== -1) {
            $topCol = (int)$room['InfoTopBackgroundColor'];
        } elseif (array_key_exists('InfoTopLeftBackgroundColor', $room) && (int)$room['InfoTopLeftBackgroundColor'] !== -1) {
            $topCol = (int)$room['InfoTopLeftBackgroundColor'];
        } elseif (array_key_exists('InfoTopMidBackgroundColor', $room) && (int)$room['InfoTopMidBackgroundColor'] !== -1) {
            $topCol = (int)$room['InfoTopMidBackgroundColor'];
        } elseif (array_key_exists('InfoTopRightBackgroundColor', $room) && (int)$room['InfoTopRightBackgroundColor'] !== -1) {
            $topCol = (int)$room['InfoTopRightBackgroundColor'];
        } else {
            $topCol = (int)($defaults['InfoTopBackgroundColor'] ?? 0x000000);
        }

        // Transparency: zuerst neues Feld, sonst alte per-Side Felder als Fallback
        if (array_key_exists('InfoTopTransparency', $room)) {
            $tmp = (float)$room['InfoTopTransparency'];
            $topAlphaPercent = ($tmp < 0)
                ? $this->normalizePercent((float)($defaults['InfoTopTransparency'] ?? 30.0))
                : $this->normalizePercent($tmp);
        } elseif (array_key_exists('InfoTopLeftTransparency', $room)) {
            $topAlphaPercent = $this->normalizePercent((float)$room['InfoTopLeftTransparency']);
        } elseif (array_key_exists('InfoTopMidTransparency', $room)) {
            $topAlphaPercent = $this->normalizePercent((float)$room['InfoTopMidTransparency']);
        } elseif (array_key_exists('InfoTopRightTransparency', $room)) {
            $topAlphaPercent = $this->normalizePercent((float)$room['InfoTopRightTransparency']);
        } else {
            $topAlphaPercent = $this->normalizePercent((float)($defaults['InfoTopTransparency'] ?? 30.0));
        }
        $topRgba = $this->cssRgba($topCol, $this->percentToAlpha($topAlphaPercent));
        $r['infotopleftbg'] = $topRgba;
        $r['infomidbg'] = $topRgba;
        $r['infotoprightbg'] = $topRgba;

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
        $r['menueimagetransparency'] = $bgAlphaPercent; // For menu status color transparency
        $r['switchalignment'] = (string)($room['SwitchAlignment'] ?? 'left');
        $r['switchdistribute'] = (bool)($room['SwitchDistribute'] ?? false);
        $r['imagetransparency'] = $this->percentToAlpha($this->normalizePercent($this->ReadNumOrDefault($room, 'ImageTransparency', $defaults, 70.0)));

        // Background image filter parameters (merged defaults + per-room overrides)
        $r['bgfilterbrightnessmin'] = (float)$this->ReadNumOrDefault($room, 'BgFilterBrightnessMin', $defaults, 0.2);
        $r['bgfilterbrightnessmax'] = (float)$this->ReadNumOrDefault($room, 'BgFilterBrightnessMax', $defaults, 1.0);
        $r['bgfiltercontrastmin']   = (float)$this->ReadNumOrDefault($room, 'BgFilterContrastMin', $defaults, 0.9);
        $r['bgfiltercontrastmax']   = (float)$this->ReadNumOrDefault($room, 'BgFilterContrastMax', $defaults, 1.0);
        $r['bgfiltergrayscalemin']  = (float)$this->ReadNumOrDefault($room, 'BgFilterGrayscaleMin', $defaults, 0.0);
        $r['bgfiltergrayscalemax']  = (float)$this->ReadNumOrDefault($room, 'BgFilterGrayscaleMax', $defaults, 0.5);

        $r['roomname'] = (string)($room['RoomName'] ?? '');
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
        if ($rncol === null || $rncol === -1) { $rncol = (int)($defaults['RoomNameFontColor'] ?? 0xFFFFFF); }
        $r['roomnamefontcolor'] = $this->toCssHex($rncol);
        // Sichtbarkeit des RoomNamens (Default: true)
        $r['showroomname'] = array_key_exists('ShowRoomName', $room) ? (bool)$room['ShowRoomName'] : true;

        $r['menuswitch'] = (bool)($room['MenuSwitch'] ?? true);

        // Use image colors for buttons per room (fallback to global flag)
        $useImgCol = array_key_exists('UseImageColorsForButtons', $room)
            ? (bool)$room['UseImageColorsForButtons']
            : (bool)$this->ReadPropertyBoolean('UseImageColorsForButtons');
        $r['useimagecolors'] = $useImgCol || $this->ReadPropertyBoolean('UseImageColorsForButtons');

        // Group menu info elements per room (fallback to global flag)
        $r['groupmenuinfoelements'] = array_key_exists('GroupMenuInfoElements', $room)
            ? (bool)$room['GroupMenuInfoElements']
            : (bool)$this->ReadPropertyBoolean('GroupMenuInfoElements');
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

        // Hintergrundfilter aus LightStatus/DimValue
        try {
            $boolId = (int)($room['LightStatus'] ?? 0);
            $dimId = (int)($room['DimValue'] ?? 0);
            $hasBool = $boolId > 0 && IPS_VariableExists($boolId);
            $hasDim = $dimId > 0 && IPS_VariableExists($dimId);
            $boolVal = false;
            $dimVal = 0.0;
            if ($hasBool) { try { $boolVal = (bool)@GetValue($boolId); } catch (Throwable $e) { $boolVal = false; } }
            if ($hasDim) { try { $dimVal = (float)@GetValue($dimId); } catch (Throwable $e) { $dimVal = 0.0; } }
            if ($dimVal < 0) {
                $dimVal = 0.0;
            } elseif ($dimVal > 100) {
                $dimVal = 100.0;
            }

            $pOut = 0.0;
            if ($hasBool) {
                if ($boolVal === false) {
                    $pOut = 100.0;
                } else {
                    if ($hasDim) {
                        $pOut = 100.0 - $dimVal;
                    }
                }
            } elseif ($hasDim) {
                $pOut = 100.0 - $dimVal;
            }

            if (!$hasBool && !$hasDim) {
                // Wenn kein LightStatus/DimValue: Standardfilter nur bei einem Bild
                $pOut = ($imageID2 > 0) ? 0.0 : 0.0;
            }
            if ($pOut < 0.0) {
                $pOut = 0.0;
            }
            // Filter deaktivieren wenn URL-Variable aktiv oder Bild 2 konfiguriert
            if ($bgUrlActive || $imageID2 > 0) { $pOut = 0.0; }
            $r['bgfilter'] = $pOut;
            // bgfade nur senden wenn zweites Bild konfiguriert ist (und keine URL-Variable)
            if (!$bgUrlActive && $imageID2 > 0) {
                $fade = 0.0;
                if ($hasBool && !$boolVal) { $fade = 100.0; }
                elseif ($hasDim) { $fade = 100.0 - $dimVal; }
                if ($fade < 0.0) $fade = 0.0; if ($fade > 100.0) $fade = 100.0;
                $r['bgfade'] = $fade;
            } else {
                $r['bgfade'] = 0.0;
            }
        } catch (Throwable $e) {}
    }

    private function applyRoomItems(array &$r, array $room): void
    {
        $roomInfoList = $this->parseRoomList($room['InfoItems'] ?? []);
        $roomMenuList = $this->parseRoomList($room['MenuItems'] ?? []);
        $infoItems = $this->buildDynamicInfo($roomInfoList, $r);
        $menuItems = $this->buildDynamicMenu($roomMenuList, $r);

        if (!empty($infoItems)) {
            $r['infoitems'] = $infoItems;
        }
        if (!empty($menuItems)) {
            $r['menuitems'] = $menuItems;
        }

        $needStaticInfo = empty($infoItems);
        $needStaticMenu = empty($menuItems);
        if ($needStaticInfo || $needStaticMenu) {
            $this->fillInfoAndButtons($r, $room, $needStaticInfo, $needStaticMenu);
        }
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
            $width = (int)($row['Width'] ?? 0);
            $fullWidth = isset($row['FullWidth']) ? (bool)$row['FullWidth'] : false;
            $order = $idx++;
            if ($id === '') {
                $seed = 'M|' . (string)$varId . '|' . trim($altName);
                $id = 'm' . substr(sha1($seed), 0, 10);
            }
            $key = 'menuitem-' . $id;

            $valueFormatted = '';
            $icon = '';
            $options = [];
            $rawValue = null;
            $color = '';
            $colorOn = '';
            $colorOff = '';
            $statusBgColor = '';
            $hasVar = ($varId > 0) && @IPS_VariableExists($varId);
            $hasObject = ($openObjectId > 0) && @IPS_ObjectExists($openObjectId);
            $hasScene = ($sceneControlId > 0) && @IPS_InstanceExists($sceneControlId);
            $typeVal = null;
            $hasValidAction = false;
            $actionType = 'none';
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
                try {
                    if ($typeVal === 1) {
                        $opts = TileVisuLib::getIntegerAssociations($varId);
                        if (is_array($opts) && !empty($opts)) { $options = $opts; }
                    } elseif ($typeVal === 3) {
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
                // Szenensteuerung: Baue Optionen aus Scene1..N inkl. Icons, ermittle aktive Szene
                $hasValidAction = true;
                $actionType = 'scenecontrol';
                $options = [];
                $labelToIndex = [];
                try {
                    foreach ((array)@IPS_GetChildrenIDs($sceneControlId) as $cid) {
                        if (!@IPS_VariableExists($cid)) continue;
                        $o = @IPS_GetObject($cid);
                        $ident = (string)($o['ObjectIdent'] ?? '');
                        if (preg_match('/^Scene(\\d+)$/i', $ident, $m)) {
                            $idxScene = (int)$m[1];
                            $label = (string)($o['ObjectName'] ?? $ident);
                            $iconName = (string)($o['ObjectIcon'] ?? '');
                            $opt = ['value' => $idxScene, 'label' => $label];
                            if ($iconName !== '') { $opt['icon'] = $iconName; }
                            $options[] = $opt;
                            $labelToIndex[$label] = $idxScene;
                        } elseif ($ident === 'ActiveScene') {
                            // Wird später zum Auslesen genutzt
                        }
                    }
                    if (!empty($options)) {
                        usort($options, fn($a, $b) => ((int)($a['value'] ?? 0)) <=> ((int)($b['value'] ?? 0)));
                    }
                } catch (Throwable $e) {}
                try {
                    $active = null;
                    if (function_exists('SZS_GetActiveScene')) {
                        $active = @SZS_GetActiveScene($sceneControlId);
                    } else {
                        $activeVar = 0;
                        foreach ((array)@IPS_GetChildrenIDs($sceneControlId) as $cid) {
                            if (!@IPS_VariableExists($cid)) continue;
                            $o = @IPS_GetObject($cid);
                            if (($o['ObjectIdent'] ?? '') === 'ActiveScene') { $activeVar = $cid; break; }
                        }
                        if ($activeVar > 0) { $active = @GetValue($activeVar); }
                    }
                    if (is_numeric($active)) {
                        $rawValue = (int)$active;
                    } elseif (is_string($active) && isset($labelToIndex[$active])) {
                        $rawValue = (int)$labelToIndex[$active];
                    }
                } catch (Throwable $e) {}
                // Optional: assoziierter Text
                if (isset($activeVar) && $activeVar > 0) {
                    try { $valueFormatted = (string)@GetValueFormatted($activeVar); } catch (Throwable $e) {}
                }
            }
            if (!$hasValidAction && $hasObject) {
                $hasValidAction = true;
                $actionType = 'object';
            }
            if ($altName !== '' || $showName || $hasObject) {
                $derivedName = $altName !== '' ? $altName : ($hasVar ? (string)@IPS_GetName($varId) : ($hasObject ? (string)@IPS_GetName($openObjectId) : ''));
                if ($derivedName !== '') { $outRoom[$key . 'name'] = $derivedName; }
            }
            if ($valueFormatted !== '' && $showValue) { $outRoom[$key] = $valueFormatted; $outRoom[$key . 'asso'] = $valueFormatted; }
            $outRoom[$key . 'showvalue'] = $showValue;
            $outRoom[$key . 'showicon'] = $showIcon || $hasObject;
            if (($icon === '' || $icon === 'Transparent') && $hasObject) {
                try { $icon = (string)@IPS_GetObject($openObjectId)['ObjectIcon']; } catch (Throwable $e) { $icon = ''; }
            }
            if ($icon !== '' && $icon !== 'Transparent') { $outRoom[$key . 'icon'] = $icon; }
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
        }
        usort($items, fn($a, $b) => $a['order'] <=> $b['order']);
        return $items;
    }

    private function NormalizeRoomLists(): bool
    {
        $rooms = $this->getRooms();
        $changed = false;
        foreach ($rooms as &$room) {
            $rawInfo = $room['InfoItems'] ?? [];
            $info = $this->parseRoomList($rawInfo);
            $resultInfo = $this->ensureListIds($info, 'i');
            $infoJson = json_encode($resultInfo['list']);
            if (!is_string($rawInfo) || $resultInfo['changed'] || $rawInfo !== $infoJson) {
                $room['InfoItems'] = $infoJson !== false ? $infoJson : '[]';
                $changed = true;
            } else {
                // ensure value is stored as string even if already normalized
                $room['InfoItems'] = $infoJson !== false ? $infoJson : '[]';
            }

            $rawMenu = $room['MenuItems'] ?? [];
            $menu = $this->parseRoomList($rawMenu);
            $resultMenu = $this->ensureListIds($menu, 'm');
            $menuJson = json_encode($resultMenu['list']);
            if (!is_string($rawMenu) || $resultMenu['changed'] || $rawMenu !== $menuJson) {
                $room['MenuItems'] = $menuJson !== false ? $menuJson : '[]';
                $changed = true;
            } else {
                $room['MenuItems'] = $menuJson !== false ? $menuJson : '[]';
            }
        }
        unset($room);

        if ($changed) {
            IPS_SetProperty($this->InstanceID, 'Rooms', json_encode($rooms));
            IPS_ApplyChanges($this->InstanceID);
            return true;
        }
        return false;
    }

    private function fillInfoAndButtons(array &$out, array $room, bool $includeInfo, bool $includeMenu): void
    {
        if ($includeInfo) {
            $pairs = [
                'InfoLeft' => 'infoleft',
                'InfoLeft2' => 'infoleft2',
                'InfoRight' => 'inforight',
                'InfoRight2' => 'inforight2'
            ];
            foreach ($pairs as $prop => $key) {
                $id = (int)($room[$prop] ?? 0);
                if ($id > 0 && IPS_VariableExists($id) && !TileVisuLib::isObjectHidden($id)) {
                    $col = $this->GetColor($id);
                    if ($col !== '') {
                        $out[$key . 'color'] = '#' . $col;
                    }
                    $val = GetValueFormatted($id);
                    $out[$key] = $val;
                    $out[$key . 'asso'] = $val;
                    $typeLocal = null;
                    try { $viTmp = isset($varInfo) ? $varInfo : IPS_GetVariable($id); $typeLocal = $viTmp['VariableType'] ?? null; } catch (Throwable $e) {}
                    $defaultShowName = ($typeLocal === 0);
                    if ($this->ReadBoolOrDefault($room, $prop . 'NameSwitch', $defaultShowName)) {
                        $out[$key . 'name'] = IPS_GetName($id);
                    }
                    $icon = $this->GetIconAdvanced($id);
                    if ($icon !== 'Transparent' && $icon !== '') {
                        $out[$key . 'icon'] = $icon;
                    }
                    $out[$key . 'showvalue'] = $this->ReadBoolOrDefault($room, $prop . 'ShowValue', true);
                    $out[$key . 'showicon'] = $this->ReadBoolOrDefault($room, $prop . 'IconSwitch', false);
                }
            }

            for ($i = 1; $i <= 5; $i++) {
                $prop = 'Info' . $i;
                $id = (int)($room[$prop] ?? 0);
                if ($id > 0 && IPS_VariableExists($id) && !TileVisuLib::isObjectHidden($id)) {
                    $key = 'info' . $i;
                    try {
                        $col = $this->GetColor($id);
                        if ($col !== '') {
                            $out[$key . 'color'] = '#' . $col;
                        }
                    } catch (Throwable $e) {}
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
                    } catch (Throwable $e) {}
                    try { $out[$key . 'value'] = GetValue($id); } catch (Throwable $e) {}
                    try {
                        $vi = isset($varInfo) ? $varInfo : IPS_GetVariable($id);
                        if (isset($vi['VariableType'])) {
                            if ($vi['VariableType'] === 1) {
                                $opts = TileVisuLib::getIntegerAssociations($id);
                                if (!empty($opts)) { $out[$key . 'options'] = $opts; }
                            } elseif ($vi['VariableType'] === 3) {
                                $opts = TileVisuLib::getStringAssociations($id);
                                if (!empty($opts)) { $out[$key . 'options'] = $opts; }
                            }
                        }
                    } catch (Throwable $e) {}
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
        }

        if ($includeMenu) {
            for ($i = 1; $i <= 5; $i++) {
                $prop = 'Switch' . $i;
                $openProp = $prop . 'OpenObjectId';
                $varId = (int)($room[$prop] ?? 0);
                $openObjectId = (int)($room[$openProp] ?? 0);
                $hasVar = ($varId > 0) && IPS_VariableExists($varId);
                $hasObject = ($openObjectId > 0) && IPS_ObjectExists($openObjectId);
                if ($hasVar && TileVisuLib::isObjectHidden($varId)) {
                    $hasVar = false;
                }
                if ($hasObject && TileVisuLib::isObjectHidden($openObjectId)) {
                    $hasObject = false;
                }
                if (!$hasVar && !$hasObject) {
                    continue;
                }
                $key = 'switch' . $i;
                $out[$key . 'configured'] = true;

                $showName = $this->ReadBoolOrDefault($room, $prop . 'NameSwitch', false);
                $showIcon = $this->ReadBoolOrDefault($room, $prop . 'IconSwitch', true);
                $showValue = $this->ReadBoolOrDefault($room, $prop . 'ShowValue', false);
                $altName = (string)($room[$prop . 'AltName'] ?? '');

                $valueFormatted = '';
                $rawValue = null;
                $typeVal = null;
                $options = [];
                $color = '';
                $colorOn = '';
                $colorOff = '';
                $iconFromVar = '';
                $hasValidAction = false;

                if ($hasVar) {
                    try {
                        $varInfo = IPS_GetVariable($varId);
                        if (isset($varInfo['VariableType'])) {
                            $typeVal = $varInfo['VariableType'];
                        }
                        $actionId = 0;
                        if (isset($varInfo['VariableCustomAction']) && $varInfo['VariableCustomAction'] > 0) {
                            $actionId = $varInfo['VariableCustomAction'];
                        } elseif (isset($varInfo['VariableAction']) && $varInfo['VariableAction'] > 0) {
                            $actionId = $varInfo['VariableAction'];
                        }
                        $hasValidAction = ($actionId > 0) && (@IPS_InstanceExists($actionId) || @IPS_ScriptExists($actionId));
                        if ($typeVal === 0 && $hasValidAction) {
                            $btnColors = $this->GetButtonColors($varId);
                            if ($btnColors['on'] !== '') { $colorOn = $btnColors['on']; }
                            if ($btnColors['off'] !== '') { $colorOff = $btnColors['off']; }
                        } elseif ($typeVal !== null) {
                            $col = $this->GetColor($varId);
                            if ($col !== '') {
                                $color = '#' . $col;
                            }
                        }
                    } catch (Throwable $e) {}
                    try { $valueFormatted = (string)GetValueFormatted($varId); } catch (Throwable $e) { $valueFormatted = ''; }
                    try { $rawValue = GetValue($varId); } catch (Throwable $e) { $rawValue = null; }
                    try {
                        if ($typeVal === 1) {
                            $opts = TileVisuLib::getIntegerAssociations($varId);
                            if (!empty($opts)) { $options = $opts; }
                        } elseif ($typeVal === 3) {
                            $opts = TileVisuLib::getStringAssociations($varId);
                            if (!empty($opts)) { $options = $opts; }
                        }
                    } catch (Throwable $e) {}
                    try { $iconFromVar = $this->GetIconAdvanced($varId); } catch (Throwable $e) { $iconFromVar = ''; }

                    $out[$key] = $valueFormatted;
                    $out[$key . 'asso'] = $valueFormatted;
                    if ($rawValue !== null) {
                        $out[$key . 'value'] = $rawValue;
                    }
                    if ($altName === '' && $showName) {
                        try { $out[$key . 'name'] = IPS_GetName($varId); } catch (Throwable $e) {}
                    }
                } else {
                    $out[$key] = '';
                    $out[$key . 'asso'] = '';
                }

                if ($iconFromVar !== '' && $iconFromVar !== 'Transparent') {
                    $out[$key . 'icon'] = $iconFromVar;
                }
                if ($color !== '') {
                    $out[$key . 'color'] = $color;
                }
                if ($colorOn !== '') {
                    $out[$key . 'colorOn'] = $colorOn;
                }
                if ($colorOff !== '') {
                    $out[$key . 'colorOff'] = $colorOff;
                }
                if (!empty($options)) {
                    $out[$key . 'options'] = $options;
                }
                if ($typeVal !== null) {
                    $out[$key . 'type'] = $typeVal;
                }
                if ($altName !== '') {
                    $out[$key . 'name'] = $altName;
                }

                if ($hasObject) {
                    $out[$key . 'openobject'] = $openObjectId;
                    try {
                        $object = IPS_GetObject($openObjectId);
                        $objectName = (string)($object['ObjectName'] ?? '');
                        $objectIcon = (string)($object['ObjectIcon'] ?? '');
                    } catch (Throwable $e) {
                        $objectName = '';
                        $objectIcon = '';
                    }
                    if ($objectName !== '' && $altName === '') {
                        $out[$key . 'name'] = $objectName;
                    }
                    if ($objectIcon !== '' && $objectIcon !== 'Transparent') {
                        $out[$key . 'icon'] = $objectIcon;
                    }
                    $hasValidAction = true;
                } else {
                    $out[$key . 'openobject'] = 0;
                }

                $out[$key . 'hasaction'] = $hasValidAction || $hasObject;
                $out[$key . 'showvalue'] = $showValue;
                $out[$key . 'showname'] = $showName;
                $out[$key . 'showicon'] = $showIcon;
                $out[$key . 'iconswitch'] = $showIcon;
            }
        }
    }

    private function getRooms(): array
    {
        $json = $this->ReadPropertyString('Rooms');
        $arr = json_decode($json, true);
        return is_array($arr) ? $arr : [];
    }

    private function isConfiguredTargetLinkPair(int $linkId, int $targetId): bool
    {
        if ($linkId <= 0 || $targetId <= 0) {
            return false;
        }

        foreach ($this->getRooms() as $room) {
            if (!is_array($room)) {
                continue;
            }

            if (
                $linkId === (int)($room['TargetLinkId'] ?? 0)
                && $targetId === (int)($room['TargetLinkValue'] ?? 0)
            ) {
                return true;
            }
        }

        return false;
    }

    private function parseRoomList(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = @json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        if (is_array($value)) {
            return $value;
        }
        return [];
    }

    private function ensureListIds(array $list, string $prefix): array
    {
        $seen = [];
        $changed = false;
        foreach ($list as &$row) {
            if (!is_array($row)) {
                $row = [];
                $changed = true;
            }
            $id = isset($row['Id']) ? (string)$row['Id'] : '';
            if ($id === '' || isset($seen[$id])) {
                try { $id = $prefix . bin2hex(random_bytes(6)); } catch (Throwable $e) { $id = $prefix . substr(sha1(uniqid('', true)), 0, 12); }
                $row['Id'] = $id;
                $changed = true;
            }
            $seen[$id] = true;
        }
        unset($row);
        return ['list' => $list, 'changed' => $changed];
    }

    private function sendMenuItemDelta(int $idx, string $itemId, int $varId): void
    {
        if ($varId <= 0 || !@IPS_VariableExists($varId)) {
            return;
        }
        try {
            $delta = $this->buildMenuItemDelta($idx, $itemId, $varId);
            if (!empty($delta)) {
                $this->UpdateVisualizationValue(json_encode(['delta' => $delta]));
            }
        } catch (Throwable $e) {
        }
    }

    private function sendInfoItemDelta(int $idx, string $itemId, int $varId): void
    {
        if ($varId <= 0 || !@IPS_VariableExists($varId)) {
            return;
        }
        try {
            $delta = $this->buildInfoItemDelta($idx, $itemId, $varId);
            if (!empty($delta)) {
                $this->UpdateVisualizationValue(json_encode(['delta' => $delta]));
            }
        } catch (Throwable $e) {
        }
    }

    private function buildMenuItemDelta(int $idx, string $itemId, int $varId): array
    {
        $delta = [];
        try {
            $value = @GetValue($varId);
            $delta[] = ['idx' => $idx, 'key' => 'menuitem-' . $itemId . 'value', 'value' => $value];
            $delta[] = ['idx' => $idx, 'key' => 'menuitem-' . $itemId . 'asso', 'value' => (string)@GetValueFormatted($varId)];
            try { $icon = (string)$this->GetIconAdvanced($varId); } catch (Throwable $e) { $icon = ''; }
            if ($icon !== '' && $icon !== 'Transparent') {
                $delta[] = ['idx' => $idx, 'key' => 'menuitem-' . $itemId . 'icon', 'value' => $icon];
            }
            // Determine correct name: prefer AltName; then object name; else only use variable name when ShowName is true
            $altName = ''; $showName = false; $openObjectId = 0; $objectName = '';
            try {
                $rooms = $this->getRooms();
                if (isset($rooms[$idx]) && is_array($rooms[$idx])) {
                    $room = $rooms[$idx];
                    $list = $this->parseRoomList($room['MenuItems'] ?? []);
                    foreach ($list as $row) {
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
                $delta[] = ['idx' => $idx, 'key' => 'menuitem-' . $itemId . 'name', 'value' => $altName];
            } elseif ($openObjectId > 0 && $objectName !== '') {
                $delta[] = ['idx' => $idx, 'key' => 'menuitem-' . $itemId . 'name', 'value' => $objectName];
            } elseif ($showName) {
                try { $name = (string)@IPS_GetName($varId); } catch (Throwable $e) { $name = ''; }
                if ($name !== '') {
                    $delta[] = ['idx' => $idx, 'key' => 'menuitem-' . $itemId . 'name', 'value' => $name];
                }
            }
            // Status-Hintergrundfarbe (useVarColor) nachführen — sonst aktualisiert sich bei
            // Variablenänderung nur Wert/Icon, nicht die Farbe (vgl. Voll-Render applyRoomItems).
            $colorEntry = $this->buildMenuStatusColorDelta($idx, $itemId, $varId);
            if ($colorEntry !== null) {
                $delta[] = $colorEntry;
            }
        } catch (Throwable $e) {
        }
        return $delta;
    }

    /**
     * Status-Hintergrundfarbe eines Menü-Items als Delta-Eintrag (oder null, wenn useVarColor
     * deaktiviert ist). Spiegelt die Voll-Render-Logik aus applyRoomItems: GetColor, für
     * Bool zusätzlich ColorTrue/ColorFalse-Override. Key passt zu dataset.statusbgcolor im Frontend.
     */
    private function buildMenuStatusColorDelta(int $idx, string $itemId, int $varId): ?array
    {
        if ($varId <= 0 || !@IPS_VariableExists($varId)) {
            return null;
        }
        $useVarColor = false; $colorTrue = -1; $colorFalse = -1; $found = false;
        try {
            $rooms = $this->getRooms();
            if (isset($rooms[$idx]) && is_array($rooms[$idx])) {
                foreach ($this->parseRoomList($rooms[$idx]['MenuItems'] ?? []) as $row) {
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
        return ['idx' => $idx, 'key' => 'menuitem-' . $itemId . 'statusbgcolor', 'value' => $statusBgColor];
    }

    private function buildInfoItemDelta(int $idx, string $itemId, int $varId): array
    {
        $delta = [];
        try {
            $delta[] = ['idx' => $idx, 'key' => 'infoitem-' . $itemId . 'asso', 'value' => (string)@GetValueFormatted($varId)];
            try { $icon = (string)$this->GetIconAdvanced($varId); } catch (Throwable $e) { $icon = ''; }
            if ($icon !== '' && $icon !== 'Transparent') {
                $delta[] = ['idx' => $idx, 'key' => 'infoitem-' . $itemId . 'icon', 'value' => $icon];
            }
            // Determine correct name: prefer AltName if set; else only use variable name when ShowName is true
            $useColor = false; $ct = -1; $cf = -1; $altName = ''; $showName = false;
            try {
                $rooms = $this->getRooms();
                if (isset($rooms[$idx]) && is_array($rooms[$idx])) {
                    $room = $rooms[$idx];
                    $list = $this->parseRoomList($room['InfoItems'] ?? []);
                    foreach ($list as $row) {
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
                $delta[] = ['idx' => $idx, 'key' => 'infoitem-' . $itemId . 'name', 'value' => $altName];
            } elseif ($showName) {
                try { $name = (string)@IPS_GetName($varId); } catch (Throwable $e) { $name = ''; }
                if ($name !== '') {
                    $delta[] = ['idx' => $idx, 'key' => 'infoitem-' . $itemId . 'name', 'value' => $name];
                }
            }
            // Bestimme zu sendende Farbe: Override für Bool > Profil-Farbe > leer
            $sendColor = null;
            $vt = null; $isOn = false;
            try { $viTmp = @IPS_GetVariable($varId); if (is_array($viTmp)) { $vt = $viTmp['VariableType'] ?? null; } } catch (Throwable $e) {}
            if ($vt === 0) {
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
        } catch (Throwable $e) {
        }
        return $delta;
    }

    // Handle WebHook requests directly in the module (no script target)
}
?>
