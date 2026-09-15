<?php

declare(strict_types=1);

/**
 * Gemeinsame Helfer für RoomTile und MultiRoomTile.
 *
 * Enthält ausschließlich Methoden, die in beiden Modulen byte-identisch waren.
 * Divergenzen sind explizit parametrisiert:
 *  - deliverImageFromHook($withCorsHeaders): RoomTile sendet CORS-Header,
 *    MultiRoomTile nicht — beide Module behalten einen ProcessHookData-Wrapper.
 *  - renderVisualizationTile($moduleDir): module.html liegt im Modulordner,
 *    nicht in libs/ — der Aufrufer übergibt sein __DIR__.
 *
 * Hinweis: dirname(__DIR__) zeigt aus libs/ wie aus den Modulordnern auf das
 * Library-Wurzelverzeichnis; die RoomHeader/assets-Pfade bleiben daher gültig.
 */
trait TileVisuRoomHelpers
{
    // ---------------------------------------------------------------------
    // Simple Locale (optional)
    //
    // Ist das Modul Simple Locale installiert, werden die frei eingegebenen
    // Texte der Kachel - Raumnamen und alternative Namen - in die aktive Sprache
    // der Visualisierung übersetzt, in der die Kachel liegt. Ohne Simple Locale
    // bleibt alles unverändert. Variablen- und Objektnamen übersetzt Simple
    // Locale selbst, hier geht es nur um Texte aus der Konfiguration.
    //
    // Die Übersetzungen werden in ApplyChanges() und bei jeder Änderung einer
    // Simple-Locale-Instanz (IM_CHANGESETTINGS, z.B. Sprachwechsel) ermittelt
    // und in einem Puffer gehalten; beim Rendern wird nur nachgeschlagen.
    // ---------------------------------------------------------------------

    private ?array $simpleLocaleLabels = null;

    private function GetSimpleLocaleInstanceIDs(): array
    {
        if (!function_exists('SLOC_IsResponsibleFor') || !function_exists('SLOC_TranslateExternalTexts')) {
            return [];
        }

        return IPS_GetInstanceListByModuleID('{1A2E3892-FE35-9E4E-A3A8-B983B0C41F64}');
    }

    private function RegisterSimpleLocaleMessages(): void
    {
        foreach ($this->GetSimpleLocaleInstanceIDs() as $id) {
            $this->RegisterMessage($id, IM_CHANGESETTINGS);
        }
    }

    // Die Simple-Locale-Instanz, in deren Visualisierungs-Baum diese Kachel liegt.
    // 0, wenn keine oder mehrere zuständig sind - dann wird nicht übersetzt.
    private function FindSimpleLocaleInstance(): int
    {
        $responsible = [];
        foreach ($this->GetSimpleLocaleInstanceIDs() as $id) {
            try {
                if (SLOC_IsResponsibleFor($id, $this->InstanceID)) {
                    $responsible[] = $id;
                }
            } catch (Throwable $e) {}
        }
        if (count($responsible) > 1) {
            $this->SendDebug('SimpleLocale', 'Mehrere zuständige Instanzen (' . implode(', ', $responsible) . ') - Kachel liegt in mehreren Visualisierungen, keine Übersetzung', 0);
        }

        return count($responsible) === 1 ? $responsible[0] : 0;
    }

    // Ermittelt die Übersetzungen neu. Liefert true, wenn sie sich geändert haben
    // und die Kachel neu gerendert werden sollte.
    private function RefreshSimpleLocaleLabels(): bool
    {
        $texts = [];
        $id = $this->FindSimpleLocaleInstance();
        if ($id !== 0) {
            $texts = array_values(array_unique($this->CollectSimpleLocaleTexts()));
        }

        $labels = [];
        if ($texts !== []) {
            try {
                // Symcon-Modulfunktionen kennen keine optionalen Parameter - die
                // Quellsprache muss immer mit; '' steht für die der Instanz.
                $translated = SLOC_TranslateExternalTexts($id, $texts, '');
            } catch (Throwable $e) {
                $this->SendDebug('SimpleLocale', 'Übersetzung fehlgeschlagen: ' . $e->getMessage(), 0);
                $translated = $texts;
            }
            foreach ($texts as $index => $text) {
                $translation = (string)($translated[$index] ?? $text);
                if ($translation !== '' && $translation !== $text) {
                    $labels[$text] = $translation;
                }
            }
        }

        $json = json_encode($labels);
        $changed = $json !== $this->GetBuffer('SimpleLocaleLabels');
        $this->SetBuffer('SimpleLocaleLabels', $json);
        $this->simpleLocaleLabels = $labels;

        return $changed;
    }

