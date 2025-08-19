import './bootstrap';

import Alpine from 'alpinejs';


Alpine.data('countdown', () => ({
    secondsToCountdown: 1,

    // Helper variables
    seconds: 0,
    timer: null,
    isPaused: false,
    isFinished: false,

    // Initialization
    init() {
        this.seconds = this.secondsToCountdown;
        this.startTimer();
    },

    // Format time
    formatTime(time) {
        let formattedTime = '';

        const days = Math.floor(time / 86400);
        const hours = Math.floor((time % 86400) / 3600);
        const minutes = Math.floor((time % 3600) / 60);
        const seconds = time % 60;

        if (days > 0) formattedTime += `${days}d `;
        if (days > 0 || hours > 0) formattedTime += `${hours.toString().padStart(2, '0')}:`;

        formattedTime += `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

        return formattedTime.trim();
    },

    // Start timer
    startTimer() {
        this.timer = setInterval(() => {
            if (this.seconds > 0) {
                this.seconds--;
            } else {
                this.stopTimer();
                this.isFinished = true;

                window.dispatchEvent(new CustomEvent('show-notification', {
                    detail: {
                        message: 'Countdown finished!',
                        type: 'success'
                    }
                }));

            }
        }, 1000);

        this.isPaused = false;
    },

    // Pause timer
    pauseTimer() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
            this.isPaused = true;
        }
    },

    // Resume timer
    resumeTimer() {
        if (this.isPaused) {
            this.startTimer();
        }
    },

    // Stop timer
    stopTimer() {
        clearInterval(this.timer);

        this.seconds = 0;
        this.timer = null;
        this.isPaused = false;
        this.isFinished = true;
    },

    // Restart timer
    restartTimer() {
        this.stopTimer();
        this.seconds = this.secondsToCountdown;
        this.isFinished = false;
        this.startTimer();
    },
}));









Alpine.data('notificationCenter', () => ({
    position: 'top-end', // 'top-start', 'top-end', 'bottom-start', 'bottom-end'
    autoClose: true,
    autoCloseDelay: 3000,
    notifications: [],
    nextId: 1,
    soundFile: '/audios/circus.mp3',
    audio: null,

    init() {
        window.addEventListener('show-notification', event => {
            const { message, type, link } = event.detail;
            this.triggerNotification(message, type, link);
        });
        document.addEventListener('click', () => {
            if (!this.audio) {
                this.audio = new Audio(this.soundFile);
            }
        }, { once: true });
    },


    transitionClasses: {
        'x-transition:enter-start'() {
            if (this.position === 'top-start' || this.position === 'bottom-start') {
                return 'opacity-0 -translate-x-12 rtl:translate-x-12';
            } else {
                return 'opacity-0 translate-x-12 rtl:-translate-x-12';
            }
        },
        'x-transition:leave-end'() {
            if (this.position === 'top-start' || this.position === 'bottom-start') {
                return 'opacity-0 -translate-x-12 rtl:translate-x-12';
            } else {
                return 'opacity-0 translate-x-12 rtl:-translate-x-12';
            }
        },
    },

    triggerNotification(message, type, link) {
        this.playSound();

        const id = this.nextId++;
        this.notifications.push({ id, message, type, link, visible: false });

        setTimeout(() => {
            const index = this.notifications.findIndex(n => n.id === id);
            if (index > -1) {
                this.notifications[index].visible = true;
            }
        }, 30);

        if (this.autoClose) {
            setTimeout(() => this.dismissNotification(id), this.autoCloseDelay);
        }
    },

    playSound() {
        try {
            if (this.audio) {
                this.audio.play();
            } else {
                this.audio = new Audio(this.soundFile);
                this.audio.play();
            }
        } catch (e) {
            console.error('Bildirim sesi çalınamadı:', e);
        }
    },

    dismissNotification(id) {
        const index = this.notifications.findIndex(n => n.id === id);
        if (index > -1) {
            this.notifications[index].visible = false;
            setTimeout(() => {
                this.notifications.splice(index, 1);
            }, 300);
        }
    }
}));
document.addEventListener('alpine:init', () => {
    Alpine.data('pomodoroTimer', () => ({
        sessionStarted: false,
        sessionType: '',
        customMinutes: 25,
        projectName: '',
        priority: 'medium',
        seconds: 0,
        totalSeconds: 0,
        isPaused: false,
        isFinished: false,
        interval: null,
        sessionId: null,
        accumulatedSeconds: 0,
        sessionStartTime: null,

        init() {
            // Request notification permission
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        },

        async selectSession(type, minutes) {
            this.sessionType = type;
            this.totalSeconds = parseInt(minutes) * 60;
            this.seconds = this.totalSeconds;
            this.sessionStarted = true;
            this.isPaused = false;
            this.isFinished = false;
            this.accumulatedSeconds = 0;

            await this.createSession();

            this.startTimer();
        },

        async startTimer() {
            if (this.interval) clearInterval(this.interval);

            this.sessionStartTime = new Date();

            this.interval = setInterval(() => {
                if (!this.isPaused && !this.isFinished) {
                    this.seconds--;

                    if (this.seconds <= 0) {
                        this.finishTimer();
                    }
                }
            }, 1000);
        },

        async pauseTimer() {
            this.isPaused = true;
            if (this.sessionStartTime) {
                const elapsed = Math.floor((new Date() - this.sessionStartTime) / 1000);
                this.accumulatedSeconds += elapsed;
            }
            await this.updateSessionStatus('paused');
        },

        async resumeTimer() {
            this.isPaused = false;
            this.sessionStartTime = new Date();
            await this.updateSessionStatus('in_progress');
        },

        async stopTimer() {
            if (this.interval) {
                clearInterval(this.interval);
                this.interval = null;
            }
            await this.updateSessionStatus('cancelled');
            this.resetSession();
        },

        async finishTimer() {
            if (this.interval) {
                clearInterval(this.interval);
                this.interval = null;
            }

            this.isFinished = true;
            this.seconds = 0;

            await this.updateSessionStatus('completed');

            this.showNotification();
            this.playSound();
        },

        async createSession() {
            try {
                const response = await fetch('/api/pomodoro/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        type: this.sessionType,
                        duration_seconds: this.totalSeconds,
                        project_name: this.projectName || null,
                        priority: this.priority
                    })
                });

                const data = await response.json();
                if (data.session) {
                    this.sessionId = data.session.id;
                    console.log('Session created:', this.sessionId);
                }
            } catch (error) {
                console.error('Error creating session:', error);
            }
        },

        async updateSessionStatus(status) {
            if (!this.sessionId) return;

            try {
                await fetch(`/api/pomodoro/${this.sessionId}/update`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        status: status,
                        accumulated_seconds: this.accumulatedSeconds
                    })
                });

                console.log('Session updated:', status);
            } catch (error) {
                console.error('Error updating session:', error);
            }
        },

        async completeSession() {
            this.resetSession();
            await this.updateSessionStatus('completed');
        },

        resetSession() {
            this.sessionStarted = false;
            this.sessionType = '';
            this.seconds = 0;
            this.totalSeconds = 0;
            this.isPaused = false;
            this.isFinished = false;
            this.sessionId = null;
            this.accumulatedSeconds = 0;
            this.sessionStartTime = null;

            if (this.interval) {
                clearInterval(this.interval);
                this.interval = null;
            }
        },

        formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        },

        getSessionTypeLabel() {
            const labels = {
                'work': 'Work Session',
                'short_break': 'Short Break',
                'long_break': 'Long Break',
                'custom': 'Custom Session'
            };
            return labels[this.sessionType] || 'Pomodoro Session';
        },

        getSessionIcon() {
            const icons = {
                'work': '🍅',
                'short_break': '☕',
                'long_break': '🌅',
                'custom': '⏱️'
            };
            return icons[this.sessionType] || '🍅';
        },

        getTimerColor() {
            if (this.isFinished) return 'text-green-500';
            if (this.isPaused) return 'text-black';

            const progress = (this.totalSeconds - this.seconds) / this.totalSeconds;
            if (progress < 0.2) return 'text-green-500';
            if (progress < 0.4) return 'text-blue-500';
            if (progress < 0.6) return 'text-yellow-500';
            if (progress < 0.8) return 'text-orange-500';
            return 'text-red-500';
        },

        showNotification() {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('🍅 Pomodoro Complete!', {
                    body: `Your ${this.getSessionTypeLabel()} is finished!`,
                    icon: '/favicon.ico'
                });
            }
        },

        playSound() {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();

            const playBeep = (frequency, duration, delay = 0) => {
                setTimeout(() => {
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();

                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);

                    oscillator.frequency.value = frequency;
                    gainNode.gain.setValueAtTime(0, audioContext.currentTime);
                    gainNode.gain.linearRampToValueAtTime(0.3, audioContext.currentTime + 0.01);
                    gainNode.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + duration);

                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + duration);
                }, delay);
            };

            playBeep(800, 0.2, 0);
            playBeep(800, 0.2, 300);
            playBeep(800, 0.4, 600);
        }
    }));
});

Alpine.start();
