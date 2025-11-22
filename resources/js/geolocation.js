/**
 * Geolocation helper for employee time tracking.
 *
 * Captures GPS coordinates using JavaScript Geolocation API.
 * Provides high accuracy (5-10m) vs IP-based geolocation (too imprecise).
 */

export class GeolocationHelper {
    /**
     * Get current position with high accuracy.
     *
     * @returns {Promise<{latitude: number, longitude: number}>}
     * @throws {Error} If geolocation is not supported or permission denied
     */
    static getCurrentPosition() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Geolocation is not supported by your browser.'));
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    resolve({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                    });
                },
                (error) => {
                    let message = 'Unable to retrieve your location.';

                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            message = 'Location permission denied. Please enable location access in your browser settings.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            message = 'Location information is unavailable. Please try again.';
                            break;
                        case error.TIMEOUT:
                            message = 'Location request timed out. Please try again.';
                            break;
                    }

                    reject(new Error(message));
                },
                {
                    enableHighAccuracy: true, // Use GPS for high accuracy (5-10m)
                    timeout: 10000, // 10 second timeout
                    maximumAge: 0, // Always get fresh position, never use cache
                }
            );
        });
    }

    /**
     * Get user's timezone using Intl API.
     *
     * @returns {string} IANA timezone identifier (e.g., 'America/New_York')
     */
    static getUserTimezone() {
        return Intl.DateTimeFormat().resolvedOptions().timeZone;
    }

    /**
     * Convenience method for Livewire components.
     * Returns object ready for wire:model binding.
     *
     * @returns {Promise<{latitude: number, longitude: number, timezone: string}>}
     */
    static async getLocationData() {
        const position = await this.getCurrentPosition();
        return {
            ...position,
            timezone: this.getUserTimezone(),
        };
    }
}

// Make available globally for Livewire components
window.GeolocationHelper = GeolocationHelper;
