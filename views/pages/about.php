<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- Hero -->
<section class="about-hero">
  <div class="about-hero-content">
    <div class="hero-badge">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      Secure Programming Project
    </div>
    <h1>Building apps that are <span>secure by design</span></h1>
    <p>
      This project demonstrates real-world security practices — CSRF protection,
      bcrypt hashing, SQL-injection prevention, session hardening, and more —
      implemented from scratch in PHP.
    </p>
  </div>
</section>

<!-- Security features -->
<section class="about-content">
  <div class="section-header">
    <h2>Security Measures Implemented</h2>
    <p>Every layer of this application was built with security as the primary goal, not an afterthought.</p>
  </div>

  <div class="security-cards">

    <div class="sec-card">
      <div class="sec-card-icon card-icon-primary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <h3>CSRF Protection</h3>
      <p>Cryptographically random 64-character tokens are bound to every session and validated on each POST request to prevent cross-site request forgery.</p>
    </div>

    <div class="sec-card">
      <div class="sec-card-icon card-icon-success">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      </div>
      <h3>bcrypt Password Hashing</h3>
      <p>User passwords are hashed with <code>password_hash()</code> using the bcrypt algorithm at cost 12. Verified via <code>password_verify()</code> — no plain-text ever stored.</p>
    </div>

    <div class="sec-card">
      <div class="sec-card-icon card-icon-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
      </div>
      <h3>SQL Injection Prevention</h3>
      <p>Every database interaction uses PDO prepared statements with bound parameters. No string concatenation or raw user input is ever passed to SQL.</p>
    </div>

    <div class="sec-card">
      <div class="sec-card-icon card-icon-warning">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
      </div>
      <h3>XSS Prevention</h3>
      <p>All output is escaped via <code>htmlspecialchars()</code> with the <code>ENT_QUOTES</code> flag. A strict Content Security Policy header blocks inline script injection.</p>
    </div>

    <div class="sec-card">
      <div class="sec-card-icon card-icon-error">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
      <h3>Brute-Force Rate Limiting</h3>
      <p>Failed login attempts are tracked per identifier in the database. After 5 failures within 15 minutes the identifier is locked out automatically.</p>
    </div>

    <div class="sec-card">
      <div class="sec-card-icon card-icon-primary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </div>
      <h3>Session Hardening</h3>
      <p>Sessions use <code>HttpOnly</code>, <code>SameSite=Lax</code>, and strict mode cookies. The session ID is regenerated on login to prevent session fixation.</p>
    </div>

    <div class="sec-card">
      <div class="sec-card-icon card-icon-success">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      </div>
      <h3>Secure HTTP Headers</h3>
      <p><code>X-Frame-Options: DENY</code> prevents clickjacking. <code>X-Content-Type-Options: nosniff</code> blocks MIME sniffing. <code>Referrer-Policy</code> limits data leakage.</p>
    </div>

    <div class="sec-card">
      <div class="sec-card-icon card-icon-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
      </div>
      <h3>Input Validation</h3>
      <p>Server-side validation runs on every input: email format, username characters, password complexity, and message length — before any data is trusted or stored.</p>
    </div>

    <div class="sec-card">
      <div class="sec-card-icon card-icon-warning">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
      </div>
      <h3>Secure File Uploads</h3>
      <p>PDF attachments are validated by <strong>magic bytes</strong> (not the client-supplied name or type), capped at 5&nbsp;MB, given a random server-side key, and stored in a <strong>private</strong> Cloudflare R2 bucket as <code>attachment</code>-disposition objects — so an upload can never be executed or served inline.</p>
    </div>

    <div class="sec-card">
      <div class="sec-card-icon card-icon-primary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 11l-3 3-2-2"/></svg>
      </div>
      <h3>Role-Based Access Control</h3>
      <p>Every page requires an authenticated session, and admin-only areas (such as the contact-request viewer) are gated by a server-side <code>requireAdmin()</code> check on each action — never by hiding a link. Roles are assigned in the database; there is no in-app promotion path to exploit.</p>
    </div>

    <div class="sec-card">
      <div class="sec-card-icon card-icon-primary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <h3>End-to-End TLS (Full Strict)</h3>
      <p>Cloudflare is set to <strong>Full (Strict)</strong> encryption. Traffic is encrypted on both legs — visitor to Cloudflare and Cloudflare to origin — and Cloudflare validates the origin's certificate, blocking man-in-the-middle interception.</p>
      <span class="where"><b>Where:</b> Cloudflare · SSL/TLS</span>
    </div>

    <div class="sec-card">
      <div class="sec-card-icon card-icon-success">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L4 5v6c0 5.5 3.8 9.5 8 11 4.2-1.5 8-5.5 8-11V5l-8-3z"/><path d="M9 12l2 2 4-4"/></svg>
      </div>
      <h3>Always HTTPS &amp; HSTS</h3>
      <p>All HTTP requests are upgraded to HTTPS automatically. A <strong>HSTS</strong> policy (6-month max-age) instructs browsers to refuse any insecure connection, and the <code>.dev</code> TLD enforces HTTPS at the registry level.</p>
      <span class="where"><b>Where:</b> Cloudflare · Edge Certificates</span>
    </div>

    <div class="sec-card">
      <div class="sec-card-icon card-icon-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v6m0 6v10M4.2 4.2l4.3 4.3m7 7l4.3 4.3M1 12h6m6 0h10M4.2 19.8l4.3-4.3m7-7l4.3-4.3"/></svg>
      </div>
      <h3>Origin IP Concealment</h3>
      <p>With Cloudflare's proxy enabled, every DNS lookup returns a Cloudflare anycast IP. The Render origin address is hidden from the public, so attackers cannot target the server directly with floods or scans.</p>
      <span class="where"><b>Where:</b> Cloudflare · DNS proxy</span>
    </div>

    <div class="sec-card">
      <div class="sec-card-icon card-icon-warning">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h18v4H3z"/><path d="M3 10h18v4H3z"/><path d="M3 17h18v4H3z"/></svg>
      </div>
      <h3>Edge Rate Limiting</h3>
      <p>A Cloudflare rate-limiting rule guards the <code>/login</code> and <code>/register</code> paths, blocking any IP that exceeds the threshold in a short window — stopping automated credential-stuffing and signup abuse <strong>before</strong> it reaches the origin.</p>
      <span class="where"><b>Where:</b> Cloudflare · Rate limiting</span>
    </div>

    <div class="sec-card">
      <div class="sec-card-icon card-icon-error">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L3 7v6c0 5 4 8 9 9 5-1 9-4 9-9V7l-9-5z"/><line x1="9" y1="12" x2="15" y2="12"/></svg>
      </div>
      <h3>DDoS Mitigation &amp; WAF</h3>
      <p>Cloudflare absorbs volumetric (L3/L4) and application-layer (L7) DDoS attacks automatically. A Web Application Firewall with managed rules filters common exploit patterns such as SQL injection and XSS at the edge.</p>
      <span class="where"><b>Where:</b> Cloudflare · Security</span>
    </div>

    <div class="sec-card">
      <div class="sec-card-icon card-icon-primary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/><circle cx="12" cy="16" r="1"/></svg>
      </div>
      <h3>Origin Verification Secret</h3>
      <p>Cloudflare injects a secret <code>X-Origin-Verify</code> header into every proxied request. The application rejects with <code>403</code> any request that lacks it — using a constant-time <code>hash_equals()</code> check — so the origin cannot be bypassed even if its address leaks.</p>
      <span class="where"><b>Where:</b> Cloudflare → Render origin</span>
    </div>

    <div class="sec-card">
      <div class="sec-card-icon card-icon-success">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/><path d="M9 12l2 2 4-4"/></svg>
      </div>
      <h3>Encrypted Database Connection</h3>
      <p>The MySQL connection to Aiven is encrypted with <strong>TLS</strong>, and the server certificate is verified against Aiven's CA via <code>MYSQL_ATTR_SSL_VERIFY_SERVER_CERT</code>. Traffic between the origin and the database cannot be read or tampered with in transit.</p>
      <span class="where"><b>Where:</b> Render → Aiven MySQL</span>
    </div>

    <div class="sec-card">
      <div class="sec-card-icon card-icon-error">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L3 7v6c0 5 4 8 9 9 5-1 9-4 9-9V7l-9-5z"/><path d="M9 12l2 2 4-4"/></svg>
      </div>
      <h3>Database IP Allow-Listing</h3>
      <p>The Aiven firewall rejects all inbound connections by default and accepts traffic <strong>only</strong> from Render's outbound IP ranges. Even if credentials leaked, the database could not be reached from anywhere else.</p>
      <span class="where"><b>Where:</b> Aiven · Allowed IPs</span>
    </div>

    <div class="sec-card">
      <div class="sec-card-icon card-icon-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M17 11l2 2 4-4"/></svg>
      </div>
      <h3>Least-Privilege DB User</h3>
      <p>The application connects with a dedicated user limited to <code>SELECT</code>, <code>INSERT</code>, <code>UPDATE</code>, and <code>DELETE</code> on a single schema — never the admin account. It cannot drop tables, alter structure, or grant privileges, containing the blast radius of any compromise.</p>
      <span class="where"><b>Where:</b> Aiven MySQL · Grants</span>
    </div>

  </div>