    private function IsSimpleLocaleInstance(int $id): bool
    {
        return in_array($id, $this->GetSimpleLocaleInstanceIDs(), true);
    }

    // Übersetzung eines Konfigurationstexts, sonst der Text selbst.
    private function TranslateLabel(string $text): string
    {
        if ($text === '') {
            return $text;
        }
        if ($this->simpleLocaleLabels === null) {
            $labels = json_decode($this->GetBuffer('SimpleLocaleLabels'), true);
            $this->simpleLocaleLabels = is_array($labels) ? $labels : [];
        }

        return $this->simpleLocaleLabels[$text] ?? $text;
    }

    // Alle Raumnamen und alternativen Namen aus der Konfiguration, auch aus den
    // als JSON gespeicherten Listen (Räume, Menü- und Info-Einträge).
    private function CollectSimpleLocaleTexts(): array
    {
        $texts = [];
        $walk = function ($node) use (&$walk, &$texts): void {
            if (!is_array($node)) {
                return;
            }
            foreach ($node as $key => $value) {
                if (is_string($value)) {
                    if (is_string($key) && ($key === 'RoomName' || str_ends_with($key, 'AltName'))) {
                        if (trim($value) !== '') {
                            $texts[] = $value;
                        }
                    } elseif ($value !== '' && ($value[0] === '[' || $value[0] === '{')) {
                        $walk(json_decode($value, true));
                    }
                } elseif (is_array($value)) {
                    $walk($value);
                }
            }
        };
        $walk(json_decode((string)@IPS_GetConfiguration($this->InstanceID), true));

        return $texts;
    }

    // ---------------------------------------------------------------------
    // Property-/Listen-Leser
    // ---------------------------------------------------------------------

    private function ReadInt(array $room, string $key): int
    {
        return (int)($room[$key] ?? 0);
    }

    private function ReadBool(array $room, string $key): bool
    {
        return (bool)($room[$key] ?? false);
    }

    private function ReadBoolOrDefault(array $room, string $key, bool $default): bool
    {
        if (array_key_exists($key, $room)) {
            return (bool)$room[$key];
        }
        return $default;
    }

    private function ReadNumOrDefault(array $room, string $key, array $defaults, float $fallback): float
    {
        if (array_key_exists($key, $room)) {
            $v = (float)$room[$key];
            if ($v < 0) {
                if (array_key_exists($key, $defaults)) {
                    return (float)$defaults[$key];
                }
                return $fallback;
            }
            return $v;
        }
        if (array_key_exists($key, $defaults)) {
            return (float)$defaults[$key];
        }
        return $fallback;
    }

    private function ReadIntOrDefault(array $room, string $key, array $defaults, int $fallback): int
    {
        if (array_key_exists($key, $room)) {
            return (int)$room[$key];
        }
        if (array_key_exists($key, $defaults)) {
            return (int)$defaults[$key];
        }
        return $fallback;
    }

    // ---------------------------------------------------------------------
    // Farb-/Format-Helfer
    // ---------------------------------------------------------------------

    private function toCssHex(int $rgb): string
    {
        return '#' . sprintf('%06X', $rgb);
    }

    private function cssRgba(int $rgb, float $alpha): string
    {
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        return 'rgba(' . $r . ', ' . $g . ', ' . $b . ', ' . $alpha . ')';
    }

    private function percentToAlpha(float $percent): float
    {
        if ($percent < 0.0) $percent = 0.0;
        if ($percent > 100.0) $percent = 100.0;
        return $percent / 100.0;
    }

    private function normalizePercent(float $val): float
    {
        // Backwards compatibility: if value is in 0..1, interpret as fraction and convert to percent
        if ($val >= 0.0 && $val <= 1.0) {
            $val = $val * 100.0;
        }
        if ($val < 0.0) $val = 0.0;
        if ($val > 100.0) $val = 100.0;
        return $val;
    }

    private function GetColor(int $id): string
    {
        return TileVisuLib::getProfileColorHex($id);
    }

