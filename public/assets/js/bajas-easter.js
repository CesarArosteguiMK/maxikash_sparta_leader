/**
 * Easter egg Bajas: Ctrl+Shift+B (carabelas + adiós) y mousedown en icono Baja (fantasma + boo).
 */
(function () {
    "use strict";

    // ----- Carabelas + adiós (Ctrl+Shift+B) -----
    document.addEventListener("keydown", function (e) {
        if (!e.ctrlKey || !e.shiftKey || (e.key !== "B" && e.keyCode !== 66)) return;
        e.preventDefault();
        var wrap = document.createElement("div");
        wrap.className = "bajas-easter-wrap";
        for (var i = 0; i < 8; i++) {
            var car = document.createElement("div");
            car.className = "bajas-easter-caravel";
            car.textContent = "\u26F5";
            car.setAttribute("aria-hidden", "true");
            wrap.appendChild(car);
        }
        document.body.appendChild(wrap);
        var t = document.createElement("div");
        t.className = "bajas-easter-toast";
        t.innerHTML = "<span class=\"bajas-easter-emoji\">\u26F5</span> \u00A1Adi\u00F3s, espartano!<br><small style=\"opacity:0.9\">Que la carabela te lleve a buen puerto</small>";
        document.body.appendChild(t);
        var a = new Audio("/assets/audio/adios.mp3");
        a.volume = 0.5;
        a.play().catch(function () {});
        var dur = 4200;
        setTimeout(function () {
            a.pause();
            a.currentTime = 0;
        }, dur);
        setTimeout(function () {
            t.style.animation = "bajasEasterOut .35s ease forwards";
            setTimeout(function () {
                if (t.parentNode) t.parentNode.removeChild(t);
                if (wrap.parentNode) wrap.parentNode.removeChild(wrap);
            }, 350);
        }, dur);
    });

    // ----- Fantasma + boo (mousedown en .bajas-easter-ghost-trigger) -----
    var ghostAnimMs = 2600; // mismo que animation bajasGhostRise (2.6s)
    function runBoo(trg) {
        if (!trg) return;
        var r = trg.getBoundingClientRect();
        var ghost = document.createElement("div");
        ghost.className = "bajas-ghost-float";
        ghost.style.left = (r.left + r.width / 2) + "px";
        ghost.style.top = (r.top + r.height / 2) + "px";
        ghost.innerHTML = "<span class=\"bajas-ghost-emoji\">\uD83D\uDC7B</span>";
        document.body.appendChild(ghost);
        var audio = new Audio("/assets/audio/boo.mp3");
        audio.volume = 0.6;
        audio.play().catch(function () {});
        setTimeout(function () {
            audio.pause();
            audio.currentTime = 0;
            if (ghost.parentNode) ghost.parentNode.removeChild(ghost);
        }, ghostAnimMs);
    }
    document.addEventListener("mousedown", function (e) {
        var trg = e.target.closest(".bajas-easter-ghost-trigger");
        if (!trg) return;
        e.preventDefault();
        e.stopPropagation();
        runBoo(trg);
    }, true);
})();
