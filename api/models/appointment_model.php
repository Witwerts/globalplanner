<?php

//Use JWT library
use \Firebase\JWT\JWT;

class Appointment_Model extends Endpoint{

    function __construct(){
        parent::__construct();
    }

    function queryAllResultOnRoll($include_others = false){
        $query = array();
        if($this->_checkIfUserIsRole("MODERATOR")){
            $query = $this->db->select("SELECT * FROM appointment a JOIN appointment_type apt on a.aptype_id=apt.aptype_id");
        }else if($this->_checkIfUserIsRole("EMPLOYEE")){
            if($this->_getSettingValue("employee_view_all_appointment") == true){
                if($include_others){
                    $query = $this->db->select("SELECT * FROM appointment a JOIN appointment_type apt on a.aptype_id=apt.aptype_id");
                }else{
                    $query = $this->db->select("SELECT * FROM appointment a JOIN appointment_type apt on a.aptype_id=apt.aptype_id
                                                WHERE employee_id=:emid",array(
                                                    "emid" => $this->jwtData->data->id
                                                ));
                }
            }else{
                $query = $this->db->select("SELECT * FROM appointment a JOIN appointment_type apt on a.aptype_id=apt.aptype_id
                                                WHERE employee_id=:emid",array(
                                                    "emid" => $this->jwtData->data->id
                                                ));
            }
        }else if($this->_checkIfUserIsRole("CUSTOMER")){
            $query = $this->db->select("SELECT * FROM appointment a JOIN appointment_type apt on a.aptype_id=apt.aptype_id
                                                WHERE user_id=:userid",array(
                                                    "userid" => $this->jwtData->data->id
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
            return $this->db->select("SELECT * FROM appointment WHERE $columSearch=:col",array(
                "col" => $user_id
            ));
        }
        return null;
    }

    function timeIsInWorkhours($user_id = null, $time = null){
        if($user_id != null && $time != null){
            $queryWorkhours = $this->db->select("SELECT * FROM workhour WHERE user_id=:uid AND day_index=:dind",array(
                "uid" => $user_id,
                "dind" => date("w",$time);
            ));
            if(count($uid) == 1){ //Employee works this day
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
            $queryAppointment = $this->db->select("SELECT * FROM appointment WHERE appointment_id=:aid",array(
                "aid" => $appointment_id
            ));
            if(count($queryAppointment) == 1){
                $app = $this->formatAppointment($queryAppointment[0]);
                if($time > $app['start_time'] && $time < $app['end_time']){
                    return true;
                }
            }
        }
        return false;
    }

    function appointmentIsPossible($user_id = null, $start_time = null, $duration_min = null, $is_employee = false){
        if($user_id != null && $start_time != null && $duration_min != null){
            $user_app = $this->getUserAppointments($user_id,$is_employee);
            if($is_employee){
                //Check if appointment is within employees work times
                if(!$this->timeIsInWorkhours($user_id,$startTime) || !$this->timeIsInWorkhours($user_id,($start_time + ($duration_min*60)))){
                    return false;
                }
            }
            foreach($user_app as $key => $value){
                $app = $this->formatAppointment($value);
                if($this->timeIsInAppointment($app['appointment_id'],$app['start_time']) || $this->timeIsInAppointment($app['appointment_id'],$app['end_time'])){
                    return false;
                }
            }
            return true;
        }
        return false;
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
    function updateAppointment(){

    }

    //Only moderators, and people who are in the appointment can do this
    function cancelAppointment(){

    }

    //Appointment needs type_id,user,employee,start_time
    function addAppointment(){
        $this->output['message'] = "Not authorized to create an appointment";
        $this->responseCode = "401 Unauthorized";
        if($this->jwtData != null){
            $app = $this->data;
            $this->responseCode = "400 Bad Request";
            $this->output['message'] = "Please include type,user_id,employee_id,start_time";
            if(isset($app['type_id'],$app['user_id'],$app['employee_id'],$app['start_time'])){
                $this->responseCode = "409 Conflict";
                $this->output['message'] = "This appointment can't be created, either the employee is not working or there is an existing appointment at this time";
                $type = $this->getAppointmentTypeById($app['type_id']);
                if($this->appointmentIsPossible($app['user_id'],$app['start_time'],$type['duration_min']) && //Check if user available
                    $this->appointmentIsPossible($app['employee_id'],$app['start_time'],$type['duration_min'],true)){ //Check if employee available
                        $this->db->insert("appointment",array(
                            "user_id" => $app['user_id'],
                            "employee_id" => $app['employee_id'],
                            "start_time" => $app['start_time'],
                            "aptype_id" => $app['type_id']
                        ));
                        $this->responseCode = "201 Created";
                        $this->output['Appointment created'];
                        $this->output['success'] = true;
                        $this->output['data'] = $app;
                }
            }
        }
    }

    function getAppointment($include_others = false, $id = null){
        if($id != null){
            $this->output['message'] = "Appointment with id $id not found";
            $appointmentQuery = $this->db->select("SELECT * FROM appointment a 
                                                    JOIN appointment_type apt on a.aptype_id=apt.aptype_id 
                                                    WHERE appointment_id=:id",
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
            $query = $this->queryAllResultOnRoll($include_others);
            if(count($query) > 0){
                $this->output['message'] = "Showing " . count($query) . " appointments";
                $this->output['success'] = true;
                foreach($query as $key => $value){
                    $query[$key] = $this->formatAppointment($value);
                }
                $this->output['data'] = $query;
            }
        }
    }
    
}