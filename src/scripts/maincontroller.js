controllers.factory("mainController", ["$rootScope", "$http", "$locale", "dataService", "userService",
    function mainController($rootScope, $http, $locale, dc, uc){
        console.log($rootScope);
    
        var func = {}

        func.getUser = uc.getUser;
        
        return func;
    }
])