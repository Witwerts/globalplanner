var gpApp = angular.module("gpApp", [
    'ui.router',
    'controllers'
]);

gpApp.config(['$stateProvider', function($stateProvider, $urlRouterProvider){
    $stateProvider
        .state('root', {
            url: '',
            views: {
                'header@root': {
                    templateUrl: 'template/header.html'
                },
                '@': {
                    templateUrl: 'template/pages/agenda.html',
                    controller: 'agendaController'
                }, 
                'footer@root': {
                    templateUrl: 'template/footer.html'
                }
            }
        })
        .state('settings', {
                url: '/settings',
                views: {
                    'header@settings': {
                        templateUrl: 'template/header.html'
                    },
                    '@': {
                        templateUrl: 'template/pages/settings.html',
                        controller: 'agendaController'
                    },
                    'footer@settings': {
                        templateUrl: 'template/footer.html'
                    }
                }
            })
}])