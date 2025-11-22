import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';

export function initializeCalendar(calendarEl, livewireComponent) {
    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        height: 'auto',
        editable: false,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        weekends: true,
        displayEventTime: false,
        displayEventEnd: false,
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
                        <p><strong>Start:</strong> ${new Date(event.start).toLocaleString()}</p>
                        ${event.end ? `<p><strong>End:</strong> ${new Date(event.end).toLocaleString()}</p>` : '<p class="text-warning">Currently clocked in</p>'}
                        <p><strong>Duration:</strong> ${duration}</p>
                    </div>
                `;
            } else if (props.type === 'absence') {
                content = `
                    <div class="p-4">
                        <h3 class="text-lg font-bold mb-2">${event.title}</h3>
                        <p><strong>Status:</strong> <span class="badge badge-${props.status === 'approved' ? 'success' : props.status === 'pending' ? 'warning' : 'error'}">${props.statusLabel}</span></p>
                        <p><strong>Start:</strong> ${new Date(event.start).toLocaleString()}</p>
                        <p><strong>End:</strong> ${new Date(event.end).toLocaleString()}</p>
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
