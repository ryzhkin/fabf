var fabfApp = angular.module('fabfApp', [
    'ngRoute',
    'texts'
]);

fabfApp.config(['$routeProvider',
    function($routeProvider) {
        $routeProvider.
            when('/texts', {
                templateUrl : 'partials/texts-list.html',
                controller  : 'texts.list'
            }).
          /*  when('/texts/:id', {
                templateUrl: 'partials/texts-detail.html',
                controller: 'TextsDetailCtrl'
            }).*/
            otherwise({
                redirectTo: '/texts'
            });
    }]);
