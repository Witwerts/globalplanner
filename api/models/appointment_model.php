<?php

//Use JWT library
use \Firebase\JWT\JWT;

class Appointment_Model extends Endpoint{

    function __construct(){
        parent::__construct();
    }

    function getTimeBoundData(){
        $result = array();
        $result['time_from'] = (isset($this->data['time_from']) ? $this->data['time_from'] : 0);
        $result['time_to'] = (isset($this->data['time_to'])) ? $this->data['time_to'] : (time() + 31536000);
        return $result;
    }

    function getAvailableEmployeeId($time = null, $duration_min = null){
        if($time != null && $duration_min != null){
            $day_index = (date("w",$time) == 0) ? 6 : date("w",$time)-1;
            $start_time = date("H:i:s",$time);
            $end_time = date("H:i:s",($time + ($duration_min * 60)));
            $checkEmployeeQuery = $this->db->select("SELECT * FROM workhour WHERE day_index = :day_index AND start_time <= :start_time AND end_time >= :end_time",array(
                "day_index" => $day_index,
                "start_time" => $start_time,
                "end_time" => $end_time
            ));
            if(count($checkEmployeeQuery) > 0){
                $idList = array();
                foreach($checkEmployeeQuery as $key => $value){
                    if($this->appointmentIsPossible($value['user_id'],$time,$duration_min,true)){
                        array_push($idList,$value['user_id']);
                    }
                }
                if(count($idList) > 0){
                    return $idList[mt_rand(0, count($idList) - 1)];
                }
            }
        }
        return 0;
    }

    function queryAllResultOnRoll(){
        $query = array();
        $timeBound = $this->getTimeBoundData();
        $baseQuery = "SELECT * FROM appointment a JOIN appointment_type apt on a.aptype_id=apt.aptype_id WHERE (start_time > :ftime AND start_time < :ttime)";
        if($this->_checkIfUserIsRole("MODERATOR")){
            $query = $this->db->select($baseQuery,array(
                "ftime" => $timeBound['time_from'],
                "ttime" => $timeBound['time_to']
            ));
        }else if($this->_checkIfUserIsRole("EMPLOYEE")){
            if($this->_getSettingValue("employee_view_all_appointment") == true){
                $query = $this->db->select($baseQuery,array(
                    "ftime" => $timeBound['time_from'],
                    "ttime" => $timeBound['time_to']
                ));
            }else{
                $query = $this->db->select("$baseQuery AND employee_id=:emid",array(
                                                    "emid" => $this->jwtData->data->id,
                                                    "ftime" => $timeBound['time_from'],
                                                    "ttime" => $timeBound['time_to']
                                                ));
            }
        }else if($this->_checkIfUserIsRole("CUSTOMER")){
            $query = $this->db->select("$baseQuery AND user_id=:userid",array(
                                                    "userid" => $this->jwtData->data->id,
                                                    "ftime" => $timeBound['time_from'],
                                                    "ttime" => $timeBound['time_to']
                                                ));
        }else{
            $this->output['message'] = "Unauthorized to view these appointments";
            $this->responseCode = "401 Unauthorized";
        }
        return $query;
    }

    function formatAppointment($appointment){
        $appointment['end_time'] = $this->formatEndpointTime(($appointment['start_time'] + ($appointment['duration_min'] * 60)));
        $appointment['start_time'] = $this->formatEndpointTime($appointment["start_time"]);
        $result = array();
        foreach($appointment as $key => $value){
            $result[$key] = $value;
        }
        return $result;
    }

    function getAppointmentTypeById($id = null){
        if($id != null){
            $query = $this->db->select("SELECT * FROM appointment_type WHERE aptype_id=:aptid",array(
                "aptid" => $id
            ));
            if(count($query) == 1){
                return $query[0];
            }
        }else{
            $query = $this->db->select("SELECT * FROM appointment_type");
            if(count($query) > 0){
                return $query;
            }
        }
        return null;
    }

    function getUserAppointments($user_id = null, $is_employee = false){
        if($user_id != null){
            $columSearch = ($is_employee ? "employee_id" : "user_id");
            return $this->db->select("SELECT * FROM appointment a JOIN appointment_type ap ON a.aptype_id=ap.aptype_id WHERE a.$columSearch=:col",array(
                "col" => $user_id
            ));
        }
        return null;
    }

