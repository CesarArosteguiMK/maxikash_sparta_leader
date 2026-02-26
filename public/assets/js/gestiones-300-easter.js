/**
 * Easter egg Gestión: Ctrl+Shift+3 — "¡Sumemos más, llegamos a los 300!" (referencia a la película 300).
 * Se carga desde el layout global para que el atajo funcione siempre.
 */
(function () {
    "use strict";

    var animDurationMs = 3600;

    // Inyectar estilos (no dependemos del controlador)
    var css = ".gestiones-300-overlay{position:fixed;inset:0;z-index:1060;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(20,15,10,0.92) 0%,rgba(60,40,25,0.94) 50%,rgba(25,18,12,0.95) 100%);pointer-events:none;opacity:0;animation:gestiones300In .6s ease-out forwards}.gestiones-300-overlay.gestiones-300-out{animation:gestiones300Out .5s ease-in forwards}@keyframes gestiones300In{0%{opacity:0}100%{opacity:1}}.gestiones-300-card{text-align:center;color:#d4a84b;opacity:0;transform:scale(0.85);animation:gestiones300CardIn .8s ease-out .2s forwards}.gestiones-300-num{font-size:clamp(4rem,12vw,8rem);font-weight:800;line-height:1;letter-spacing:-0.02em;text-shadow:0 0 30px rgba(212,168,75,0.6),0 2px 4px rgba(0,0,0,0.5),0 0 60px rgba(212,168,75,0.3);color:#e8c547;margin-bottom:0.1em}.gestiones-300-lambda{font-size:clamp(2rem,6vw,3.5rem);font-weight:700;opacity:0.95;letter-spacing:0.2em;margin-bottom:0.15em;text-shadow:0 0 20px rgba(212,168,75,0.5)}.gestiones-300-line{font-size:1.2rem;opacity:0.6;margin:0.2em 0}.gestiones-300-text{font-size:clamp(1rem,2.5vw,1.35rem);font-weight:600;max-width:90%;margin:0.4em auto 0;text-shadow:0 1px 3px rgba(0,0,0,0.5);letter-spacing:0.03em}@keyframes gestiones300CardIn{0%{opacity:0;transform:scale(0.85)}100%{opacity:1;transform:scale(1)}}@keyframes gestiones300Out{0%{opacity:1}100%{opacity:0}}";
    var style = document.createElement("style");
    style.id = "gestiones-300-easter-styles";
    style.textContent = css;
    if (!document.getElementById(style.id)) {
        (document.head || document.documentElement).appendChild(style);
    }

    document.addEventListener("keydown", function (e) {
        // Tecla 3 (principal keyCode 51, o numpad 99). Con Ctrl+Shift a veces e.key es "#" en vez de "3".
        var isThree = (e.key === "3" || e.key === "#" || e.keyCode === 51 || e.keyCode === 99);
        if (!e.ctrlKey || !e.shiftKey || !isThree) return;
        e.preventDefault();
        e.stopPropagation();

        var overlay = document.createElement("div");
        overlay.className = "gestiones-300-overlay";
        overlay.setAttribute("aria-hidden", "true");

        var card = document.createElement("div");
        card.className = "gestiones-300-card";

        var num = document.createElement("div");
        num.className = "gestiones-300-num";
        num.textContent = "300";

        var lambda = document.createElement("div");
        lambda.className = "gestiones-300-lambda";
        lambda.textContent = "\u039B";

        var line = document.createElement("div");
        line.className = "gestiones-300-line";
        line.innerHTML = "&#8212;";

        var text = document.createElement("div");
        text.className = "gestiones-300-text";
        text.textContent = "\u00A1Sumemos m\u00E1s, llegamos a los 300!";

        card.appendChild(lambda);
        card.appendChild(num);
        card.appendChild(line);
        card.appendChild(text);
        overlay.appendChild(card);
        document.body.appendChild(overlay);

        // Grito de guerra (distinto a thisissparta) — épico para 300
        var audio = new Audio("/assets/audio/grito-guerra-.mp3");
        audio.volume = 0.5;
        audio.play().catch(function () {});
        setTimeout(function () {
            audio.pause();
            audio.currentTime = 0;
        }, animDurationMs);

        setTimeout(function () {
            overlay.classList.add("gestiones-300-out");
            setTimeout(function () {
                if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
            }, 500);
        }, animDurationMs);
    }, true);
})();
