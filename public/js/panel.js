// Perilaku panel analitik: sidebar yang bisa diciutkan, filter yang langsung diterapkan, dan tooltip.
document.documentElement.classList.add('js');

document.addEventListener('DOMContentLoaded', () => {
    const app = document.getElementById('app');
    const tombol = document.getElementById('tombol-ciut');
    if (document.documentElement.classList.contains('ciut-awal')) {
        document.documentElement.classList.remove('ciut-awal');
        app.classList.add('ciut');
    }
    const sinkron = () => {
        const ciut = app.classList.contains('ciut');
        tombol.setAttribute('aria-expanded', String(!ciut));
        tombol.setAttribute('aria-label', ciut ? 'Lebarkan sidebar' : 'Ciutkan sidebar');
    };
    sinkron();
    tombol.addEventListener('click', () => {
        app.classList.toggle('ciut');
        localStorage.setItem('sibuker-sidebar', app.classList.contains('ciut') ? 'ciut' : 'lebar');
        sinkron();
    });

    // Filter langsung diterapkan begitu pilihan berubah. Tombol Terapkan tetap ada kalau JavaScript mati.
    document.querySelectorAll('form.filter select').forEach((s) => s.addEventListener('change', () => s.form.submit()));

    // Satu tooltip dipakai bersama. Baris pertama data-tip jadi judul, sisanya keterangan.
    const tip = document.querySelector('.tip');
    const tampil = (el, x, y) => {
        const [judul, ...isi] = el.dataset.tip.split('|');
        tip.replaceChildren();
        const b = document.createElement('b'); b.textContent = judul; tip.append(b);
        if (isi.length) { const s = document.createElement('span'); s.textContent = isi.join(' · '); tip.append(s); }
        tip.hidden = false;
        tip.style.left = Math.min(window.innerWidth - tip.offsetWidth - 12, x + 14) + 'px';
        tip.style.top = Math.min(window.innerHeight - tip.offsetHeight - 12, y + 16) + 'px';
    };
    document.querySelectorAll('[data-tip]').forEach((el) => {
        el.addEventListener('mousemove', (e) => tampil(el, e.clientX, e.clientY));
        el.addEventListener('mouseleave', () => { tip.hidden = true; });
        el.addEventListener('focus', () => { const r = el.getBoundingClientRect(); tampil(el, r.left, r.bottom - 10); });
        el.addEventListener('blur', () => { tip.hidden = true; });
    });
});