    function timeIsInWorkhours($user_id = null, $time = null){
        if($user_id != null && $time != null){
            $day_index = (date("w",$time) == 0) ? 6 : date("w",$time)-1;
            $queryWorkhours = $this->db->select("SELECT * FROM workhour WHERE user_id=:uid AND day_index=:dind",array(
                "uid" => $user_id,
                "dind" => $day_index
            ));
            if(count($queryWorkhours) == 1){ //Employee works this day
                $startTime = explode(":",$queryWorkhours[0]['start_time']);
                $endTime = explode(":",$queryWorkhours[0]['end_time']);
                $workStart = mktime($startTime[0],$startTime[1],$startTime[2],date("n",$time),date("j",$time),date("Y",$time));
                $workEnd = mktime($endTime[0],$endTime[1],$endTime[2],date("n",$time),date("j",$time),date("Y",$time));
                if($time >= $workStart && $time <= $workEnd){
                    return true;
                }
            }
        }
        return false;
    }

    function timeIsInAppointment($appointment_id = null, $time = null){
        if($appointment_id != null && $time != null){
            $queryAppointment = $this->db->select("SELECT * FROM appointment a JOIN appointment_type ap ON a.aptype_id=ap.aptype_id WHERE appointment_id=:aid",array(
                "aid" => $appointment_id
            ));
            if(count($queryAppointment) == 1){
                $app = $this->formatAppointment($queryAppointment[0]);
                if($time >= $app['start_time'] && $time < $app['end_time']){
                    return true;
                }
            }
        }
        return false;
    }

