/*
 * TileVisu: gemeinsame JS-Funktionen von RoomTile und MultiRoomTile.
 * Wird von renderVisualizationTile() (TileVisuRoomHelpers) anstelle des
 * Markers /*__TILEVISU_SHARED_JS__* / in module.html eingesetzt.
 * Nur reine Funktionsdeklarationen (gehoistet) — keine Top-Level-Statements!
 */
    function prepareIconClass(iconName) {
      if (!iconName || iconName === 'Transparent') return '';
      const resolvedRaw = String(iconName).trim();
      const mapped = iconMapping[resolvedRaw] ?? resolvedRaw;
      let iconClass = String(mapped).trim();
      // Respektiere vorhandene Prefixe (fa-light/fa-solid/fa-regular/fa-brands/fas/far/fal/fab/fa-kit/fak)
      const tokens = iconClass.split(/\s+/).filter(Boolean);
      const hasPrefix = tokens.some(t => (
        t === 'fa-light' || t === 'fa-solid' || t === 'fa-regular' || t === 'fa-brands' ||
        t === 'fas' || t === 'far' || t === 'fal' || t === 'fab' || t === 'fa-kit' || t === 'fak'
      ));
      if (hasPrefix) {
        if (!tokens.includes('fa-fw')) {
          iconClass = 'fa-fw ' + iconClass;
        }
        return iconClass;
      }
      const baseName = iconClass.startsWith('fa-') ? iconClass.substring(3) : iconClass;
      const styles = [
        { classes: 'fa-kit fak', find: 'fak' },
        { classes: 'fa-solid',  find: 'fas' },
        { classes: 'fas',       find: 'fas' },
        { classes: 'fa-regular',find: 'far' },
        { classes: 'far',       find: 'far' },
        { classes: 'fa-light',  find: 'fal' },
        { classes: 'fal',       find: 'fal' },
        { classes: 'fa-brands', find: 'fab' },
        { classes: 'fab',       find: 'fab' },
      ];
      try {
        if (window.FontAwesome && typeof window.FontAwesome.findIconDefinition === 'function') {
          for (const s of styles) {
            try {
              const def = window.FontAwesome.findIconDefinition({ prefix: s.find, iconName: baseName });
              if (def) {
                return `fa-fw ${s.classes} fa-${baseName}`;
              }
            } catch (e) {}
          }
        }
      } catch (e) {}
      return `fa-fw fa-kit fak fa-${baseName}`;
    }

    function ensureFaSvg(node) {
      try {
        if (!node) return;
        // Kit-Icons werden als Font gerendert – nicht in SVG konvertieren
        const hasKitIcon = (node.classList && node.classList.contains('fak')) ||
          (typeof node.querySelector === 'function' && !!node.querySelector('.fak'));
        if (hasKitIcon) return;
        if (window.FontAwesome && window.FontAwesome.dom && typeof window.FontAwesome.dom.i2svg === 'function') {
          window.FontAwesome.dom.i2svg({ node, observeMutations: false });
        }
      } catch (e) { }
    }

    function el(tag, attrs = {}, children = []) {
      const e = document.createElement(tag);
      for (const [k, v] of Object.entries(attrs)) {
        if (k === 'class') e.className = v; else if (k === 'style') e.setAttribute('style', v); else e.id = k === 'id' ? v : e.id, e.setAttribute(k, v);
      }
      for (const c of [].concat(children)) e.append(c instanceof Node ? c : document.createTextNode(c));
      return e;
    }

    function setIconElementById(elId, newClass) {
      const cur = document.getElementById(elId);
      if (!cur) return null;
      const p = cur.parentElement;
      if (!p) return null;
      if (cur.tagName !== 'I') {
        const i = document.createElement('i');
        i.id = elId;
        i.className = newClass || '';
        p.replaceChild(i, cur);
        return i;
      }
      cur.className = newClass || '';
      return cur;
    }

    function _parseAlphaFromRgba(str, def = 0.3) {
      try {
        if (!str) return def;
        const m = String(str).match(/rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+(?:\s*,\s*([0-9.]+))?\s*\)/i);
        if (!m) return def;
        if (m[1] === undefined) return def;
        const a = parseFloat(m[1]);
        return isFinite(a) ? Math.max(0, Math.min(1, a)) : def;
      } catch { return def; }
    }

    function _hexToRgba(hex, alpha) {
      try {
        let h = String(hex).trim();
        if (h[0] === '#') h = h.substring(1);
        if (h.length === 3) h = h.split('').map(c => c + c).join('');
        const r = parseInt(h.substring(0,2), 16) || 0;
        const g = parseInt(h.substring(2,4), 16) || 0;
        const b = parseInt(h.substring(4,6), 16) || 0;
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
      } catch { return hex; }
    }

    function _rgbToRgba(rgb, alpha) {
      try {
        const m = String(rgb).match(/rgb\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/i);
        if (!m) return rgb;
        const r = parseInt(m[1], 10) || 0;
        const g = parseInt(m[2], 10) || 0;
        const b = parseInt(m[3], 10) || 0;
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
      } catch { return rgb; }
    }

    function applyBadgeBg(grp, color, alphaRef) {
      if (!grp) return;
      if (!color) { grp.style.backgroundColor = ''; return; }
      // global flag: opaque status colors when disabled
      if (GRID_FLAGS.transparentStatusColors === false) {
        const c0 = String(color).trim();
        let out0 = c0;
        if (/^rgba\(/i.test(c0)) {
          out0 = c0.replace(/rgba\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*,\s*[0-9.]+\s*\)/i, 'rgb($1, $2, $3)');
        }
        grp.style.backgroundColor = out0;
        return;
      }
      const a = _getInfoTopAlpha(alphaRef);
      const c = String(color).trim();
      let out = c;
      if (c.startsWith('#')) out = _hexToRgba(c, a);
      else if (/^rgb\(/i.test(c)) out = _rgbToRgba(c, a);
      else if (/^rgba\(/i.test(c)) {
        out = c.replace(/rgba\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*,\s*[0-9.]+\s*\)/i, `rgba($1, $2, $3, ${a})`);
      }
      grp.style.backgroundColor = out;
    }

    function computeBgFilterString(percent, src) {
      const p = Math.max(0, Math.min(100, Number(percent) || 0));
      const f = p / 100;
      // Defaults
      let bmin = 0.2, bmax = 1.0, cmin = 0.9, cmax = 1.0, gmin = 0.0, gmax = 0.5;
      // Read from element dataset or plain object
      if (src) {
        const d = src.dataset ? src.dataset : src;
        const pf = (v, def) => {
          const x = parseFloat(v);
          return isFinite(x) ? x : def;
        };
        bmin = pf(d.bmin, bmin);
        bmax = pf(d.bmax, bmax);
        cmin = pf(d.cmin, cmin);
        cmax = pf(d.cmax, cmax);
        gmin = pf(d.gmin, gmin);
        gmax = pf(d.gmax, gmax);
      }
      const brightness = bmax - (bmax - bmin) * f;
      const contrast = cmax - (cmax - cmin) * f;
      const grayscale = gmin + (gmax - gmin) * f;
      return `brightness(${brightness.toFixed(3)}) contrast(${contrast.toFixed(3)}) grayscale(${grayscale.toFixed(3)})`;
    }

    function _clamp(v, min, max) { return Math.max(min, Math.min(max, v)); }

    function _luminance(r, g, b) {
      // relative luminance 0..1
      const srgb = [r, g, b].map(v => {
        v = v / 255;
        return (v <= 0.04045) ? (v / 12.92) : Math.pow((v + 0.055) / 1.055, 2.4);
      });
      return 0.2126 * srgb[0] + 0.7152 * srgb[1] + 0.0722 * srgb[2];
    }

    function _rgbToHex(r, g, b) {
      return '#' + [r, g, b].map(x => x.toString(16).padStart(2, '0')).join('').toUpperCase();
    }

    function _adjustLum(r, g, b, targetMin = 0.25, targetMax = 0.8) {
      // keep colors within a pleasant brightness range for buttons
      const lum = _luminance(r, g, b);
      if (lum < targetMin || lum > targetMax) {
        // scale towards target midpoint
        const mid = (targetMin + targetMax) / 2;
        // simple linear blend with white/black depending on lum
        const t = lum < mid ? (mid - lum) : (lum - mid);
        const toward = lum < mid ? 255 : 0;
        const blend = (c) => _clamp(Math.round(c * (1 - t) + toward * t), 0, 255);
        return [blend(r), blend(g), blend(b)];
      }
      return [r, g, b];
    }

    function colorsForCount(palette, count) {
      if (!Array.isArray(palette) || palette.length === 0) return [];
      const out = [];
      for (let i = 0; i < count; i++) out.push(palette[i % palette.length]);
      return out;
    }

    function updateAllBadgeMinWidths() {
      const tiles = document.querySelectorAll('.tile');
      tiles.forEach(t => { if (t.id) updateBadgeMinWidth(t.id); });
    }

    function infoSwitchGroup(prefixId, idx, n) {
      const group = el('div', { id: prefixId + '-info' + n + '-group', class: 'switch-group' });
      // Fallback: simple button if boolean
      const btn = el('button', { id: prefixId + '-info' + n, class: 'hidden switch', onclick: `requestAction('room:${idx}:Info${n}', 1);` });
      const name = el('span', { id: prefixId + '-info' + n + 'name', class: 'hidden info-name' });
      const colon = el('span', { id: prefixId + '-info' + n + 'colon', class: 'hidden info-name' }, ':\u00A0');
      const val = el('span', { id: prefixId + '-info' + n + 'asso', class: 'hidden info-value' });
      btn.append(name, colon, val);
      group.appendChild(btn);
      return group;
    }

    function groupInfo(prefixId, key) {
      const g = el('div', { class: 'group' });
      const icon = el('i', { id: prefixId + '-' + key + 'icon', class: 'hidden info-icon' });
      const name = el('span', { id: prefixId + '-' + key + 'name', class: 'hidden info-name' });
      const colon = el('span', { id: prefixId + '-' + key + 'colon', class: 'hidden info-name' }, ': ');
      const val = el('span', { id: prefixId + '-' + key + 'asso', class: 'hidden info-value' });
      const textWrap = el('span', { class: 'nv' });
      textWrap.append(name, colon, val);
      g.append(icon, textWrap);
      return g;
    }

    function isGroupVisible(prefixId, key) {
      const parts = ['name', 'asso', 'icon'];
      for (const p of parts) {
        const el = document.getElementById(prefixId + '-' + key + p);
        if (el && !el.classList.contains('hidden')) return true;
      }
      return false;
    }

    function adjustIconSolo(prefixId, key) {
      const icon = document.getElementById(prefixId + '-' + key + 'icon');
      const name = document.getElementById(prefixId + '-' + key + 'name');
      const val = document.getElementById(prefixId + '-' + key + 'asso');
      if (!icon || !name || !val) return;
      const onlyIcon = !icon.classList.contains('hidden') &&
                       name.classList.contains('hidden') &&
                       val.classList.contains('hidden');
      icon.classList.toggle('solo', onlyIcon);
      const group = icon.parentElement;
      if (group && group.classList) {
        group.classList.toggle('only-icon', onlyIcon);
      }
    }

    function updateSingleButtonState(prefixId) {
      const cont = document.getElementById(prefixId + '-switches');
      if (!cont) return;
      // Count visible switch groups/buttons
      const groups = Array.from(cont.querySelectorAll('.switch-group, .group'));
      const visibleGroups = groups.filter(g => {
        return !g.classList.contains('hidden') && g.offsetParent !== null;
      });
      // Single button: apply full-width class
      cont.classList.toggle('single-button', visibleGroups.length === 1);
    }

    function normalizeDistribute(prefixId) {
      const cont = document.getElementById(prefixId + '-switches');
      if (!cont) return;
      if (!cont.classList.contains('distribute')) return;
      const hasFull = Array.from(cont.children).some(c => c.classList && c.classList.contains('full-width'));
      if (hasFull) cont.classList.remove('distribute');
    }

    function groupSwitch(prefixId, n) {
      const base = prefixId + '-schalter' + n;
      const g = el('div', { id: base + '-group', class: 'group hidden' });
      const icon = el('i', { id: base + 'icon', class: 'hidden info-icon' });
      const name = el('span', { id: base + 'name', class: 'hidden info-name' });
      const colon = el('span', { id: base + 'colon', class: 'hidden info-name' }, ': ');
      const val = el('span', { id: base + 'asso', class: 'hidden info-value' });
      const textWrap = el('span', { class: 'nv' });
      textWrap.append(name, colon, val);
      g.append(icon, textWrap);
      return g;
    }

    function switchGroup(prefixId, idx, n) {
      const group = el('div', { id: prefixId + '-schalter' + n + '-group', class: 'switch-group' });
      // Fallback: initial single button until data arrives
      const fallback = switchButton(prefixId, idx, n);
      group.appendChild(fallback);
      return group;
    }

    function renderSwitchGroup(prefixId, idx, n, options, currentValue, fallbackColor, widthPx, iconswitch, showName, showValue, showIcon, fullWidth) {
      const group = document.getElementById(prefixId + '-schalter' + n + '-group');
      if (!group) return;
      group.innerHTML = '';
      group.classList.toggle('full-width', !!fullWidth);
      // Toggle visibility for label/value/icon inside buttons
      // Defaults: labels shown, values hidden (unless explicitly enabled), icons shown (subject to iconswitch)
      group.classList.toggle('hide-labels', showName === false);
      group.classList.toggle('hide-values', showValue !== true);
      group.classList.toggle('hide-icons', showIcon === false);
      if (Array.isArray(options) && options.length > 0) {
        const rootEl = document.getElementById(prefixId);
        const useImgCol = !!(rootEl && rootEl.dataset && rootEl.dataset.useimagecolors === '1');
        for (const opt of options) {
          const btn = el('button', { class: 'switch-option' + (String(opt.value) === String(currentValue) ? ' active' : ' inactive'), onclick: `requestAction('room:${idx}:Schalter${n}', ${JSON.stringify(opt.value)})`, 'data-value': String(opt.value) });
          const icon = el('i', { class: 'hidden switch-icon' });
          if (iconswitch && opt.icon) {
            const cls = prepareIconClass(opt.icon);
            if (cls) {
              icon.className = cls + ' switch-icon';
            }
          }
          const label = el('span', { class: 'info-name' }, opt.label ?? '');
          const valueText = el('span', { class: 'info-value' }, String(opt.value ?? ''));
          if (!useImgCol) { if (opt.color) btn.style.backgroundColor = opt.color; else if (fallbackColor) btn.style.backgroundColor = fallbackColor; }
          if (widthPx && !fullWidth) btn.style.minWidth = widthPx + 'px'; else btn.style.minWidth = '';
          if (icon.className.indexOf('hidden') === -1) btn.appendChild(icon);
          btn.appendChild(label);
          btn.appendChild(valueText);
          group.appendChild(btn);
        }
      } else {
        // Fallback to single button like legacy rendering
        group.appendChild(switchButton(prefixId, idx, n));
      }
    }

    // -----------------------------------------------------------------------
    // Dimmer-Buttons (Zahlenvariable mit Wertebereich)
    //
    // Tippen schaltet wie bisher um (aus / an auf Maximum, entschieden in PHP).
    // Streichen stellt einen Wert ein: die Fingerposition auf dem Button ist der
    // Wert, links das Minimum, rechts das Maximum. Am Rand rastet der Wert auf
    // Minimum bzw. Maximum ein. Gesendet wird erst beim Loslassen - ein Befehl
    // statt vieler Zwischenwerte, schonend für Funk-Aktoren.
    // -----------------------------------------------------------------------
    const DIM_DRAG_THRESHOLD_PX = 10;
    const DIM_EDGE_SNAP = 0.06;
    const DIM_CLICK_SUPPRESS_MS = 500;

    function dimFraction(btn, value) {
      const min = Number(btn.dataset.rangeMin);
      const max = Number(btn.dataset.rangeMax);
      const v = Number(value);
      if (!isFinite(min) || !isFinite(max) || max <= min || !isFinite(v)) return 0;
      return Math.max(0, Math.min(1, (v - min) / (max - min)));
    }

    // Füllstand und Aktiv-Zustand eines Dimmer-Buttons nach einem Wert setzen.
    function setDimLevel(btn, value) {
      if (!btn || btn.dataset.rangeMin === undefined) return;
      let fill = btn.querySelector(':scope > .dim-fill');
      if (!fill) {
        fill = document.createElement('span');
        fill.className = 'dim-fill';
        btn.insertBefore(fill, btn.firstChild);
      }
      fill.style.width = (dimFraction(btn, value) * 100) + '%';
      btn.classList.toggle('inactive', !numericIsOn(btn, value));
    }

    // Ist ein Zahlen-Button "an"? Mit Wertebereich: Wert über dem Minimum,
    // ohne Wertebereich: Wert ungleich 0.
    function numericIsOn(btn, value) {
      const v = Number(value);
      if (!isFinite(v)) return false;
      if (btn && btn.dataset.rangeMin !== undefined) return v > Number(btn.dataset.rangeMin);
      return v !== 0;
    }

    // An/Aus-Darstellung eines Zahlen-Buttons (Integer/Float) nach einem Wert setzen.
    // Andere Buttons bleiben unberührt.
    function updateNumericButtonState(btn, value) {
      if (!btn || !btn.dataset) return;
      if (btn.dataset.vartype !== '1' && btn.dataset.vartype !== '2') return;
      if (btn.classList.contains('object-action')) return;
      if (btn.dataset.rangeMin !== undefined) {
        if (!btn.classList.contains('dimming')) setDimLevel(btn, value);
        return;
      }
      btn.classList.toggle('inactive', !numericIsOn(btn, value));
    }

    // Wertanzeige beim Streichen: eine gemeinsame Blase, direkt im body, damit
    // "overflow: hidden" von Button und Kachel sie nicht abschneidet.
    const DIM_BUBBLE_GAP_PX = 8;
    const DIM_BUBBLE_MARGIN_PX = 4;

    function ensureDimBubble() {
      let bubble = document.getElementById('tilevisu-dim-bubble');
      if (!bubble && document.body) {
        bubble = document.createElement('div');
        bubble.id = 'tilevisu-dim-bubble';
        bubble.className = 'dim-bubble';
        bubble.textContent = '100 %';
        document.body.appendChild(bubble);
      }
      return bubble;
    }

    function showDimBubble(btn, clientX, text) {
      const bubble = ensureDimBubble();
      if (!bubble) return;
      if (bubble.textContent !== text) bubble.textContent = text;
      const rect = btn.getBoundingClientRect();
      const w = bubble.offsetWidth;
      const h = bubble.offsetHeight;
      const viewW = document.documentElement.clientWidth || window.innerWidth;
      const viewH = document.documentElement.clientHeight || window.innerHeight;
      // Über dem Finger, innerhalb des sichtbaren Bereichs
      const left = Math.max(DIM_BUBBLE_MARGIN_PX, Math.min(viewW - w - DIM_BUBBLE_MARGIN_PX, clientX - w / 2));
      // Oberhalb des Buttons; ist dort kein Platz, darunter
      let top = rect.top - h - DIM_BUBBLE_GAP_PX;
      if (top < DIM_BUBBLE_MARGIN_PX) top = Math.min(rect.bottom + DIM_BUBBLE_GAP_PX, viewH - h - DIM_BUBBLE_MARGIN_PX);
      bubble.style.transform = 'translate3d(' + Math.round(left) + 'px, ' + Math.round(top) + 'px, 0)';
      bubble.classList.add('visible');
    }

    function hideDimBubble() {
      const bubble = document.getElementById('tilevisu-dim-bubble');
      if (bubble) bubble.classList.remove('visible');
    }

    // Wurde gerade gestrichen? Dann gehört der folgende Klick zur Streichbewegung.
    function consumeDimClick(btn) {
      return (Date.now() - Number(btn.dataset.dimDragEndedAt || 0)) < DIM_CLICK_SUPPRESS_MS;
    }

    function bindSwipeDimmer(btn, actionIdent, range, varType, currentValue) {
      const min = Number(range.min);
      const max = Number(range.max);
      const step = Number(range.step) || 0;
      if (!isFinite(min) || !isFinite(max) || max <= min) return;
      btn.dataset.rangeMin = String(min);
      btn.dataset.rangeMax = String(max);
      btn.classList.add('dimmable');
      setDimLevel(btn, currentValue);
      ensureDimBubble();
      // Streichen soll keinen Text markieren
      btn.addEventListener('selectstart', (e) => e.preventDefault());
      btn.addEventListener('contextmenu', (e) => e.preventDefault());

      let pointerId = null;
      let startX = 0;
      let startY = 0;
      let dragging = false;
      let pending = null;

      const valueAt = (clientX) => {
        const rect = btn.getBoundingClientRect();
        let fraction = rect.width > 0 ? (clientX - rect.left) / rect.width : 0;
        fraction = Math.max(0, Math.min(1, fraction));
        if (fraction <= DIM_EDGE_SNAP) fraction = 0;
        if (fraction >= 1 - DIM_EDGE_SNAP) fraction = 1;
        let value = min + fraction * (max - min);
        if (step > 0) value = min + Math.round((value - min) / step) * step;
        value = Math.max(min, Math.min(max, value));
        return varType === 1 ? Math.round(value) : Math.round(value * 1000) / 1000;
      };

      btn.addEventListener('pointerdown', (e) => {
        if (e.pointerType === 'mouse' && e.button !== 0) return;
        pointerId = e.pointerId;
        startX = e.clientX;
        startY = e.clientY;
        dragging = false;
        pending = null;
      });

      btn.addEventListener('pointermove', (e) => {
        if (e.pointerId !== pointerId) return;
        if (!dragging) {
          const dx = Math.abs(e.clientX - startX);
          const dy = Math.abs(e.clientY - startY);
          if (dx < DIM_DRAG_THRESHOLD_PX || dx < dy) return;
          dragging = true;
          btn.classList.add('dimming');
          try { btn.setPointerCapture(e.pointerId); } catch (err) {}
          try { const sel = window.getSelection && window.getSelection(); if (sel) sel.removeAllRanges(); } catch (err) {}
        }
        pending = valueAt(e.clientX);
        setDimLevel(btn, pending);
        showDimBubble(btn, e.clientX, Math.round(dimFraction(btn, pending) * 100) + ' %');
        e.preventDefault();
      });

      const finish = (e, commit) => {
        if (e.pointerId !== pointerId) return;
        pointerId = null;
        if (!dragging) return;
        dragging = false;
        btn.classList.remove('dimming');
        hideDimBubble();
        btn.dataset.dimDragEndedAt = String(Date.now());
        if (commit && pending !== null) {
          btn.dataset.currentValue = String(pending);
          setDimLevel(btn, pending);
          requestAction(actionIdent, pending);
        } else {
          setDimLevel(btn, btn.dataset.currentValue);
        }
        pending = null;
      };
      btn.addEventListener('pointerup', (e) => finish(e, true));
      btn.addEventListener('pointercancel', (e) => finish(e, false));
    }

    // Senkrechte Ausrichtung des Zimmernamens umschalten
    function applyRoomNameAlign(tile, align) {
      if (!tile) return;
      const center = tile.querySelector(':scope > .center');
      if (!center) return;
      center.classList.toggle('align-bars', align === 'bars');
      center.classList.toggle('align-tile', align === 'tile');
      applyTileBarMetrics(tile);
    }

    // Senkrechte Ausrichtung des Info-Centers umschalten
    function applyInfoCenterAlign(tile, align) {
      if (!tile) return;
      const centerInfo = tile.querySelector(':scope > .center-info');
      if (!centerInfo) return;
      centerInfo.classList.toggle('align-bars', align === 'bars');
      centerInfo.classList.toggle('align-tile', align === 'tile');
      applyTileBarMetrics(tile);
    }

    function observeTileBars(tile) {
      if (!tile || tile.dataset.barsObserved === '1') return;
      tile.dataset.barsObserved = '1';
      const update = () => applyTileBarMetrics(tile);

      // Direkt nach dem Aufbau stehen Schriften und Icons noch nicht,
      // deshalb spaeter noch einmal messen.
      update();
      requestAnimationFrame(update);
      for (const delay of [150, 600]) setTimeout(update, delay);

      if (typeof ResizeObserver === 'function') {
        const observer = new ResizeObserver(update);
        for (const selector of ['.row-top', '.menubar']) {
          const el = tile.querySelector(selector);
          if (el) observer.observe(el);
        }
        observer.observe(tile);
        // Referenz halten, sonst kann der Beobachter eingesammelt werden
        tile.tileBarObserver = observer;
      }
      window.addEventListener('resize', update);
    }

    function switchButton(prefixId, idx, n) {
      const btn = el('button', { id: prefixId + '-schalter' + n, class: 'hidden switch', onclick: `requestAction('room:${idx}:Schalter${n}', 1);` });
      const icon = el('i', { id: prefixId + '-schalter' + n + 'icon', class: 'hidden switch-icon' });
      const name = el('span', { id: prefixId + '-schalter' + n + 'name', class: 'hidden info-name' });
      const colon = el('span', { id: prefixId + '-schalter' + n + 'colon', class: 'hidden info-name' }, ': ');
      const val = el('span', { id: prefixId + '-schalter' + n + 'asso', class: 'hidden info-value' });
      const textWrap = el('span', { class: 'nv' });
      textWrap.append(name, colon, val);
      btn.append(icon, textWrap);
      ensureFaSvg(btn);
      return btn;
    }

