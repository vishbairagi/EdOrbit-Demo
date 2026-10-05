<script>
/**
 * Print ID cards on the current screen: loads the card sheet in a hidden iframe,
 * which opens the browser print dialog by itself. No new tab / extra screen.
 */
function printCards(url) {
    document.querySelectorAll('iframe.id-card-print-frame').forEach(function (f) { f.remove(); });
    const frame = document.createElement('iframe');
    frame.className = 'id-card-print-frame';
    frame.setAttribute('aria-hidden', 'true');
    frame.style.cssText = 'position:fixed;left:-10000px;top:0;width:1100px;height:800px;border:0;opacity:0;pointer-events:none;';
    frame.src = url;
    document.body.appendChild(frame);
    return false;
}
</script>
