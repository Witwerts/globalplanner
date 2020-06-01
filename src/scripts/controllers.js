var controllers = angular.module('controllers', []);

controllers.controller("agendaController",
    function Controller($scope, $http) {
        $http.get('data/appointments.json').then(function(response) {
            $scope.appointments = response.data;
        });
});