</section>

<!-- Contact form -->
<section class="contact-section" id="contact">
  <div class="contact-inner">

    <?php if (!empty($success)): ?>
      <div class="alert alert-success" style="margin-bottom:1.5rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <span><?= e($success) ?></span>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="alert alert-error" style="margin-bottom:1.5rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div><?= $error ?></div>
      </div>
    <?php endif; ?>

    <div class="section-header">
      <h2>Get in Touch</h2>
      <p>Have a question about secure programming? Send us a message and we'll respond promptly.</p>
    </div>

    <div class="contact-card">
      <form method="POST" action="<?= url('contact') ?>" enctype="multipart/form-data" novalidate>
        <?= csrfField() ?>

        <div class="grid-2">
          <div class="form-group">
            <label class="form-label" for="name">Full name</label>
            <input
              type="text"
              id="name"
              name="name"
              class="form-input"
              placeholder="Jane Smith"
              maxlength="100"
              required
            >
          </div>
          <div class="form-group">
            <label class="form-label" for="contact_email">Email address</label>
            <input
              type="email"
              id="contact_email"
              name="email"
              class="form-input"
              placeholder="jane@example.com"
              required
            >
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="message">Message</label>
          <textarea
            id="message"
            name="message"
            class="form-input"
            placeholder="Tell us what's on your mind…"
            maxlength="2000"
            required
          ></textarea>
        </div>

        <div class="form-group">
          <label class="form-label" for="attachment">Attachment <span style="font-weight:400;opacity:.7;">(optional)</span></label>
          <input
            type="file"
            id="attachment"
            name="attachment"
            class="form-input"
            accept="application/pdf,.pdf"
          >
          <small style="display:block;margin-top:.4rem;opacity:.7;">PDF only, max 5&nbsp;MB.</small>
        </div>

        <button type="submit" class="btn btn-primary btn-full">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          Send Message
        </button>
      </form>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
