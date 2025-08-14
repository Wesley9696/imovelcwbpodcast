<?php
function get_youtube_videos() {
    $apiKey = 'AIzaSyAPGQ_N6WddNz0FRyfuktZTA59wl3BtzC0'; // <-- SUBSTITUA PELA SUA CHAVE DE API
    $channelId = 'UCPm9P4m4-Q3GwKsWnhFg5uA'; // <-- SEU ID DE CANAL CORRETO

    $cacheFile = 'videos_cache.json';
    $cacheTime = 86400; // Tempo em segundos (86400s = 24 horas)

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
        return json_decode(file_get_contents($cacheFile), true);
    }

    $videos = [];
    try {
        $uploadsApiUrl = "https://www.googleapis.com/youtube/v3/search?key=$apiKey&channelId=$channelId&part=snippet,id&order=date&maxResults=50";
        $uploadsResponse = @file_get_contents($uploadsApiUrl);
        $uploadsData = json_decode($uploadsResponse, true);

        $videoIds = [];
        if (isset($uploadsData['items'])) {
            foreach ($uploadsData['items'] as $item) {
                if ($item['id']['kind'] === 'youtube#video') {
                    $videoIds[] = $item['id']['videoId'];
                }
            }
        }
        
        if (empty($videoIds)) {
            $error = ['error' => 'Nenhum vídeo normal encontrado.'];
            file_put_contents($cacheFile, json_encode($error));
            return $error;
        }
        
        $videoDetailsApiUrl = "https://www.googleapis.com/youtube/v3/videos?key=$apiKey&id=" . implode(',', $videoIds) . "&part=contentDetails,snippet";
        $videoDetailsResponse = @file_get_contents($videoDetailsApiUrl);
        $videoDetailsData = json_decode($videoDetailsResponse, true);

        $filteredVideos = [];
        if (isset($videoDetailsData['items'])) {
            foreach ($videoDetailsData['items'] as $item) {
                $duration = new DateInterval($item['contentDetails']['duration']);
                $totalSeconds = $duration->s + $duration->i * 60 + $duration->h * 3600;
                
                // Filtro final: duração maior que 120s e não ser uma transmissão ao vivo
                if ($totalSeconds > 120 && (!isset($item['snippet']['liveBroadcastContent']) || $item['snippet']['liveBroadcastContent'] !== 'live')) {
                    $filteredVideos[] = [
                        'videoId' => $item['id'],
                        'title' => $item['snippet']['title'],
                        'thumbnail' => $item['snippet']['thumbnails']['medium']['url'],
                    ];
                }
            }
        }

        file_put_contents($cacheFile, json_encode($filteredVideos));

    } catch (Exception $e) {
        $error = ['error' => 'Ocorreu um erro ao buscar os vídeos: ' . $e->getMessage()];
        file_put_contents($cacheFile, json_encode($error));
        return $error;
    }

    return $filteredVideos;
}
?>