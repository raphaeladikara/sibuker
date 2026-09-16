// Perilaku kecil yang dipakai beberapa halaman. Tanpa library.
document.addEventListener('DOMContentLoaded', () => {
    // Konfirmasi sebelum aksi yang menghapus atau mengubah status.
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!confirm(form.dataset.confirm)) e.preventDefault();
        });
    });

    // Menu dropdown tertutup saat klik di luar atau tekan Escape.
    const menus = document.querySelectorAll('details.menu');
    document.addEventListener('click', (e) => {
        menus.forEach((m) => { if (!m.contains(e.target)) m.removeAttribute('open'); });
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') menus.forEach((m) => m.removeAttribute('open'));
    });

    // Dropzone PDF: tampilkan nama berkas yang dipilih.
    document.querySelectorAll('.dropzone').forEach((zone) => {
        const input = zone.querySelector('input[type=file]');
        const nama = zone.querySelector('.nama-berkas');
        if (!input || !nama) return;
        input.addEventListener('change', () => {
            const f = input.files[0];
            nama.textContent = f ? `${f.name} (${Math.ceil(f.size / 1024)} KB)` : nama.dataset.kosong;
            zone.classList.toggle('aktif', !!f);
        });
    });

    // Salin tautan halaman.
    document.querySelectorAll('[data-salin]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(btn.dataset.salin);
                btn.setAttribute('title', 'Tautan tersalin');
                btn.querySelector('.bi').className = 'bi bi-check-lg';
            } catch { /* clipboard ditolak browser, biarkan */ }
        });
    });

    // Isi otomatis akun demo di halaman masuk.
    document.querySelectorAll('[data-demo]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const form = document.querySelector('#form-masuk');
            form.email.value = btn.dataset.demo;
            form.password.value = 'password';
            form.email.focus();
        });
    });

    // Form pemeriksaan: pilihan keahlian dan masa berlaku hanya berarti kalau disetujui.
    const periksa = document.querySelector('#form-periksa');
    if (periksa) {
        const sinkron = () => {
            const setuju = periksa.keputusan.value === 'disetujui';
            periksa.querySelectorAll('[data-hanya-setuju]').forEach((el) => { el.hidden = !setuju; });
        };
        periksa.querySelectorAll('input[name=keputusan]').forEach((r) => r.addEventListener('change', sinkron));
        sinkron();
    }

    // Form daftar: field perusahaan hanya muncul kalau peran perusahaan dipilih.
    const daftar = document.querySelector('#form-daftar');
    if (daftar) {
        const sinkron = () => {
            const perusahaan = daftar.peran.value === 'perusahaan';
            daftar.querySelectorAll('[data-perusahaan]').forEach((el) => { el.hidden = !perusahaan; });
        };
        daftar.querySelectorAll('input[name=peran]').forEach((r) => r.addEventListener('change', sinkron));
        sinkron();
    }
});
