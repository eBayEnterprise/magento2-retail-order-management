define([
    "jquery",
    "jquery/ui"
], function($) {
    $.widget('mage.suggestionControl', {
        options: {
            'controlSelector': '.suggestion-control',
            'showNewValue': 'new-address',
            'newAddressFieldsSelector': '.new-address'
        },
        _create: function () {
            this.controlElement = $(this.options.controlSelector);
            this.newAddressElement = $(this.options.newAddressFieldsSelector);
            this._bind();
            this._toggleForm();
        },
        _destroy: function () {
            this._unbind();
        },
        _bind: function () {
            this.controlElement.on('change', $.proxy(this._toggleForm, this));
        },
        _unbind: function () {
            this.controlElement.off('change');
        },
        _toggleForm: function () {
            this.newAddressElement.toggle(this.controlElement.filter(':checked').val() === this.options.showNewValue);
        }
    });
    return $.mage.suggestionControl;
});