    private function GetColorRGBFrom(int $hexcolor, float $transparency): string
    {
        if ($hexcolor === -1) {
            return '';
        }
        $hexColor = sprintf('%06X', $hexcolor);
        if (strlen($hexColor) === 6) {
            $r = hexdec(substr($hexColor, 0, 2));
            $g = hexdec(substr($hexColor, 2, 2));
            $b = hexdec(substr($hexColor, 4, 2));
            return "rgba($r, $g, $b, $transparency)";
        }
        return $hexColor;
    }

    private function GetIcon(int $id, bool $varicon): string
    {
        return TileVisuLib::getIcon($id, $varicon);
    }

    private function GetIconAdvanced(int $id): string
    {
        return TileVisuLib::getIconAdvanced($id);
    }

    /**
     * Zielwert, wenn ein Schalter-Button eine Zahlenvariable bedient.
     *
     * Die Buttons schicken bei Zahlen einen festen Wert (1). Für Auslöser-Variablen
     * ist das richtig, für einen Dimmer 0-100 % bedeutete es "1 %": Ausschalten
     * ging nie, und die Hardware blieb auf ihrem kleinsten Wert hängen. Hat die
     * Variable einen Wertebereich, wird deshalb umgeschaltet: steht sie über dem
     * Minimum, auf das Minimum (aus), sonst auf das Maximum (an). Ohne
     * Wertebereich bleibt es beim übergebenen Wert.
     */
    private function ResolveNumericSwitchValue(int $varId, int $vType, mixed $Value): int|float
    {
        $range = TileVisuLib::getNumericRange($varId);
        if ($range === null) {
            return $vType === 1 ? (int)$Value : (float)$Value;
        }

        $current = 0.0;
        try { $current = (float)@GetValue($varId); } catch (Throwable $e) {}
        $target = $current > $range['min'] ? $range['min'] : $range['max'];

        return $vType === 1 ? (int)round($target) : (float)$target;
    }

    /**
     * Zielwert, wenn über einen Dimmer-Button gestrichen wurde: der übergebene
     * Wert, begrenzt auf den Wertebereich und gerundet auf dessen Schrittweite.
     */
    private function ResolveNumericSetValue(int $varId, int $vType, mixed $Value): int|float
    {
        $target = is_numeric($Value) ? (float)$Value : 0.0;
        $range = TileVisuLib::getNumericRange($varId);
        if ($range !== null) {
            $target = max($range['min'], min($range['max'], $target));
            $step = (float)($range['step'] ?? 0);
            if ($step > 0) {
                $target = $range['min'] + round(($target - $range['min']) / $step) * $step;
                $target = max($range['min'], min($range['max'], $target));
            }
        }

        return $vType === 1 ? (int)round($target) : $target;
    }

    private function GetButtonColors(int $id): array
    {
        $colors = ['on' => '', 'off' => ''];
        if (!function_exists('IPS_VariableExists') || !IPS_VariableExists($id)) {
            return $colors;
        }
        $variable = IPS_GetVariable($id);
        if (!isset($variable['VariableType']) || $variable['VariableType'] !== 0) {
            return $colors;
        }
        $profile = $variable['VariableCustomProfile'] ?: $variable['VariableProfile'];
        if ($profile && IPS_VariableProfileExists($profile)) {
            $p = IPS_GetVariableProfile($profile);
            if (isset($p['Associations']) && is_array($p['Associations'])) {
                foreach ($p['Associations'] as $a) {
                    if (isset($a['Value'], $a['Color']) && $a['Color'] !== -1) {
                        $hex = '#' . sprintf('%06X', (int)$a['Color']);
                        if ($a['Value'] == 1 || $a['Value'] === true) {
                            $colors['on'] = $hex;
                        } elseif ($a['Value'] == 0 || $a['Value'] === false) {
                            $colors['off'] = $hex;
                        }
                    }
                }
            }
        }
        if (isset($variable['VariableCustomPresentation']) && is_array($variable['VariableCustomPresentation'])) {
            $pres = $variable['VariableCustomPresentation'];
            $useFalse = $pres['USE_COLOR_FALSE'] ?? true;
            if (isset($pres['COLOR_TRUE']) && is_int($pres['COLOR_TRUE']) && $pres['COLOR_TRUE'] !== -1) {
                $colors['on'] = '#' . sprintf('%06X', (int)$pres['COLOR_TRUE']);
            }
            if ($useFalse && isset($pres['COLOR_FALSE']) && is_int($pres['COLOR_FALSE']) && $pres['COLOR_FALSE'] !== -1) {
                $colors['off'] = '#' . sprintf('%06X', (int)$pres['COLOR_FALSE']);
            }
        }
        return $colors;
    }

