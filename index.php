<?php
/**
 * MoleKVM Landing Page
 * Plesk-compatible PHP wrapper
 * 
 * This file serves as the main entry point for the MoleKVM product website.
 * Compatible with Plesk hosting panels using Apache/Nginx + PHP-FPM.
 */

// Basic security headers for Plesk environments
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Optional: Load environment variables from .env file if exists
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Output the HTML content
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MoleKVM — AI-Powered Remote Control for Any Computer</title>
<meta name="description" content="The world's first AI-powered KVM device. Plug a thumb-sized dongle into any computer and control it remotely with AI autopilot. No drivers. No software. Works even in BIOS.">
<meta property="og:title" content="MoleKVM — AI-Powered Remote Control">
<meta property="og:description" content="Plug USB-C → AI controls anything → From anywhere. €99 device + cloud panel.">
<meta property="og:type" content="website">
<meta property="og:url" content="https://molekvm.com">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://js.stripe.com/v3/"></script>
<style>
:root {
  --earth: #1a1610;
  --tunnel: #0d0b08;
  --soil: #2a2318;
  --clay: #3d3225;
  --amber: #f0a030;
  --amber-glow: #f0a03040;
  --amber-bright: #ffc040;
  --mole-pink: #e8a0a0;
  --root: #5a7040;
  --root-light: #8aad60;
  --cream: #f0e8d8;
  --cream-dim: #c0b8a8;
  --danger: #d04030;
  --font-display: 'Outfit', sans-serif;
  --font-mono: 'Space Mono', monospace;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

html { scroll-behavior: smooth; }

body {
  background: var(--tunnel);
  color: var(--cream);
  font-family: var(--font-display);
  line-height: 1.6;
  overflow-x: hidden;
}

/* ═══ TEXTURE OVERLAY ═══ */
body::before {
  content: '';
  position: fixed;
  inset: 0;
  background: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
  pointer-events: none;
  z-index: 9999;
}

/* ═══ NAV ═══ */
nav {
  position: fixed;
  top: 0;
  width: 100%;
  z-index: 100;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--tunnel);
  border-bottom: 1px solid var(--clay);
  backdrop-filter: blur(20px);
}

.nav-logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
}

.nav-logo svg { width: 32px; height: 32px; }

.nav-logo span {
  font-family: var(--font-mono);
  font-weight: 700;
  font-size: 1.1rem;
  color: var(--amber);
  letter-spacing: 2px;
}

.nav-links {
  display: flex;
  gap: 2rem;
  align-items: center;
}

.nav-links a {
  color: var(--cream-dim);
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 500;
  letter-spacing: 1px;
  text-transform: uppercase;
  transition: color 0.3s;
}

.nav-links a:hover { color: var(--amber); }

.nav-cta {
  background: var(--amber) !important;
  color: var(--tunnel) !important;
  padding: 0.5rem 1.2rem;
  border-radius: 4px;
  font-weight: 700 !important;
  transition: transform 0.2s, box-shadow 0.3s !important;
}

.nav-cta:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 20px var(--amber-glow);
}

/* ═══ HERO ═══ */
.hero {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding: 8rem 2rem 4rem;
  position: relative;
  overflow: hidden;
}

.hero::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 800px;
  height: 800px;
  background: radial-gradient(circle, var(--amber-glow) 0%, transparent 70%);
  opacity: 0.3;
  animation: pulse-glow 4s ease-in-out infinite;
}

@keyframes pulse-glow {
  0%, 100% { opacity: 0.2; transform: translate(-50%, -50%) scale(1); }
  50% { opacity: 0.4; transform: translate(-50%, -50%) scale(1.1); }
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: var(--soil);
  border: 1px solid var(--clay);
  padding: 0.4rem 1rem;
  border-radius: 100px;
  font-size: 0.8rem;
  font-weight: 500;
  color: var(--amber);
  margin-bottom: 2rem;
  position: relative;
  z-index: 1;
  animation: fadeInUp 0.8s ease-out;
}

.hero-badge::before {
  content: '';
  width: 6px;
  height: 6px;
  background: var(--root-light);
  border-radius: 50%;
  animation: blink 2s ease-in-out infinite;
}

@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.3; }
}

.hero h1 {
  font-size: clamp(3rem, 8vw, 6.5rem);
  font-weight: 900;
  line-height: 0.95;
  letter-spacing: -3px;
  position: relative;
  z-index: 1;
  animation: fadeInUp 0.8s ease-out 0.1s both;
}

