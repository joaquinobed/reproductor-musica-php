# 🎵 Modern PHP Music Player

Un reproductor de música web moderno, ligero y potente construido con **PHP 8**, **SQLite/MySQL** y las últimas tecnologías de frontend. Diseñado para ofrecer una experiencia de usuario fluida con una estética minimalista y profesional.

## 🚀 Características Principales

### 🎧 Reproducción y Control
- **Modos Avanzados**: Soporte para reproducción aleatoria (**Shuffle**) y tres modos de repetición (**No repetir, Repetir Todo, Repetir Una**).
- **Visualizador de Frecuencias en Tiempo Real**: Analizador de espectro dinámico integrado utilizando la **Web Audio API** y HTML5 Canvas que se mueve al ritmo de tu música.
- **Gestión de Volumen y Progreso**: Control preciso de la línea de tiempo y volumen con una interfaz intuitiva.

### 📂 Gestión de Contenido
- **Drag & Drop**: Sube tus canciones simplemente arrastrando archivos `.mp3` desde tu computadora directamente al navegador.
- **Extracción Automática de Metadatos (ID3)**: El servidor lee automáticamente el **Título** y **Artista** de los tags internos de los archivos al subirlos.
- **Sistema de Álbumes**: Organiza tu biblioteca en álbumes con portadas personalizadas.
- **Favoritos**: Marca tus pistas preferidas con un solo clic y accede a ellas en la sección especial de Favoritos.

### 🛡️ Seguridad y Robustez
- **Validación de Archivos Profesional**: Verificación de tipo MIME real en el servidor para prevenir la subida de archivos maliciosos (no solo revisa la extensión).
- **Consultas Seguras**: Implementación de **Sentencias Preparadas (PDO)** para proteger la aplicación contra inyecciones SQL.
- **Interfaz Responsiva**: Diseño adaptativo optimizado para móviles y escritorio usando **Tailwind CSS** y **Bootstrap 5**.

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 8.x
- **Base de Datos**: SQLite (configurable a MySQL via `config.php`)
- **Frontend**: Tailwind CSS, Bootstrap 5, Lucide Icons & Bootstrap Icons.
- **APIs**: Web Audio API (para el visualizador).
- **Librerías**: Librería personalizada `SimpleID3` para lectura de metadatos.

## 📋 Requisitos

- Servidor Web (Apache/Nginx)
- PHP 8.0 o superior
- Extensiones PHP: `pdo_sqlite` (o `pdo_mysql`), `fileinfo`, `mbstring`.

## 🔧 Instalación

1.  **Clonar el repositorio** en tu servidor local (XAMPP, Laragon, etc.).
2.  Asegurarse de que las carpetas `audio/` y `uploads/covers/` tengan **permisos de escritura**.
3.  Configurar la base de datos en `config.php` (por defecto utiliza SQLite para una configuración instantánea).
4.  Abrir `index.php` en tu navegador.

## 📸 Capturas de Pantalla

*Diseño oscuro elegante con tarjetas de cristal (Glassmorphism), visualizador de ondas integrado en la portada del álbum y navegación lateral fluida.*

---
Desarrollado con enfoque en simplicidad y rendimiento.
