/**
 * Easter egg Bajas: Ctrl+Shift+B (carabelas + adiós) y long-press 1,5 s en icono Baja (fantasma + boo).
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

    // ----- Fantasma + boo: long-press 1,5 s en .bajas-easter-ghost-trigger -----
    var ghostHoldMs = 1500;
    var ghostAnimMs = 3200;
    var ghostTimer = null;
    var ghostJustTriggered = false;

    function runBoo(trg) {
        if (!trg) return;
        ghostJustTriggered = true;
        setTimeout(function () { ghostJustTriggered = false; }, 400);
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

    function startHold(ev) {
        var trg = ev.target.closest(".bajas-easter-ghost-trigger");
        if (!trg || ghostTimer) return;
        ev.preventDefault();
        ev.stopPropagation();
        ghostTimer = setTimeout(function () {
            ghostTimer = null;
            runBoo(trg);
        }, ghostHoldMs);
    }

    function cancelHold() {
        if (ghostTimer) {
            clearTimeout(ghostTimer);
            ghostTimer = null;
        }
    }

    document.addEventListener("click", function (e) {
        var trg = e.target.closest(".bajas-easter-ghost-trigger");
        if (!trg || !ghostJustTriggered) return;
        e.preventDefault();
        e.stopPropagation();
    }, true);

    document.addEventListener("mousedown", function (e) {
        var trg = e.target.closest(".bajas-easter-ghost-trigger");
        if (!trg) return;
        startHold(e);
    }, true);

    document.addEventListener("mouseup", cancelHold);
    document.addEventListener("mouseout", function (e) {
        if (e.target.closest(".bajas-easter-ghost-trigger") && (!e.relatedTarget || !e.relatedTarget.closest(".bajas-easter-ghost-trigger")))
            cancelHold();
    });

    document.addEventListener("touchstart", function (e) {
        var trg = e.target.closest(".bajas-easter-ghost-trigger");
        if (!trg) return;
        e.preventDefault();
        startHold(e);
    }, { passive: false });

    document.addEventListener("touchend", function (e) {
        e.preventDefault();
        cancelHold();
    }, { passive: false });

    document.addEventListener("touchcancel", cancelHold);
})();