.hero h1 .accent {
  background: linear-gradient(135deg, var(--amber), var(--amber-bright));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-sub {
  font-size: clamp(1.1rem, 2.5vw, 1.4rem);
  color: var(--cream-dim);
  max-width: 640px;
  margin: 1.5rem auto 0;
  font-weight: 300;
  position: relative;
  z-index: 1;
  animation: fadeInUp 0.8s ease-out 0.2s both;
}

.hero-sub strong { color: var(--cream); font-weight: 600; }

.hero-actions {
  display: flex;
  gap: 1rem;
  margin-top: 3rem;
  position: relative;
  z-index: 1;
  animation: fadeInUp 0.8s ease-out 0.3s both;
  flex-wrap: wrap;
  justify-content: center;
}

.btn-primary {
  background: var(--amber);
  color: var(--tunnel);
  padding: 1rem 2.5rem;
  border: none;
  border-radius: 6px;
  font-family: var(--font-display);
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 30px var(--amber-glow);
}

.btn-secondary {
  background: transparent;
  color: var(--cream);
  padding: 1rem 2.5rem;
  border: 1px solid var(--clay);
  border-radius: 6px;
  font-family: var(--font-display);
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
}

.btn-secondary:hover {
  border-color: var(--amber);
  color: var(--amber);
}

.hero-stats {
  display: flex;
  gap: 3rem;
  margin-top: 4rem;
  position: relative;
  z-index: 1;
  animation: fadeInUp 0.8s ease-out 0.4s both;
}

.hero-stat {
  text-align: center;
}

.hero-stat .num {
  font-family: var(--font-mono);
  font-size: 2rem;
  font-weight: 700;
  color: var(--amber);
}

.hero-stat .label {
  font-size: 0.75rem;
  color: var(--cream-dim);
  text-transform: uppercase;
  letter-spacing: 2px;
  margin-top: 0.2rem;
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ═══ SECTION COMMON ═══ */
section {
  padding: 6rem 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.section-label {
  font-family: var(--font-mono);
  font-size: 0.7rem;
  color: var(--amber);
  text-transform: uppercase;
  letter-spacing: 4px;
  margin-bottom: 1rem;
}

.section-title {
  font-size: clamp(2rem, 4vw, 3rem);
  font-weight: 800;
  letter-spacing: -1px;
  margin-bottom: 1rem;
}

.section-desc {
  color: var(--cream-dim);
  max-width: 600px;
  font-weight: 300;
  font-size: 1.1rem;
  margin-bottom: 3rem;
}

/* ═══ HOW IT WORKS ═══ */
.steps-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
}

.step-card {
  background: var(--soil);
  border: 1px solid var(--clay);
  border-radius: 12px;
  padding: 2.5rem 2rem;
  position: relative;
  transition: all 0.4s;
}

.step-card:hover {
  border-color: var(--amber);
  transform: translateY(-4px);
  box-shadow: 0 20px 60px rgba(0,0,0,0.4);
}

.step-num {
  font-family: var(--font-mono);
  font-size: 3rem;
  font-weight: 700;
  color: var(--clay);
  position: absolute;
  top: 1rem;
  right: 1.5rem;
}

.step-card h3 {
  font-size: 1.3rem;
  font-weight: 700;
  margin-bottom: 0.8rem;
}

.step-card p {
  color: var(--cream-dim);
  font-size: 0.95rem;
  font-weight: 300;
}

.step-icon {
  width: 48px;
  height: 48px;
  background: var(--amber);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.5rem;
  font-size: 1.5rem;
}

/* ═══ USE CASES / USER STORIES ═══ */
.stories-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.story-card {
  background: var(--earth);
  border: 1px solid var(--clay);
  border-radius: 12px;
  padding: 2rem;
  transition: all 0.3s;
  position: relative;
  overflow: hidden;
}

.story-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: var(--amber);
  opacity: 0;
  transition: opacity 0.3s;
}

.story-card:hover::before { opacity: 1; }
.story-card:hover { border-color: var(--clay); transform: translateX(4px); }

.story-persona {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  margin-bottom: 1rem;
}

.story-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  flex-shrink: 0;
}

