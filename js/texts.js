var texts = angular.module('texts', []);

texts.controller('texts.list', ['$scope', '$http',
    function ($scope, $http) {
        $scope.texts = [1, 2, 3, 4, 5, 6, 7];

/* $http.get('data/texts_bad.json').success(function(data) {
         $scope.texts = data;
         });
         */

        $scope.orderProp = 'age';
        console.log('+++');
    }]);

/*
texts.controller('TextsDetailCtrl', ['$scope', '$routeParams',
    function($scope, $routeParams) {
        $scope.id = $routeParams.id;
    }]);
*/



console.log('Success ...');
/*
fabfApp.controller('TextsListCtrl', ['$scope', '$http',
    function ($scope, $http) {
        $scope.texts = [1, 2, 3, 4, 5, 6, 7];
        $scope.orderProp = 'age';
    }]);*/
