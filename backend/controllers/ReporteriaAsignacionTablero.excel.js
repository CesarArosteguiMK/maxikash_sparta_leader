(function () {
    var btn = document.getElementById('asg-btn-descargar-excel');
    if (!btn) return;
    var url = btn.getAttribute('href');
    if (!url) return;
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        if (typeof Swal === 'undefined') {
            window.location.href = url;
            return;
        }
        Swal.fire({
            title: 'Descargando Excel',
            html: '<p class="text-body-secondary mb-3 mb-md-4">Por favor espere mientras se genera el archivo con <strong>todo</strong> el portafolio…</p>' +
                '<div class="spinner-border text-success" style="width:3rem;height:3rem;" role="status" aria-hidden="true"></div>' +
                '<span class="visually-hidden">Cargando</span>',
            allowOutsideClick: false,
            showConfirmButton: false,
            customClass: { popup: 'shadow' }
        });
        fetch(url, { credentials: 'same-origin' })
            .then(function (r) {
                if (!r.ok) {
                    throw new Error('HTTP ' + r.status);
                }
                var cd = r.headers.get('Content-Disposition') || '';
                var name = 'Asignacion_Tablero.xlsx';
                var m = cd.match(/filename="([^"]+)"/i) || cd.match(/filename=([^;\s]+)/i);
                if (m && m[1]) {
                    name = m[1].replace(/^["']|["']$/g, '');
                }
                return r.blob().then(function (blob) {
                    return { blob: blob, name: name };
                });
            })
            .then(function (x) {
                Swal.close();
                var u = URL.createObjectURL(x.blob);
                var a = document.createElement('a');
                a.href = u;
                a.download = x.name;
                a.rel = 'noopener';
                document.body.appendChild(a);
                a.click();
                a.remove();
                setTimeout(function () { URL.revokeObjectURL(u); }, 120000);
            })
            .catch(function () {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo descargar',
                    text: 'Intente de nuevo o contacte a sistemas si el problema continúa.'
                });
            });
    });
})();
