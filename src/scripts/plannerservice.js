gpApp.factory("plannerService", ["$rootScope", "$localStorage", "dataService", "userService",
    function plannerService($rs, $ls, ds, us) {
        var func = {};
        var apps = {};
        var tApps = {};
        
        func.setAppointments = setAppointments;
        
        return func;
        
        function addAppointment(){
            
        }
        
        function setAppointments(appList){
            console.log(appList);
        }
    }
])