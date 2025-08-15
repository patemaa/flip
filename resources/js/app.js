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
                // Eğer ses dosyası henüz yüklenmediyse, yeniden deneriz
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


Alpine.start();
