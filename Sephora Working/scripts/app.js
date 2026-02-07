// Roger Fashion Hub - simple AJAX signup
// This replaces the original Elementor/WP form submission behavior.
// Expects a backend endpoint at /api/subscribe.php that returns JSON.
//
// If you don't have a backend yet, you can temporarily set the form action to a
// service like Formspree or your own serverless function, but keep privacy-compliant messaging.

(() => {
  const form = document.getElementById('rfhSignupForm');
  const msg  = document.getElementById('rfhFormMsg');
  if (!form) return;

  const setMsg = (text, ok = false) => {
    if (!msg) return;
    msg.textContent = text;
    msg.style.opacity = '1';
    msg.style.padding = text ? '10px 12px' : '0';
    msg.style.borderRadius = '10px';
    msg.style.marginTop = '10px';
    msg.style.border = text ? '1px solid rgba(255,255,255,.18)' : '0';
    msg.style.background = ok ? 'rgba(16,185,129,.12)' : 'rgba(239,68,68,.12)';
  };

  