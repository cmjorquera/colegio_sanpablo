<div class="submenu-media-card menu-header-editor">
    <fieldset>
        <legend class="form-label">Imagen/video de cabecera del menú</legend>
        <div class="submenu-hero-mode">
            <label><input type="radio" name="menu_hero_tipo" value="imagen" checked><span><i class="bi bi-image" aria-hidden="true"></i> Imagen</span></label>
            <label><input type="radio" name="menu_hero_tipo" value="video"><span><i class="bi bi-play-circle" aria-hidden="true"></i> Video</span></label>
        </div>
    </fieldset>
    <div data-menu-header-panel="imagen">
        <div class="submenu-media-preview" id="menuHeaderImagePreview"><span>Sin imagen de cabecera</span></div>
        <label class="form-label" for="menuHeaderImage">Nueva imagen de cabecera</label>
        <input type="file" class="form-control" id="menuHeaderImage" name="menu_imagen_hero" accept="image/*">
        <small class="submenu-media-note" id="menuHeaderImageCurrent"></small>
        <label class="form-check mt-2"><input type="checkbox" class="form-check-input" name="delete_menu_imagen_hero" value="1"><span class="form-check-label">Eliminar imagen de cabecera</span></label>
    </div>
    <div data-menu-header-panel="video" hidden>
        <div class="submenu-media-preview" id="menuHeaderVideoPreview"><span>Sin video de cabecera</span></div>
        <label class="form-label" for="menuHeaderVideoUrl">URL de YouTube o Vimeo</label>
        <input type="url" class="form-control mb-2" id="menuHeaderVideoUrl" name="menu_hero_video_url" placeholder="https://…" disabled>
        <label class="form-label" for="menuHeaderVideoFile">O subir un video</label>
        <input type="file" class="form-control" id="menuHeaderVideoFile" name="menu_hero_video_archivo" accept=".mp4,.webm,.mov,.m4v" disabled>
        <small class="submenu-media-note" id="menuHeaderVideoCurrent"></small>
        <label class="form-check mt-2"><input type="checkbox" class="form-check-input" name="delete_menu_hero_video" value="1" disabled><span class="form-check-label">Eliminar video de cabecera</span></label>
    </div>
</div>
