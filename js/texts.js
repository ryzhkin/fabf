var texts = angular.module('texts', []);

texts.controller('texts.list', ['$scope', '$http',
    function ($scope, $http) {
        $scope.texts = [];
        $scope.page = 1;
        $scope.pages = [];
        $scope.countNavPagesView = 10;
        /*$http.get('data/texts_bad.json').success(function(data) {
          $scope.texts = data;
        });*/

        $http.post('service/ajax.php', {
            ajaxAction : 'getTexts',
            page       : $scope.page
        }).
            success(function(data, status, headers, config) {
                console.log(data);
                $scope.texts = data.texts;
                var countPage = Math.ceil(data.count/data.pageSize);
                var minPage = data.page - Math.ceil($scope.countNavPagesView/2);
                minPage = ((minPage > 0)?minPage:1);
                var maxPage = minPage + $scope.countNavPagesView;
                maxPage = ((maxPage < countPage)?maxPage:countPage);
                for (var i = minPage; i < maxPage; i++) {
                  $scope.pages.push({
                    page:   i,
                    active: (data.page == i)
                  });
                }
                console.log($scope.pages);
            }).
            error(function(data, status, headers, config) {
            });
        $scope.order = true;
    }]);

texts.filter('getDateTimeFromMySQL', function() {
    return function(input) {
        var t = input.split(/[- :]/);
        var d = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
        return d.getTime();
    };
});

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