    private function CheckAndGetValueFormattedFromId(int $id): string|false
    {
        if ($id > 0 && IPS_VariableExists($id)) {
            return GetValueFormatted($id);
        }
        return false;
    }

    // ---------------------------------------------------------------------
    // Dynamische Info-Items
    // ---------------------------------------------------------------------

    private function buildDynamicInfo(array $list, array &$outRoom): array
    {
        $items = [];
        $idx = 0;
        foreach ($list as $row) {
            if (!is_array($row)) continue;
            $id = (string)($row['Id'] ?? '');
            $area = (string)($row['Area'] ?? 'left');
            $varId = (int)($row['VariableId'] ?? 0);
            $showName = isset($row['ShowName']) ? (bool)$row['ShowName'] : false;
            $showIcon = isset($row['ShowIcon']) ? (bool)$row['ShowIcon'] : false;
            $showValue = isset($row['ShowValue']) ? (bool)$row['ShowValue'] : true;
            $useVarColor = isset($row['UseVarColor']) ? (bool)$row['UseVarColor'] : false;
            $altName = (string)($row['AltName'] ?? '');
            $order = $idx++;
            if ($id === '') {
                $seed = 'I|' . (string)$varId . '|' . $area . '|' . trim($altName);
                $id = 'i' . substr(sha1($seed), 0, 10);
            }
            $key = 'infoitem-' . $id;

            $nameVal = '';
            $valueFormatted = '';
            $icon = '';
            $hasVar = ($varId > 0) && @IPS_VariableExists($varId);
            $bgColor = '';
            if ($hasVar && TileVisuLib::isObjectHidden($varId)) {
                continue;
            }
            if ($hasVar) {
                $vt = null;
                try {
                    $viTmp = @IPS_GetVariable($varId);
                    if (is_array($viTmp)) { $vt = $viTmp['VariableType'] ?? null; }
                } catch (Throwable $e) {}
                try { $valueFormatted = (string)@GetValueFormatted($varId); } catch (Throwable $e) {}
                if ($showName) {
                    try { $nameVal = $altName !== '' ? $this->TranslateLabel($altName) : (string)@IPS_GetName($varId); } catch (Throwable $e) { $nameVal = $this->TranslateLabel($altName); }
                } else {
                    $nameVal = $this->TranslateLabel($altName);
                }
                if ($showIcon) {
                    try { $icon = (string)$this->GetIconAdvanced($varId); } catch (Throwable $e) {}
                }
                // Fallback: Profil-/Präsentationsfarbe
                if ($useVarColor) {
                    try {
                        $col = (string)$this->GetColor($varId);
                        if ($col !== '') { $bgColor = '#' . $col; }
                    } catch (Throwable $e) {}
                }
                // Explizite Überschreibung für Bool mit ColorTrue/ColorFalse, wenn gesetzt (nicht Transparent)
                $ct = isset($row['ColorTrue']) ? (int)$row['ColorTrue'] : -1;
                $cf = isset($row['ColorFalse']) ? (int)$row['ColorFalse'] : -1;
                if ($vt === 0 && ($ct !== -1 || $cf !== -1)) {
                    $isOn = false;
                    try { $isOn = (bool)@GetValue($varId); } catch (Throwable $e) { $isOn = false; }
                    if ($isOn && $ct !== -1) {
                        $bgColor = '#' . sprintf('%06X', $ct);
                    } elseif (!$isOn && $cf !== -1) {
                        $bgColor = '#' . sprintf('%06X', $cf);
                    }
                }
            } else {
                $nameVal = $this->TranslateLabel($altName);
            }

            if ($nameVal !== '') { $outRoom[$key . 'name'] = $nameVal; }
            if ($valueFormatted !== '') { $outRoom[$key] = $valueFormatted; $outRoom[$key . 'asso'] = $valueFormatted; }
            $outRoom[$key . 'showvalue'] = $showValue;
            $outRoom[$key . 'showicon'] = $showIcon;
            if ($icon !== '' && $icon !== 'Transparent') { $outRoom[$key . 'icon'] = $icon; }

            // Normalisierte Area (left/mid/right)
            $areaNorm = 'left';
            if (in_array($area, ['left','mid','right'], true)) {
                $areaNorm = $area;
            }

            $items[] = [
                'id' => $id,
                'key' => $key,
                'area' => $areaNorm,
                'order' => $order,
                'variableId' => $varId,
                'color' => $bgColor,
            ];
        }
        usort($items, fn($a, $b) => $a['order'] <=> $b['order']);
        return $items;
    }

