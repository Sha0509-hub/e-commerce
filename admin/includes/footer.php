    </div><!-- /.admin-content -->
  </div><!-- /.admin-main -->

</div><!-- /.admin-layout -->

<!-- Toast container -->
<div class="toast-container" id="toastContainer" aria-live="polite"></div>

<script src="/ecommerce/js/main.js"></script>
<script>
  // Show sidebar toggle on small screens
  (function() {
    const toggle = document.getElementById('sidebarToggle');
    if (toggle && window.innerWidth <= 900) toggle.style.display = 'flex';
    window.addEventListener('resize', () => {
      if (toggle) toggle.style.display = window.innerWidth <= 900 ? 'flex' : 'none';
    });
  })();
</script>
</body>
</html>
