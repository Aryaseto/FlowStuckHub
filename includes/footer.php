<?php if (user()): ?>
      </div><!-- /.mx-auto -->
    </main>
  </div><!-- /#main-wrap -->
</div><!-- /.min-h-dvh -->

<script>
(function () {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('nav-overlay');
  const openBtn = document.getElementById('nav-open');
  const closeBtn = document.getElementById('nav-close');
  const mainWrap = document.getElementById('main-wrap');
  if (!sidebar || !overlay || !openBtn || !closeBtn) return;

  const desktop = () => window.matchMedia('(min-width: 1024px)').matches;
  const isOpen = () => !sidebar.classList.contains('-translate-x-full');

  function setOpen(open) {
    sidebar.classList.toggle('-translate-x-full', !open);
    overlay.classList.toggle('hidden', !open);
    openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    document.body.classList.toggle('overflow-hidden', open);
    if (mainWrap) mainWrap.inert = open;
    if (open) {
      closeBtn.focus();
    } else if (!desktop() && mainWrap) {
      mainWrap.inert = false;
      openBtn.focus();
    }
  }

  openBtn.addEventListener('click', () => setOpen(true));
  closeBtn.addEventListener('click', () => setOpen(false));
  overlay.addEventListener('click', () => setOpen(false));

  window.addEventListener('resize', () => {
    if (desktop() && isOpen()) setOpen(false);
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isOpen() && !desktop()) {
      setOpen(false);
    }
  });
})();
</script>
<?php endif; ?>
</body>
</html>
