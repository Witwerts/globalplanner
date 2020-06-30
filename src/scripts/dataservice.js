gpApp.factory("dataService", ["$rootScope", "$http", "$localStorage",
    function dataService($rootScope, $http, $ls) {
        var func = {};

        func.getData = getData;
        func.postData = postData;
        func.setHeader = setHeader;

        return func;

        function setHeader(){
            var uToken = $ls.currentUser.token;

            if(uToken == null)
                return false;

            $http.defaults.headers.common.Authorization = ("JWT " + uToken);
            
            return true;
        }
        
        function postData(url, data, callback, useHeaders) {
            if(useHeaders && !setHeader())
                return null;
            
            $http.post(url, data)
                .then(function successCallback(response) {
                    callback(response.data);
                }, function errorCallback(response) {
                    callback(response);
                });
        }

        function getData(url, callback, useHeaders) {
            if(useHeaders && !setHeader())
                return null;
            
            $http.get(url)
                .then(function successCallback(response) {
                    console.log(response.data);
                }, function errorCallback(response) {
                    console.log(response);
                });
        }
    }
])