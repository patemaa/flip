<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pomodoro extends Model
{
    protected $fillable = [
        'type',
        'duration_seconds',
        'started_at',
        'ended_at',
        'status',
        'project_name',
        'priority',
        'tags',
        'expected_end_at',
        'accumulated_seconds',
        'paused_at',
    ];
    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'expected_end_at' => 'datetime',
        'paused_at' => 'datetime',
        'tags' => 'array',
        'duration_seconds' => 'integer',
        'accumulated_seconds' => 'integer',
    ];
    public function startSession()
    {
        $now = now();

        $this->update([
            'status' => 'in_progress',
            'started_at' => $now,
            'expected_end_at' => $now->copy()->addSeconds($this->duration_seconds - $this->accumulated_seconds),
            'paused_at' => null,
        ]);
    }
    public function pauseSession()
    {
        if ($this->status !== 'in_progress' || !$this->started_at) {
            return;
        }

        $now = now();
        $sessionTime = $this->started_at->diffInSeconds($now);

        $this->update([
            'status' => 'paused',
            'accumulated_seconds' => $this->accumulated_seconds + $sessionTime,
            'paused_at' => $now,
            'expected_end_at' => null, // Pause'da teorik zaman sıfırlanır
        ]);
    }
    public function resumeSession()
    {
        if ($this->status !== 'paused') {
            return;
        }

        $now = now();
        $remainingSeconds = $this->duration_seconds - $this->accumulated_seconds;

        $this->update([
            'status' => 'in_progress',
            'started_at' => $now, // Yeni başlangıç zamanı
            'expected_end_at' => $now->copy()->addSeconds($remainingSeconds),
            'paused_at' => null,
        ]);
    }
    public function completeSession()
    {
        $this->update([
            'status' => 'completed',
            'ended_at' => now(),
            'expected_end_at' => null,
        ]);
    }

    /**
     * Kalan süreyi hesapla (saniye)
     */
    public function getRemainingSecondsAttribute()
    {
        if ($this->status === 'in_progress' && $this->expected_end_at) {
            return max(0, now()->diffInSeconds($this->expected_end_at, false));
        }

        if ($this->status === 'paused') {
            return max(0, $this->duration_seconds - $this->accumulated_seconds);
        }

        return 0;
    }

    /**
     * Formatlanmış kalan süre (MM:SS)
     */
    public function getFormattedRemainingTimeAttribute()
    {
        $seconds = $this->remaining_seconds;
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Session'ın süresi dolmuş mu?
     */
    public function getIsExpiredAttribute()
    {
        return $this->status === 'in_progress'
            && $this->expected_end_at
            && now()->greaterThan($this->expected_end_at);
    }

    /**
     * Geçen toplam süre (pause'lar dahil)
     */
    public function getTotalElapsedSecondsAttribute()
    {
        $total = $this->accumulated_seconds;

        if ($this->status === 'in_progress' && $this->started_at) {
            $total += $this->started_at->diffInSeconds(now());
        }

        return $total;
    }
}