    function appointmentIsPossible($user_id = null, $start_time = null, $duration_min = null, $is_employee = false, $exclude_id = 0){
        if($user_id != null && $start_time != null && $duration_min != null){
            $user_app = $this->getUserAppointments($user_id,$is_employee);
            if($is_employee){
                //Check if appointment is within employees work times
                if(!$this->timeIsInWorkhours($user_id,$start_time) || !$this->timeIsInWorkhours($user_id,($start_time + ($duration_min*60)))){
                    return false;
                }
            }
            foreach($user_app as $key => $value){
                $app = $this->formatAppointment($value);
                if($this->timeIsInAppointment($app['appointment_id'],$app['start_time']) || $this->timeIsInAppointment($app['appointment_id'],$app['end_time'])){
                    if($app['appointment_id'] != $exclude_id){
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }

    function addAppointmentType(){
        $this->output['message'] = "Not authorized to add appointment types";
        $this->responseCode = "401 Unauthorized";
        if($this->jwtData != null){
            if($this->_checkIfUserIsRole("MODERATOR")){
                $type = $this->data;
                $this->output['message'] = "Please provide name,description,duration_min";
                $this->responseCode = "400 Bad Request";
                if(isset($type['name'],$type['description'],$type['duration_min'])){
                    $query = $this->db->insert("appointment_type",array(
                        "name" => $type['name'],
                        "description" => $type['description'],
                        "duration_min" => $type['duration_min']
                    ));
                    $this->output['message'] = $type['name'] . " added as a type";
                    $this->output['success'] = true;
                    $this->output['data'] = $type;
                    $this->responseCode = "201 Created";
                }
            }
        }
    }

    function getAppointmentType($id = null){
        $this->output['message'] = "Not authorized to view appointment types";
        $this->responseCode = "401 Unauthorized";
        if($this->jwtData != null){
            $this->output['message'] = "Appointment type not found";
            $this->responseCode = "404 Not found";
            $type = $this->getAppointmentTypeById($id);
            $this->responseCode = "200 OK";
            $this->output['success'] = true;
            $this->output['data'] = $type;
            if($id != null){
                $this->output['message'] = "Displaying appointment type $id";
            }else{
                $this->output['message'] = "Displaying " . count($type) . " appointment types";
            }
        }
    }

    //Updateable fields: employee_id,start_time,type_id
    //Format array('key' => 'value');
    function updateAppointment($id = null){
        $this->output['message'] = "Appointment id not specified";
        $this->responseCode = "400 Bad Request";
        if($id != null){
            $this->output['message'] = "No valid update data supplied";
            $this->responseCode = "400 Bad Request";
            if(is_array($this->data)){
                $app = $this->data;
                if(count($app) > 0){
                    foreach($app as $key => $value){
                        if($key != "employee_id" && $key != "start_time" && $key != "type_id"){
                            $this->output['message'] = "$key is not a valid updateable field, use employee_id, start_time or type_id";
                            $this->responseCode = "400 Bad Request";
                            return;
                        }
                    }
                    $selectQuery = $this->db->select("SELECT * FROM appointment WHERE appointment_id=:aid",array(
                        "aid" => $id
                    ));
                    $this->output['message'] = "Appointment $id not found";
                    $this->responseCode = "404 Not Found";
                    if(count($selectQuery) == 1){
                        $toUpdate = $selectQuery[0];
                        $updateCounter = 0;
                        if(isset($app['employee_id'])){
                            $updateCounter++;
                            if($app['employee_id'] != $toUpdate['employee_id']){
                                if(!$this->_checkIfUserIsRole("MODERATOR")){
                                    $this->output['message'] = "Only moderators are allowed to assign different employees to appointments";
                                    $this->responseCode = "401 Unauthorized";
                                    return;
                                }
                            }
                        }else{
                            $app['employee_id'] = $toUpdate['employee_id'];
                        }
                        if(isset($app['type_id'])){
                            $updateCounter++;
                            if($app['type_id'] != $toUpdate['aptype_id']){
                                if(isset($app['start_time'])){
                                    $type = $this->getAppointmentTypeById($app['type_id']);
                                    if(!$this->appointmentIsPossible($toUpdate['user_id'],$app['start_time'],$type['duration_min'],false,$id) ||
                                        !$this->appointmentIsPossible($app['employee_id'],$app['start_time'],$type['duration_min'],true,$id)){
                                        $this->responseCode = "409 Conflict";
                                        $this->output['message'] = "This appointment can't be updated, the new type makes it that the appointment gets in the way";
                                        return;
                                    }
                                }else{
                                    $type = $this->getAppointmentTypeById($app['type_id']);
                                    if(!$this->appointmentIsPossible($toUpdate['user_id'],$toUpdate['start_time'],$type['duration_min'],false,$id) ||
                                        !$this->appointmentIsPossible($app['employee_id'],$toUpdate['start_time'],$type['duration_min'],true,$id)){
                                        $this->responseCode = "409 Conflict";
                                        $this->output['message'] = "This appointment can't be updated, the new type makes it that the appointment gets in the way";
                                        return;
                                    }
                                }
                                if(!$this->_checkIfUserIsRole("MODERATOR") && $toUpdate['employee_id'] != $this->jwtData->data->id){
                                    $this->output['message'] = "Only a moderator or the original employee is allowed to change the type";
                                    $this->responseCode = "401 Unauthorized";
                                    return;
                                }
                            }
                        }else{
                            $app['type_id'] = $toUpdate['aptype_id'];
                        }
                        if(isset($app['start_time'])){
                            $updateCounter++;
                            if($app['start_time'] != $toUpdate['start_time']){
                                $type = $this->getAppointmentTypeById($app['type_id']);
                                if(!$this->appointmentIsPossible($toUpdate['user_id'],$app['start_time'],$type['duration_min'],false,$id) ||
                                    !$this->appointmentIsPossible($app['employee_id'],$app['start_time'],$type['duration_min'],true,$id)){
                                    $this->responseCode = "409 Conflict";
                                    $this->output['message'] = "This appointment can't be updated, either the employee is not working or there is an existing appointment at this time";
                                    return;
                                }
                            }
                        }else{
                            $app['start_time'] = $toUpdate['start_time'];
                        }
                        $updateQuery = $this->db->update("appointment",array(
                                "employee_id" => $app['employee_id'],
                                "start_time" => $app['start_time'],
                                "aptype_id" => $app['type_id'],
                                "appointment_id" => $id
                            ),"appointment_id=:appointment_id");
                        $this->output['message'] = "Appointment updated, $updateCounter field(s) affected";
                        $this->output['sucess'] = true;
                        $this->responseCode = "201 Created";
                    }
                }
            }
            
            
        }
    }

    //Only moderators, and people who are in the appointment can do this
    function cancelAppointment($id = null){
        $this->output['message'] = "Appointment id not specified";
        $this->responseCode = "400 Bad Request";
        if($id != null){
            $this->output['message'] = "No JWT supplied";
            $this->responseCode = "401 Unauthorized";
            if($this->jwtData != null){
                $selectQuery = $this->db->select("SELECT * FROM appointment WHERE appointment_id=:aid",array(
                    "aid" => $id
                ));
                $this->output['message'] = "Appointment $id not found";
                $this->responseCode = "404 Not Found";
                if(count($selectQuery) == 1){
                    $this->output['message'] = "Only moderators and people who are in the appointment can do this";
                    $this->responseCode = "401 Unauthorized";
                    $app = $selectQuery[0];
                    if($this->_checkIfUserIsRole("MODERATOR") || $app['user_id'] == $this->jwtData->data->id){
                        //TODO
                    }
                }
            }
        }
    }

    //Appointment needs type_id,user,employee,start_time
    function addAppointment(){
        $this->output['message'] = "Not authorized to create an appointment";
        $this->responseCode = "401 Unauthorized";
        if($this->jwtData != null){
            $app = $this->data;
            $this->responseCode = "400 Bad Request";
            $this->output['message'] = "Please include type_id,start_time";
            if(isset($app['type_id'],$app['start_time'])){
                $app['user_id'] = $this->jwtData->data->id;
                if($app['start_time'] < time()){
                    $this->responseCode = "400 Bad Request";
                    $this->output['message'] = "The given start_time is in the past";
                    return;
                }
                $this->responseCode = "409 Conflict";
                $this->output['message'] = "This appointment can't be created, either there is no employee available or there is an existing appointment at this time";
                $type = $this->getAppointmentTypeById($app['type_id']);
                $app['employee_id'] = $this->getAvailableEmployeeId($app['start_time'],$type['duration_min']);
                if($app['employee_id'] == 0){
                    $this->output['message'] = "There is no employee available at this time";
                    return;
                }
                if($this->appointmentIsPossible($app['user_id'],$app['start_time'],$type['duration_min']) && //Check if user available
                    $this->appointmentIsPossible($app['employee_id'],$app['start_time'],$type['duration_min'],true)){ //Check if employee available
                        $this->db->insert("appointment",array(
                            "user_id" => $app['user_id'],
                            "employee_id" => $app['employee_id'],
                            "start_time" => $app['start_time'],
                            "aptype_id" => $app['type_id']
                        ));
                        $this->responseCode = "201 Created";
                        $this->output['message'] = "Appointment created";
                        $this->output['success'] = true;
                        $this->output['data'] = $app;
                }
            }
        }
    }

    function getAppointment($id = null){
        if($id != null){

            if($this->jwtData == null){
                $this->output['message'] = "No valid JWT supplied";
                $this->responseCode = "400 Bad Request";
                return;
            }
            
            $this->output['message'] = "Appointment with id $id not found";
            $appointmentQuery = $this->db->select("SELECT * FROM appointment a 
                                                    JOIN appointment_type apt on a.aptype_id=apt.aptype_id 
                                                    WHERE appointment_id=:id" ,
            array(
                "id" => $id
            ));
            if(count($appointmentQuery) == 1){
                $appointment = $appointmentQuery[0];
                $this->output['message'] = "Showing appointment " . $appointment['appointment_id'];
                $this->output['success'] = true;
                $this->responseCode = "200 OK";
                if($this->_checkIfUserIsRole("MODERATOR") == true){ //Moderators can see all appointments
                    $this->output['data'] = $this->formatAppointment($appointment);
                }else if($this->_checkIfUserIsRole("EMPLOYEE") == true){ //Employees
                    if($this->getSettingValue("employee_view_all_appointment") == true){ //Check if employees are allowed to view all
                        $this->output['data'] = $this->formatAppointment($appointment);
                    }else{
                        if($this->jwtData->data->id == $appointment['employee_id']){ //Check if employee is mentioned in appointment
                            $this->output['data'] = $this->formatAppointment($appointment);
                        }else{
                            $this->output['message'] = "Unauthorized to view this appointment";
                            $this->output['success'] = false;
                            $this->responseCode = "401 Unauthorized";
                        }
                    }
                }else if($this->_checkIfUserIsRole("CUSTOMER") == true){
                    if($this->jwtData->data->id == $appointment['user_id']){ //Check if user is mentioned in appointment
                        $this->output['data'] = $this->formatAppointment($appointment);
                    }else{
                        $this->output['message'] = "Unauthorized to view this appointment";
                        $this->output['success'] = false;
                        $this->responseCode = "401 Unauthorized";
                    }
                }else{
                    $this->output['message'] = "Unauthorized to view this appointment";
                    $this->output['success'] = false;
                    $this->responseCode = "401 Unauthorized";
                }
            }
        }else{
            $this->output['message'] = "No appointments found";
            $query = $this->queryAllResultOnRoll();
            if(count($query) > 0){
                $timeBound = $this->getTimeBoundData();
                $this->output['message'] = "Showing " . count($query) . " appointments between " . $timeBound['time_from'] . " and " . $timeBound['time_to'];
                $this->output['success'] = true;
                foreach($query as $key => $value){
                    $query[$key] = $this->formatAppointment($value);
                }
                $this->output['data'] = $query;
            }
        }
    }
    
}