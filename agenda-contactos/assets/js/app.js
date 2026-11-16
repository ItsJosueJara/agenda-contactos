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

    // 3) Evento "input" en el buscador: filtra la tabla en tiempo real
    const buscador = document.getElementById('buscador');
    const tabla = document.getElementById('tabla-contactos');
    const sinResultados = document.getElementById('sin-resultados');

    if (buscador && tabla) {
        buscador.addEventListener('input', () => {
            const texto = buscador.value.toLowerCase().trim();
            let visibles = 0;

            tabla.querySelectorAll('tbody tr').forEach((fila) => {
                const coincide = fila.textContent.toLowerCase().includes(texto);
                fila.style.display = coincide ? '' : 'none';
                if (coincide) visibles++;
            });

            sinResultados.classList.toggle('oculto', visibles > 0);
        });
    }

    // 4) Los mensajes de éxito se ocultan solos después de 4 segundos
    document.querySelectorAll('.alerta-exito').forEach((alerta) => {
        setTimeout(() => alerta.classList.add('desvanecer'), 4000);
    });
});