    // ---------------------------------------------------------------------
    // Raum-Reihenfolge
    // ---------------------------------------------------------------------

    private function ReorderRoom(int $index, string $direction): void
    {
        $rooms = $this->getRooms();
        $count = count($rooms);
        if ($count === 0 || $index < 0 || $index >= $count) {
            return;
        }
        if ($direction === 'up' && $index > 0) {
            $tmp = $rooms[$index - 1];
            $rooms[$index - 1] = $rooms[$index];
            $rooms[$index] = $tmp;
        } elseif ($direction === 'down' && $index < $count - 1) {
            $tmp = $rooms[$index + 1];
            $rooms[$index + 1] = $rooms[$index];
            $rooms[$index] = $tmp;
        } else {
            return;
        }
        IPS_SetProperty($this->InstanceID, 'Rooms', json_encode($rooms));
        IPS_ApplyChanges($this->InstanceID);
    }

    // ---------------------------------------------------------------------
    // Bilder & WebHook
    // ---------------------------------------------------------------------

    private function GetImageDataURI(int $imageID): string
    {
        if ($imageID > 0 && IPS_MediaExists($imageID)) {
            $image = IPS_GetMedia($imageID);
            if ($image['MediaType'] === MEDIATYPE_IMAGE) {
                $imageFile = explode('.', $image['MediaFile']);
                $imageContent = '';
                switch (strtolower((string)end($imageFile))) {
                    case 'bmp': $imageContent = 'data:image/bmp;base64,'; break;
                    case 'jpg':
                    case 'jpeg': $imageContent = 'data:image/jpeg;base64,'; break;
                    case 'gif': $imageContent = 'data:image/gif;base64,'; break;
                    case 'png': $imageContent = 'data:image/png;base64,'; break;
                    case 'ico': $imageContent = 'data:image/x-icon;base64,'; break;
                }
                if ($imageContent) {
                    $imageContent .= IPS_GetMediaContent($imageID);
                    return $imageContent;
                }
            }
        }
        // Fallback: nutze Placeholder aus RoomHeader/assets
        $fallbackPath = dirname(__DIR__) . '/RoomHeader/assets/placeholder.png';
        if (@file_exists($fallbackPath)) {
            // (string)-Cast: file_get_contents kann false liefern; unter strict_types
            // würde base64_encode(false) werfen — Cast erhält das bisherige Verhalten
            return 'data:image/png;base64,' . base64_encode((string)@file_get_contents($fallbackPath));
        }
        return '';
    }

    private function BuildImageHookUrl(int $mediaId): string
    {
        $base = '/hook/roomgridimages/' . $this->InstanceID;
        $token = $this->ReadAttributeString('HookToken');
        $q = '';
        if ($mediaId > 0) {
            $q = 'mid=' . (int)$mediaId;
        } else {
            $q = 'placeholder=1';
        }
        if ($token !== '') {
            $q .= '&token=' . rawurlencode($token);
        }
        // Content-adressierte Versionierung statt time(): die URL wechselt nur, wenn sich der
        // Media-Inhalt ändert. Dadurch kann der Browser das Bild cachen und lädt es nur bei
        // echter Änderung neu (vorher: bei jedem Render eine neue URL -> immer Neu-Download).
        $q .= '&v=' . rawurlencode($this->getMediaCacheVersion($mediaId));
        return $base . '?' . $q;
    }

