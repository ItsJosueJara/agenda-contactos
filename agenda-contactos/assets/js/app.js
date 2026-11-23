/**
 * Interacción del lado del cliente basada en eventos.
 * (La validación definitiva siempre se realiza en el servidor con PHP.)
 */
document.addEventListener('DOMContentLoaded', () => {

    // 1) Evento "submit": validación rápida antes de enviar formularios
    document.querySelectorAll('form[data-validar]').forEach((form) => {
        form.addEventListener('submit', (evento) => {
            let valido = true;

            form.querySelectorAll('input[required]').forEach((input) => {
                input.classList.remove('invalido');
                if (!input.checkValidity() || input.value.trim() === '') {
                    input.classList.add('invalido');
                    valido = false;
                }
            });

            const pass = form.querySelector('#password');
            const conf = form.querySelector('#confirmar');
            if (pass && conf && pass.value !== conf.value) {
                conf.classList.add('invalido');
                valido = false;
            }

            if (!valido) {
                evento.preventDefault();
                form.querySelector('.invalido')?.focus();
            }
        });

        // 2) Evento "input": quita el resaltado de error mientras se escribe
        form.addEventListener('input', (evento) => {
            evento.target.classList.remove('invalido');
        });
    });

    // 3) Evento "change": los filtros del panel (categoría, orden, campo y
    //    "solo favoritos") se aplican automáticamente al cambiar su valor
    const formFiltros = document.getElementById('form-filtros');
    if (formFiltros) {
        formFiltros.querySelectorAll('[data-autoenviar]').forEach((control) => {
            control.addEventListener('change', () => formFiltros.submit());
        });

        // Evento "search": al borrar el buscador con la X se muestran todos otra vez
        const q = document.getElementById('q');
        q?.addEventListener('search', () => {
            if (q.value === '') formFiltros.submit();
        });
    }

    // 4) Evento "submit" en la estrella: evita doble clic mientras se guarda
    document.querySelectorAll('.form-estrella').forEach((form) => {
        form.addEventListener('submit', () => {
            form.querySelector('button').disabled = true;
        });
    });

    // 5) Los mensajes de éxito se ocultan solos después de 4 segundos
    document.querySelectorAll('.alerta-exito').forEach((alerta) => {
        setTimeout(() => alerta.classList.add('desvanecer'), 4000);
    });
});
