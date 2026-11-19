</main><!-- /#main-content -->

<!-- ══════════════ FOOTER ══════════════ -->
<footer class="footer" role="contentinfo">
  <div class="container">
    <div class="footer-grid">

      <!-- Brand -->
      <div class="footer-brand">
        <div class="nav-logo" style="font-size:1.3rem;margin-bottom:1rem;">
          <div class="logo-icon" style="width:30px;height:30px;font-size:0.9rem;background:linear-gradient(135deg,#7c3aed,#4f46e5);border-radius:6px;display:flex;align-items:center;justify-content:center;">⚡</div>
          Nexus<span style="color:#a78bfa;">Store</span>
        </div>
        <p>Your premium destination for electronics, fashion, and lifestyle products. Quality guaranteed, delivered fast.</p>
      </div>

      <!-- Shop Links -->
      <div class="footer-col">
        <h4>Shop</h4>
        <ul>
          <li><a href="/ecommerce/index.php?cat=electronics">Electronics</a></li>
          <li><a href="/ecommerce/index.php?cat=clothing">Clothing</a></li>
          <li><a href="/ecommerce/index.php?cat=accessories">Accessories</a></li>
          <li><a href="/ecommerce/index.php?cat=home-living">Home &amp; Living</a></li>
        </ul>
      </div>

      <!-- Account -->
      <div class="footer-col">
        <h4>Account</h4>
        <ul>
          <li><a href="/ecommerce/login.php">Sign In</a></li>
          <li><a href="/ecommerce/register.php">Register</a></li>
          <li><a href="/ecommerce/profile.php">My Orders</a></li>
          <li><a href="/ecommerce/cart.php">Cart</a></li>
        </ul>
      </div>

      <!-- Support -->
      <div class="footer-col">
        <h4>Support</h4>
        <ul>
          <li><a href="#">Help Center</a></li>
          <li><a href="#">Returns</a></li>
          <li><a href="#">Shipping Info</a></li>
          <li><a href="#">Contact Us</a></li>
        </ul>
      </div>

    </div>

    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> NexusStore. All rights reserved.</p>
      <p>Built with ❤️ using PHP &amp; MySQL</p>
    </div>
  </div>
</footer>
<!-- ════════════════════════════════════ -->

<!-- Toast container (populated by JS) -->
<div class="toast-container" id="toastContainer" aria-live="polite"></div>

<!-- Scripts -->
<script src="/ecommerce/js/main.js"></script>
</body>
</html>
