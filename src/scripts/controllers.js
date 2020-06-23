var controllers = angular.module('controllers', []);

controllers.controller("agendaController",
    function Controller($scope, $http, $locale) {
        $http.get('data/appointments.json').then(function (response) {
            $scope.appointments = response.data;
        });
        
        $scope.remainingWeekDays = 7;
        $scope.daysInMonth = 31;
        $scope.showTypes = [{
            key: "day",
            title: "Dag",
            dateFormat: 'EEEE - d MMMM yyyy',
            addSeconds: function(time, days){
                var milliseconds = 24 * 60 * 60 * 1000;
                
                return time + (days * milliseconds);
            }
        }, {
            key: "week",
            title: "Week",
            dateFormat: 'Week w - MMMM yyyy',
            getDays: function(){
                return [];
            },
            addSeconds: function(time, weeks) {
                var milliseconds = 7 * 24 * 60 * 60 * 1000;

                return time + (weeks * milliseconds);
            }
        }, {
            key: "month",
            title: "Maand",
            dateFormat: 'LLLL yyyy',
            addSeconds: function(time, months){
                var newTime = time;
                var currDate = new Date(time);
                var daySeconds = 24 * 60 * 60;
                
                if(months < 0) {
                    var prevMonth = $scope.getPrevMonth(time);
                    
                    newTime -= $scope.getTotalDays(prevMonth) * daySeconds * 1000;
                }
                else {
                    var nextMonth = $scope.getNextMonth(time);
                    
                    newTime += $scope.getTotalDays(nextMonth) * daySeconds * 1000;
                }
                
                if(months < -1) {
                    return $scope.settings.showType.addSeconds(newTime, months + 1);
                }
                else if(months > 1){
                    return $scope.settings.showType.addSeconds(newTime, months - 1);
                }
                
                return newTime;
            }
        }];

        $scope.range = function(min, max, step) {
            step = step || 1;
            var input = [];
            for (var i = min; i <= max; i += step) {
                input.push(i);
            }
            return input;
        };
        
        $scope.getWeekDay = function(key){
            return $scope.weekDays[key % 7];
        }

        $scope.getShortDay = function(key){
            return $scope.shortWeekDays[key % 7];
        }
        
        $scope.getFirstDay = function(time){
            var currDate = new Date(time);
            
            return new Date(currDate.getFullYear(), currDate.getMonth(), 1, 0, 0, 0).getTime();
        }
        
        $scope.getWeek = function(time, startDay){
            var startDay = startDay | $scope.settings.startDay;
            var daySeconds = 24*60*60;
            
            var days = [];
            var currDate = new Date(time);
            var prevDays = (7 + (currDate.getDay() - (startDay%7)))%7;
            var startDate = new Date(time - (prevDays*daySeconds*1000));
            var startTime = $scope.getNextDate(time, startDate.getTime());
            
            for(var i = 0; i < 7; i++){
                var newDate = new Date(startTime + i*daySeconds*1000);
                
                days.push($scope.getTime(newDate.getTime(), false));
            }

            return days;
        }
        
        $scope.getTime = function(time, offset){
            var currDate = new Date(time);
            
            if(offset)
                return currDate.getTime() - (currDate.getTimezoneOffset() * 60 * 1000);
            else
                return currDate.getTime();
        }
        
        $scope.getTotalDays = function(time){
            var currDate = new Date(time);
            
            return new Date(currDate.getFullYear(), currDate.getMonth(), 0).getDate();
        }
        
        $scope.inCurrWeek = function(time){
            var currWeek = $scope.getWeek($scope.settings.currTime);
            var thisWeek = $scope.getWeek($scope.getToday());
            
            return currWeek.indexOf(time) >= 0 || thisWeek.indexOf(time) >= 0;
        }
        
        $scope.getPrevMonth = function(time, offset){
            var currDate = new Date(time);
            var offset = offset | false;
            
            var month = currDate.getMonth() <= 0 ? 12 : currDate.getMonth();
            var year = currDate.getMonth() <= 0 ? currDate.getFullYear() - 1 : currDate.getFullYear();
            
            var newDate = new Date(year, month, 1);
            
            if(offset)
                return $scope.getTime(newDate.getTime(), true);
            else
                return newDate.getTime();
        }
        
        $scope.getNextMonth = function(time, offset){
            var currDate = new Date(time);
            var offset = offset | false;
            
            var month = currDate.getMonth()+1 > 11 ? 0 : currDate.getMonth()+1;
            var year = currDate.getMonth()+1 > 11 ? currDate.getFullYear()+1 : currDate.getFullYear();

            var newDate = new Date(year, month, 1);
            
            if(offset)
                return $scope.getTime(newDate.getTime(), true);
            else
                return newDate.getTime();
        }
        
        $scope.getNextDate = function(oldTime, newTime){
            var oldDate = new Date(oldTime);
            var newDate = new Date(newTime);
            
            var oldOffset = oldDate.getTimezoneOffset();
            var newOffset = newDate.getTimezoneOffset();
            
            return newTime + ((newOffset - oldOffset) * 60 * 1000);
        }
        
        $scope.getToday = function(offset){
            var offset = offset | false;
            
            var currTime = new Date();
            var today = new Date(currTime.getUTCFullYear(), currTime.getUTCMonth(), currTime.getUTCDate(), 0,0,0);
            
            return $scope.getTime(today.getTime(), offset);
        }
        
        $scope.getRand = function(min, max){
            return Math.floor((Math.random()*max)+min);
        }

        $scope.settings = {
            showType: $scope.showTypes[2],
            currTime: $scope.getToday(false),
            startDay: 1
        }
        
        $scope.shortWeekDays = $locale.DATETIME_FORMATS.SHORTDAY;
        $scope.weekDays = $locale.DATETIME_FORMATS.DAY;
    }
);