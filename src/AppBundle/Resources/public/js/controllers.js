'use strict';

(function() {
    var app = angular.module('testjob');

    app.factory('Product', function($resource) {
        return $resource('/api/products/:id', {
                id: '@id'
        });
    });

    app.controller('ModalFormController', function($scope, $modalInstance, $http, product, Product) {

        $scope.product = product;

        $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
        };

        $scope.sendForm = function() {
            $scope.errors = [];

            Product.save(new Product($scope.product), function(r) {
                $modalInstance.close(r);
            }, function(e) {
                $scope.errors = e.data;
            });
        };
    });

    app.controller('DeleteDialogController', function($scope, $modalInstance, $http, product, Product) {

        $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
        };

        $scope.ok = function() {
            Product.delete(new Product(product), function(r) {
                $modalInstance.close(r);
            }, function(e) {
                $scope.errors = e.data;
            });
        };
    });



    app.controller('MainController', function ($scope, $http, $modal, $filter, Product) {

        $scope.products = Product.query();

        $scope.newForm = function() {
            var modalInstance = $modal.open({
                templateUrl: '/bundles/app/partial/modalForm.html',
                controller: 'ModalFormController',
                resolve: {
                    product: {}
                }
            });

            modalInstance.result.then(function (result) {
                $scope.products.push(result);
            });
        };

        $scope.editForm = function($index) {
            var modalInstance = $modal.open({
                templateUrl: '/bundles/app/partial/modalForm.html',
                controller: 'ModalFormController',
                resolve: {
                    product: function () {
                        return angular.copy($scope.products[$index]);
                    }
                }
            });

            modalInstance.result.then(function (result) {
                $scope.products.splice($index, 1);
                $scope.products[$index] = result;
            });
        };

        $scope.deleteDialog = function($index) {
            var modalInstance = $modal.open({
                templateUrl: '/bundles/app/partial/deleteDialog.html',
                controller: 'DeleteDialogController',
                resolve: {
                    product: function () {
                        return $scope.products[$index];
                    }
                }
            });

            modalInstance.result.then(function (result) {
                $scope.products.splice($index, 1);
            });
        };

    });
})();
