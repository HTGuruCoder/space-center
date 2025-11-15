import './bootstrap';

import './../../vendor/power-components/livewire-powergrid/dist/powergrid'

import flatpickr from "flatpickr";
import { Spanish } from "flatpickr/dist/l10n/es.js";

// Detect locale from URL path
const pathSegments = window.location.pathname.split('/').filter(Boolean);
const locale = pathSegments[0] || 'en'; // First segment is the locale (en, es, etc.)

// Configure Flatpickr locale based on URL
const flatpickrLocales = {
    es: Spanish,
    en: null // English is the default
};

// Date format configuration per locale
const dateFormats = {
    es: {
        dateFormat: 'Y-m-d',      // Format for storage (ISO format)
        altFormat: 'd/m/Y',       // Format for display (DD/MM/YYYY)
        altInput: true,           // Use alternate input for display
        allowInput: true          // Allow manual input
    },
    en: {
        dateFormat: 'Y-m-d',      // Format for storage (ISO format)
        altFormat: 'm/d/Y',       // Format for display (MM/DD/YYYY)
        altInput: true,           // Use alternate input for display
        allowInput: true          // Allow manual input
    }
};

// Set default locale for Flatpickr
if (flatpickrLocales[locale]) {
    flatpickr.localize(flatpickrLocales[locale]);
}

// Set default date format configuration
flatpickr.defaultConfig = {
    ...flatpickr.defaultConfig,
    ...dateFormats[locale]
};

// Make Flatpickr available globally
window.flatpickr = flatpickr;

import TomSelect from "tom-select";
window.TomSelect = TomSelect

import Cropper from 'cropperjs';
window.Cropper = Cropper;

import Chart from 'chart.js/auto';
window.Chart = Chart;
