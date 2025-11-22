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
function formatDuration(minutes) {
    if (!minutes || minutes === 0) {
        return '-';
    }

    // Less than 1 hour - show in minutes
    if (minutes < 60) {
        return `${minutes}min`;
    }

    // Less than 24 hours - show in hours and minutes
    if (minutes < 1440) {
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;

        if (mins > 0) {
            return `${hours}h ${mins}min`;
        }
        return `${hours}h`;
    }

    // 24 hours or more - show in days and hours
    const days = Math.floor(minutes / 1440);
    const remainingMinutes = minutes % 1440;
    const hours = Math.floor(remainingMinutes / 60);

    if (hours > 0) {
        const dayLabel = days > 1 ? 'days' : 'day';
        return `${days} ${dayLabel} ${hours}h`;
    }

    const dayLabel = days > 1 ? 'days' : 'day';
    return `${days} ${dayLabel}`;
}

export function initializeCalendar(calendarEl, livewireComponent) {
    // Get locale from HTML lang attribute
    const locale = document.documentElement.lang || 'en';

    // Map locale codes to FullCalendar locale objects
    const localeMap = {
        'es': esLocale,
        'fr': frLocale,
        'en': 'en'
    };

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
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
                    ? formatDuration(props.duration)
                    : 'In progress';

                content = `
                    <div class="p-4">
                        <h3 class="text-lg font-bold mb-2">${event.title}</h3>
                        <p><strong>Start:</strong> ${new Date(event.start).toLocaleString(locale)}</p>
                        ${event.end ? `<p><strong>End:</strong> ${new Date(event.end).toLocaleString(locale)}</p>` : '<p class="text-warning">Currently clocked in</p>'}
                        <p><strong>Duration:</strong> ${duration}</p>
                    </div>
                `;
            } else if (props.type === 'absence') {
                // Calculate duration for absence
                const startDate = new Date(event.start);
                const endDate = new Date(event.end);
                const durationMinutes = Math.floor((endDate - startDate) / (1000 * 60));
                const duration = formatDuration(durationMinutes);

                content = `
                    <div class="p-4">
                        <h3 class="text-lg font-bold mb-2">${event.title}</h3>
                        <p><strong>Status:</strong> <span class="badge badge-${props.status === 'approved' ? 'success' : props.status === 'pending' ? 'warning' : 'error'}">${props.statusLabel}</span></p>
                        <p><strong>Start:</strong> ${new Date(event.start).toLocaleString(locale)}</p>
                        <p><strong>End:</strong> ${new Date(event.end).toLocaleString(locale)}</p>
                        <p><strong>Duration:</strong> ${duration}</p>
                        ${props.isBreak ? '<p class="text-info">🍽 Break</p>' : ''}
                        ${props.reason ? `<p class="mt-2"><strong>Reason:</strong> ${props.reason}</p>` : ''}
                    </div>
                `;
            }

            // Show modal with event details
            showEventModal(content);
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

function showEventModal(content) {
    // Create modal element
    const modal = document.createElement('dialog');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-box">
            ${content}
            <div class="modal-action">
                <button class="btn" onclick="this.closest('dialog').close()">Close</button>
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
