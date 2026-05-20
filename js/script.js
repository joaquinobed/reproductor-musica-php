/**
 * Script Principal - Music Player Pro
 * Modernizado con ES6+, sin eventos inline y con fetch dinámico a PHP
 */

document.addEventListener('DOMContentLoaded', () => {
    // Referencias al DOM
    const player = document.getElementById('player');
    const playerSource = document.getElementById('player-source');
    const playPauseBtn = document.getElementById('btn-play-pause');
    const playIcon = document.getElementById('play-icon');
    const prevBtn = document.getElementById('btn-prev');
    const nextBtn = document.getElementById('btn-next');
    const shuffleBtn = document.getElementById('btn-shuffle');
    const repeatBtn = document.getElementById('btn-repeat');
    const volumeSlider = document.getElementById('volume-slider');
    const progressWrapper = document.getElementById('progress-wrapper');
    const progressBar = document.getElementById('progress-bar');
    const timeCurrentLabel = document.getElementById('time-current');
    const timeTotalLabel = document.getElementById('time-total');
    const currentTitleLabel = document.getElementById('current-song-title');
    const currentInfoLabel = document.getElementById('current-song-info');
    const playlistContainer = document.getElementById('playlist');
    const albumList = document.getElementById('album-list');
    const albumSelect = document.getElementById('select-albums');
    const albumArt = document.getElementById('album-art');
    const albumArtInner = document.getElementById('album-art-inner');
    const songCountLabel = document.getElementById('song-count');
    const visualizer = document.getElementById('visualizer');
    const dropZone = document.getElementById('drop-zone');

    // Web Audio API Elements
    let audioCtx;
    let analyser;
    let source;
    let dataArray;
    let bufferLength;
    let animationId;

    // Formularios
    const formAlbum = document.getElementById('formAlbum');
    const formSong = document.getElementById('formSong');

    // Estado de la aplicación
    let songs = [];
    let albums = [];
    let currentIdx = 0;
    let currentAlbumId = 'all';
    let isShuffle = false;
    let repeatMode = 0; // 0: Off, 1: Repeat All, 2: Repeat One
    const audioFolder = 'audio';

    // 1. Cargar datos desde el backend
    const fetchAlbums = async () => {
        try {
            const response = await fetch('api/get_albums.php');
            albums = await response.json();
            renderAlbums();
            updateAlbumSelect();
        } catch (error) {
            console.error('Error cargando álbumes:', error);
        }
    };

    const fetchPlaylist = async (albumId = 'all') => {
        try {
            currentAlbumId = albumId;
            let url = 'api/get_songs.php';
            if (albumId === 'favs') {
                url = 'api/get_songs.php?favs=1'; // El backend debe soportar esto
            } else if (albumId !== 'all') {
                url = `api/get_songs.php?album_id=${albumId}`;
            }
            
            const response = await fetch(url);
            songs = await response.json();

            if (songs.error) {
                playlistContainer.innerHTML = `<div class="alert alert-warning p-2 small">${songs.error}</div>`;
                return;
            }

            renderPlaylist();
            songCountLabel.innerText = `${songs.length} canciones`;
            
            if (songs.length > 0 && player.paused) {
                loadSong(0);
            }
        } catch (error) {
            console.error('Error cargando la lista:', error);
            playlistContainer.innerHTML = '<div class="text-center py-3 text-danger">Error de servidor al cargar canciones</div>';
        }
    };

    // 2. Renderizar UI
    const renderAlbums = () => {
        // Mantener el botón "Todos" y "Favoritos"
        albumList.innerHTML = `
            <div class="text-center py-2 px-4 shadow-sm border border-secondary border-opacity-25 rounded-4 cursor-pointer album-item ${currentAlbumId === 'all' ? 'active' : ''}" data-id="all">
                <small class="fw-bold d-block">Todos</small>
            </div>
            <div class="text-center py-2 px-4 shadow-sm border border-secondary border-opacity-25 rounded-4 cursor-pointer album-item ${currentAlbumId === 'favs' ? 'active' : ''}" data-id="favs">
                <small class="fw-bold d-block"><i class="bi bi-heart-fill text-danger me-1"></i> Favoritos</small>
            </div>
        `;

        albums.forEach(album => {
            const div = document.createElement('div');
            div.className = `text-center py-2 px-4 shadow-sm border border-secondary border-opacity-25 rounded-4 cursor-pointer album-item position-relative group-hover ${currentAlbumId == album.id ? 'active' : ''}`;
            div.dataset.id = album.id;
            div.innerHTML = `
                <small class="fw-bold d-block text-truncate" style="max-width: 100px;">${album.name}</small>
                <div class="album-actions position-absolute top-0 end-0 p-1 d-flex gap-1 opacity-0 transition-all">
                    <i class="bi bi-pencil-fill text-white small btn-edit-album" title="Editar"></i>
                    <i class="bi bi-x-circle-fill text-danger small btn-delete-album" title="Eliminar"></i>
                </div>
            `;
            
            div.addEventListener('click', () => {
                document.querySelectorAll('.album-item').forEach(item => item.classList.remove('active'));
                div.classList.add('active');
                fetchPlaylist(album.id);
            });

            // Action: Edit Album
            div.querySelector('.btn-edit-album').addEventListener('click', (e) => {
                e.stopPropagation();
                openEditAlbumModal(album);
            });

            // Action: Delete Album
            div.querySelector('.btn-delete-album').addEventListener('click', (e) => {
                e.stopPropagation();
                if (confirm(`¿Estás seguro de eliminar el álbum "${album.name}"? Solo se borrará si no contiene canciones.`)) {
                    deleteAlbum(album.id);
                }
            });

            albumList.appendChild(div);
        });

        // Evento para "Todos"
        albumList.querySelector('[data-id="all"]').addEventListener('click', (e) => {
            document.querySelectorAll('.album-item').forEach(item => item.classList.remove('active'));
            e.currentTarget.classList.add('active');
            fetchPlaylist('all');
        });

        // Evento para "Favoritos"
        albumList.querySelector('[data-id="favs"]').addEventListener('click', (e) => {
            document.querySelectorAll('.album-item').forEach(item => item.classList.remove('active'));
            e.currentTarget.classList.add('active');
            fetchPlaylist('favs');
        });
    };

    const openEditAlbumModal = (album) => {
        document.getElementById('edit-album-id').value = album.id;
        document.getElementById('edit-album-name').value = album.name;
        const modal = new bootstrap.Modal(document.getElementById('modalEditAlbum'));
        modal.show();
    };

    const deleteAlbum = async (id) => {
        const formData = new FormData();
        formData.append('id', id);
        try {
            const res = await fetch('api/delete_album.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                fetchAlbums();
                if (currentAlbumId == id) fetchPlaylist('all');
            } else {
                alert(data.error);
            }
        } catch (error) {
            console.error('Error al eliminar álbum:', error);
        }
    };

    document.getElementById('formEditAlbum').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        try {
            const res = await fetch('api/update_album.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalEditAlbum')).hide();
                fetchAlbums();
            } else {
                alert(data.error);
            }
        } catch (error) {
            console.error('Error al actualizar álbum:', error);
        }
    });

    const updateAlbumSelect = () => {
        albumSelect.innerHTML = '<option value="">Sin Álbum</option>';
        albums.forEach(album => {
            const opt = document.createElement('option');
            opt.value = album.id;
            opt.textContent = album.name;
            albumSelect.appendChild(opt);
        });
    };

    const renderPlaylist = () => {
        playlistContainer.innerHTML = '';
        if (songs.length === 0) {
            playlistContainer.innerHTML = `<div class="text-center py-5 text-muted small">No hay canciones ${currentAlbumId === 'favs' ? 'favoritas' : 'en este álbum'}</div>`;
            return;
        }

        songs.forEach((song, index) => {
            const btn = document.createElement('div');
            btn.className = 'list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 border-0 mb-2 rounded-4 cursor-pointer';
            btn.dataset.index = index;
            
            const displayIndex = (index + 1).toString().padStart(2, '0');
            const favIcon = song.is_favorite == 1 ? 'bi-heart-fill text-danger' : 'bi-heart text-muted';
            
            btn.innerHTML = `
                <div class="d-flex align-items-center flex-grow-1 song-trigger">
                    <div class="badge rounded-3 me-3 fw-bold">${displayIndex}</div>
                    <div class="text-start">
                        <p class="mb-0 text-truncate font-medium song-name" style="max-width: 150px;">${song.title || song.filename}</p>
                        <p class="mb-0 text-muted small font-monospace">${song.artist || 'Artista Desconocido'}</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <i class="bi ${favIcon} hover-task btn-fav-song" data-index="${index}" title="Favorito"></i>
                    <i class="bi bi-pencil-square text-muted hover-task btn-edit-song" data-index="${index}" title="Editar"></i>
                    <i class="bi bi-trash3 text-danger hover-task btn-delete-song" data-index="${index}" title="Eliminar"></i>
                    <i class="bi bi-play-circle-fill text-primary opacity-0 fs-5 play-status-icon transition-all"></i>
                </div>
            `;

            // Trigger play only on clicking the song info area
            btn.querySelector('.song-trigger').addEventListener('click', () => {
                loadSong(index);
                playAudio();
            });

            // Double click to edit
            btn.querySelector('.song-trigger').addEventListener('dblclick', () => {
                openEditModal(index);
            });

            // Delete song
            btn.querySelector('.btn-delete-song').addEventListener('click', (e) => {
                e.stopPropagation();
                if (confirm(`¿Estás seguro de eliminar "${song.title || song.filename}"?`)) {
                    deleteSong(song.id);
                }
            });

            // Edit song modal trigger
            btn.querySelector('.btn-edit-song').addEventListener('click', (e) => {
                e.stopPropagation();
                openEditModal(index);
            });

            // Favorite toggle
            btn.querySelector('.btn-fav-song').addEventListener('click', (e) => {
                e.stopPropagation();
                toggleFavorite(song.id);
            });

            playlistContainer.appendChild(btn);
        });
        updateActiveItem();
    };

    const toggleFavorite = async (id) => {
        const formData = new FormData();
        formData.append('id', id);
        try {
            const res = await fetch('api/toggle_favorite.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                fetchPlaylist(currentAlbumId);
            }
        } catch (error) {
            console.error('Error al marcar favorito:', error);
        }
    };

    const deleteSong = async (id) => {
        const formData = new FormData();
        formData.append('id', id);
        try {
            const res = await fetch('api/delete_song.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                fetchPlaylist(currentAlbumId);
            } else {
                alert('Error: ' + (data.error || 'No se pudo eliminar la canción'));
            }
        } catch (error) {
            console.error('Error al eliminar canción:', error);
            alert('Error de conexión al intentar eliminar');
        }
    };

    const openEditModal = (index) => {
        const song = songs[index];
        document.getElementById('edit-song-id').value = song.id;
        document.getElementById('edit-song-title').value = song.title || '';
        document.getElementById('edit-song-artist').value = song.artist || '';
        
        // Cargar álbumes en el select de música
        const editAlbumSelect = document.getElementById('edit-select-albums');
        editAlbumSelect.innerHTML = '<option value="">Sin Álbum</option>';
        albums.forEach(album => {
            const opt = document.createElement('option');
            opt.value = album.id;
            opt.textContent = album.name;
            if (album.id == song.album_id) opt.selected = true;
            editAlbumSelect.appendChild(opt);
        });

        const modal = new bootstrap.Modal(document.getElementById('modalEditSong'));
        modal.show();
    };

    document.getElementById('formEditSong').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        try {
            const res = await fetch('api/update_song.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                const modalEl = document.getElementById('modalEditSong');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                fetchPlaylist(currentAlbumId);
            } else {
                alert('Error: ' + (data.error || 'No se pudo actualizar'));
            }
        } catch (error) {
            console.error('Error al actualizar canción:', error);
            alert('Error de conexión al intentar actualizar');
        }
    });

    // 3. Cargar una canción específica
    const loadSong = (index) => {
        currentIdx = index;
        const songData = songs[currentIdx];
        
        playerSource.src = `${audioFolder}/${songData.filename}`;
        player.load();
        
        // UI Updates
        currentTitleLabel.innerText = songData.title || songData.filename;
        currentInfoLabel.innerText = songData.artist || 'Artista Desconocido';
        
        // Update Cover
        if (songData.cover_url) {
            albumArtInner.innerHTML = `<img src="${songData.cover_url}" class="w-100 h-100 object-fit-cover">`;
        } else {
            albumArtInner.innerHTML = `<i class="bi bi-music-note"></i>`;
        }

        updateActiveItem();
    };

    const updateActiveItem = () => {
        document.querySelectorAll('.list-group-item').forEach((el, index) => {
            if (index === currentIdx) {
                el.classList.add('active');
                const icon = el.querySelector('.play-status-icon');
                if (icon) icon.classList.remove('opacity-0');
            } else {
                el.classList.remove('active');
                const icon = el.querySelector('.play-status-icon');
                if (icon) icon.classList.add('opacity-0');
            }
        });
    };

    // 4. Manejo de Formularios
    formAlbum.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(formAlbum);
        formData.append('type', 'image');

        try {
            const res = await fetch('api/create_album.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalAlbum')).hide();
                formAlbum.reset();
                fetchAlbums();
            }
        } catch (error) {
            console.error('Error al crear álbum:', error);
        }
    });

    formSong.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(formSong);
        formData.append('type', 'audio');

        try {
            const res = await fetch('api/upload_song.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalSong')).hide();
                formSong.reset();
                fetchPlaylist(currentAlbumId);
            }
        } catch (error) {
            console.error('Error al subir canción:', error);
        }
    });

    // 5. Controles de Reproducción
    const togglePlay = () => {
        if (player.paused) {
            playAudio();
        } else {
            pauseAudio();
        }
    };

    const playAudio = () => {
        if (!audioCtx) setupVisualizer();
        
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        
        player.play()
            .then(() => {
                playIcon.className = 'bi bi-pause-fill fs-1';
                currentInfoLabel.innerText = songs[currentIdx]?.artist || 'Reproduciendo...';
            })
            .catch(err => {
                console.error('Error al reproducir:', err);
                if (err.name === 'NotAllowedError') {
                    alert('La reproducción automática fue bloqueada. Haz clic en Play para comenzar.');
                }
            });
    };

    const pauseAudio = () => {
        player.pause();
        playIcon.className = 'bi bi-play-fill fs-1';
        currentInfoLabel.innerText = 'Pausado';
    };

    const nextSong = () => {
        if (songs.length === 0) return;

        if (repeatMode === 2) {
            // Repeat One: Restart current song
            loadSong(currentIdx);
            playAudio();
            return;
        }

        if (isShuffle && songs.length > 1) {
            let nextIdx;
            do {
                nextIdx = Math.floor(Math.random() * songs.length);
            } while (nextIdx === currentIdx);
            currentIdx = nextIdx;
        } else {
            currentIdx = (currentIdx + 1) % songs.length;
        }

        loadSong(currentIdx);
        playAudio();
    };

    const prevSong = () => {
        if (songs.length === 0) return;
        
        if (isShuffle && songs.length > 1) {
            let prevIdx;
            do {
                prevIdx = Math.floor(Math.random() * songs.length);
            } while (prevIdx === currentIdx);
            currentIdx = prevIdx;
        } else {
            currentIdx = (currentIdx - 1 + songs.length) % songs.length;
        }
        
        loadSong(currentIdx);
        playAudio();
    };

    const toggleShuffle = () => {
        isShuffle = !isShuffle;
        shuffleBtn.classList.toggle('text-primary', isShuffle);
        shuffleBtn.classList.toggle('text-muted', !isShuffle);
    };

    const toggleRepeat = () => {
        repeatMode = (repeatMode + 1) % 3;
        
        // Update UI Icons and Colors
        const icon = repeatBtn.querySelector('i');
        if (repeatMode === 0) {
            repeatBtn.className = 'btn btn-link text-muted p-0';
            icon.className = 'bi bi-repeat fs-4';
            repeatBtn.title = 'Repetir Desactivado';
        } else if (repeatMode === 1) {
            repeatBtn.className = 'btn btn-link text-primary p-0';
            icon.className = 'bi bi-repeat fs-4';
            repeatBtn.title = 'Repetir Todo';
        } else if (repeatMode === 2) {
            repeatBtn.className = 'btn btn-link text-primary p-0';
            icon.className = 'bi bi-repeat-1 fs-4';
            repeatBtn.title = 'Repetir Una';
        }
    };

    // 6. Visualizador de Espectro
    const setupVisualizer = () => {
        try {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            analyser = audioCtx.createAnalyser();
            source = audioCtx.createMediaElementSource(player);
            source.connect(analyser);
            analyser.connect(audioCtx.destination);
            
            analyser.fftSize = 256;
            bufferLength = analyser.frequencyBinCount;
            dataArray = new Uint8Array(bufferLength);
            
            // Ajustar resolución del canvas
            const resizeCanvas = () => {
                visualizer.width = visualizer.offsetWidth;
                visualizer.height = visualizer.offsetHeight;
            };
            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();

            drawVisualizer();
        } catch (e) {
            console.error("No se pudo inicializar el Web Audio API:", e);
        }
    };

    const drawVisualizer = () => {
        animationId = requestAnimationFrame(drawVisualizer);
        analyser.getByteFrequencyData(dataArray);
        
        const canvas = visualizer;
        const ctx = canvas.getContext('2d');
        const width = canvas.width;
        const height = canvas.height;
        
        ctx.clearRect(0, 0, width, height);
        
        const barWidth = (width / bufferLength) * 1.5;
        let barHeight;
        let x = 0;
        
        for(let i = 0; i < bufferLength; i++) {
            // Escalar la altura de la barra para que llene mejor el espacio
            barHeight = (dataArray[i] / 255) * height;
            
            // Color degradado (de índigo a cian)
            const hue = 220 + (i / bufferLength) * 40;
            ctx.fillStyle = `hsla(${hue}, 80%, 60%, 0.7)`;
            
            // Dibujar barra con bordes ligeramente redondeados (opcional, aquí rectos para performance)
            ctx.fillRect(x, height - barHeight, barWidth, barHeight);
            
            x += barWidth + 2;
            if (x > width) break;
        }
    };

    // 7. Drag and Drop
    const setupDragAndDrop = () => {
        window.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.remove('d-none');
            dropZone.classList.add('d-flex');
        });

        dropZone.addEventListener('dragleave', (e) => {
            if (e.relatedTarget === null) {
                dropZone.classList.add('d-none');
                dropZone.classList.remove('d-flex');
            }
        });

        window.addEventListener('drop', async (e) => {
            e.preventDefault();
            dropZone.classList.add('d-none');
            dropZone.classList.remove('d-flex');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                for (const file of files) {
                    if (file.type.includes('audio')) {
                        await uploadFileDirectly(file);
                    }
                }
            }
        });
    };

    const uploadFileDirectly = async (file) => {
        const formData = new FormData();
        formData.append('music_file', file);
        formData.append('album_id', currentAlbumId !== 'all' && currentAlbumId !== 'favs' ? currentAlbumId : '');

        try {
            const res = await fetch('api/upload_song.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                fetchPlaylist(currentAlbumId);
            } else {
                alert(data.error || 'Error al subir archivo');
            }
        } catch (error) {
            console.error('Error en subida directa:', error);
        }
    };

    // 8. Barra de Progreso y Tiempo
    const secondsToString = (seconds) => {
        if (isNaN(seconds)) return '00:00';
        const min = Math.floor(seconds / 60);
        const sec = Math.floor(seconds % 60);
        return `${min.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}`;
    };

    const updateUIProgress = () => {
        const { currentTime, duration } = player;
        if (!duration) return;
        const progressPercent = (currentTime / duration) * 100;
        
        progressBar.style.width = `${progressPercent}%`;
        timeCurrentLabel.innerText = secondsToString(currentTime);
        timeTotalLabel.innerText = secondsToString(duration);
    };

    const setProgress = (e) => {
        const width = progressWrapper.clientWidth;
        const clickX = e.offsetX;
        const duration = player.duration;
        if (!duration) return;
        player.currentTime = (clickX / width) * duration;
    };

    // 7. Event Listeners
    playPauseBtn.addEventListener('click', togglePlay);
    nextBtn.addEventListener('click', nextSong);
    prevBtn.addEventListener('click', prevSong);
    shuffleBtn.addEventListener('click', toggleShuffle);
    repeatBtn.addEventListener('click', toggleRepeat);
    volumeSlider.addEventListener('input', (e) => player.volume = e.target.value);
    
    player.addEventListener('timeupdate', updateUIProgress);
    player.addEventListener('ended', nextSong);
    player.addEventListener('loadedmetadata', updateUIProgress);
    
    player.addEventListener('error', (e) => {
        console.error('Error de carga de audio:', e);
        currentInfoLabel.innerText = 'Error: Archivo no encontrado';
        playIcon.className = 'bi bi-play-fill fs-1';
        
        const song = songs[currentIdx];
        if (song) {
            if (confirm(`El archivo "${song.filename}" no se encuentra en el servidor. ¿Deseas eliminar el registro de la base de datos?`)) {
                deleteSong(song.id);
            }
        }
    });
    
    progressWrapper.addEventListener('click', setProgress);

    // Inicialización
    fetchAlbums();
    fetchPlaylist();
    setupDragAndDrop();
});