    /**
     * Cache-Version eines Media-Objekts: bevorzugt MediaCRC (ändert sich nur bei
     * Inhaltsänderung), sonst MediaUpdated; für den Placeholder die Datei-mtime.
     * Dient als Cache-Buster in der Hook-URL und als ETag bei der Auslieferung.
     */
    private function getMediaCacheVersion(int $mediaId): string
    {
        if ($mediaId > 0 && @IPS_MediaExists($mediaId)) {
            try {
                $m = @IPS_GetMedia($mediaId);
                if (is_array($m)) {
                    $crc = (string)($m['MediaCRC'] ?? '');
                    if ($crc !== '') {
                        return $crc;
                    }
                    $upd = (int)($m['MediaUpdated'] ?? 0);
                    if ($upd > 0) {
                        return (string)$upd;
                    }
                }
            } catch (Throwable $e) {}
            return '0';
        }
        $placeholder = dirname(__DIR__) . '/RoomHeader/assets/placeholder.png';
        $mt = @filemtime($placeholder);
        return $mt !== false ? (string)$mt : 'ph';
    }

    /**
     * Setzt cachebare Header (ETag + lange max-age, immutable) für die Bildauslieferung und
     * beantwortet ein passendes If-None-Match direkt mit 304. Rückgabe true = 304 gesendet,
     * der Aufrufer muss dann ohne Body zurückkehren.
     */
    private function emitImageCacheHeaders(string $version): bool
    {
        $etag = '"' . $version . '"';
        header('ETag: ' . $etag);
        header('Cache-Control: private, max-age=31536000, immutable');
        $inm = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim((string)$_SERVER['HTTP_IF_NONE_MATCH']) : '';
        if ($inm !== '' && $inm === $etag) {
            http_response_code(304);
            return true;
        }
        return false;
    }

    /**
     * Eigene Hook-Registrierung inkl. HookToken-Erzeugung. Bewusst NICHT
     * RegisterHook genannt: IPSModuleStrict bringt nativ ein protected
     * RegisterHook(string): bool mit, das kein Token-Handling kennt.
     */
    private function registerImageHook(string $hookPath): void
    {
        $webhookModuleId = '{015A6EB8-D6E5-4B93-B496-0D3F77AE9FE1}';
        $ids = @IPS_GetInstanceListByModuleID($webhookModuleId);
        if (!is_array($ids) || count($ids) === 0) {
            $this->LogMessage('WebHook Control not found. Skipping hook registration.', KL_WARNING);
            return;
        }
        $whId = $ids[0];
        $instToken = $this->ReadAttributeString('HookToken');
        if ($instToken === '') {
            try {
                $instToken = bin2hex(random_bytes(16));
            } catch (Throwable $e) {
                $instToken = substr(sha1(uniqid('', true)), 0, 32);
            }
            $this->WriteAttributeString('HookToken', $instToken);
        }
        $hooks = @json_decode(IPS_GetProperty($whId, 'Hooks'), true);
        if (!is_array($hooks)) {
            $hooks = [];
        }
        $found = false;
        foreach ($hooks as &$h) {
            if (isset($h['Hook']) && $h['Hook'] === $hookPath) {
                $h['TargetID'] = $this->InstanceID;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $hooks[] = [
                'Hook' => $hookPath,
                'TargetID' => $this->InstanceID
            ];
        }
        IPS_SetProperty($whId, 'Hooks', json_encode($hooks));
        IPS_ApplyChanges($whId);
    }

    private function ensureHookScript(): int
    {
        return 0;
    }

    /**
     * Liefert das per Hook angeforderte Media-Bild bzw. den Placeholder aus.
     * $withCorsHeaders: RoomTile sendet zusätzlich CORS-Header, MultiRoomTile nicht.
     */
    private function deliverImageFromHook(bool $withCorsHeaders): void
    {
        $instT = $this->ReadAttributeString('HookToken');
        $token = isset($_GET['token']) ? (string)$_GET['token'] : '';
        if (!is_string($token) || $token === '' || $instT === '' || !hash_equals($instT, $token)) {
            http_response_code(403);
            return;
        }
        if ($withCorsHeaders) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET');
        }
        $mid = isset($_GET['mid']) ? (int)$_GET['mid'] : 0;
        if ($mid > 0 && @IPS_MediaExists($mid)) {
            $m = @IPS_GetMedia($mid);
            if (is_array($m) && isset($m['MediaType']) && $m['MediaType'] === MEDIATYPE_IMAGE) {
                $b64 = @IPS_GetMediaContent($mid);
                if (is_string($b64) && $b64 !== '') {
                    $bin = @base64_decode($b64, true);
                    if ($bin !== false) {
                        // Cachebar ausliefern: URL ist über ?v= inhaltsadressiert. Bei passendem
                        // If-None-Match sofort 304. Header erst hier setzen (nach erfolgreichem
                        // Dekodieren), damit Fehlerpfade (404/Placeholder) nicht cachebar werden.
                        if ($this->emitImageCacheHeaders($this->getMediaCacheVersion($mid))) {
                            return;
                        }
                        $mime = 'application/octet-stream';
                        if (strlen($bin) >= 12) {
                            $hdr = substr($bin, 0, 12);
                            if (strncmp($hdr, "\xFF\xD8\xFF", 3) === 0) {
                                $mime = 'image/jpeg';
                            } elseif (strncmp($hdr, "\x89PNG\x0D\x0A\x1A\x0A", 8) === 0) {
                                $mime = 'image/png';
                            } elseif (strncmp($hdr, 'GIF87a', 6) === 0 || strncmp($hdr, 'GIF89a', 6) === 0) {
                                $mime = 'image/gif';
                            } elseif (substr($hdr, 0, 4) === 'RIFF' && substr($hdr, 8, 4) === 'WEBP') {
                                $mime = 'image/webp';
                            }
                        }
                        header('Content-Type: ' . $mime);
                        header('Content-Length: ' . strlen($bin));
                        echo $bin;
                        return;
                    }
                }
            }
        }

        if (isset($_GET['placeholder'])) {
            $placeholder = dirname(__DIR__) . '/RoomHeader/assets/placeholder.png';
            if (@is_file($placeholder)) {
                $bin = @file_get_contents($placeholder);
                if ($bin !== false) {
                    if ($this->emitImageCacheHeaders($this->getMediaCacheVersion(0))) {
                        return;
                    }
                    header('Content-Type: image/png');
                    header('Content-Length: ' . strlen($bin));
                    echo $bin;
                    return;
                }
            }
        }

        http_response_code(404);
    }

