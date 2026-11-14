/* KYAF Food Donation Management System - front-end helpers (no build step required) */
(function () {
  'use strict';
  const U = (window.KYAF && window.KYAF.urls) || {};
  const BN = { '০': '0', '১': '1', '২': '2', '৩': '3', '৪': '4', '৫': '5', '৬': '6', '৭': '7', '৮': '8', '৯': '9' };

  /* ---------- Show / hide password ---------- */
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.toggle-pass');
    if (!btn) return;
    const input = document.getElementById(btn.dataset.target);
    if (!input) return;
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.setAttribute('aria-label', show ? 'পাসওয়ার্ড লুকান' : 'পাসওয়ার্ড দেখান');
    const icon = btn.querySelector('i');
    if (icon) icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
  });

  /* ---------- Live password rule checklist ---------- */
  document.querySelectorAll('input[data-pw-rules]').forEach(function (input) {
    const list = document.getElementById(input.dataset.pwRules);
    if (!list) return;
    const rules = {
      len: (v) => v.length >= 8 && v.length <= 64,
      upper: (v) => /[A-Z]/.test(v), lower: (v) => /[a-z]/.test(v),
      digit: (v) => /\d/.test(v), special: (v) => /[^A-Za-z0-9]/.test(v),
    };
    input.addEventListener('input', function () {
      list.querySelectorAll('li[data-rule]').forEach(function (li) {
        li.classList.toggle('ok', rules[li.dataset.rule](input.value));
      });
    });
  });

  /* ---------- Bangla digits -> ASCII while typing ---------- */
  document.addEventListener('input', function (e) {
    const el = e.target;
    if (el.matches && el.matches('[data-bn-digits]')) {
      const fixed = el.value.replace(/[০-৯]/g, (d) => BN[d]);
      if (fixed !== el.value) el.value = fixed;
    }
  });

  /* ---------- Confirmation dialog + loading state ---------- */
  const confirmEl = document.getElementById('confirmModal');
  let pendingForm = null;
  let pendingSubmitter = null;
  document.addEventListener('submit', function (e) {
    const form = e.target;
    if (form.dataset.confirm && !form.dataset.confirmed && confirmEl && window.bootstrap) {
      e.preventDefault();
      pendingForm = form;
      pendingSubmitter = e.submitter || null;
      confirmEl.querySelector('[data-confirm-text]').textContent = form.dataset.confirm;
      window.bootstrap.Modal.getOrCreateInstance(confirmEl).show();
      return;
    }
    form.querySelectorAll('button[type=submit]:not([data-no-loading])').forEach(function (b) {
      if (!b.dataset.label) b.dataset.label = b.innerHTML;
      b.classList.add('loading');
      b.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> অপেক্ষা করুন...';
    });
    setTimeout(function () {
      form.querySelectorAll('button.loading').forEach(function (b) { b.disabled = true; });
    }, 0);
  });
  if (confirmEl) {
    confirmEl.querySelector('[data-confirm-ok]').addEventListener('click', function () {
      if (pendingForm) {
        pendingForm.dataset.confirmed = '1';
        window.bootstrap.Modal.getInstance(confirmEl).hide();
        pendingForm.requestSubmit(pendingSubmitter || undefined);
      }
    });
  }
  window.addEventListener('pageshow', function () { // back button: restore buttons
    document.querySelectorAll('button.loading').forEach(function (b) { b.disabled = false; b.classList.remove('loading'); if (b.dataset.label) b.innerHTML = b.dataset.label; });
  });

  /* ---------- Mobile sidebar ---------- */
  const tgl = document.getElementById('sidebarToggle');
  if (tgl) tgl.addEventListener('click', function () { document.getElementById('sidebar').classList.toggle('open'); });

  /* ---------- Dependent location selectors: Division > District > Upazila > Thana ---------- */
  function getJson(url) {
    return fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
      .then((r) => (r.ok ? r.json() : [])).catch(() => []);
  }
  function fill(sel, items, placeholder, coveredOnly) {
    sel.innerHTML = '';
    const p = document.createElement('option');
    p.value = ''; p.textContent = placeholder; sel.appendChild(p);
    items.forEach(function (it) {
      const o = document.createElement('option');
      o.value = it.id; o.textContent = it.name;
      if (it.covered !== undefined) {
        o.dataset.covered = it.covered ? '1' : '0';
        if (coveredOnly && !it.covered) { o.disabled = true; o.textContent += ' (সেবা এলাকার বাইরে)'; }
      }
      sel.appendChild(o);
    });
    sel.disabled = items.length === 0;
  }
  function initPicker(box) {
    const sel = {
      division: box.querySelector('[data-level=division]'), district: box.querySelector('[data-level=district]'),
      upazila: box.querySelector('[data-level=upazila]'), thana: box.querySelector('[data-level=thana]'),
    };
    const coveredOnly = box.dataset.coveredOnly === '1';
    const clear = function (from) {
      const order = ['district', 'upazila', 'thana'];
      const names = { district: 'জেলা', upazila: 'উপজেলা', thana: 'থানা' };
      order.slice(order.indexOf(from)).forEach(function (l) { fill(sel[l], [], names[l] + ' নির্বাচন করুন', false); });
    };
    const notify = function () {
      const o = sel.thana.options[sel.thana.selectedIndex];
      box.dispatchEvent(new CustomEvent('loc:change', { bubbles: true, detail: { thanaId: sel.thana.value, covered: o && o.dataset.covered === '1', text: o ? o.textContent : '' } }));
    };
    const loadDistricts = (div) => getJson(U.districts + '?division=' + encodeURIComponent(div)).then((r) => fill(sel.district, r, 'জেলা নির্বাচন করুন'));
    const loadUpazilas = (id) => getJson(U.upazilas + '?district_id=' + id).then((r) => fill(sel.upazila, r, 'উপজেলা নির্বাচন করুন'));
    const loadThanas = (id) => getJson(U.thanas + '?upazila_id=' + id).then((r) => fill(sel.thana, r, 'থানা নির্বাচন করুন', coveredOnly));

    sel.division.addEventListener('change', function () { clear('district'); notify(); if (sel.division.value) loadDistricts(sel.division.value); });
    sel.district.addEventListener('change', function () { clear('upazila'); notify(); if (sel.district.value) loadUpazilas(sel.district.value); });
    sel.upazila.addEventListener('change', function () { clear('thana'); notify(); if (sel.upazila.value) loadThanas(sel.upazila.value); });
    sel.thana.addEventListener('change', notify);

    getJson(U.divisions).then(function (divs) {
      fill(sel.division, divs.map((d) => ({ id: d, name: d })), 'বিভাগ নির্বাচন করুন');
      clear('district');
      const q = box.dataset.thana ? 'thana_id=' + box.dataset.thana : box.dataset.upazila ? 'upazila_id=' + box.dataset.upazila : box.dataset.district ? 'district_id=' + box.dataset.district : '';
      if (!q) return;
      getJson(U.chain + '?' + q).then(async function (c) {
        if (!c) return;
        sel.division.value = c.division;
        await loadDistricts(c.division); sel.district.value = c.district_id;
        await loadUpazilas(c.district_id);
        if (c.upazila_id) { sel.upazila.value = c.upazila_id; await loadThanas(c.upazila_id); }
        if (c.thana_id) { sel.thana.value = c.thana_id; notify(); }
      });
    });
  }
  document.querySelectorAll('[data-loc-picker]').forEach(initPicker);

  /* ---------- Donation form: service-area popup (modal) ---------- */
  document.querySelectorAll('[data-loc-modal]').forEach(function (wrap) {
    const hidden = wrap.querySelector('[data-loc-target]');
    const label = wrap.querySelector('[data-loc-label]');
    const modalEl = document.getElementById(wrap.dataset.locModal);
    const confirmBtn = modalEl.querySelector('[data-loc-confirm]');
    const warn = modalEl.querySelector('[data-loc-warning]');
    let current = null;
    modalEl.addEventListener('loc:change', function (e) {
      current = e.detail;
      confirmBtn.disabled = !(current.thanaId && current.covered);
      if (warn) warn.classList.toggle('d-none', !(current.thanaId && !current.covered));
    });
    confirmBtn.addEventListener('click', function () {
      if (!current || !current.thanaId) return;
      hidden.value = current.thanaId;
      getJson(U.chain + '?thana_id=' + current.thanaId).then(function (c) {
        label.textContent = c ? c.label : current.text;
        label.closest('.loc-summary').classList.remove('d-none');
      });
      window.bootstrap.Modal.getInstance(modalEl).hide();
    });
    if (hidden.value) {
      getJson(U.chain + '?thana_id=' + hidden.value).then(function (c) {
        if (c) { label.textContent = c.label; label.closest('.loc-summary').classList.remove('d-none'); }
      });
    }
  });

  /* ---------- Admin service-area form: pick several thanas as chips ---------- */
  document.querySelectorAll('[data-area-thanas]').forEach(function (wrap) {
    const box = wrap.querySelector('[data-loc-picker]');
    const list = wrap.querySelector('[data-chip-list]');
    const addBtn = wrap.querySelector('[data-chip-add]');
    const msg = wrap.querySelector('[data-chip-msg]');
    const own = new Set((wrap.dataset.initial || '').split(',').filter(Boolean));
    let current = null;
    box.addEventListener('loc:change', function (e) { current = e.detail; addBtn.disabled = !current.thanaId; msg.textContent = ''; });
    function chip(id, text) {
      if (list.querySelector('[data-id="' + id + '"]')) return;
      const s = document.createElement('span');
      s.className = 'thana-chip'; s.dataset.id = id;
      s.innerHTML = '<span></span><input type="hidden" name="thana_ids[]"><button type="button" aria-label="সরান">&times;</button>';
      s.querySelector('span').textContent = text;
      s.querySelector('input').value = id;
      s.querySelector('button').addEventListener('click', function () { s.remove(); });
      list.appendChild(s);
    }
    list.querySelectorAll('.thana-chip button').forEach(function (b) { b.addEventListener('click', function () { b.closest('.thana-chip').remove(); }); });
    addBtn.addEventListener('click', function () {
      if (!current || !current.thanaId) return;
      if (current.covered && !own.has(String(current.thanaId))) { msg.textContent = 'এই থানা ইতোমধ্যে অন্য সেবা এলাকায় আছে।'; return; }
      chip(current.thanaId, current.text.replace(' (সেবা এলাকার বাইরে)', ''));
    });
  });

  /* ---------- Filter a table by clicking "clear" ---------- */
  document.querySelectorAll('[data-clear-filters]').forEach(function (a) {
    a.addEventListener('click', function (e) { e.preventDefault(); window.location = window.location.pathname; });
  });
})();
