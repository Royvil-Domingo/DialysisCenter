<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// prevent double-submit (slow connection / double click)
document.addEventListener('submit', e => {
  if (e.defaultPrevented) return;
  const b = e.target.querySelector('[type=submit], button:not([type])');
  if (b) setTimeout(() => { b.disabled = true; b.insertAdjacentHTML('beforeend', ' <span class="spinner-border spinner-border-sm"></span>'); }, 0);
});
window.addEventListener('pageshow', ev => { if (ev.persisted) location.reload(); });
</script>