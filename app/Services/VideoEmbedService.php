<?php

namespace App\Services;

class VideoEmbedService
{
    /**
     * @return array{kind: string, src: string}|null
     */
    public function resolve(?string $url): ?array
    {
        if (! filled($url)) {
            return null;
        }

        $url = trim($url);

        if ($youtubeId = $this->extractYouTubeId($url)) {
            return [
                'kind' => 'iframe',
                'src' => 'https://www.youtube-nocookie.com/embed/'.$youtubeId.'?autoplay=1&mute=1&loop=1&playlist='.$youtubeId.'&controls=0&rel=0',
            ];
        }

        if ($vimeoId = $this->extractVimeoId($url)) {
            return [
                'kind' => 'iframe',
                'src' => 'https://player.vimeo.com/video/'.$vimeoId.'?autoplay=1&muted=1&loop=1&background=1',
            ];
        }

        if ($this->isDirectVideoFile($url)) {
            return [
                'kind' => 'video',
                'src' => $url,
            ];
        }

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return [
                'kind' => 'iframe',
                'src' => $url,
            ];
        }

        return null;
    }

    private function extractYouTubeId(string $url): ?string
    {
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function extractVimeoId(string $url): ?string
    {
        if (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function isDirectVideoFile(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';

        return (bool) preg_match('/\.(mp4|webm|ogg)$/i', $path);
    }
}
