var texts = angular.module('texts', []);

texts.controller('texts.list', ['$scope', '$http', '$location', '$routeParams',
    function ($scope, $http, $location, $routeParams) {
        $scope.texts = [];
        $scope.page = $routeParams.page;
        $scope.date = $routeParams.date;
        $scope.pages = [];
        $scope.dates = [];
        $scope.countNavPagesView = 9;
        $scope.countNavDatesView = 9;
        $scope.general = {
          stat  : [],
          graph : {}
        };


        /*$http.get('data/texts_bad.json').success(function(data) {
          $scope.texts = data;
        });*/


        $scope.order = true;


        $scope.showFullText = function (index) {
          jQuery('tr').removeClass('fabf-row-active');
          //console.log(jQuery('.short-text[index=' + index + ']').parent());
          jQuery('.short-text[index=' + index + ']').parent().addClass('fabf-row-active');


          jQuery('.short-text').show();
          jQuery('.full-text').hide();


          jQuery('html, body').animate({
            scrollTop:  jQuery('.short-text[index=' + index + ']').parent().offset().top - 85
          }, 500, function() {
              // Animation complete.
              jQuery('.short-text[index=' + index + ']').hide();
              jQuery('.full-text[index=' + index + ']').show();
          });

        }

        $scope.hideFullText = function (index) {
          jQuery('.short-text[index=' + index + ']').show();
          jQuery('.full-text[index=' + index + ']').hide();
        }

        $scope.getTextStat = function (text, index) {
            //console.log(text);
            jQuery('.ajax-loader[index=' + index + ']').show();
            $http.post('service/ajax.php', {
              ajaxAction : 'getTextStat',
              text       : text
            }).
            success(function(data, status, headers, config) {
              jQuery('.ajax-loader[index=' + index + ']').hide();
              //console.log(data);
              $scope.texts[index].stat = [];
              for (var s in data.stat.ru) {
                //console.log(s);
                $scope.texts[index].stat.push(data.stat.ru[s]);
              }
              console.log($scope.texts[index].stat);

            }).
            error(function(data, status, headers, config) {

            });
        }

        $scope.hideStat = function () {
            jQuery($scope.texts).each(function (index) {
                $scope.texts[index].stat = [];
            });
            jQuery('.statGeneral').hide();
            jQuery('.statGraph').hide();
            jQuery('.textsList').show();
        }

        $scope.getPeriodTextStat = function () {
            jQuery('.statGeneral').show();
            jQuery('.statGraph').hide();
            jQuery('.textsList').hide();
            jQuery('.general-ajax-loader').show();
            jQuery('.general-stat-graph-pie').html('');
            $scope.general.stat = [];
            var pieData = [
                ['Word', 'Count']
            ];
            $http.post('service/ajax.php', {
                ajaxAction : 'getPeriodTextStat',
                date       : $scope.date
            }).success(function(data, status, headers, config) {
                jQuery('.general-ajax-loader').hide();
                console.log(data);
                $scope.general.stat = [];
                for (var s in data.stat.ru) {
                    $scope.general.stat.push({
                        word    : s,
                        count   : data.stat.ru[s].count,
                        percent : data.stat.ru[s].percent
                    });
                    pieData.push([s, data.stat.ru[s].count]);
                }
                pieData = google.visualization.arrayToDataTable(pieData);
                var chart = new google.visualization.PieChart(jQuery('.general-stat-graph-pie')[0]);
                chart.draw(pieData, {
                    title: 'Статистика за период'
                });
            }).error(function(data, status, headers, config) {
            });

        }

        $scope.getPeriodTextGraph = function () {
            jQuery('.statGeneral').hide();
            jQuery('.statGraph').show();
            jQuery('.textsList').hide();
        }


        $scope.loadTexts = function () {
            console.info('Start load texts');
            $http.post('service/ajax.php', {
                ajaxAction : 'loadTexts',
                date       : $scope.date
            }).success(function(data, status, headers, config) {
                console.log(data);
                console.info('End load texts');
            }).error(function(data, status, headers, config) {

            });
        }

        $scope.getData = function (page, data) {
          $scope.page = page;
          $scope.date = data;
          $location.path('/texts/' + $scope.page + '/' + $scope.date, false);
          $scope.getDataPage();
        }

        $scope.getDataPage = function() {

            $http.post('service/ajax.php', {
                ajaxAction : 'getTexts',
                page       : $scope.page,
                date       : $scope.date
            }).
                success(function(data, status, headers, config) {
                    if (jQuery('.statGeneral').is(':visible')) {
                      $scope.getPeriodTextStat();
                    }

                    console.log(data);

                    // console.log(data.minDate);
                    // console.log(data.maxDate);
                    if (typeof(data.period) !== 'undefined') {
                      $('input[name="daterange"]').data('daterangepicker').setStartDate(moment(data.period.startDate));
                      $('input[name="daterange"]').data('daterangepicker').setEndDate(moment(data.period.endDate));
                    } else {
                      $('input[name="daterange"]').data('daterangepicker').setStartDate(moment(data.minDate));
                      $('input[name="daterange"]').data('daterangepicker').setEndDate(moment(data.maxDate));
                    }



                    $scope.texts = data.texts;

                    var countPage = Math.floor(data.count/data.pageSize);


                    var minPage = data.page - Math.floor($scope.countNavPagesView/2);
                    //minPage = (((data.page % $scope.countNavPagesView) == 0)?(data.page - Math.floor($scope.countNavPagesView/2)):0);
                    minPage = ((minPage > 0)?minPage:1);
                    var maxPage = minPage + $scope.countNavPagesView;
                    maxPage = ((maxPage < countPage)?maxPage:countPage);


                    $scope.pages = [];
                    if (minPage > 1) {
                        $scope.pages.push({
                            page:   1,
                            class:  'btn-primary'
                        });
                    }
                    for (var i = minPage; i <= maxPage; i++) {
                        $scope.pages.push({
                            page:   i,
                            class: (data.page == i)?'btn-success':'btn-default'
                        });
                    }
                    if (maxPage < countPage) {
                        $scope.pages.push({
                            page:   countPage,
                            class:  'btn-primary'
                        });
                    }


                    var minDateIndex = data.dates.indexOf(data.date) - Math.floor($scope.countNavDatesView/2);;
                    minDateIndex = (minDateIndex >= 0)?minDateIndex:0;
                    var maxDateIndex = minDateIndex + $scope.countNavDatesView;
                    maxDateIndex = ((maxDateIndex < data.dates.length)?maxDateIndex:(data.dates.length-1));
                    $scope.dates = [];
                    $scope.dates.push({
                        date:   '*',
                        class:  (data.date == '*')?'btn-success':'btn-primary'
                    });
                    for (var i = minDateIndex; i <= maxDateIndex; i++) {
                      $scope.dates.push({
                        date  :  data.dates[i],
                        class : (data.date == data.dates[i])?'btn-success':'btn-default'
                      });
                    }
                    if (maxDateIndex < data.dates.length-1) {
                        $scope.dates.push({
                            date  :  data.dates[data.dates.length - 1],
                            class :  'btn-primary'
                        });
                    }



                    /*
                    if (data.page == maxPage) {
                        for (var i = maxPage + 1; (i <= maxPage + Math.floor($scope.countNavPagesView/2)) && i < countPage ; i++) {
                            $scope.pages.push({
                                page:   i,
                                class: 'btn-default btn-hide',
                                hide: true
                            });
                        }
                        setTimeout(function () {
                            var serialShow = function (i, limit) {
                                jQuery('button[page=' + i + ']').fadeIn('fast', function () {
                                  i++;
                                  if (i <= limit) {
                                     serialShow(i, limit);
                                  }
                                });
                            }

                            var lastB = maxPage + Math.floor($scope.countNavPagesView/2);
                            lastB = (lastB < countPage)?lastB:countPage;
                            serialShow(maxPage + 1, lastB);

                            var serialHide = function (i, limit) {
                                jQuery('button[page=' + i + ']').fadeOut('fast', function () {
                                    i++;
                                    if (i <= limit) {
                                        serialHide(i, limit);
                                    }
                                });
                            }

                            var lastC = minPage + Math.floor($scope.countNavPagesView/2);
                            lastC = (lastC < countPage)?lastC:countPage;
                            serialHide(minPage, lastC);
                        }, 1000);
                    }
                    //*/

                    setTimeout(function () {
                        jQuery('.fabf-table-head th').each(function () {
                            //  console.log(jQuery(this).outerWidth());
                            jQuery(this).outerWidth(jQuery(this).outerWidth());
                        });
                        jQuery('.fabf-table-head').addClass('fabf-table-head-fixed');

                        /*jQuery('.fabf-table-head').css({
                            position : 'fixed'
                        });*/
                    }, 0);
                    //*/


                    //console.log($scope.pages);
                }).
                error(function(data, status, headers, config) {
                });
        };

        $scope.getDataPage();
    }]);

texts.filter('getDateTimeFromMySQL', function() {
    return function(input) {
        var t = input.split(/[- :]/);
        if (t.length >=6 ) {
          var d = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
        } else {
          var d = new Date(t[0], t[1]-1, t[2]);
        }
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
