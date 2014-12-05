'use strict';

(function() {
    var app = angular.module('testjob', ['ui.bootstrap', 'ngResource'])
        .config(function($interpolateProvider, $httpProvider, $resourceProvider){
            $interpolateProvider.startSymbol('[[').endSymbol(']]');
            $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
        });
})();