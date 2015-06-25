var fabfApp = angular.module('fabfApp', [
    'ngRoute',
    'texts'
]);

fabfApp.config(['$routeProvider',
    function($routeProvider) {
        $routeProvider.
            when('/texts/:page/:date', {
                templateUrl    : 'partials/texts-list.html',
                controller     : 'texts.list',
                reloadOnSearch : false
            }).
          /*  when('/texts/:id', {
                templateUrl: 'partials/texts-detail.html',
                controller: 'TextsDetailCtrl'
            }).*/
            otherwise({
               // redirectTo: '/texts/1/' + moment().format('YYYY-MM-DD')
                redirectTo: '/texts/1/*'
            });
    }]);



// Добавляем возможность изменять путь без перезагрузки
fabfApp.run(['$route', '$rootScope', '$location', function ($route, $rootScope, $location) {
    var original = $location.path;
    $location.path = function (path, reload) {
        if (reload === false) {
            var lastRoute = $route.current;
            var un = $rootScope.$on('$locationChangeSuccess', function () {
                $route.current = lastRoute;
                un();
            });
        }
        return original.apply($location, [path]);
    };
}]);