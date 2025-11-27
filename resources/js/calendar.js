import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';
import frLocale from '@fullcalendar/core/locales/fr';

/**
 * Format duration in minutes to human-readable format.
 * Matches DurationHelper.php logic.
 */
function formatDuration(minutes, translations = {}) {
    // Round to integer to avoid decimal issues
    minutes = Math.round(minutes);

    if (!minutes || minutes === 0) {
        return '-';
    }

    const minLabel = translations.min || 'min';
    const hLabel = translations.h || 'h';

    // Less than 1 hour - show in minutes
    if (minutes < 60) {
        return `${minutes}${minLabel}`;
    }

    // Less than 24 hours - show in hours and minutes
    if (minutes < 1440) {
        const hours = Math.floor(minutes / 60);
        const mins = Math.round(minutes % 60);

        if (mins > 0) {
            return `${hours}${hLabel} ${mins}${minLabel}`;
        }
        return `${hours}${hLabel}`;
    }

    // 24 hours or more - show in days and hours
    const days = Math.floor(minutes / 1440);
    const remainingMinutes = minutes % 1440;
    const hours = Math.floor(remainingMinutes / 60);

    const dayLabel = days > 1 ? (translations.days || 'days') : (translations.day || 'day');

    if (hours > 0) {
        return `${days} ${dayLabel} ${hours}${hLabel}`;
    }

    return `${days} ${dayLabel}`;
}

/**
 * Format a date for display.
 * Matches DateHelper.php format for consistency.
 * - Spanish (es): d/m/Y H:i (24-hour)
 * - English (en): m/d/Y h:i A (12-hour with AM/PM)
 *
 * Note: PHP already sends dates in the user's timezone, so no timezone conversion needed here.
 */
function formatDateForDisplay(date, locale) {
    const d = new Date(date);

    // Extract date parts directly (date is already in user's timezone from PHP)
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    const hours = d.getHours();
    const minutes = String(d.getMinutes()).padStart(2, '0');

    if (locale === 'es') {
        // Spanish: d/m/Y H:i (24-hour)
        const hour24 = String(hours).padStart(2, '0');
        return `${day}/${month}/${year} ${hour24}:${minutes}`;
    } else {
        // English: m/d/Y h:i A (12-hour with AM/PM)
        const hour12 = hours % 12 || 12;
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const hourStr = String(hour12).padStart(2, '0');
        return `${month}/${day}/${year} ${hourStr}:${minutes} ${ampm}`;
    }
}

export function initializeCalendar(calendarEl, livewireComponent, translations = {}) {
    // Get locale from HTML lang attribute
    const locale = document.documentElement.lang || 'en';

    // Get user timezone from data attribute (from DB) - this takes priority
    // Only fallback to browser timezone if no user timezone is set in DB
    const userTimezone = document.body.dataset.userTimezone || Intl.DateTimeFormat().resolvedOptions().timeZone;

    // Map locale codes to FullCalendar locale objects
    const localeMap = {
        'es': esLocale,
        'fr': frLocale,
        'en': 'en'
    };

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        timeZone: userTimezone,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        locale: localeMap[locale] || 'en',
        height: 'auto',
        editable: false,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        weekends: true,
        displayEventTime: true,
        displayEventEnd: true,
        eventDisplay: 'block',

        // Fetch events from Livewire
        events: function(info, successCallback, failureCallback) {
            livewireComponent.call('getEvents', info.startStr, info.endStr).then(events => {
                successCallback(events);
            }).catch(error => {
                console.error('Error loading events:', error);
                failureCallback(error);
            });
        },

        // Event click handler
        eventClick: function(info) {
            const event = info.event;
            const props = event.extendedProps;

            let content = '';

            if (props.type === 'work') {
                const duration = props.duration
                    ? formatDuration(props.duration, translations)
                    : translations.inProgress || 'In progress';

                content = `
                    <div class="p-4">
                        <h3 class="text-lg font-bold mb-2">${event.title}</h3>
                        <p><strong>${translations.start || 'Start'}:</strong> ${formatDateForDisplay(event.start, locale)}</p>
                        ${event.end ? `<p><strong>${translations.end || 'End'}:</strong> ${formatDateForDisplay(event.end, locale)}</p>` : `<p class="text-warning">${translations.currentlyClocked || 'Currently clocked in'}</p>`}
                        <p><strong>${translations.duration || 'Duration'}:</strong> ${duration}</p>
                    </div>
                `;
            } else if (props.type === 'absence') {
                // Calculate duration for absence
                const startDate = new Date(event.start);
                const endDate = new Date(event.end);
                const durationMinutes = Math.floor((endDate - startDate) / (1000 * 60));
                const duration = formatDuration(durationMinutes, translations);

                content = `
                    <div class="p-4">
                        <h3 class="text-lg font-bold mb-2">${event.title}</h3>
                        <p><strong>${translations.status || 'Status'}:</strong> <span class="badge badge-${props.status === 'approved' ? 'success' : props.status === 'pending' ? 'warning' : 'error'}">${props.statusLabel}</span></p>
                        <p><strong>${translations.start || 'Start'}:</strong> ${formatDateForDisplay(event.start, locale)}</p>
                        <p><strong>${translations.end || 'End'}:</strong> ${formatDateForDisplay(event.end, locale)}</p>
                        <p><strong>${translations.duration || 'Duration'}:</strong> ${duration}</p>
                        ${props.isBreak ? '<p class="text-info">🍽 Break</p>' : ''}
                        ${props.reason ? `<p class="mt-2"><strong>${translations.reason || 'Reason'}:</strong> ${props.reason}</p>` : ''}
                    </div>
                `;
            }

            // Show modal with event details
            showEventModal(content, translations);
        },

        // Date select handler (for future: quick absence request)
        select: function(info) {
            console.log('Selected dates:', info.startStr, 'to', info.endStr);
            // Future: Open absence request modal with pre-filled dates
        }
    });

    calendar.render();
    return calendar;
}

function showEventModal(content, translations = {}) {
    // Create modal element
    const modal = document.createElement('dialog');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-box">
            ${content}
            <div class="modal-action">
                <button class="btn" onclick="this.closest('dialog').close()">${translations.close || 'Close'}</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    `;

    document.body.appendChild(modal);
    modal.showModal();

    // Remove modal when closed
    modal.addEventListener('close', () => {
        modal.remove();
    });
}

// Make it globally available
window.initializeCalendar = initializeCalendar;
