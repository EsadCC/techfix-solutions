<?php
// includes/footer.php
global $SITE_URL;
?>
<footer class="hoofdfooter">
    <section class="footer-grid">
        <section class="footer-merk">
            <p class="footer-logo">🔧 <strong>Techfix</strong> Solutions</p>
            <p>Dé specialist in tech reparatie, onderdelen en accessoires. Betrouwbare kwaliteit, snelle levering.</p>
            <nav class="footer-sociaal" aria-label="Sociale media">
                <a href="#" aria-label="Facebook">📘</a>
                <a href="#" aria-label="Instagram">📸</a>
                <a href="#" aria-label="Twitter">🐦</a>
                <a href="#" aria-label="YouTube">▶️</a>
                <a href="#" aria-label="LinkedIn">💼</a>
            </nav>
            <p class="footer-beoordeling">⭐⭐⭐⭐⭐ 4.8/5 — 2.847 reviews</p>
        </section>

        <nav class="footer-kolom" aria-label="Categorieën">
            <h4>Categorieën</h4>
            <ul>
                <li><a href="<?= $SITE_URL ?>/producten.php?cat=smartphones">Smartphones</a></li>
                <li><a href="<?= $SITE_URL ?>/producten.php?cat=laptops">Laptops</a></li>
                <li><a href="<?= $SITE_URL ?>/producten.php?cat=onderdelen">Onderdelen</a></li>
                <li><a href="<?= $SITE_URL ?>/producten.php?cat=accessoires">Accessoires</a></li>
                <li><a href="<?= $SITE_URL ?>/producten.php?cat=audio">Audio</a></li>
            </ul>
        </nav>

        <nav class="footer-kolom" aria-label="Klantenservice">
            <h4>Klantenservice</h4>
            <ul>
                <li><a href="#">Helpcentrum</a></li>
                <li><a href="#">Bestelling volgen</a></li>
                <li><a href="#">Retouren</a></li>
                <li><a href="#">Garantie &amp; reparatie</a></li>
                <li><a href="#">Veelgestelde vragen</a></li>
            </ul>
        </nav>

        <nav class="footer-kolom" aria-label="Bedrijf">
            <h4>Bedrijf</h4>
            <ul>
                <li><a href="#">Over Techfix</a></li>
                <li><a href="#">Vacatures</a></li>
                <li><a href="#">Zakelijk bestellen</a></li>
                <li><a href="#">Privacy &amp; Voorwaarden</a></li>
                <li><a href="#">Algemene voorwaarden</a></li>
            </ul>
        </nav>

        <address class="footer-kolom" style="font-style:normal;">
            <h4>Hulp nodig?</h4>
            <p>📞 <a href="tel:0851234567" style="color:rgba(255,255,255,0.6);">085 - 123 4567</a></p>
            <p>Ma-Vr 08:00 - 18:00</p>
            <p>✉️ <a href="mailto:support@techfixsolutions.nl" style="color:rgba(255,255,255,0.6);">support@techfixsolutions.nl</a></p>
        </address>
    </section>

    <section class="footer-onderkant">
        <p>© 2024 Techfix Solutions B.V. · KvK: 12345678 · BTW NL123456789B01</p>
        <p>💳 iDEAL · PayPal · Visa · Mastercard · Apple Pay · Klarna</p>
    </section>
</footer>

<script src="<?= $SITE_URL ?>/js/main.js"></script>
</body>
</html>
