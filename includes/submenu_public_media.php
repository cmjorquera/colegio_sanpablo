<?php

function sp_submenu_video_embed(?string $url): string
{
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }

    if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }

    if (preg_match('~vimeo\.com/([0-9]+)~', $url, $m)) {
        return 'https://player.vimeo.com/video/' . $m[1];
    }

    return '';
}
