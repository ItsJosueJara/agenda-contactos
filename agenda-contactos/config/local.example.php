<?php
/**
 * Plantilla de credenciales de la base de datos.
 * Para usarla se copia como config/local.php (archivo excluido del repositorio).
 *
 * Entorno local (XAMPP con contraseña en MySQL):
 *   return ['db_pass' => 'contraseña_de_mysql'];
 *
 * Entorno de hosting (datos del panel → MySQL Databases):
 */
return [
    'db_host' => 'servidor_mysql_del_hosting',
    'db_name' => 'nombre_de_la_base',
    'db_user' => 'usuario_mysql',
    'db_pass' => 'contraseña_mysql',
];
