// Techfix Solutions — main.js

// ─── Tab wisseling ──────────────────────────────────────────
document.querySelectorAll('.tab-knop').forEach(function(knop) {
    knop.addEventListener('click', function() {
        var doel = knop.dataset.tab;

        document.querySelectorAll('.tab-knop').forEach(function(k) {
            k.classList.remove('actief');
            k.setAttribute('aria-selected', 'false');
        });
        document.querySelectorAll('.tab-inhoud').forEach(function(t) {
            t.classList.remove('actief');
        });

        knop.classList.add('actief');
        knop.setAttribute('aria-selected', 'true');

        var tabInhoud = document.getElementById(doel);
        if (tabInhoud) tabInhoud.classList.add('actief');
    });
});

// ─── Gallerij foto wisselen ─────────────────────────────────
function wisselFoto(knop) {
    var nieuwSrc = knop.dataset.src;
    var hoofdImg = document.getElementById('hoofdfoto-img');

    if (hoofdImg && nieuwSrc) {
        hoofdImg.style.opacity = '0';
        setTimeout(function() {
            hoofdImg.src = nieuwSrc;
            hoofdImg.style.opacity = '1';
        }, 150);
    }

    // Actieve miniatuur bijwerken
    document.querySelectorAll('.gallerij-miniatuur').forEach(function(m) {
        m.classList.remove('actief');
    });
    knop.classList.add('actief');
}

// ─── Foto upload preview (admin) ───────────────────────────
document.querySelectorAll('.foto-bestand-invoer').forEach(function(invoer) {
    invoer.addEventListener('change', function() {
        var vakId  = invoer.dataset.vak;
        var vak    = document.getElementById(vakId);
        var bestand = invoer.files[0];
        if (!bestand || !vak) return;

        var lezer = new FileReader();
        lezer.onload = function(e) {
            // Bestaand label vervangen door preview afbeelding
            var bestaandLabel = vak.querySelector('.foto-upload-label');
            var bestaandeFigure = vak.querySelector('.foto-huidige');

            var preview = document.createElement('figure');
            preview.className = 'foto-huidige';
            preview.innerHTML =
                '<img src="' + e.target.result + '" alt="Voorbeeldweergave">' +
                '<figcaption>Nieuwe foto (nog niet opgeslagen)</figcaption>';

            if (bestaandeFigure) {
                bestaandeFigure.replaceWith(preview);
            } else if (bestaandLabel) {
                bestaandLabel.replaceWith(preview);
            } else {
                vak.insertBefore(preview, invoer);
            }

            vak.classList.add('heeft-foto');
        };
        lezer.readAsDataURL(bestand);
    });
});

// ─── Sterren beoordeling ────────────────────────────────────
document.querySelectorAll('.ster-beoordeling').forEach(function(container) {
    var sterren = container.querySelectorAll('.ster');
    var invoer  = document.getElementById('rating_input');

    sterren.forEach(function(ster, index) {
        ster.addEventListener('mouseenter', function() {
            sterren.forEach(function(s, j) {
                s.classList.toggle('actief', j <= index);
            });
        });

        ster.addEventListener('click', function() {
            var waarde = index + 1;
            if (invoer) invoer.value = waarde;
            sterren.forEach(function(s, j) {
                s.classList.toggle('actief', j <= index);
            });
        });
    });

    container.addEventListener('mouseleave', function() {
        var huidigeWaarde = invoer ? parseInt(invoer.value) : 0;
        sterren.forEach(function(s, j) {
            s.classList.toggle('actief', j < huidigeWaarde);
        });
    });
});

// ─── Aantal bijhouden (winkelwagen / product) ───────────────
document.querySelectorAll('.aantal-control').forEach(function(control) {
    var invoer   = control.querySelector('input[type="number"]');
    var minKnop  = control.querySelector('.aantal-min');
    var plusKnop = control.querySelector('.aantal-plus');

    if (!invoer) return;

    if (minKnop) {
        minKnop.addEventListener('click', function() {
            var huidig = parseInt(invoer.value) || 1;
            if (huidig > 1) invoer.value = huidig - 1;
        });
    }

    if (plusKnop) {
        plusKnop.addEventListener('click', function() {
            var huidig  = parseInt(invoer.value) || 1;
            var maximum = parseInt(invoer.max) || 999;
            if (huidig < maximum) invoer.value = huidig + 1;
        });
    }
});

// ─── Meldingen automatisch verbergen ───────────────────────
setTimeout(function() {
    document.querySelectorAll('.melding').forEach(function(melding) {
        melding.style.transition = 'opacity 0.5s';
        melding.style.opacity    = '0';
        setTimeout(function() {
            if (melding.parentNode) melding.parentNode.removeChild(melding);
        }, 500);
    });
}, 4000);

// ─── Opslag opties (product detail) ────────────────────────
document.querySelectorAll('.opslag-optie').forEach(function(knop) {
    knop.addEventListener('click', function() {
        var opties = knop.closest('.opslag-opties');
        if (opties) {
            opties.querySelectorAll('.opslag-optie').forEach(function(k) {
                k.classList.remove('actief');
            });
        }
        knop.classList.add('actief');
    });
});