.story-avatar.admin { background: #2d4a7a; }
.story-avatar.devops { background: #4a2d6a; }
.story-avatar.msp { background: #2d6a4a; }
.story-avatar.pentest { background: #6a2d2d; }
.story-avatar.homelab { background: #5a5a2d; }
.story-avatar.field { background: #2d5a5a; }

.story-role {
  font-size: 0.75rem;
  color: var(--amber);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.story-name {
  font-size: 0.85rem;
  color: var(--cream-dim);
}

.story-quote {
  font-style: italic;
  font-size: 0.95rem;
  color: var(--cream);
  margin-bottom: 1rem;
  line-height: 1.5;
}

.story-result {
  font-family: var(--font-mono);
  font-size: 0.8rem;
  color: var(--root-light);
  padding: 0.5rem 0.8rem;
  background: rgba(90,112,64,0.15);
  border-radius: 6px;
  display: inline-block;
}

/* ═══ PRICING ═══ */
.pricing-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
  align-items: start;
}

.price-card {
  background: var(--soil);
  border: 1px solid var(--clay);
  border-radius: 16px;
  padding: 2.5rem 2rem;
  transition: all 0.3s;
  position: relative;
}

.price-card.featured {
  border-color: var(--amber);
  background: linear-gradient(180deg, var(--soil) 0%, rgba(240,160,48,0.05) 100%);
  transform: scale(1.05);
}

.price-card.featured::after {
  content: 'MOST POPULAR';
  position: absolute;
  top: -12px;
  left: 50%;
  transform: translateX(-50%);
  background: var(--amber);
  color: var(--tunnel);
  font-size: 0.65rem;
  font-weight: 800;
  padding: 0.3rem 1rem;
  border-radius: 100px;
  letter-spacing: 2px;
}

.price-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.price-card.featured:hover {
  transform: scale(1.05) translateY(-4px);
}

.price-tier {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--amber);
  text-transform: uppercase;
  letter-spacing: 2px;
  margin-bottom: 0.5rem;
}

.price-amount {
  font-size: 3rem;
  font-weight: 900;
  letter-spacing: -2px;
}

.price-amount .currency { font-size: 1.5rem; vertical-align: super; }
.price-amount .period { font-size: 0.9rem; color: var(--cream-dim); font-weight: 300; }

.price-desc {
  color: var(--cream-dim);
  font-size: 0.9rem;
  margin: 1rem 0 1.5rem;
  font-weight: 300;
}

.price-features {
  list-style: none;
  margin-bottom: 2rem;
}

.price-features li {
  padding: 0.4rem 0;
  font-size: 0.9rem;
  color: var(--cream-dim);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.price-features li::before {
  content: '✓';
  color: var(--root-light);
  font-weight: 700;
  font-size: 0.8rem;
}

.price-btn {
  width: 100%;
  padding: 0.9rem;
  border: none;
  border-radius: 8px;
  font-family: var(--font-display);
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
}

.price-btn.primary {
  background: var(--amber);
  color: var(--tunnel);
}

.price-btn.primary:hover {
  box-shadow: 0 8px 30px var(--amber-glow);
  transform: translateY(-1px);
}

.price-btn.outline {
  background: transparent;
  color: var(--cream);
  border: 1px solid var(--clay);
}

.price-btn.outline:hover {
  border-color: var(--amber);
  color: var(--amber);
}

/* ═══ KICKSTARTER BANNER ═══ */
.kickstarter {
  background: linear-gradient(135deg, #0a3d2a 0%, #0d1a10 50%, #1a0d05 100%);
  border: 1px solid var(--root);
  border-radius: 20px;
  padding: 4rem 3rem;
  margin: 2rem auto;
  max-width: 1200px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3rem;
  align-items: center;
  position: relative;
  overflow: hidden;
}

.kickstarter::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -20%;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(138,173,96,0.1) 0%, transparent 70%);
}

.ks-content { position: relative; z-index: 1; }

.ks-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(138,173,96,0.15);
  border: 1px solid var(--root);
  padding: 0.3rem 0.8rem;
  border-radius: 100px;
  font-size: 0.75rem;
  color: var(--root-light);
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin-bottom: 1.5rem;
}

.ks-content h2 {
  font-size: 2.5rem;
  font-weight: 800;
  letter-spacing: -1px;
  margin-bottom: 1rem;
}

.ks-content p {
  color: var(--cream-dim);
  font-size: 1rem;
  margin-bottom: 2rem;
  font-weight: 300;
  max-width: 480px;
}

.ks-tiers { position: relative; z-index: 1; }

.ks-tier {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(138,173,96,0.2);
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: all 0.3s;
}

.ks-tier:hover {
  border-color: var(--root-light);
  background: rgba(138,173,96,0.05);
}

.ks-tier-info h4 {
  font-size: 1rem;
  font-weight: 700;
  margin-bottom: 0.2rem;
}

.ks-tier-info p {
  font-size: 0.8rem;
  color: var(--cream-dim);
  margin: 0;
}

.ks-tier-price {
  font-family: var(--font-mono);
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--root-light);
}

/* ═══ SPECS ═══ */
.specs-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
}

.spec-item {
  background: var(--soil);
  border: 1px solid var(--clay);
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
  transition: all 0.3s;
}

.spec-item:hover { border-color: var(--amber); }

.spec-val {
  font-family: var(--font-mono);
  font-size: 1.8rem;
  font-weight: 700;
  color: var(--amber);
}

.spec-label {
  font-size: 0.8rem;
  color: var(--cream-dim);
  margin-top: 0.3rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* ═══ COMPARISON TABLE ═══ */
.compare-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 2rem;
}

.compare-table th,
.compare-table td {
  padding: 1rem 1.2rem;
  text-align: left;
  border-bottom: 1px solid var(--clay);
  font-size: 0.9rem;
}

.compare-table th {
  color: var(--cream-dim);
  font-weight: 500;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 2px;
}

.compare-table td:first-child { font-weight: 600; }

.compare-table .highlight {
  background: rgba(240,160,48,0.05);
  border-left: 3px solid var(--amber);
}

.compare-table .highlight td { color: var(--amber-bright); }

.check { color: var(--root-light); }
.cross { color: var(--cream-dim); opacity: 0.4; }

/* ═══ FAQ ═══ */
.faq-list { max-width: 700px; }

.faq-item {
  border-bottom: 1px solid var(--clay);
  padding: 1.5rem 0;
}

.faq-q {
  font-weight: 600;
  font-size: 1.05rem;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.faq-q::after {
  content: '+';
  font-family: var(--font-mono);
  font-size: 1.2rem;
  color: var(--amber);
  transition: transform 0.3s;
}

.faq-item.open .faq-q::after { transform: rotate(45deg); }

.faq-a {
  color: var(--cream-dim);
  font-size: 0.9rem;
  font-weight: 300;
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.4s ease, padding 0.3s;
}

.faq-item.open .faq-a {
  max-height: 300px;
  padding-top: 1rem;
}

/* ═══ CTA FOOTER ═══ */
.cta-section {
  text-align: center;
  padding: 8rem 2rem;
  position: relative;
}

.cta-section::before {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 600px;
  height: 400px;
  background: radial-gradient(ellipse, var(--amber-glow) 0%, transparent 70%);
  opacity: 0.2;
}

.cta-section h2 {
  font-size: clamp(2.5rem, 5vw, 4rem);
  font-weight: 900;
  letter-spacing: -2px;
  position: relative;
  z-index: 1;
}

.cta-section p {
  color: var(--cream-dim);
  font-size: 1.1rem;
  margin: 1rem auto 2.5rem;
  max-width: 500px;
  position: relative;
  z-index: 1;
}

/* ═══ FOOTER ═══ */
footer {
  border-top: 1px solid var(--clay);
  padding: 3rem 2rem;
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

footer .copy {
  font-size: 0.8rem;
  color: var(--cream-dim);
}

footer .links {
  display: flex;
  gap: 1.5rem;
}

footer .links a {
  color: var(--cream-dim);
  text-decoration: none;
  font-size: 0.8rem;
  transition: color 0.3s;
}

footer .links a:hover { color: var(--amber); }

/* ═══ MODAL ═══ */
.modal-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.8);
  backdrop-filter: blur(10px);
  z-index: 1000;
  justify-content: center;
  align-items: center;
}

.modal-overlay.active { display: flex; }

.modal {
  background: var(--earth);
  border: 1px solid var(--clay);
  border-radius: 16px;
  padding: 3rem;
  width: 90%;
  max-width: 480px;
  position: relative;
}

.modal-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: none;
  border: none;
  color: var(--cream-dim);
  font-size: 1.5rem;
  cursor: pointer;
}

.modal h3 {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.modal p {
  color: var(--cream-dim);
  font-size: 0.9rem;
  margin-bottom: 1.5rem;
}

.modal-form { display: flex; flex-direction: column; gap: 1rem; }

.modal-form input,
.modal-form select {
  background: var(--soil);
  border: 1px solid var(--clay);
  border-radius: 8px;
  padding: 0.8rem 1rem;
  color: var(--cream);
  font-family: var(--font-display);
  font-size: 0.95rem;
  outline: none;
  transition: border-color 0.3s;
}

.modal-form input:focus,
.modal-form select:focus { border-color: var(--amber); }

.modal-form input::placeholder { color: var(--cream-dim); }

#stripe-card {
  background: var(--soil);
  border: 1px solid var(--clay);
  border-radius: 8px;
  padding: 0.9rem 1rem;
}

.modal-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;
  border-top: 1px solid var(--clay);
  font-weight: 600;
}

