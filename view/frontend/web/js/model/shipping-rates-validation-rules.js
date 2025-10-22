define([], function () {
    'use strict';

    return {
        getRules: function () {
            return {
                'country_id': {
                    'required': true
                },
                'region': {
                    'required': false
                },
                'region_id': {
                    'required': false
                },
                'postcode': {
                    'required': true
                }
            };
        }
    };
});
