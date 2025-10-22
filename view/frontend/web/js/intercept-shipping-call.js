(function(open, send) {
    let lastRequest = null;

    XMLHttpRequest.prototype.open = function() {
        this._isEstimateCall = arguments[1] && arguments[1].includes('estimate-shipping-methods');
        open.apply(this, arguments);
    };

    XMLHttpRequest.prototype.send = function() {
        if (this._isEstimateCall) {
            // If a previous request is still pending, abort it
            if (lastRequest && lastRequest.readyState !== 4) {
                lastRequest.abort();
            }

            // Mark this request as the latest one
            lastRequest = this;

            // Add a delay before sending the request (optional)
            const args = arguments;
            // Only continue if this is still the most recent request
            if (lastRequest === this) {
                send.apply(this, args);
            };

            return;
        }

        send.apply(this, arguments);
    };
})(XMLHttpRequest.prototype.open, XMLHttpRequest.prototype.send);
