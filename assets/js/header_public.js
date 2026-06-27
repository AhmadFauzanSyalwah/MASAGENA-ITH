    // ===== DROPDOWN PROFIL =====
    const profileToggle = document.getElementById('profileToggle');
    const profileMenu = document.getElementById('profileMenu');

    if (profileToggle && profileMenu) {
        profileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = profileMenu.classList.toggle('open');
            this.setAttribute('aria-expanded', isOpen);
        });

        // Tutup dropdown jika klik di luar
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.profile-dropdown')) {
                profileMenu.classList.remove('open');
                profileToggle.setAttribute('aria-expanded', 'false');
            }
        });

        // Tutup dropdown saat klik salah satu link (opsional)
        profileMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function() {
                profileMenu.classList.remove('open');
                profileToggle.setAttribute('aria-expanded', 'false');
            });
        });
    }