controllers.controller("loginController", ["$rootScope", "$http", "$window", "$locale", "$location", "userService", "dataService",
    function loginController($rs, $http, $window, $locale, $location, us, ds) {
        $rs.user = {
            mail: "mobieljoy@gmail.com",
            pass: "testing123"
        }
        
        $rs.isOnline = navigator.onLine;

        $rs.isLoggedIn = function(){
            return us.isLoggedIn();
        }

        $rs.checkLoggedIn = function(redirect){
            var redirect = redirect | true;
            var check = $rs.isLoggedIn();
            
            if(!check && redirect)
                $location.path("/login");
            
            return check;
        }

        $rs.logout = function(){
            $location.path("/login");
            
            us.logout();
        }

        $rs.getUser = function() {
            return us.getUser();
        }

        $rs.login = function() {
            var uData = this.user;
            
            if(uData == null || uData.mail == null || uData.pass == null)
                return;

            us.login(uData.mail, uData.pass, function(success, result){
                if(success)
                    $location.path("/");
                else
                    $location.path("/login");
            });
        }

        $rs.register = function() {
            var uData = this.user;

            console.log(uData);

            if(uData == null || uData.mail == null || uData.pass == null)
                return;

            us.register(uData.mail, uData.pass);
        }
    }
])