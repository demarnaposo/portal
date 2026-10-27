@AGENTS.md

# Catatan proyek (portal Sertifika)

- Modular monolith: `modules/<Modul>`; modul lain hanya boleh memakai `Modules\<Lain>\Contracts\…`
  (dijaga `tests/Unit/ModuleBoundariesTest.php`). Identity tidak bergantung pada modul lain.
- Dua area, dua guard: portal customer (root `/`, guard `customer`, halaman `resources/js/pages/portal`) dan
  panel admin (`/admin`, guard `admin`, Fortify + 2FA wajib, halaman `resources/js/pages/admin`).
- Harga & katalog selalu dari CMS (`Modules\Cms\Contracts\CmsApi`), tidak pernah dari browser.
- Desain: token di `resources/css/theme.css` (salinan identik `astro/src/styles/theme.css`); skill
  `ui-ux-pro-max`/`design-system` untuk layar baru, `vue-best-practices` untuk komponen Vue.
- UI berbahasa Indonesia. Gaya perubahan: kecil dan tuntas (skill ponytail), tanpa abstraksi spekulatif.
