var gpApp = angular.module("gpApp", [
    'ui.router',
    'ngStorage',
    'controllers'
]);

var controllers = angular.module('controllers', []);

gpApp.config(['$stateProvider', "$urlRouterProvider", "$httpProvider", function($stateProvider, $urlRouterProvider){
    $urlRouterProvider.otherwise('/notfound');
    
    $stateProvider
        .state('root', {
            url: '/',
            views: {
                'header@root': {
                    templateUrl: 'template/header.html',
                    controller: 'loginController'
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
        .state('makeAppointment', {
            url: '/appointment/make',
            views: {
                'header@makeAppointment': {
                    templateUrl: 'template/header.html'
                },
                '@': {
                    templateUrl: 'template/pages/appointmentForm.html',
                    controller: 'agendaController'
                },
                'footer@makeAppointment': {
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
                        controller: 'settingController'
                    },
                    'footer@settings': {
                        templateUrl: 'template/footer.html'
                    }
                }
            })
        .state('login', {
            url: '/login',
            views: {
                'header@login': {
                    templateUrl: 'template/header.html'
                },
                '@': {
                    templateUrl: 'template/pages/login.html',
                    controller: 'loginController'
                },
                'footer@login': {
                    templateUrl: 'template/footer.html'
                }
            }
        })
        .state('register', {
            url: '/register',
            views: {
                'header@register': {
                    templateUrl: 'template/header.html'
                },
                '@': {
                    templateUrl: 'template/pages/register.html',
                    controller: 'loginController'
                },
                'footer@register': {
                    templateUrl: 'template/footer.html'
                }
            }
        })

        .state('notfound', {
            url: '/notfound', 
            templateUrl: 'template/pages/fallback.html'
        })
}])