.modal-total .amount {
  font-family: var(--font-mono);
  font-size: 1.3rem;
  color: var(--amber);
}

/* ═══ NOTIFICATION ═══ */
.toast {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  background: var(--soil);
  border: 1px solid var(--root);
  border-radius: 12px;
  padding: 1rem 1.5rem;
  z-index: 2000;
  display: flex;
  align-items: center;
  gap: 0.8rem;
  transform: translateY(100px);
  opacity: 0;
  transition: all 0.4s;
}

.toast.show { transform: translateY(0); opacity: 1; }

.toast-icon {
  width: 32px;
  height: 32px;
  background: var(--root);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ═══ RESPONSIVE ═══ */
@media (max-width: 900px) {
  .steps-grid,
  .pricing-grid { grid-template-columns: 1fr; }
  .stories-grid { grid-template-columns: 1fr; }
  .specs-grid { grid-template-columns: repeat(2, 1fr); }
  .kickstarter { grid-template-columns: 1fr; padding: 2.5rem 2rem; }
  .price-card.featured { transform: none; }
  .price-card.featured:hover { transform: translateY(-4px); }
  .nav-links { display: none; }
  .hero-stats { gap: 1.5rem; }
  .compare-table { font-size: 0.8rem; }
  .compare-table th, .compare-table td { padding: 0.6rem; }
}
</style>
</head>
<body>

<!-- ═══ NAV ═══ -->
<nav>
  <a href="#" class="nav-logo">
    <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="16" cy="16" r="14" stroke="#f0a030" stroke-width="2" fill="none"/>
      <circle cx="12" cy="13" r="2" fill="#f0a030"/>
      <circle cx="20" cy="13" r="2" fill="#f0a030"/>
      <ellipse cx="16" cy="18" rx="4" ry="3" fill="#f0a030"/>
      <circle cx="16" cy="17" rx="2" ry="1.5" fill="#1a1610"/>
    </svg>
    <span>MOLEKVM</span>
  </a>
  <div class="nav-links">
    <a href="#how">How it works</a>
    <a href="#stories">Use Cases</a>
    <a href="#pricing">Pricing</a>
    <a href="#kickstarter">Kickstarter</a>
    <a href="#faq">FAQ</a>
    <a href="#pricing" class="nav-cta">Pre-order →</a>
  </div>
</nav>

<!-- ═══ HERO ═══ -->
<section class="hero">
  <div class="hero-badge">
    <span></span>
    Now accepting pre-orders — ships April 2026
  </div>
  <h1>
    Your AI mole<br>
    <span class="accent">inside any computer</span>
  </h1>
  <p class="hero-sub">
    Plug this <strong>thumb-sized device</strong> into any USB-C port. An AI assistant takes over keyboard &amp; mouse, reads the screen through a built-in camera, and <strong>executes your commands autonomously</strong> — from anywhere.
  </p>
  <div class="hero-actions">
    <a href="#pricing" class="btn-primary">Pre-order for €99 →</a>
    <a href="#how" class="btn-secondary">See how it works</a>
  </div>
  <div class="hero-stats">
    <div class="hero-stat">
      <div class="num">21mm</div>
      <div class="label">Device size</div>
    </div>
    <div class="hero-stat">
      <div class="num">€99</div>
      <div class="label">Complete kit</div>
    </div>
    <div class="hero-stat">
      <div class="num">0</div>
      <div class="label">Drivers needed</div>
    </div>
    <div class="hero-stat">
      <div class="num">∞</div>
      <div class="label">OS supported</div>
    </div>
  </div>
</section>

<!-- ═══ HOW IT WORKS ═══ -->
<section id="how">
  <div class="section-label">How it works</div>
  <h2 class="section-title">Three steps to remote control</h2>
  <p class="section-desc">No software installation on the target machine. No network configuration. No drivers. Just plug and control.</p>
  <div class="steps-grid">
    <div class="step-card">
      <div class="step-num">01</div>
      <div class="step-icon">🔌</div>
      <h3>Plug into USB-C</h3>
      <p>MoleKVM appears as a standard keyboard and mouse. Works instantly with any OS — Windows, Linux, macOS, even BIOS and bootloaders. The built-in camera points at the screen.</p>
    </div>
    <div class="step-card">
      <div class="step-num">02</div>
      <div class="step-icon">📡</div>
      <h3>Connects via WiFi</h3>
      <p>The device joins your network automatically. Access the control panel from any browser — locally, through VPN, or via our secure cloud relay. No port forwarding needed.</p>
    </div>
    <div class="step-card">
      <div class="step-num">03</div>
      <div class="step-icon">🤖</div>
      <h3>AI takes over</h3>
      <p>Tell the AI what to do in plain language. It reads the screen via camera, types commands, clicks buttons, and verifies results — all autonomously. You just watch and approve.</p>
    </div>
  </div>
</section>

<!-- ═══ SPECS ═══ -->
<section>
  <div class="section-label">Hardware</div>
  <h2 class="section-title">Tiny but powerful</h2>
  <div class="specs-grid">
    <div class="spec-item">
      <div class="spec-val">240MHz</div>
      <div class="spec-label">Dual-core CPU</div>
    </div>
    <div class="spec-item">
      <div class="spec-val">3MP</div>
      <div class="spec-label">OV3660 Camera</div>
    </div>
    <div class="spec-item">
      <div class="spec-val">8MB</div>
      <div class="spec-label">PSRAM</div>
    </div>
    <div class="spec-item">
      <div class="spec-val">WiFi</div>
      <div class="spec-label">+ BLE 5.0</div>
    </div>
    <div class="spec-item">
      <div class="spec-val">USB-C</div>
      <div class="spec-label">HID + Power</div>
    </div>
    <div class="spec-item">
      <div class="spec-val">32GB</div>
      <div class="spec-label">microSD slot</div>
    </div>
    <div class="spec-item">
      <div class="spec-val">14μA</div>
      <div class="spec-label">Deep sleep</div>
    </div>
    <div class="spec-item">
      <div class="spec-val">21mm</div>
      <div class="spec-label">Width</div>
    </div>
  </div>
</section>

<!-- ═══ COMPARISON ═══ -->
<section>
  <div class="section-label">Comparison</div>
  <h2 class="section-title">How MoleKVM stacks up</h2>
  <p class="section-desc">The world's first KVM with AI autopilot, at a fraction of the price.</p>
  <table class="compare-table">
    <thead>
      <tr>
        <th>Feature</th>
        <th>NanoKVM</th>
        <th>JetKVM</th>
        <th>PiKVM V4</th>
        <th class="highlight">MoleKVM</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Price</td>
        <td>€40–50</td>
        <td>€115–140</td>
        <td>€349</td>
        <td class="highlight">€99</td>
      </tr>
      <tr>
        <td>AI Autopilot</td>
        <td class="cross">✗</td>
        <td class="cross">✗</td>
        <td class="cross">✗</td>
        <td class="highlight"><span class="check">✓ LLM-powered</span></td>
      </tr>
      <tr>
        <td>Video capture</td>
        <td>HDMI dongle</td>
        <td>HDMI built-in</td>
        <td>HDMI built-in</td>
        <td class="highlight">Camera built-in</td>
      </tr>
      <tr>
        <td>HDMI cable needed</td>
        <td class="check">✓</td>
        <td class="check">✓</td>
        <td class="check">✓</td>
        <td class="highlight"><span class="check">✗ No cables</span></td>
      </tr>
      <tr>
        <td>Subscription</td>
        <td>None</td>
        <td>Free cloud</td>
        <td>None</td>
        <td class="highlight">Free tier + AI Pro</td>
      </tr>
      <tr>
        <td>Security audit</td>
        <td class="cross">F rating</td>
        <td class="check">Passed</td>
        <td class="check">A+ rating</td>
        <td class="highlight"><span class="check">Open-source</span></td>
      </tr>
      <tr>
        <td>Size</td>
        <td>55×22mm</td>
        <td>62×42mm</td>
        <td>120×68mm</td>
        <td class="highlight">21×17mm</td>
      </tr>
      <tr>
        <td>USB Mass Storage</td>
        <td class="cross">✗</td>
        <td class="check">✓</td>
        <td class="check">✓</td>
        <td class="highlight"><span class="check">✓ microSD</span></td>
      </tr>
    </tbody>
  </table>
</section>

<!-- ═══ USER STORIES ═══ -->
<section id="stories">
  <div class="section-label">Use cases</div>
  <h2 class="section-title">Who is MoleKVM for?</h2>
  <p class="section-desc">Real problems solved by a device that fits on your keychain.</p>
  <div class="stories-grid">
    <div class="story-card">
      <div class="story-persona">
        <div class="story-avatar admin">🖥️</div>
        <div>
          <div class="story-role">IT Administrator</div>
          <div class="story-name">Marcus, Munich — 200 workstations</div>
        </div>
      </div>
      <div class="story-quote">"A user locked themselves out of BIOS on a machine in our warehouse. Instead of driving 40 minutes, I told the AI: 'Enter BIOS, reset password to default, save and exit.' Done in 3 minutes."</div>
      <div class="story-result">→ Saved 2h + €80 per incident</div>
    </div>
    <div class="story-card">
      <div class="story-persona">
        <div class="story-avatar msp">🏢</div>
        <div>
          <div class="story-role">MSP / Managed Service Provider</div>
          <div class="story-name">Agnieszka, Warsaw — 45 client sites</div>
        </div>
      </div>
      <div class="story-quote">"We deploy MoleKVM at every client site. When a server needs OS reinstall at 2 AM, I open the panel, mount an ISO from the microSD, and let the AI guide the installation. No on-site visit."</div>
      <div class="story-result">→ €4,500/mo revenue from 45 devices × €99/mo</div>
    </div>
    <div class="story-card">
      <div class="story-persona">
        <div class="story-avatar devops">⚙️</div>
        <div>
          <div class="story-role">DevOps / Homelab</div>
          <div class="story-name">Sven, Stockholm — 8 headless servers</div>
        </div>
      </div>
      <div class="story-quote">"My Proxmox cluster doesn't have IPMI. I plugged MoleKVM into each node — now I have BIOS-level access to every machine via one dashboard. Total cost: €800 instead of €8,000 for IPMI cards."</div>
      <div class="story-result">→ 90% cost reduction vs enterprise IPMI</div>
    </div>
    <div class="story-card">
      <div class="story-persona">
        <div class="story-avatar field">🔧</div>
        <div>
          <div class="story-role">Field Service Technician</div>
          <div class="story-name">Pierre, Lyon — on-site repairs</div>
        </div>
      </div>
      <div class="story-quote">"I carry one MoleKVM in my pocket instead of a toolkit. I plug it into the client's machine, the camera sees the screen, and I troubleshoot from my phone while explaining the fix to the client."</div>
      <div class="story-result">→ Replaced €500 toolkit with €99 device</div>
    </div>
    <div class="story-card">
      <div class="story-persona">
        <div class="story-avatar pentest">🔒</div>
        <div>
          <div class="story-role">Security Auditor</div>
          <div class="story-name">Elena, Berlin — penetration testing</div>
        </div>
      </div>
      <div class="story-quote">"For USB security audits, MoleKVM with the AI autopilot runs our entire HID attack test suite automatically. It probes USB port policies, types test payloads, and documents results — all unattended."</div>
      <div class="story-result">→ 4h audit reduced to 15min automated scan</div>
    </div>
    <div class="story-card">
      <div class="story-persona">
        <div class="story-avatar homelab">🏭</div>
        <div>
          <div class="story-role">Data Center / NOC</div>
          <div class="story-name">Jan, Amsterdam — 200-rack facility</div>
        </div>
      </div>
      <div class="story-quote">"We mount MoleKVM devices in server racks — the camera watches the front panel LEDs. The AI detects amber/red status lights and creates tickets before we even notice. It's like IPMI + monitoring for €99/port."</div>
      <div class="story-result">→ €99/port vs €3,500/port for Raritan</div>
    </div>
  </div>
</section>

<!-- ═══ PRICING ═══ -->
<section id="pricing">
  <div class="section-label">Pricing</div>
  <h2 class="section-title">Simple, transparent pricing</h2>
  <p class="section-desc">Hardware + optional AI cloud. No lock-in — self-host everything if you want.</p>
  <div class="pricing-grid">
    <div class="price-card">
      <div class="price-tier">Device Only</div>
      <div class="price-amount"><span class="currency">€</span>99</div>
      <div class="price-desc">MoleKVM hardware with free local-access tier. Self-host the GUI server.</div>
      <ul class="price-features">
        <li>MoleKVM device + MJF nylon case</li>
        <li>USB-C cable included</li>
        <li>Local WiFi control panel</li>
        <li>Open-source firmware</li>
        <li>Manual keyboard &amp; mouse</li>
        <li>Community support</li>
      </ul>
      <button class="price-btn outline" onclick="openCheckout('device', 99)">Pre-order device</button>
    </div>
    <div class="price-card featured">
      <div class="price-tier">MoleKVM + AI Pro</div>
      <div class="price-amount"><span class="currency">€</span>99 <span class="period">+ €9.99/mo</span></div>
      <div class="price-desc">Full AI-powered autonomous control from anywhere in the world.</div>
      <ul class="price-features">
        <li>Everything in Device Only</li>
        <li>AI Autopilot (unlimited commands)</li>
        <li>Cloud access from anywhere</li>
        <li>61 one-click repair plans</li>
        <li>Multi-device dashboard</li>
        <li>Priority support</li>
        <li>API access</li>
      </ul>
      <button class="price-btn primary" onclick="openCheckout('pro', 99)">Pre-order + AI Pro →</button>
    </div>
    <div class="price-card">
      <div class="price-tier">Fleet / Enterprise</div>
      <div class="price-amount"><span class="currency">€</span>79 <span class="period">/device + custom</span></div>
      <div class="price-desc">For MSPs and IT teams managing 10+ machines. Volume pricing.</div>
      <ul class="price-features">
        <li>10+ devices at €79 each</li>
        <li>Fleet management dashboard</li>
        <li>DQL query language</li>
        <li>Team access &amp; audit logs</li>
        <li>Custom repair plans</li>
        <li>SLA support</li>
        <li>On-premise server option</li>
      </ul>
      <button class="price-btn outline" onclick="openCheckout('fleet', 79)">Contact sales</button>
    </div>
  </div>
</section>

<!-- ═══ KICKSTARTER ═══ -->
<section id="kickstarter" style="max-width: 100%; padding: 4rem 2rem;">
  <div class="kickstarter">
    <div class="ks-content">
      <div class="ks-badge">🚀 Coming to Kickstarter — Q2 2026</div>
      <h2>Back the mole.<br>Shape the future.</h2>
      <p>Early backers get exclusive pricing, lifetime AI Pro access, and a voice in product development. We're building the cheapest AI-powered KVM in the world — and you can be part of it.</p>
      <a href="#" class="btn-primary" id="ks-notify">Notify me on launch →</a>
    </div>
    <div class="ks-tiers">
      <div class="ks-tier">
        <div class="ks-tier-info">
          <h4>🐛 Early Mole</h4>
          <p>1× MoleKVM device + 12 months AI Pro</p>
        </div>
        <div class="ks-tier-price">€69</div>
      </div>
      <div class="ks-tier">
        <div class="ks-tier-info">
          <h4>🔧 IT Pro Pack</h4>
          <p>3× MoleKVM + lifetime AI Pro + API</p>
        </div>
        <div class="ks-tier-price">€179</div>
      </div>
      <div class="ks-tier">
        <div class="ks-tier-info">
          <h4>🏢 MSP Fleet</h4>
          <p>10× MoleKVM + fleet dashboard + SLA</p>
        </div>
        <div class="ks-tier-price">€549</div>
      </div>
      <div class="ks-tier">
        <div class="ks-tier-info">
          <h4>🏭 Data Center</h4>
          <p>50× MoleKVM + on-premise server + custom</p>
        </div>
        <div class="ks-tier-price">€2,499</div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ FAQ ═══ -->
<section id="faq">
  <div class="section-label">FAQ</div>
  <h2 class="section-title">Questions & answers</h2>
  <div class="faq-list">
    <div class="faq-item">
      <div class="faq-q">How does the camera-based screen capture work?</div>
      <div class="faq-a">MoleKVM uses a 3MP OV3660 camera built into the device. You mount it on a small clip facing the monitor. It captures the screen at 1–2 fps and sends JPEG frames over WiFi to the control panel. AI vision models (GPT-4o, Gemini) can read text from these frames with high accuracy — enough for system administration tasks.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Is it as good as HDMI capture?</div>
      <div class="faq-a">For pixel-perfect work or gaming — no. For IT administration, BIOS access, OS installation, troubleshooting — absolutely. The AI compensates for any image quality differences by using advanced OCR and contextual understanding. And you save €50–150 on HDMI capture hardware plus the cable.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Does it work without the AI subscription?</div>
      <div class="faq-a">Yes! The free tier gives you full manual remote keyboard/mouse control over your local network. The AI Pro subscription adds autonomous task execution, cloud access from anywhere, and the 61 pre-built repair plans. You can also self-host the entire GUI server — it's open-source.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">What about security?</div>
      <div class="faq-a">MoleKVM firmware is fully open-source (Apache 2.0) — audit it yourself. All cloud communication uses end-to-end encryption. Unlike NanoKVM (which received an "F" security rating), we have no hidden microphones, no hardcoded credentials, and enforce password changes on first setup. EU-hosted (Hetzner), GDPR-compliant.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">Can I use it as a USB drive too?</div>
      <div class="faq-a">Yes. The microSD slot supports up to 32GB. MoleKVM appears as a composite USB device — keyboard + mouse + mass storage simultaneously. Pre-load installation ISOs, drivers, or config files, and the AI can use them during automated tasks.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q">How does Kickstarter delivery work?</div>
      <div class="faq-a">We ship from Poland (EU) to all European countries. Early Mole backers ship first (estimated Q3 2026). Each unit is assembled and flashed with the latest firmware before shipping. EU customs duties are included in the price for EU backers.</div>
    </div>
  </div>
</section>

<!-- ═══ CTA ═══ -->
<section class="cta-section">
  <h2>Ready to deploy<br>your <span style="color: var(--amber)">mole</span>?</h2>
  <p>Join the waitlist or pre-order now. First 100 buyers get a free aluminum case upgrade.</p>
  <div class="hero-actions" style="justify-content: center;">
    <a href="#pricing" class="btn-primary">Pre-order MoleKVM →</a>
    <a href="#kickstarter" class="btn-secondary">Back on Kickstarter</a>
  </div>
</section>

<!-- ═══ FOOTER ═══ -->
<footer>
  <div class="copy">© 2026 MoleKVM by Tom Sapletta. Apache 2.0 License.</div>
  <div class="links">
    <a href="https://github.com/tom-sapletta-com/him">GitHub</a>
    <a href="mailto:tom@sapletta.com">Contact</a>
    <a href="#">Privacy</a>
    <a href="#">Terms</a>
    <a href="#">WEEE DE12345678</a>
  </div>
</footer>

<!-- ═══ CHECKOUT MODAL ═══ -->
<div class="modal-overlay" id="checkout-modal">
  <div class="modal">
    <button class="modal-close" onclick="closeCheckout()">&times;</button>
    <h3>Pre-order MoleKVM</h3>
    <p id="modal-desc">Reserve your device — charged when we ship.</p>
    <div class="modal-form">
      <input type="email" id="cust-email" placeholder="Email address" required>
      <input type="text" id="cust-name" placeholder="Full name" required>
      <select id="cust-country">
        <option value="">Select country</option>
        <option value="DE">Germany</option>
        <option value="FR">France</option>
        <option value="NL">Netherlands</option>
        <option value="PL">Poland</option>
        <option value="IT">Italy</option>
        <option value="ES">Spain</option>
        <option value="AT">Austria</option>
        <option value="BE">Belgium</option>
        <option value="SE">Sweden</option>
        <option value="DK">Denmark</option>
        <option value="OTHER">Other EU country</option>
      </select>
      <div id="stripe-card"></div>
      <div class="modal-total">
        <span>Total (incl. VAT)</span>
        <span class="amount" id="modal-price">€99.00</span>
      </div>
      <button class="price-btn primary" id="pay-btn" onclick="processPayment()">
        Pay &amp; reserve →
      </button>
      <p style="font-size: 0.7rem; color: var(--cream-dim); text-align: center; margin-top: 0.5rem;">
        Secure payment via Stripe. Charged at shipment. Full refund if campaign doesn't reach goal.
      </p>
    </div>
  </div>
</div>

<!-- ═══ TOAST ═══ -->
<div class="toast" id="toast">
  <div class="toast-icon">✓</div>
  <div>
    <div style="font-weight: 600; font-size: 0.9rem;">Order confirmed!</div>
    <div style="font-size: 0.8rem; color: var(--cream-dim);">Check your email for confirmation.</div>
  </div>
</div>

<script>
// ═══ STRIPE INTEGRATION ═══
// Replace with your real Stripe publishable key
const STRIPE_PK = 'pk_test_YOUR_STRIPE_KEY_HERE';
let stripe, cardElement, selectedPlan, selectedPrice;

function initStripe() {
  if (typeof Stripe === 'undefined') return;
  try {
    stripe = Stripe(STRIPE_PK);
    const elements = stripe.elements({
      appearance: {
        theme: 'night',
        variables: {
          colorPrimary: '#f0a030',
          colorBackground: '#2a2318',
          colorText: '#f0e8d8',
          colorTextPlaceholder: '#c0b8a8',
          borderRadius: '8px',
          fontFamily: 'Outfit, sans-serif',
        }
      }
    });
    cardElement = elements.create('card');
    cardElement.mount('#stripe-card');
  } catch(e) {
    document.getElementById('stripe-card').innerHTML =
      '<p style="color:var(--cream-dim);font-size:0.85rem;padding:0.5rem;">Stripe demo mode — enter any details</p>';
  }
}

function openCheckout(plan, price) {
  selectedPlan = plan;
  selectedPrice = price;
  document.getElementById('modal-price').textContent = `€${price}.00`;

  const descs = {
    device: 'MoleKVM device with free local-access tier.',
    pro: 'MoleKVM device + AI Pro subscription (first month free).',
    fleet: 'Enterprise inquiry — we\'ll contact you within 24h.'
  };
  document.getElementById('modal-desc').textContent = descs[plan] || '';
  document.getElementById('checkout-modal').classList.add('active');

  if (!stripe) initStripe();
}

function closeCheckout() {
  document.getElementById('checkout-modal').classList.remove('active');
}

async function processPayment() {
  const btn = document.getElementById('pay-btn');
  const email = document.getElementById('cust-email').value;
  const name = document.getElementById('cust-name').value;
  const country = document.getElementById('cust-country').value;

  if (!email || !name || !country) {
    btn.textContent = 'Please fill all fields';
    setTimeout(() => btn.textContent = 'Pay & reserve →', 2000);
    return;
  }

  btn.textContent = 'Processing...';
  btn.disabled = true;

  // In production: call your backend to create a Stripe PaymentIntent
  // POST /api/create-payment-intent { plan, email, name, country }
  // Then confirm with stripe.confirmCardPayment(clientSecret, { ... })

  try {
    // Simulated API call for demo
    await new Promise(r => setTimeout(r, 2000));

    /*
    // === PRODUCTION CODE ===
    const res = await fetch('/api/create-payment-intent', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        plan: selectedPlan,
        price: selectedPrice * 100, // cents
        email, name, country
      })
    });
    const { clientSecret } = await res.json();

    const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
      payment_method: {
        card: cardElement,
        billing_details: { name, email }
      }
    });

    if (error) throw error;
    */

    closeCheckout();
    showToast();
  } catch (err) {
    btn.textContent = err.message || 'Payment failed — try again';
    setTimeout(() => {
      btn.textContent = 'Pay & reserve →';
      btn.disabled = false;
    }, 3000);
  }
}

function showToast() {
  const t = document.getElementById('toast');
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 5000);
}

// ═══ FAQ ACCORDION ═══
document.querySelectorAll('.faq-q').forEach(q => {
  q.addEventListener('click', () => {
    const item = q.parentElement;
    document.querySelectorAll('.faq-item').forEach(i => {
      if (i !== item) i.classList.remove('open');
    });
    item.classList.toggle('open');
  });
});

// ═══ SMOOTH SCROLL FOR NAV ═══
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) {
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
});

// ═══ CLOSE MODAL ON OVERLAY CLICK ═══
document.getElementById('checkout-modal').addEventListener('click', e => {
  if (e.target === e.currentTarget) closeCheckout();
});

// ═══ KICKSTARTER NOTIFY ═══
document.getElementById('ks-notify').addEventListener('click', e => {
  e.preventDefault();
  const email = prompt('Enter your email to get notified when we launch on Kickstarter:');
  if (email && email.includes('@')) {
    // POST /api/kickstarter-notify { email }
    showToast();
    document.querySelector('#toast div div:first-child').textContent = 'You\'re on the list!';
    document.querySelector('#toast div div:last-child').textContent = 'We\'ll email you on launch day.';
  }
});
</script>

</body>
</html>
