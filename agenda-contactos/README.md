# Agenda Personal de Contactos con Autenticación en PHP

Aplicación web desarrollada en **PHP** y **MySQL** que permite a los usuarios registrarse, iniciar sesión y administrar su propia agenda de contactos. El sistema garantiza que solo los usuarios autenticados accedan a la aplicación y que cada uno vea y gestione **únicamente sus propios contactos**.

> Actividad: [ACDB1-35%] Foro – Desarrollo Web (UTPL)
> Autor: Josue Martin Jara Panchez

---

## 1. Descripción del proyecto

| Módulo | Funcionalidad |
|---|---|
| **Registro** | Alta de nuevos usuarios, validación de correo único y almacenamiento de la contraseña cifrada con `password_hash()`. |
| **Inicio de sesión** | Formulario de correo y contraseña, verificación con `password_verify()`, creación de sesión y mensajes de error genéricos. |
| **Panel principal** | Página privada que muestra el nombre, el correo del usuario y la lista de sus contactos, con buscador en tiempo real. |
| **Gestión de contactos** | Registrar, editar y eliminar contactos (con página de confirmación antes de eliminar). |
| **Cierre de sesión** | Destrucción completa de la sesión y de su cookie. |

## 2. Tecnologías utilizadas

- **PHP 8.1+** (PDO, sesiones nativas, `password_hash` / `password_verify`)
- **MySQL 8.x** o **MariaDB 10.x**
- **HTML5** y **CSS3** (diseño responsivo, sin frameworks)
- **JavaScript** (eventos `submit`, `input` y `DOMContentLoaded` para validación y búsqueda en el cliente)
- **Apache** (XAMPP)

## 3. Requisitos para su ejecución

- XAMPP (Apache + PHP 8.1 o superior) o cualquier servidor con PHP 8.1+
- Extensión `pdo_mysql` habilitada (viene activa en XAMPP)
- MySQL 8.x o MariaDB 10.x
- Navegador web actualizado

## 4. Pasos para instalar la aplicación

1. 1. **Copiar el proyecto** en la carpeta pública del servidor. Descargue el repositorio (botón **Code → Download ZIP**) y descomprímalo en `C:\xampp\htdocs\`, de modo que quede la ruta `C:\xampp\htdocs\agenda-contactos\`.

2. **Crear la base de datos.** Ejecutar el script `database/agenda_contactos.sql` desde MySQL Workbench, phpMyAdmin o la terminal:
   ```bash
   mysql -u root -p < database/agenda_contactos.sql
   ```

3. **Configurar la conexión.** Los valores por defecto están en `config/database.php` (host `127.0.0.1`, puerto `3306`, usuario `root`, sin contraseña).
   Si su MySQL tiene contraseña, cree el archivo `config/local.php` (está excluido del repositorio en `.gitignore`):
   ```php
   <?php
   return ['db_pass' => 'su_contraseña'];
   ```

4. **Iniciar Apache** (y MySQL si usa el de XAMPP) desde el panel de control de XAMPP.

5. **Abrir en el navegador:** `http://localhost/agenda-contactos/`

## 5. Estructura del proyecto

```
agenda-contactos/
├── index.php                 # Redirige a login o panel según la sesión
├── registro.php              # Registro de usuarios
├── login.php                 # Inicio de sesión
├── logout.php                # Cierre de sesión
├── panel.php                 # Panel privado con la lista de contactos
├── contacto_crear.php        # Registrar contacto
├── contacto_editar.php       # Editar contacto
├── contacto_eliminar.php     # Confirmar y eliminar contacto
├── config/
│   └── database.php          # Conexión PDO
├── includes/
│   ├── bootstrap.php         # Configuración segura de la sesión
│   ├── auth.php              # Login, verificación y cierre de sesión
│   ├── functions.php         # Escape XSS, CSRF, validaciones, mensajes
│   ├── contacto_form.php     # Formulario reutilizable
│   ├── header.php / footer.php
├── assets/
│   ├── css/styles.css
│   └── js/app.js             # Validación y búsqueda basadas en eventos
└── database/
    └── agenda_contactos.sql  # Script de la base de datos
```

## 6. Seguridad implementada

| Requisito | Implementación |
|---|---|
| `password_hash()` | La contraseña se guarda cifrada con bcrypt (`PASSWORD_DEFAULT`); nunca en texto plano. |
| `password_verify()` | Compara la contraseña ingresada con el hash almacenado al iniciar sesión. |
| Validación en el servidor | Todos los formularios se validan en PHP (campos obligatorios, formato de correo y teléfono, longitudes, contraseña mínima de 8 caracteres con letras y números). |
| Sesión activa en páginas protegidas | `requerirLogin()` se ejecuta al inicio de `panel.php` y de todas las páginas de contactos; sin sesión redirige al login. La sesión expira tras 30 minutos de inactividad. |
| Acceso solo a contactos propios | Toda consulta usa `WHERE id = ? AND id_usuario = ?`, con `id_usuario` tomado de la **sesión**, nunca de la URL ni del formulario. Cambiar el `id` en la URL no permite ver, editar ni eliminar contactos ajenos. |
| Mensajes de error sin información sensible | El login responde siempre "Correo o contraseña incorrectos" (no revela si el correo existe) y los errores de base de datos se registran en el log, no se muestran. |
| Adicionales | Consultas preparadas con PDO (contra inyección SQL), `htmlspecialchars()` en toda la salida (contra XSS), token CSRF en los formularios, `session_regenerate_id()` al iniciar sesión, cookies `HttpOnly` y `SameSite=Strict`, eliminación solo por POST y `.htaccess` que bloquea el acceso directo a `config/`, `includes/` y `database/`. |
