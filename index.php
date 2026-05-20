<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Reproductor Moderno</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="d-flex flex-column min-vh-100 p-0 overflow-y-auto overflow-md-hidden">

    <!-- Drop Zone Overlay -->
    <div id="drop-zone" class="position-fixed top-0 start-0 w-100 h-100 bg-primary bg-opacity-10 d-none flex-column align-items-center justify-content-center z-3" style="backdrop-filter: blur(8px); pointer-events: none;">
        <div class="p-5 border-4 border-dashed border-primary rounded-5 text-primary text-center bg-dark bg-opacity-75 shadow-2xl">
            <i class="bi bi-cloud-arrow-up-fill display-1 mb-3"></i>
            <h2 class="fw-bold">Suelta para subir música</h2>
            <p>Solo archivos MP3</p>
        </div>
    </div>

    <div class="container-fluid d-flex flex-column h-screen p-3 p-md-5">
        <!-- Header -->
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 mb-md-5 gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary rounded-3 d-flex align-items-center justify-content-center shadow-lg shadow-indigo-500/20" style="width: 45px; height: 45px; background: #6366f1 !important;">
                    <i class="bi bi-music-note-beamed text-white fs-4"></i>
                </div>
                <h1 class="h3 fw-bold tracking-tight mb-0">
                    PHP SoundStream 
                    <span class="badge font-monospace bg-dark text-primary border border-secondary fw-normal ms-2 py-1 px-2 d-none d-sm-inline-block" style="font-size: 0.65rem; letter-spacing: 1px;">V2.0 ES6</span>
                </h1>
            </div>
            <div class="d-flex align-items-center gap-2 gap-md-3">
                <button class="btn btn-outline-primary btn-sm rounded-pill px-3 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalAlbum">
                    <i class="bi bi-folder-plus me-md-2"></i> <span class="d-none d-md-inline">Nuevo Álbum</span>
                </button>
                <button class="btn btn-primary btn-sm rounded-pill px-3 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalSong">
                    <i class="bi bi-cloud-upload me-md-2"></i> <span class="d-none d-md-inline">Subir Música</span>
                </button>
                <div class="d-none d-lg-flex align-items-center gap-4 small text-muted font-monospace ms-3 border-start ps-3 border-secondary border-opacity-25">
                    <div class="d-flex align-items-center">
                        <span class="p-1 bg-success rounded-circle me-2 animate-pulse"></span>
                        Backend Active
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow-1 row g-4 g-lg-5 min-h-0">
            <!-- Left: Player Section (7 cols) -->
            <div class="col-lg-7 d-flex flex-column gap-4">
                <div class="music-card p-4 p-lg-5 flex-grow-1 d-flex flex-column justify-content-between shadow-xl">
                    <!-- Album Art Area -->
                    <div class="text-center">
                        <div id="album-art" class="album-art-placeholder mb-4 overflow-hidden position-relative">
                            <div id="album-art-inner" class="w-100 h-100 d-flex align-items-center justify-content-center">
                                <i class="bi bi-music-note"></i>
                            </div>
                            <canvas id="visualizer" class="position-absolute bottom-0 start-0 w-100 h-100 pointer-events-none" style="z-index: 10; opacity: 0.7;"></canvas>
                        </div>
                        <div class="mb-4">
                            <h2 id="current-song-title" class="h1 fw-bold text-white mb-2 text-truncate px-3">Selecciona una canción</h2>
                            <p id="current-song-info" class="text-muted text-uppercase tracking-widest small fw-semibold">Listo para reproducir</p>
                        </div>
                    </div>

                    <!-- Progress and Controls Area -->
                    <div class="player-controls-wrapper">
                        <audio id="player" class="d-none">
                            <source id="player-source" src="" type="audio/mpeg">
                        </audio>

                        <!-- Progress Bar -->
                        <div class="progress-section mb-5">
                            <div class="d-flex justify-content-between mb-2 small font-monospace text-muted">
                                <span id="time-current">00:00</span>
                                <span id="time-total">00:00</span>
                            </div>
                            <div class="progress cursor-pointer overflow-visible" id="progress-wrapper" style="height: 6px;">
                                <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>

                        <!-- Controls -->
                        <div class="d-flex align-items-center justify-content-center gap-4 pb-2">
                            <button id="btn-shuffle" class="btn btn-link text-muted p-0" title="Aleatorio">
                                <i class="bi bi-shuffle fs-4"></i>
                            </button>
                            <button id="btn-prev" class="btn btn-link p-0">
                                <i class="bi bi-rewind-fill fs-1"></i>
                            </button>
                            <button id="btn-play-pause" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i id="play-icon" class="bi bi-play-fill fs-1" style="transform: translateX(2px);"></i>
                            </button>
                            <button id="btn-next" class="btn btn-link p-0">
                                <i class="bi bi-fast-forward-fill fs-1"></i>
                            </button>
                            <button id="btn-repeat" class="btn btn-link text-muted p-0" title="Repetir">
                                <i class="bi bi-repeat fs-4"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Library & Playlist Section (5 cols) -->
            <div class="col-lg-5 d-flex flex-column min-h-0 gap-4">
                <!-- Albums Horizontal List -->
                <div class="bg-dark bg-opacity-25 border border-secondary border-opacity-25 rounded-5 p-3 overflow-hidden">
                    <h6 class="text-uppercase tracking-widest small fw-bold text-muted mb-3 px-2">Álbumes</h6>
                <div id="album-list" class="d-flex gap-3 overflow-x-auto pb-2 custom-scrollbar">
                        <!-- Albums load here -->
                        <div class="text-center py-2 px-4 shadow-sm border border-secondary border-opacity-25 rounded-4 cursor-pointer album-item active" data-id="all">
                            <small class="fw-bold d-block">Todos</small>
                        </div>
                        <div class="text-center py-2 px-4 shadow-sm border border-secondary border-opacity-25 rounded-4 cursor-pointer album-item" data-id="favs">
                             <small class="fw-bold d-block"><i class="bi bi-heart-fill text-danger me-1"></i> Favoritos</small>
                        </div>
                    </div>
                </div>

                <!-- Playlist -->
                <div class="bg-dark bg-opacity-25 border border-secondary border-opacity-25 rounded-5 flex-grow-1 overflow-hidden d-flex flex-column">
                    <div class="p-4 border-bottom border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                        <h3 class="h6 fw-bold mb-0 d-flex align-items-center">
                            <i class="bi bi-list-task me-2 text-primary"></i>
                            Canciones
                        </h3>
                        <span id="song-count" class="badge bg-secondary bg-opacity-25 text-muted fw-normal">0 canciones</span>
                    </div>
                    <div id="playlist" class="playlist-section custom-scrollbar p-3 flex-grow-1">
                        <!-- Songs dynamic loading -->
                        <div class="text-center py-5">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Modals -->
        <div class="modal fade" id="modalAlbum" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark border-secondary text-white rounded-5 shadow-2xl">
                    <div class="modal-header border-0 p-4">
                        <h5 class="modal-title fw-bold">Nuevo Álbum</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="formAlbum">
                        <div class="modal-body p-4 pt-0">
                            <div class="mb-3">
                                <label class="form-label text-slate-300 small">Nombre del Álbum</label>
                                <input type="text" name="name" class="form-control bg-secondary bg-opacity-25 border-secondary text-white rounded-3" placeholder="Ej: Verano 2024" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-slate-300 small">Imagen de Portada</label>
                                <input type="file" name="cover" class="form-control bg-secondary bg-opacity-25 border-secondary text-white rounded-3" accept="image/*">
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="submit" id="btnSaveAlbum" class="btn btn-primary w-100 rounded-pill py-2">Guardar Álbum</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalEditAlbum" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark border-secondary text-white rounded-5 shadow-2xl">
                    <div class="modal-header border-0 p-4">
                        <h5 class="modal-title fw-bold">Editar Álbum</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="formEditAlbum">
                        <input type="hidden" name="id" id="edit-album-id">
                        <div class="modal-body p-4 pt-0">
                            <div class="mb-3">
                                <label class="form-label text-slate-300 small">Nombre del Álbum</label>
                                <input type="text" name="name" id="edit-album-name" class="form-control bg-secondary bg-opacity-25 border-secondary text-white rounded-3" required>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">Actualizar Álbum</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalSong" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark border-secondary text-white rounded-5 shadow-2xl">
                    <div class="modal-header border-0 p-4">
                        <h5 class="modal-title fw-bold">Subir Canción</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="formSong">
                        <div class="modal-body p-4 pt-0">
                            <div class="row g-3 mb-3">
                                <div class="col-12">
                                    <label class="form-label text-slate-300 small">Título</label>
                                    <input type="text" name="title" class="form-control bg-secondary bg-opacity-25 border-secondary text-white rounded-3" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-slate-300 small">Artista</label>
                                    <input type="text" name="artist" class="form-control bg-secondary bg-opacity-25 border-secondary text-white rounded-3">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-slate-300 small">Álbum</label>
                                    <select name="album_id" id="select-albums" class="form-select bg-secondary bg-opacity-25 border-secondary text-white rounded-3">
                                        <option value="">Sin Álbum</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-slate-300 small">Archivo MP3</label>
                                    <input type="file" name="music_file" class="form-control bg-secondary bg-opacity-25 border-secondary text-white rounded-3" accept="audio/*" required>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="duration" value="00:00">
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="submit" id="btnSaveSong" class="btn btn-primary w-100 rounded-pill py-2">Subir a la nube</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalEditSong" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark border-secondary text-white rounded-5 shadow-2xl">
                    <div class="modal-header border-0 p-4">
                        <h5 class="modal-title fw-bold">Editar Canción</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="formEditSong">
                        <input type="hidden" name="id" id="edit-song-id">
                        <div class="modal-body p-4 pt-0">
                            <div class="row g-3 mb-3">
                                <div class="col-12">
                                    <label class="form-label text-slate-300 small">Título</label>
                                    <input type="text" name="title" id="edit-song-title" class="form-control bg-secondary bg-opacity-25 border-secondary text-white rounded-3" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-slate-300 small">Artista</label>
                                    <input type="text" name="artist" id="edit-song-artist" class="form-control bg-secondary bg-opacity-25 border-secondary text-white rounded-3">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-slate-300 small">Álbum</label>
                                    <select name="album_id" id="edit-select-albums" class="form-select bg-secondary bg-opacity-25 border-secondary text-white rounded-3">
                                        <option value="">Sin Álbum</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="submit" id="btnUpdateSong" class="btn btn-primary w-100 rounded-pill py-2">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-5 pt-4 border-t border-dark flex-shrink-0 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-4">
                <div class="d-flex align-items-center gap-3 font-monospace small">
                    <span class="text-muted">VOLUME:</span>
                    <div class="volume-container d-flex align-items-center" style="width: 120px;">
                        <input type="range" class="form-range" id="volume-slider" min="0" max="1" step="0.01" value="0.75">
                    </div>
                </div>
            </div>
            <div class="text-muted text-uppercase tracking-tighter small opacity-50 d-none d-md-block" style="font-size: 0.6rem;">
                Architecture: PHP 8.2 + Bootstrap 5.3 + ES6 Modules
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js" type="module"></script>
</body>
</html>
