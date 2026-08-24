(() => {
  'use strict';

  /**
   * Initializes FullCalendar instances rendered by the extension without requiring jQuery.
   * Instance-specific configuration is provided by the matching Fluid template via data attributes.
   */
  const supportedViews = new Set(['dayGridMonth', 'timeGridWeek', 'timeGridDay']);

  /**
   * Returns validated category class names selected in the calendar's filter form.
   */
  const selectedCategories = (filterElement) => {
    if (!(filterElement instanceof HTMLElement)) {
      return [];
    }

    return Array.from(filterElement.querySelectorAll('input[type="checkbox"]:checked'))
      .map((checkbox) => checkbox.value)
      .filter((value) => /^category\d+$/.test(value));
  };

  /**
   * Loads the server-rendered event details and opens the configured Bootstrap modal.
   * Bootstrap 5 is preferred while the jQuery fallback keeps older integrations working.
   */
  const showModal = async (modalElement, url) => {
    if (!(modalElement instanceof HTMLElement) || typeof url !== 'string' || url === '') {
      return false;
    }

    const response = await fetch(url, {
      credentials: 'same-origin',
      headers: {'X-Requested-With': 'XMLHttpRequest'},
    });
    if (!response.ok) {
      throw new Error(`Detail request failed with status ${response.status}`);
    }

    const modalContent = modalElement.querySelector('.modal-content-inner');
    if (!(modalContent instanceof HTMLElement)) {
      return false;
    }

    // Dynamic content must already be escaped or sanitized by the server-side detail endpoint.
    const documentFragment = document.createRange().createContextualFragment(await response.text());
    modalContent.replaceChildren(documentFragment);

    if (window.bootstrap?.Modal) {
      window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
      return true;
    }
    if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
      window.jQuery(modalElement).modal('show');
      return true;
    }

    return false;
  };

  /**
   * Checks whether either the Bootstrap 5 or legacy jQuery modal API is available.
   */
  const supportsModal = () => Boolean(
    window.bootstrap?.Modal
    || (window.jQuery && typeof window.jQuery.fn.modal === 'function'),
  );

  /**
   * Creates one FullCalendar instance from the configuration stored on its container element.
   */
  const initializeCalendar = (calendarElement) => {
    if (!(calendarElement instanceof HTMLElement) || !window.FullCalendar?.Calendar) {
      return;
    }

    const eventsUrl = calendarElement.dataset.eventsUrl ?? '';
    const filterElement = document.getElementById(calendarElement.dataset.filterId ?? '');
    const modalElement = document.getElementById(calendarElement.dataset.modalId ?? '');
    const configuredView = calendarElement.dataset.initialView ?? '';
    const initialView = supportedViews.has(configuredView) ? configuredView : 'dayGridMonth';

    const calendarOptions = {
      firstDay: 1,
      initialView,
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay',
      },
      dayMaxEventRows: 4,
      navLinks: true,
      fixedWeekCount: false,
      // FullCalendar requests the visible date range whenever its view changes.
      events: async (info, successCallback, failureCallback) => {
        try {
          const body = new URLSearchParams({start: info.startStr, end: info.endStr});
          const response = await fetch(eventsUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
              'X-Requested-With': 'XMLHttpRequest',
            },
            body,
          });
          if (!response.ok) {
            throw new Error(`Event request failed with status ${response.status}`);
          }
          successCallback(await response.json());
        } catch (error) {
          failureCallback(error);
        }
      },
      // Apply event metadata and the currently selected category filters after rendering.
      eventDidMount: (info) => {
        const abstract = info.event.extendedProps.abstract;
        if (typeof abstract === 'string' && abstract !== '') {
          info.el.title = abstract;
        }

        const filters = selectedCategories(filterElement);
        if (filters.length > 0 && !filters.some((filter) => info.event.classNames.includes(filter))) {
          info.el.classList.add('d-none');
        }
      },
      // Load the event detail into the modal instead of following the event URL.
      eventClick: async (info) => {
        const detailUrl = info.event.extendedProps.uriAjax;
        if (typeof detailUrl !== 'string' || detailUrl === '' || !supportsModal()) {
          return;
        }

        info.jsEvent.preventDefault();
        try {
          await showModal(modalElement, detailUrl);
        } catch (error) {
          console.error(error);
        }
      },
    };

    // Only pass known locale formats to FullCalendar; an empty locale uses its default.
    const locale = calendarElement.dataset.locale ?? '';
    if (/^[a-z]{2,3}(?:-[a-z]{2})?$/.test(locale)) {
      calendarOptions.locale = locale;
    }

    const calendar = new window.FullCalendar.Calendar(calendarElement, calendarOptions);
    if (filterElement instanceof HTMLElement) {
      filterElement.addEventListener('change', () => calendar.refetchEvents());
    }
    calendar.render();
  };

  // A page may contain multiple plugin instances; each marked container is initialized once.
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-md-fullcalendar]').forEach(initializeCalendar);
  });
})();