    // ---------------------------------------------------------------------
    // Visualisierungs-Auslieferung
    // ---------------------------------------------------------------------

    /**
     * Baut das auszuliefernde Kachel-HTML zusammen.
     * $moduleDir: __DIR__ des aufrufenden Moduls (dort liegt module.html).
     *
     * Die Marker im module.html werden serverseitig durch die gemeinsamen
     * CSS-/JS-Dateien aus libs/html/ ersetzt (fail-soft: bei Lesefehler bleibt
     * die Kachel mit dem modul-lokalen Bestand lauffähig).
     */
    private function renderVisualizationTile(string $moduleDir): string
    {
        $mapping = '';
        $mapPath = dirname(__DIR__) . '/RoomHeader/assets/iconMapping.json';
        if (@file_exists($mapPath)) {
            $json = @file_get_contents($mapPath);
            if (is_string($json) && $json !== '') {
                $mapping = '<script>window.iconMapping=' . $json . ';</script>';
            }
        }
        $initial = '<script>handleMessage(' . json_encode($this->GetFullUpdateMessage()) . ')</script>';
        $module = (string)file_get_contents($moduleDir . '/module.html');
        $module = $this->injectSharedAsset($module, '/*__TILEVISU_SHARED_CSS__*/', dirname(__DIR__) . '/libs/html/tilevisu-shared.css');
        $module = $this->injectSharedAsset($module, '/*__TILEVISU_SHARED_JS__*/', dirname(__DIR__) . '/libs/html/tilevisu-shared.js');
        if ($mapping !== '') {
            $module = str_replace('<script src="/icons.js" crossorigin="anonymous"></script>', '<script src="/icons.js" crossorigin="anonymous"></script>' . $mapping, $module);
        }
        return $module . $initial;
    }

    private function injectSharedAsset(string $html, string $marker, string $assetPath): string
    {
        if (strpos($html, $marker) === false) {
            return $html;
        }
        $asset = @file_get_contents($assetPath);
        if (!is_string($asset)) {
            $this->SendDebug('renderVisualizationTile', 'Shared-Asset nicht lesbar: ' . $assetPath, 0);
            $asset = '';
        }
        return str_replace($marker, $asset, $html);
    }
}
