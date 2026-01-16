# Guía de Liberación y Mantenimiento - Proyecto HoraExtra

Este documento contiene la información estratégica para liberar y mantener el proyecto "HoraExtra" como software de código abierto.

## 1. Descripción Profesional del Proyecto

> **HoraExtra** es una solución web integral y ligera para la gestión corporativa de horas extraordinarias, dietas y viáticos. Desarrollada en PHP sobre arquitectura LAMP/WAMP, permite a las organizaciones digitalizar el flujo de solicitudes, desde la creación por parte del empleado hasta la validación jerárquica y aprobación administrativa. Su diseño modular facilita la integración en entornos existentes, ofreciendo trazabilidad completa, generación de reportes en PDF y auditoría en tiempo real. Ideal para departamentos de Recursos Humanos que buscan transparencia y eficiencia en el control de nómina variable.

## 2. Propuesta de Estructura del Repositorio (Ideal)

Aunque el proyecto actual utiliza una estructura plana (archivos PHP en la raíz), se recomienda migrar paulatinamente a una estructura moderna compatible con estándares PSR.

### Estructura Recomendada:
```
HoraExtra/
├── .github/                # Plantillas de Issues, PRs y flujos de trabajo (Actions)
├── config/                 # Archivos de configuración (ej. database.php)
├── docs/                   # Documentación adicional
├── public/                 # Único punto de entrada expuesto al servidor web
│   ├── index.php
│   ├── css/
│   ├── js/
│   └── img/
├── src/                    # Código fuente de PHP (Clases, Modelos, Controladores)
│   ├── Controllers/
│   ├── Models/
│   └── Helpers/
├── templates/              # Vistas o archivos HTML/PHP de presentación
├── vendor/                 # Dependencias de Composer
├── tests/                  # Pruebas unitarias
├── .gitignore              # Archivos ignorados por git
├── composer.json           # Definición de dependencias
├── LICENSE                 # Términos de la licencia
└── README.md               # Punto de entrada para usuarios
```

## 3. Buenas Prácticas para Liberar Software PHP (Open Source)

Para garantizar la calidad y seguridad del proyecto al abrirlo a la comunidad:

1.  **Seguridad de Credenciales (CRÍTICO)**:
    *   **Nunca** subir archivos con contraseñas reales (como `lib/conexion.php` con claves hardcodeadas).
    *   Utilizar variables de entorno (`.env`) o crear un archivo `config.example.php` con valores vacíos y añadir `config.php` y `conexion.php` al `.gitignore`.

2.  **Gestión de Dependencias**:
    *   Migrar las librerías manuales (`lib/fpdf`, `lib/PHPMailer`) a **Composer**. Esto facilita la actualización y seguridad de librerías de terceros.

3.  **Estándares de Código**:
    *   Adoptar **PSR-12** para el estilo de codificación.
    *   Utilizar **PDO** en lugar de funciones `sqlsrv_` directas o concatenación de strings para prevenir inyección SQL.

4.  **Versionado Semántico**:
    *   Usar tags de git (v1.0.0, v1.1.0) para marcar lanzamientos estables.

5.  **Documentación Continua**:
    *   Mantener el `README.md` actualizado.
    *   Comentar el código complejo.

## 4. Guía de Publicación en GitHub

Pasos para subir este proyecto a GitHub por primera vez:

1.  **Limpieza de Credenciales**:
    *   Revisa minuciosamente `lib/conexion.php` y cualquier otro archivo en busca de contraseñas. Reemplázalas por placeholders o cadenas vacías.

2.  **Crear el archivo .gitignore**:
    *   Crea un archivo llamado `.gitignore` en la raíz con el siguiente contenido mínimo:
        ```text
        /vendor/
        /node_modules/
        .env
        config.php
        lib/conexion.php # Si contiene claves reales, ignorarlo y subir una plantilla
        .DS_Store
        .idea/
        .vscode/
        ```

3.  **Inicializar Git** (Si no se ha hecho):
    ```bash
    git init
    git branch -M main
    ```

4.  **Preparar archivos**:
    ```bash
    git add .
    git commit -m "Primer commit: Liberación de HoraExtra v1.0"
    ```

5.  **Conectar con GitHub**:
    *   Crea un nuevo repositorio **vacío** en GitHub.
    *   Copia la URL del repositorio (terminada en `.git`).
    *   Ejecuta:
        ```bash
        git remote add origin https://github.com/TU_USUARIO/HoraExtra.git
        git push -u origin main
        ```

¡Listo! Tu proyecto estará disponible para el mundo.
