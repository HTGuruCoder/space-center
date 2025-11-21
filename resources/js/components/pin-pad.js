export default (wireModel = 'pin', minLength = 4, maxLength = 6) => ({
    pin: '',
    isShaking: false,

    init() {
        this.pin = '';
    },

    addDigit(digit) {
        if (this.pin.length < maxLength) {
            this.pin += digit;
            this.updateWireModel();
        }
    },

    deleteDigit() {
        if (this.pin.length > 0) {
            this.pin = this.pin.slice(0, -1);
            this.updateWireModel();
        }
    },

    clear() {
        this.pin = '';
        this.updateWireModel();
    },

    updateWireModel() {
        this.$wire.set(wireModel, this.pin);
    },

    get displayPin() {
        return '•'.repeat(this.pin.length);
    },

    get isMinLength() {
        return this.pin.length >= minLength;
    },

    get isMaxLength() {
        return this.pin.length >= maxLength;
    },

    get isPinValid() {
        return this.pin.length >= minLength && this.pin.length <= maxLength;
    },

    shake() {
        this.isShaking = true;
        setTimeout(() => {
            this.isShaking = false;
        }, 500);
    },

    handleKeyPress(event) {
        const key = event.key;

        if (key >= '0' && key <= '9') {
            event.preventDefault();
            this.addDigit(key);
        } else if (key === 'Backspace') {
            event.preventDefault();
            this.deleteDigit();
        } else if (key === 'Enter' && this.isPinValid) {
            event.preventDefault();
            this.$wire.call('submit');
        }
    }
});
