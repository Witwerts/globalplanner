controllers.controller("agendaController", ["$rootScope", "$http", "$locale", "$window", "userService", "plannerService", "dataService", "$location",
    function agendaController($rootScope, $http, $locale, $window, $uc, $ps, $ds, $location,) {
        $rootScope.remainingWeekDays = 7;
        $rootScope.daysInMonth = 31;
        $rootScope.showTypes = {
            "day": {
                title: "Dag",
                dateFormat: 'EEEE - d MMMM yyyy',
                dateFormatShort: 'EEE. d MMM yyyy',
                addSeconds: function (time, days) {
                    var milliseconds = 24 * 60 * 60 * 1000;

                    return time + (days * milliseconds);
                }
            }, "week": {
                title: "Week",
                dateFormat: 'Week w - MMMM yyyy',
                dateFormatShort: 'Wk w - MMM yyyy',
                addSeconds: function (time, weeks) {
                    var milliseconds = 7 * 24 * 60 * 60 * 1000;

                    return time + (weeks * milliseconds);
                }
            }, "month": {
                title: "Maand",
                dateFormat: 'LLLL yyyy',
                dateFormatShort: 'LLLL yyyy',
                addSeconds: function (time, months) {
                    var newTime = time;
                    var daySeconds = 24 * 60 * 60;

                    if (months < 0) {
                        var prevMonth = $rootScope.getPrevMonth(time);

                        newTime -= $rootScope.getTotalDays(prevMonth) * daySeconds * 1000;
                    } else {
                        var nextMonth = $rootScope.getNextMonth(time);

                        newTime += $rootScope.getTotalDays(nextMonth) * daySeconds * 1000;
                    }

                    if (months < -1) {
                        return $rootScope.settings.showType.addSeconds(newTime, months + 1);
                    } else if (months > 1) {
                        return $rootScope.settings.showType.addSeconds(newTime, months - 1);
                    }

                    return newTime;
                }
            }
        };

        $rootScope.getWeekDay = function (key) {
            return $rootScope.weekDays[key % 7];
        }

        $rootScope.getShortDay = function (key) {
            return $rootScope.shortWeekDays[key % 7];
        }

        $rootScope.getFirstDay = function (time) {
            var currDate = new Date(time);

            return new Date(currDate.getFullYear(), currDate.getMonth(), 1, 0, 0, 0).getTime();
        }

        $rootScope.getWeek = function (time, startDay) {
            var startDay = startDay | $rootScope.settings.startDay;
            var daySeconds = 24 * 60 * 60;

            var days = [];
            var currDate = new Date(time);
            var prevDays = (7 + (currDate.getDay() - (startDay % 7))) % 7;
            var startDate = new Date(time - (prevDays * daySeconds * 1000));
            var startTime = $rootScope.getNextDate(time, startDate.getTime());

            for (var i = 0; i < 7; i++) {
                var newDate = new Date(startTime + i * daySeconds * 1000);

                days.push($rootScope.getTime(newDate.getTime(), false));
            }

            return days;
        }

        $rootScope.getTime = function (time, offset) {
            var currDate = new Date(time);

            if (offset)
                return currDate.getTime() - (currDate.getTimezoneOffset() * 60 * 1000);
            else
                return currDate.getTime();
        }

        $rootScope.getTotalDays = function (time) {
            var currDate = new Date(time);

            return new Date(currDate.getFullYear(), currDate.getMonth(), 0).getDate();
        }

        $rootScope.inCurrWeek = function (time) {
            var currWeek = $rootScope.getWeek($rootScope.settings.currTime);
            var thisWeek = $rootScope.getWeek($rootScope.getToday());

            return currWeek.indexOf(time) >= 0 || thisWeek.indexOf(time) >= 0;
        }

        $rootScope.getPrevMonth = function (time, offset) {
            var currDate = new Date(time);
            var offset = offset | false;

            var month = currDate.getMonth() <= 0 ? 12 : currDate.getMonth();
            var year = currDate.getMonth() <= 0 ? currDate.getFullYear() - 1 : currDate.getFullYear();

            var newDate = new Date(year, month, 1);

            if (offset)
                return $rootScope.getTime(newDate.getTime(), true);
            else
                return newDate.getTime();
        }

        $rootScope.getNextMonth = function (time, offset) {
            var currDate = new Date(time);
            var offset = offset | false;

            var month = currDate.getMonth() + 1 > 11 ? 0 : currDate.getMonth() + 1;
            var year = currDate.getMonth() + 1 > 11 ? currDate.getFullYear() + 1 : currDate.getFullYear();

            var newDate = new Date(year, month, 1);

            if (offset)
                return $rootScope.getTime(newDate.getTime(), true);
            else
                return newDate.getTime();
        }

        $rootScope.getNextDate = function (oldTime, newTime) {
            var oldDate = new Date(oldTime);
            var newDate = new Date(newTime);

            var oldOffset = oldDate.getTimezoneOffset();
            var newOffset = newDate.getTimezoneOffset();

            return newTime + ((newOffset - oldOffset) * 60 * 1000);
        }

        $rootScope.getToday = function (offset) {
            var offset = offset | false;

            var currTime = new Date();
            var today = new Date(currTime.getUTCFullYear(), currTime.getUTCMonth(), currTime.getUTCDate(), 0, 0, 0);

            return $rootScope.getTime(today.getTime(), offset);
        }
        
        $rootScope.getRange = function(min, max, step) {
            step = step || 1;
            var input = [];

            for (var i = min; i <= max; i += step) {
                input.push(i);
            }

            return input;
        }

        $rootScope.getRand = function(min, max) {
            return Math.floor((Math.random() * max) + min);
        }

        $rootScope.getKeyByValue = function(obj, val) {
            return Object.keys(obj).find(key => obj[key] === val);
        }
        
        $rootScope.call = function(obj){
            console.log(obj);
        }

        $rootScope.settings = {
            showType: $rootScope.showTypes["week"],
            currTime: $rootScope.getToday(false),
            startDay: 1,
            startHour: 8,
            endHour: 17
        }

        $window.addEventListener("offline", function() {
            $rs.$apply(function() {
                $rs.isOnline = false;
            });
        }, false);

        $window.addEventListener("online", function() {
            $rs.$apply(function() {
                $rs.isOnline = true;
            });
        }, false);

        $rootScope.shortWeekDays = $locale.DATETIME_FORMATS.SHORTDAY;
        $rootScope.weekDays = $locale.DATETIME_FORMATS.DAY;

        $rootScope.makeAppointment = function() {
            var toSend = { "type_id": this.appointment.type, "start_time": new Date(this.appointment.date).getTime()/1000};
            $ds.postData('api/appointment', JSON.stringify(toSend), function(result){
                console.log("appointmentDate: " + new Date(this.appointmentDate.value).getTime());
                console.log("appointmentType: " + this.appointmentType.value);
                console.log(toSend);
                if(result.success){
                    $location.path("/");
                }
                else {

                }
                
                
            }, true);
        }
        $rootScope.getAppointmentTypes = function() {
            $ds.getData('api/appointment/type', {}, function(response) {
                if(response.success){
                    $rootScope.appointmentTypes = response.data;
                }else{
                    $rootScope.appointmentTypes = [];
                }
            }, true);
        }
        $rootScope.getAppointments = function() {
            $ds.getData('api/appointment', {}, function(response) {
                if(response.success){
                    console.log(response.data);
                    $rootScope.appointments = response.data;
                }
                else{

                    console.log(response.data);
                    $rootScope.appointments = [];
                }
            }, true);
        }
    }
])