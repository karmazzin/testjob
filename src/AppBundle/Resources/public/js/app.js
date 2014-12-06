'use strict';

(function() {
    var app = angular.module('testjob', ['ui.bootstrap', 'ngResource'])
        .config(function($interpolateProvider, $httpProvider, $resourceProvider){
            $interpolateProvider.startSymbol('[[').endSymbol(']]');
            $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
            $httpProvider.defaults.headers.post['Content-Type'] = undefined;
            $httpProvider.defaults.transformRequest = function(data) {
                if (data === undefined)
                    return data;

                var fd = new FormData();
                angular.forEach(data, function(value, key) {
                    if (value instanceof FileList) {
                        if (value.length == 1) {
                            fd.append(key, value[0]);
                        } else {
                            angular.forEach(value, function(file, index) {
                                fd.append(key + '_' + index, file);
                            });
                        }
                    } else {
                        fd.append(key, value);
                    }
                });

                return fd;
            };


    });

    //https://github.com/angular/angular.js/issues/1375#issuecomment-21933012
    app.directive('filesModel', function filesModelDirective(){
        return {
            controller: function($parse, $element, $attrs, $scope){
                var exp = $parse($attrs.filesModel);

                $element.on('change', function(){
                    exp.assign($scope, this.files);
                    $scope.$apply();
                });
            }
        };
    })
})();