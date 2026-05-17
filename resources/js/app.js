import Sortable from 'sortablejs';
import './dashy-ui.js';
import './dashy-charts.js';
import './fullcalendar-instance.js';

window.Sortable = Sortable;

/**
 * Browsers can't read the duration from a WebM/Matroska container produced
 * by MediaRecorder — the format streams without a duration cue, so
 * <audio>.duration reads as Infinity (or NaN) and the scrubber stays at
 * 0:00 / 0:00. The fix: once metadata loads, seek to a very large position
 * to force the decoder to scan to EOF, then jump back to the start.
 */
window.fixWebmDuration = (audio) => {
    if (!audio) {
        return;
    }

    const repair = () => {
        if (audio.duration === Infinity || Number.isNaN(audio.duration)) {
            const restoreOnce = () => {
                audio.removeEventListener('timeupdate', restoreOnce);
                audio.currentTime = 0;
            };
            audio.addEventListener('timeupdate', restoreOnce);
            audio.currentTime = 1e101;
        }
    };

    if (audio.readyState >= 1) {
        repair();
    } else {
        audio.addEventListener('loadedmetadata', repair, { once: true });
    }
};
