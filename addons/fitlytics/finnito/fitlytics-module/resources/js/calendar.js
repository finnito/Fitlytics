import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import iCalendarPlugin from '@fullcalendar/icalendar';

require('babel-core').transform('code', {
  plugins: ['dynamic-import-node']
});

document.addEventListener('DOMContentLoaded', function() {

    var calendarEl = document.getElementById('calendar');
    let calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, iCalendarPlugin],
        initialView: 'dayGridMonth',
        events: {
            url: 'localhost:8000/activities.ics',
            format: 'ics'
        }
    });
    calendar.render();

});