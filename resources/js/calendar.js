import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';
import frLocale from '@fullcalendar/core/locales/fr';

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
                    ? `${Math.floor(props.duration / 60)}h ${props.duration % 60}m`
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
                content = `
                    <div class="p-4">
                        <h3 class="text-lg font-bold mb-2">${event.title}</h3>
                        <p><strong>Status:</strong> <span class="badge badge-${props.status === 'approved' ? 'success' : props.status === 'pending' ? 'warning' : 'error'}">${props.statusLabel}</span></p>
                        <p><strong>Start:</strong> ${new Date(event.start).toLocaleString(locale)}</p>
                        <p><strong>End:</strong> ${new Date(event.end).toLocaleString(locale)}</p>
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
