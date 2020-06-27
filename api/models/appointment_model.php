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