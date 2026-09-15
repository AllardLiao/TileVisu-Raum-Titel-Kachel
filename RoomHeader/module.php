<?php
 require_once __DIR__ . '/../libs/TileVisuLib.php';
class TileVisuRoomHeaderTileEOL extends IPSModule
{
    public function Create()
    {
        // Nie diese Zeile löschen!
        parent::Create();


        // Drei Eigenschaften für die dargestellten Zähler
        $this->RegisterPropertyInteger("BackgroundImage", 1);
        $this->RegisterPropertyInteger("Target", 1);
        $this->RegisterPropertyInteger("InfoLeft", 1);
        $this->RegisterPropertyBoolean('InfoLeftNameSwitch', false);
        $this->RegisterPropertyBoolean('InfoLeftIconSwitch', true);
        $this->RegisterPropertyBoolean('InfoLeftVarIconSwitch', false);
        $this->RegisterPropertyBoolean('InfoLeftAssoSwitch', true);
        $this->RegisterPropertyString('InfoLeftAltName', '');
        $this->RegisterPropertyInteger("InfoLeft2", 1);
        $this->RegisterPropertyBoolean('InfoLeft2NameSwitch', false);
        $this->RegisterPropertyBoolean('InfoLeft2IconSwitch', true);
        $this->RegisterPropertyBoolean('InfoLeft2VarIconSwitch', false);
        $this->RegisterPropertyBoolean('InfoLeft2AssoSwitch', true);
        $this->RegisterPropertyString('InfoLeft2AltName', '');
        $this->RegisterPropertyInteger("InfoRight", 1);
        $this->RegisterPropertyBoolean('InfoRightNameSwitch', false);
        $this->RegisterPropertyBoolean('InfoRightIconSwitch', true);
        $this->RegisterPropertyBoolean('InfoRightVarIconSwitch', false);
        $this->RegisterPropertyBoolean('InfoRightAssoSwitch', true);
        $this->RegisterPropertyString('InfoRightAltName', '');
        $this->RegisterPropertyInteger("InfoRight2", 1);
        $this->RegisterPropertyBoolean('InfoRight2NameSwitch', false);
        $this->RegisterPropertyBoolean('InfoRight2IconSwitch', true);
        $this->RegisterPropertyBoolean('InfoRight2VarIconSwitch', false);
        $this->RegisterPropertyBoolean('InfoRight2AssoSwitch', true);
        $this->RegisterPropertyString('InfoRight2AltName', '');
        $this->RegisterPropertyFloat('InfoFontSize', 30);
        $this->RegisterPropertyBoolean('MenuSwitch', true);
        $this->RegisterPropertyFloat('MenuFontSize', 20);
        $this->RegisterPropertyFloat('MenuTransparency', 0.3);
        $this->RegisterPropertyInteger('MenuBackgroundColor', 0x000000);
        $this->RegisterPropertyFloat('ImageTransparency', 0.7);
        $this->RegisterPropertyInteger('TileBackgroundColor', 0x000000);
        $this->RegisterPropertyInteger('InfoFontColor', 0xFFFFFF);
        $this->RegisterPropertyInteger('MenuFontColor', 0xFFFFFF);
        $this->RegisterPropertyString('RoomName', '');
        $this->RegisterPropertyFloat('RoomNameFontSize', 70);
        $this->RegisterPropertyInteger('RoomNameFontColor', 0xFFFFFF);
        $this->RegisterPropertyInteger('Switch1', 1);
        $this->RegisterPropertyFloat('Switch1FontSize', 20);
        $this->RegisterPropertyFloat('Switch1Width', 100);
        $this->RegisterPropertyString('Switch1AltName', '');
        $this->RegisterPropertyInteger('Switch2', 1);
        $this->RegisterPropertyFloat('Switch2FontSize', 20);
        $this->RegisterPropertyFloat('Switch2Width', 100);
        $this->RegisterPropertyString('Switch2AltName', '');
        $this->RegisterPropertyInteger('Switch3', 1);
        $this->RegisterPropertyFloat('Switch3FontSize', 20);
        $this->RegisterPropertyFloat('Switch3Width', 100);
        $this->RegisterPropertyString('Switch3AltName', '');
        $this->RegisterPropertyInteger('Switch4', 1);
        $this->RegisterPropertyFloat('Switch4FontSize', 20);
        $this->RegisterPropertyFloat('Switch4Width', 100);
        $this->RegisterPropertyString('Switch4AltName', '');
        $this->RegisterPropertyInteger('Switch5', 1);
        $this->RegisterPropertyFloat('Switch5FontSize', 20);
        $this->RegisterPropertyFloat('Switch5Width', 100);
        $this->RegisterPropertyString('Switch5AltName', '');
        $this->RegisterPropertyInteger('Info1', 1);
        $this->RegisterPropertyString('Info1AltLabel', '');
        $this->RegisterPropertyInteger('Info2', 1);
        $this->RegisterPropertyString('Info2AltLabel', '');
        $this->RegisterPropertyInteger('Info3', 1);
        $this->RegisterPropertyString('Info3AltLabel', '');
        $this->RegisterPropertyInteger('Info4', 1);
        $this->RegisterPropertyString('Info4AltLabel', '');
        $this->RegisterPropertyInteger('Info5', 1);
        $this->RegisterPropertyString('Info5AltLabel', '');
        $this->RegisterPropertyBoolean('Info1ShowName', true);
        $this->RegisterPropertyBoolean('Info2ShowName', true);
        $this->RegisterPropertyBoolean('Info3ShowName', true);
        $this->RegisterPropertyBoolean('Info4ShowName', true);
        $this->RegisterPropertyBoolean('Info5ShowName', true);
        $this->RegisterPropertyBoolean('Info1ShowIcon', true);
        $this->RegisterPropertyBoolean('Info2ShowIcon', true);
        $this->RegisterPropertyBoolean('Info3ShowIcon', true);
        $this->RegisterPropertyBoolean('Info4ShowIcon', true);
        $this->RegisterPropertyBoolean('Info5ShowIcon', true);
        $this->RegisterPropertyBoolean('Info1UseVarIcon', false);
        $this->RegisterPropertyBoolean('Info2UseVarIcon', false);
        $this->RegisterPropertyBoolean('Info3UseVarIcon', false);
        $this->RegisterPropertyBoolean('Info4UseVarIcon', false);
        $this->RegisterPropertyBoolean('Info5UseVarIcon', false);
        $this->RegisterPropertyBoolean('Info1ShowAssociation', true);
        $this->RegisterPropertyBoolean('Info2ShowAssociation', true);
        $this->RegisterPropertyBoolean('Info3ShowAssociation', true);
        $this->RegisterPropertyBoolean('Info4ShowAssociation', true);
        $this->RegisterPropertyBoolean('Info5ShowAssociation', true);
        $this->RegisterPropertyBoolean('Switch1NameSwitch', true);
        $this->RegisterPropertyBoolean('Switch2NameSwitch', true);
        $this->RegisterPropertyBoolean('Switch3NameSwitch', true);
        $this->RegisterPropertyBoolean('Switch4NameSwitch', true);
        $this->RegisterPropertyBoolean('Switch5NameSwitch', true);
        $this->RegisterPropertyBoolean('Switch1IconSwitch', true);
        $this->RegisterPropertyBoolean('Switch2IconSwitch', true);
        $this->RegisterPropertyBoolean('Switch3IconSwitch', true);
        $this->RegisterPropertyBoolean('Switch4IconSwitch', true);
        $this->RegisterPropertyBoolean('Switch5IconSwitch', true);
        $this->RegisterPropertyBoolean('Switch1VarIconSwitch', false);
        $this->RegisterPropertyBoolean('Switch2VarIconSwitch', false);
        $this->RegisterPropertyBoolean('Switch3VarIconSwitch', false);
        $this->RegisterPropertyBoolean('Switch4VarIconSwitch', false);
        $this->RegisterPropertyBoolean('Switch5VarIconSwitch', false);
        $this->RegisterPropertyBoolean('Switch1AssoSwitch', true);
        $this->RegisterPropertyBoolean('Switch2AssoSwitch', true);
        $this->RegisterPropertyBoolean('Switch3AssoSwitch', true);
        $this->RegisterPropertyBoolean('Switch4AssoSwitch', true);
        $this->RegisterPropertyBoolean('Switch5AssoSwitch', true);
        // Visualisierungstyp auf 1 setzen, da wir HTML anbieten möchten
        $this->SetVisualizationType(1);
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        if (IPS_GetKernelRunlevel() !== KR_READY) {
            return;
        }

        // One-time migration: German → English property names (v2)
        if (TileVisuLib::migrateV2($this, $this->InstanceID)) {
            return;
        }

        //Referenzen Registrieren
        $ids = [
            $this->ReadPropertyInteger('Target'),
            $this->ReadPropertyInteger('BackgroundImage'),
            $this->ReadPropertyInteger('InfoLeft'),
            $this->ReadPropertyInteger('InfoLeft2'),
            $this->ReadPropertyInteger('InfoRight'),
            $this->ReadPropertyInteger('InfoRight2'),
            $this->ReadPropertyInteger('Switch1'),
            $this->ReadPropertyInteger('Switch2'),
            $this->ReadPropertyInteger('Switch3'),
            $this->ReadPropertyInteger('Switch4'),
            $this->ReadPropertyInteger('Switch5'),
            $this->ReadPropertyInteger('Info1'),
            $this->ReadPropertyInteger('Info2'),
            $this->ReadPropertyInteger('Info3'),
            $this->ReadPropertyInteger('Info4'),
            $this->ReadPropertyInteger('Info5')
        ];
        $refs = $this->GetReferenceList();
            foreach($refs as $ref) {
                $this->UnregisterReference($ref);
            } 
            foreach ($ids as $id) {
                if ($id > 9999) {
                    $this->RegisterReference($id);
                }
            }

        
        // Aktualisiere registrierte Nachrichten
        foreach ($this->GetMessageList() as $senderID => $messageIDs)
        {
            foreach ($messageIDs as $messageID)
            {
                $this->UnregisterMessage($senderID, $messageID);
            }
        }


        foreach (['BackgroundImage', 'InfoLeft', 'InfoLeft2', 'InfoRight', 'InfoRight2', 'Switch1', 'Switch2', 'Switch3', 'Switch4', 'Switch5', 'Info1', 'Info2', 'Info3', 'Info4', 'Info5'] as $VariableProperty)        {
            $id = (int)$this->ReadPropertyInteger($VariableProperty);
            if ($id > 0 && @IPS_ObjectExists($id)) {
                $this->RegisterMessage($id, OM_CHANGEHIDDEN);
                $this->RegisterMessage($id, OM_CHANGENAME);
            }
            if ($id > 0 && @IPS_VariableExists($id)) {
                $this->RegisterMessage($id, VM_UPDATE);
            }
        }

        // Schicke eine komplette Update-Nachricht an die Darstellung, da sich ja Parameter geändert haben können
        // Aber in einem eigenen Script Thread, da sonst ApplyChanges blockiert wird und auch die Konsole hängt.
        IPS_RunScriptText('RMH_ForceUpdate('.$this->InstanceID.');');
        
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        // Angezeigte Namen stammen aus IPS_GetName() - nach einer Umbenennung neu aufbauen
        if ($Message === OM_CHANGENAME) {
            $this->ForceUpdate();
            return;
        }
        if ($Message === OM_CHANGEHIDDEN) {
            $this->ForceUpdate();
            return;
        }
        $properties = [
            'BackgroundImage', 'InfoLeft', 'InfoLeft2', 'InfoRight', 'InfoRight2',
            'Switch1', 'Switch2', 'Switch3', 'Switch4', 'Switch5',
            'Info1', 'Info2', 'Info3', 'Info4', 'Info5'
        ];

        foreach ($properties as $VariableProperty) // $VariableProperty ist der Name der Eigenschaft, z.B. "Switch1"
        {
            // Prüfen, ob der Sender (geänderte Variable) zu dieser Eigenschaft gehört
            if ($SenderID === $this->ReadPropertyInteger($VariableProperty))
            {
                if (TileVisuLib::isObjectHidden($SenderID)) {
                    $this->ForceUpdate();
                    return;
                }
                // Wenn ja, und es ist eine Aktualisierungsnachricht...
                if ($Message === VM_UPDATE)
                {
                    // Erster Update-Aufruf: Sendet den Hauptwert der geänderten Variable.
                    // $SenderID ist hier die ID der Variablen.
                    $this->UpdateVisualizationValue(json_encode([
                        $VariableProperty => GetValueFormatted($SenderID) // Verwende SenderID
                    ]));
                
                    // Vorbereitung der Daten für den zweiten Update-Aufruf (assoziierte Eigenschaften).
                    $result = []; 
                    // Verwende SenderID für GetColor
                    $result[$VariableProperty . 'Color'] = $this->GetColor($SenderID);

                    if ($VariableProperty != 'BackgroundImage')
                {
                        // Info1-Info5 verwenden andere Property-Suffixe als InfoLeft/Right und Switch1-5
                        $isInfoN = preg_match('/^Info[1-5]$/', $VariableProperty);
                        $nameSwitchProp = $isInfoN ? $VariableProperty . 'ShowName' : $VariableProperty . 'NameSwitch';
                        $iconSwitchProp = $isInfoN ? $VariableProperty . 'ShowIcon' : $VariableProperty . 'IconSwitch';
                        $varIconProp    = $isInfoN ? $VariableProperty . 'UseVarIcon' : $VariableProperty . 'VarIconSwitch';
                        $assoSwitchProp = $isInfoN ? $VariableProperty . 'ShowAssociation' : $VariableProperty . 'AssoSwitch';
                        $altNameProp    = $isInfoN ? $VariableProperty . 'AltLabel' : $VariableProperty . 'AltName';

                        if ($this->ReadPropertyBoolean($nameSwitchProp)) {
                            $result[$VariableProperty . 'name'] = IPS_GetName($SenderID);
                        }
                        
                        $iconValue = $this->GetIcon($SenderID, $this->ReadPropertyBoolean($varIconProp));
                        if ($this->ReadPropertyBoolean($iconSwitchProp) && $iconValue !== "Transparent") {
                           $result[$VariableProperty . 'icon'] = $iconValue;
                        }

                        if ($this->ReadPropertyBoolean($assoSwitchProp)) {
                            $result[$VariableProperty . 'asso'] = $this->CheckAndGetValueFormatted($VariableProperty);
                                }
                                $result[$VariableProperty . ($isInfoN ? 'AltLabel' : 'AltName')] = $this->ReadPropertyString($altNameProp);
                            }

                    // Zweiter Update-Aufruf: Sendet die assoziierten Eigenschaften.
                            $this->UpdateVisualizationValue(json_encode($result));

                    // Da die passende Variable gefunden und verarbeitet wurde, die Schleife und Methode verlassen.
                    return; 
                }
                // Hier könnten andere Nachrichten-Typen (außer VM_UPDATE) für den gematchten SenderID behandelt werden.
            }
        }
    }


