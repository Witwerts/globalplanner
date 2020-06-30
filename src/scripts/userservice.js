gpApp.factory("userService", ["$rootScope", "$http", "$window", "$localStorage", "dataService",
    function userService($rs, $http, $window, $ls, dc) {
        var func = {};

        func.isLoggedIn = isLoggedIn;
        func.getUser = getUser;
        func.getToken = getToken;
        func.login = login;
        func.logout = logout;
        func.register = register;
        
        return func;

        function isLoggedIn() {
            var uData = $ls.currentUser;
            
            if(uData != null){
                var currTime = Math.round(new Date() / 1000);
                
                if((uData.expires - currTime) <= 0) {
                    $rs.logout();
                    return false;
                }
                
                return true;
            }
            
            return false;
        }

        function getUser() {
            if($ls.currentUser != null)
                return $ls.currentUser;
                
            return null;
        }
        
        function getToken(){
            var uData = getUser();
            
            if(uData != null)
                return uData.token;
            
            return null;
        }

        function login(mail, pass, callback) {
            dc.postData('api/auth/login/', { "email": mail, "password": pass}, function(result){
                if(result.success){
                    $ls.currentUser = { mail: mail, token: result.data.jwt, expires: result.data.expires_at };
                }
                
                callback(result.success, result.data);
            }, false);
        }
        
        function logout(){
            delete $ls.currentUser;
        }
        
        function register(mail, pass){
            dc.getData('api/auth/user', function(){}, true);
        }
    }
])