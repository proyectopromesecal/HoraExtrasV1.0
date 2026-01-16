# HoraExtra

**HoraExtra** es una aplicación web desarrollada en PHP diseñada para facilitar la gestión, autorización y reporte de horas extra del personal en una organización. Su objetivo principal es optimizar el flujo de solicitudes y aprobaciones, proporcionando una interfaz clara tanto para empleados como para administradores.

## Características

*   **Gestión de Solicitudes**: Los empleados pueden solicitar horas extra, dietas y viáticos.
*   **Flujo de Aprobación**: Sistema jerárquico para la autorización de solicitudes (Departamental, Administrativa).
*   **Reportes**: Generación de reportes detallados y acumulados de horas extra y viáticos.
*   **Exportación PDF**: Capacidad para generar documentos PDF de las solicitudes y dietas (usando FPDF).
*   **Control de Acceso**: Sistema de login con diferentes roles de usuario.
*   **Historial**: Registro de auditoría de las acciones realizadas.

## Requisitos del Sistema

Para ejecutar HoraExtra, necesitas un servidor con preconfiguración LAMP/WAMP/XAMPP:

*   **Servidor Web**: Apache 2.4+ (o compatible).
*   **Lenguaje**: PHP 7.4 o superior (compatible con PHP 8.x).
*   **Base de Datos**: Microsoft SQL Server.
*   **Extensiones de PHP**:
    *   `sqlsrv` (Driver de Microsoft para PHP para SQL Server).
    *   `pdo_sqlsrv` (Opcional, pero recomendado si se decide migrar a PDO).
    *   `gd` (Para manejo de imágenes).
    *   `mbstring` (Para manejo de caracteres multibyte).

## Instalación

1.  **Clonar el repositorio**:
    ```bash
    git clone https://github.com/tu-usuario/HoraExtra.git
    cd HoraExtra
    ```

2.  **Configurar Base de Datos**:
    *   Asegúrate de tener una instancia de SQL Server corriendo.
    *   Restaura la base de datos (si se provee script SQL en `backups/` o similar) o crea la estructura necesaria manualmente.

3.  **Configurar Conexión**:
    *   Navega a la carpeta `lib/`.
    *   Edita el archivo `conexion.php` (o `config.php` si aplica tras refactorización) para establecer tus credenciales de base de datos (`Database`, `UID`, `PWD`).

    > **Nota de Seguridad**: Nunca subas archivos con contraseñas reales a un repositorio público. Usa variables de entorno o un archivo de configuración ignorado por git.

4.  **Configurar Servidor Web (Apache)**:
    *   Apunta el `DocumentRoot` de tu VirtualHost a la carpeta raíz del proyecto.
    *   Asegúrate de que el módulo `mod_rewrite` esté habilitado si usas URLs amigables (aunque este proyecto usa estructura plana por defecto).

    Ejemplo de configuración básica en Apache (`httpd-vhosts.conf`):
    ```apache
    <VirtualHost *:80>
        ServerName horaextra.local
        DocumentRoot "C:/Ruta/A/HoraExtra"
        <Directory "C:/Ruta/A/HoraExtra">
            Options Indexes FollowSymLinks
            AllowOverride All
            Require all granted
        </Directory>
    </VirtualHost>
    ```

5.  **Permisos**:
    *   Asegúrate de que las carpetas de carga de archivos (ej. `imagenes/`, `archivos/`) tengan permisos de escritura si la aplicación lo requiere.

## Uso

1.  Abre tu navegador y ve a `http://localhost/HoraExtra` (o el dominio que hayas configurado).
2.  Inicia sesión con las credenciales de administrador (por defecto, consultar documentación interna o base de datos inicial).
3.  Navega por el menú para gestionar empleados, departamentos y aprobar solicitudes.

## Estructura del Proyecto

*   `css/`, `js/`: Activos estáticos de frontend.
*   `lib/`: Librerías del núcleo, clases PHP y scripts de conexión.
    *   `fpdf/`: Librería para generación de PDFs.
    *   `PHPMailer/`: Librería para envío de correos.
*   `imagenes/`: Recursos gráficos del sitio.
*   `reportes/`: Scripts específicos para generación de reportes.
*   `*.php`: Archivos de controladores y vistas de la aplicación (estructura plana).

## Contribuir

¡Las contribuciones son bienvenidas! Por favor, lee `CONTRIBUTING.md` (si existe) para detalles sobre nuestro código de conducta y el proceso para enviarnos pull requests.

## Licencia

Este proyecto está bajo la Licencia [MIT](LICENSE).
