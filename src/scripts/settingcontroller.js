controllers.controller("settingController",
    function settingController($scope, $http, $localStorage) {
        $http.get('data/appointments.json').then(function (response) {
            $scope.appointments = response.data;
        });
    }
);