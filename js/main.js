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
            when('/stat/words/', {
               // templateUrl    : 'partials/texts-list.html',
               // controller     : 'texts.list',
                reloadOnSearch : false
            }).
            otherwise({
               // redirectTo: '/texts/1/' + moment().format('YYYY-MM-DD')
                redirectTo: '/texts/1/*'
            });
    }]);


fabfApp.controller('NavigatorController', ['$scope', '$location', function($scope, $location) {
    $scope.menu = [
        {
          title : 'Тексты',
          path  : '/texts',
          class : 'active'
        },
        {
          title : 'Статистика слов',
          path  :  '/stat/words/'
        }
    ];
    $scope.$on('$routeChangeSuccess', function() {
      for (var i = 0; i < $scope.menu.length; i++) {
         if ($location.path().indexOf($scope.menu[i].path) !== -1) {
           $scope.menu[i].class = 'active';
         } else {
           $scope.menu[i].class = '';
         }
      }
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


// For init standart jQuery components
$(function() {

    // http://www.daterangepicker.com/#options
    $('input[name="daterange"]').daterangepicker({
        opens  : 'right',
        locale : {
            applyLabel: 'Submit',
            cancelLabel: 'Cancel',
            fromLabel: 'From',
            toLabel: 'To',
            customRangeLabel: 'Custom',
            daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr','Sa'],
            monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            firstDay: 1
        }
    });
});