    public function RequestAction($Ident, $value) {
        // Nachrichten von der HTML-Darstellung schicken immer den Ident passend zur Eigenschaft und im Wert die Differenz, welche auf die Variable gerechnet werden soll
        $variableID = $this->ReadPropertyInteger($Ident);
        if (!IPS_VariableExists($variableID)) {
            return;
        }
            // Umschalten des Werts der Variable
        $currentValue = GetValue($variableID);
        //SetValue($variableID, !$currentValue);
        RequestAction($variableID, !$currentValue);
    }


    public function GetVisualizationTile()
    {
        // Füge ein Skript hinzu, um beim Laden, analog zu Änderungen bei Laufzeit, die Werte zu setzen
        $initialHandling = '<script>handleMessage(' . json_encode($this->GetFullUpdateMessage()) . ')</script>';

        // Füge statisches HTML aus Datei hinzu
        $module = file_get_contents(__DIR__ . '/module.html');

        // Gebe alles zurück.
        // Wichtig: $initialHandling nach hinten, da die Funktion handleMessage erst im HTML definiert wird
        return $module . $initialHandling;
    }

    public function GetConfigurationForm()
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
		$Form['elements'][4]['items'][2]['visible'] = ((float)IPS_GetKernelVersion() > 8.1);
		return json_encode($Form);
	}

    public function ForceUpdate()
    {
        $this->UpdateVisualizationValue($this->GetFullUpdateMessage());
    }

    // Generiere eine Nachricht, die alle Elemente in der HTML-Darstellung aktualisiert
    private function GetFullUpdateMessage() {
        $result = [];
        $result['fullupdate'] = true;
    
        $infoProperties = ['InfoLeft', 'InfoLeft2', 'InfoRight', 'InfoRight2', 'Info1', 'Info2', 'Info3', 'Info4', 'Info5'];
        $switchProperties = ['Switch1', 'Switch2', 'Switch3', 'Switch4', 'Switch5'];

        foreach ($infoProperties as $propName) {
            $this->_addVisualisationProperty($result, $propName, 'info');
        }

        foreach ($switchProperties as $propName) {
            $this->_addVisualisationProperty($result, $propName, 'schalter');
        }

        // Restliche Properties und Bildbehandlung
            $result['targetlink'] =  $this->ReadPropertyInteger('Target');
            $result['infofontsize'] =  $this->ReadPropertyFloat('InfoFontSize');
            $result['tilebackgroundcolor'] =  '#' . sprintf('%06X', $this->ReadPropertyInteger('TileBackgroundColor'));
            $result['infofontcolor'] =  '#' . sprintf('%06X', $this->ReadPropertyInteger('InfoFontColor'));
            $result['menufontcolor'] =  '#' . sprintf('%06X', $this->ReadPropertyInteger('MenuFontColor'));
            $result['menufontsize'] =  $this->ReadPropertyFloat('MenuFontSize');
            $result['menutransparency'] =  $this->ReadPropertyFloat('MenuTransparency');
            $result['menubackgroundcolor'] =  $this->GetColorRGB($this->ReadPropertyInteger('MenuBackgroundColor'));
            $result['imagetransparency'] =  $this->ReadPropertyFloat('ImageTransparency');
            $result['roomname'] =  $this->ReadPropertyString('RoomName');
            $result['roomnamefontsize'] =  $this->ReadPropertyFloat('RoomNameFontSize');
            $result['roomnamefontcolor'] =  '#' . sprintf('%06X', $this->ReadPropertyInteger('RoomNameFontColor'));
            $s1 = (int)$this->ReadPropertyInteger('Switch1');
            if (@IPS_VariableExists($s1) && !TileVisuLib::isObjectHidden($s1)) { $result['switch1altname'] =  $this->ReadPropertyString('Switch1AltName'); }
            $s2 = (int)$this->ReadPropertyInteger('Switch2');
            if (@IPS_VariableExists($s2) && !TileVisuLib::isObjectHidden($s2)) { $result['switch2altname'] =  $this->ReadPropertyString('Switch2AltName'); }
            $s3 = (int)$this->ReadPropertyInteger('Switch3');
            if (@IPS_VariableExists($s3) && !TileVisuLib::isObjectHidden($s3)) { $result['switch3altname'] =  $this->ReadPropertyString('Switch3AltName'); }
            $s4 = (int)$this->ReadPropertyInteger('Switch4');
            if (@IPS_VariableExists($s4) && !TileVisuLib::isObjectHidden($s4)) { $result['switch4altname'] =  $this->ReadPropertyString('Switch4AltName'); }
            $s5 = (int)$this->ReadPropertyInteger('Switch5');
            if (@IPS_VariableExists($s5) && !TileVisuLib::isObjectHidden($s5)) { $result['switch5altname'] =  $this->ReadPropertyString('Switch5AltName'); }

            $i1 = (int)$this->ReadPropertyInteger('Info1');
            if (@IPS_VariableExists($i1) && !TileVisuLib::isObjectHidden($i1)) { $result['info1altlabel'] =  $this->ReadPropertyString('Info1AltLabel'); }
            $i2 = (int)$this->ReadPropertyInteger('Info2');
            if (@IPS_VariableExists($i2) && !TileVisuLib::isObjectHidden($i2)) { $result['info2altlabel'] =  $this->ReadPropertyString('Info2AltLabel'); }
            $i3 = (int)$this->ReadPropertyInteger('Info3');
            if (@IPS_VariableExists($i3) && !TileVisuLib::isObjectHidden($i3)) { $result['info3altlabel'] =  $this->ReadPropertyString('Info3AltLabel'); }
            $i4 = (int)$this->ReadPropertyInteger('Info4');
            if (@IPS_VariableExists($i4) && !TileVisuLib::isObjectHidden($i4)) { $result['info4altlabel'] =  $this->ReadPropertyString('Info4AltLabel'); }
            $i5 = (int)$this->ReadPropertyInteger('Info5');
            if (@IPS_VariableExists($i5) && !TileVisuLib::isObjectHidden($i5)) { $result['info5altlabel'] =  $this->ReadPropertyString('Info5AltLabel'); }

            $il = (int)$this->ReadPropertyInteger('InfoLeft');
            if (@IPS_VariableExists($il) && !TileVisuLib::isObjectHidden($il)) { $result['infoleftaltname'] =  $this->ReadPropertyString('InfoLeftAltName'); }
            $ir = (int)$this->ReadPropertyInteger('InfoRight');
            if (@IPS_VariableExists($ir) && !TileVisuLib::isObjectHidden($ir)) { $result['inforightaltname'] =  $this->ReadPropertyString('InfoRightAltName'); }
            $il2 = (int)$this->ReadPropertyInteger('InfoLeft2');
            if (@IPS_VariableExists($il2) && !TileVisuLib::isObjectHidden($il2)) { $result['infoleft2altname'] =  $this->ReadPropertyString('InfoLeft2AltName'); }
            $ir2 = (int)$this->ReadPropertyInteger('InfoRight2');
            if (@IPS_VariableExists($ir2) && !TileVisuLib::isObjectHidden($ir2)) { $result['inforight2altname'] =  $this->ReadPropertyString('InfoRight2AltName'); }
            $result['menuswitch'] =  $this->ReadPropertyBoolean('MenuSwitch');   
            
            // Prüfe vorweg, ob ein Bild ausgewählt wurde
            $imageID = $this->ReadPropertyInteger('BackgroundImage');
            if (IPS_MediaExists($imageID))
            {
                $image = IPS_GetMedia($imageID);
                if ($image['MediaType'] === MEDIATYPE_IMAGE)
                {
                    $imageFile = explode('.', $image['MediaFile']);
                    $imageContent = '';
                    switch (end($imageFile))
                    {
                    case 'bmp': $imageContent = 'data:image/bmp;base64,'; break;
                    case 'jpg': case 'jpeg': $imageContent = 'data:image/jpeg;base64,'; break;
                    case 'gif': $imageContent = 'data:image/gif;base64,'; break;
                    case 'png': $imageContent = 'data:image/png;base64,'; break;
                    case 'ico': $imageContent = 'data:image/x-icon;base64,'; break;
                    }
                    if ($imageContent)
                    {
                        $imageContent .= IPS_GetMediaContent($imageID);
                        $result['image1'] = $imageContent;
                    }
                }
            }
            else
            {
                $imageContent = 'data:image/png;base64,';
                $imageContent .= base64_encode(file_get_contents(__DIR__ . '/assets/placeholder.png'));
                $result['image1'] = $imageContent;
            }
        return json_encode($result);
    }

    private function _addVisualisationProperty(&$result, $propertyName, $propertyType) {
        $varID = $this->ReadPropertyInteger($propertyName);
        if (IPS_VariableExists($varID) && !TileVisuLib::isObjectHidden($varID)) {
            $baseKey = strtolower($propertyName); // e.g., "infoleft", "switch1"

            $result[$baseKey] = $this->CheckAndGetValueFormatted($propertyName);

            // Info1-Info5 verwenden andere Property-Suffixe als InfoLeft/Right und Switch1-5
            $isInfoN = preg_match('/^Info[1-5]$/', $propertyName);
            $nameSwitchProp = $isInfoN ? $propertyName . 'ShowName' : $propertyName . 'NameSwitch';
            $iconSwitchProp = $isInfoN ? $propertyName . 'ShowIcon' : $propertyName . 'IconSwitch';
            $varIconProp    = $isInfoN ? $propertyName . 'UseVarIcon' : $propertyName . 'VarIconSwitch';
            $assoSwitchProp = $isInfoN ? $propertyName . 'ShowAssociation' : $propertyName . 'AssoSwitch';

            if ($this->ReadPropertyBoolean($nameSwitchProp)) {
                $result[$baseKey . 'name'] = IPS_GetName($varID);
            }

            $iconValue = $this->GetIcon($varID, $this->ReadPropertyBoolean($varIconProp));
            if ($this->ReadPropertyBoolean($iconSwitchProp) && $iconValue !== "Transparent") {
                $result[$baseKey . 'icon'] = $iconValue;
            }

            if ($this->ReadPropertyBoolean($assoSwitchProp)) {
                $result[$baseKey . 'asso'] = $this->CheckAndGetValueFormatted($propertyName);
            }

            if ($propertyType === 'schalter') {
                $result[$baseKey . 'breite'] = $this->ReadPropertyFloat($propertyName . 'Breite');
                $result[$baseKey . 'color'] = $this->GetColor($varID);
            }
        }
    }
    private function CheckAndGetValueFormatted($property) {
        $id = $this->ReadPropertyInteger($property);
        if (IPS_VariableExists($id) && !TileVisuLib::isObjectHidden($id)) {
            return GetValueFormatted($id);
        }
        return false;
    }


    private function GetColor($id) {
        $variable = IPS_GetVariable($id);
        $Value = GetValue($id);
        $profile = $variable['VariableCustomProfile'] ?: $variable['VariableProfile'];

        if ($profile && IPS_VariableProfileExists($profile)) {
            $p = IPS_GetVariableProfile($profile);
            
            foreach ($p['Associations'] as $association) {
                if (isset($association['Value'], $association['Color']) && $association['Value'] == $Value) {
                    return $association['Color'] === -1 ? "" : sprintf('%06X', $association['Color']);
                    
                }
            }
        }
        return "";
    }


    private function GetColorRGB($hexcolor) {
        $imagetransparency = $this->ReadPropertyFloat('MenuTransparency');
        if($hexcolor != "-1")
        {
                $hexColor = sprintf('%06X', $hexcolor);
                // Prüft, ob der Hex-Farbwert gültig ist
                if (strlen($hexColor) == 6) {
                    $r = hexdec(substr($hexColor, 0, 2));
                    $g = hexdec(substr($hexColor, 2, 2));
                    $b = hexdec(substr($hexColor, 4, 2));
                    return "rgba($r, $g, $b, $imagetransparency)";
                } else {
                    // Fallback für ungültige Eingaben
                    return $hexColor;
                }
        }
        else {
            return "";
        }
    }

    private function GetIcon($id, $varicon) {
        $variable = IPS_GetVariable($id);
        $Value = GetValue($id);
        $icon = "";
        //Abfragen ob das Variablen-Icon oder das Profil-Icon verwendet werden soll
        if($varicon == true){
        $icon = IPS_GetObject($id);
        //print_r($icon); // Debug-Ausgabe
            if($icon['ObjectIcon'] != ""){
                $icon = $icon['ObjectIcon'];
            }
            else {
                $icon = "Transparent";
            }
        }
        else {
        // Profil-Icon abrufen
        $profile = $variable['VariableCustomProfile'] ?: $variable['VariableProfile'];
        $icon = "";

        if ($profile && IPS_VariableProfileExists($profile)) {
            $p = IPS_GetVariableProfile($profile);

            foreach ($p['Associations'] as $association) {
                if (isset($association['Value']) && $association['Icon'] != "" && $association['Value'] == $Value) {
                    $icon = $association['Icon'];
                    break;
                }
            }

            if ($icon == "" && isset($p['Icon']) && $p['Icon'] != "") {
                $icon = $p['Icon'];
            }

            if ($icon == "") {
                $icon = "Transparent";
            }
        }
        else {
            $icon = "Transparent";
        }
        
        }
        return $icon;
    }

}
