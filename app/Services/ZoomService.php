<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZoomService
{
    public function isConfigured(): bool
    {
        return !empty(config('services.zoom.account_id'))
            && !empty(config('services.zoom.client_id'))
            && !empty(config('services.zoom.client_secret'));
    }

    private function getToken(): ?string
    {
        return Cache::remember('zoom_access_token', 3500, function () {
            $response = Http::withBasicAuth(
                config('services.zoom.client_id'),
                config('services.zoom.client_secret')
            )->asForm()->post('https://zoom.us/oauth/token', [
                'grant_type' => 'account_credentials',
                'account_id' => config('services.zoom.account_id'),
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('Zoom token error', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        });
    }

    public function createMeeting(string $topic, Carbon $startTime, int $durationMinutes, string $agenda = ''): ?array
    {
        $token = $this->getToken();
        if (!$token) return null;

        $response = Http::withToken($token)
            ->post('https://api.zoom.us/v2/users/me/meetings', [
                'topic'      => $topic,
                'type'       => 2,
                'start_time' => $startTime->utc()->format('Y-m-d\TH:i:s\Z'),
                'duration'   => $durationMinutes,
                'agenda'     => $agenda,
                'settings'   => [
                    'host_video'        => true,
                    'participant_video' => true,
                    'join_before_host'  => true,
                    'waiting_room'      => false,
                ],
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Zoom create meeting error', ['status' => $response->status(), 'body' => $response->body()]);
        return null;
    }

    public function deleteMeeting(string $zoomMeetingId): void
    {
        $token = $this->getToken();
        if (!$token) return;

        Http::withToken($token)->delete("https://api.zoom.us/v2/meetings/{$zoomMeetingId}");
    }
}
