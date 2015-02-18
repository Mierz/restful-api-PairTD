<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Restful API
 * 
 * @author Medunitsa Vladimir (medunitsa@outlook.com)
 * @version 1.0
 */

require APPPATH.'/libraries/REST_Controller.php';

class Restapi extends REST_Controller
{
	
	/**
	 * User authorization 
	 */    
    function login_post()
    {
        $params = array(
            'email' => $this->post("email"),
            'password' => crypt($this->post("password"), "PairToDo")
            );

        if($result = $this->user_model->get('user', $params)) {

            if($token = $this->user_model->save('devices', array('userID' => $result->userID, "tokenID" => $this->post("tokenID")))) {
                $this->response(array("status" => "true", "msg" => "200" , "userData" => $result), 200);
            } else {
                $this->response(array("status" => "false", "msg" => "1", "userData" => array()), 200);   
            }               
        } else {
            $this->response(array("status" => "false", "msg" => "1", "userData" => array()), 200);   
        }
    }

    /**
     * User registration
     */    
    function join_post()
    {
        if($this->user_model->check('user', 'email', $this->post("email"))) {            
            $this->response(array("status" => "false", "msg" => "3"), 200);
            exit();
        }

        $replacements = array("password" => crypt($this->post("password"), "PairToDo"), "recovery" => $this->post("password"), "pairID" => "0", "pairName" => "0");
        $user = array_replace($this->post(), $replacements);
        unset( $user["tokenID"] );
        
        if($this->user_model->save('user', $user)) {
            $params = array(
                'email' => $this->post("email"),
            );
            if($result = $this->user_model->get('user', $params)) {
                $param = array("userID" => $result->userID, "tokenID" => $this->post("tokenID"));

                if($token = $this->user_model->save('devices', $param)) {
                    $this->response(array("status" => "true", "msg" => "200" , "userData" => $result), 200);
                } else {
                   $this->response(array("status" => "false", "msg" => "1", "userData" => array()), 200);   
                }
            } else {
                $this->response(array("status" => "false", "msg" => "4", "userData" => array()), 200);
            }
        } else {
            $this->response(array("status" => "false", "msg" => "4", "userData" => array()), 200);
        }
    }

    /**
     * Recovery password
     */
    function recovery_post()
    {
        if($this->user_model->check('user', 'email', $this->post("email"))) {   

            $result = $this->user_model->get('user', array('email' => $this->post("email")));

            $name = $result->name;
            $userID = $result->userID;
            $email = $result->email;
            $password = $result->recovery;
            $message = '<table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-family:Verdana;"><tr><td align="center"><table width="800" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;background:#315568;"><tr style="height:150px;text-align:center;"><td><img src="http://api.pairtodo.com/imgs/logo.png" /></td></tr><tr><td colspan="3" align="center" ><table width="600" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;background:#ffffff;border-radius:6px;min-height:320px;"><tr style="text-align:center;" valign="top"><td><h1 style="color:315568;font-weight:normal;font-size:23px;margin-top:40px;margin-bottom:40px;">'.$name.', Вы забыли пароль?</h1><p style="color:315568;margin-bottom:50px;">ПараДел - зафиксировал запрос на восстановление доступа.<br/>По вашей просьбе был выслан пароль.</p><p><span style="color:315568;font-weight:bold;font-size:18px;">Ваш пароль:</span> '.$password.'</p><p style="color:#888888;font-size:12px;margin-top:50px;">Это приглашение было отправлено от имени '.$name.' (<a href="mailto: '.$email.'" style="color:#888888;">'.$email.'</a>)</p><p style="color:#888888;font-size:12px;">Если у вас есть какие-либо проблемы, предложения или пожелания, пожалуйста, напишите <a href="mailto: support@pairtodo.com" style="color:#888888;">support@pairtodo.com</a></p><p style="color:#888888;font-size:12px;padding-bottom:40px;">Вы можете <a href="http://api.pairtodo.com/user/unsubscribe/user/'.$userID.'/email/'.$email.'" style="color:#888888;">отказаться</a> от этих писем в любое время.</p></td></tr></table></td></tr><tr style="height:80px;"><td colspan="3" align="center"><table width="540" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;"><tr><td style="text-align:center;color:#9dadb5;font-size:13px;">&copy; 2015 <a href="http://pairtodo.com/" style="color:#9dadb5;" target="_blank">ПараДел</a></td></tr></table></td></tr></table></td></tr></table>';

            if($this->send_mail('support@pairtodo.com', 'ПараДел', $this->post('email'), $message, 'Восстановление доступа')) {
                $this->response(array("status" => "true", "msg" => "200"), 200); 
            }   else {
                $this->response(array("status" => "false", "msg" => "8"), 200);
            }

        } else {
            $this->response(array("status" => "false", "msg" => "6"), 200);     
        }
    }

    /**
     * Search user in database
     */
    function get_user_mail_get()
    {    	
        if($this->user_model->check('user', 'email', $this->get("email"))) {   

            $result = $this->user_model->get('user', array('email' => $this->get("email")));

            if($result->pairID != 0) {
            	$this->response(array("status" => "false", "msg" => "6", "userData" => array()), 200);
            	exit();
            }
            
            $this->response(array("status" => "true", "msg" => "200", "userData" => $result), 200);                            
        } else {
            $name = $this->get("name");
            $email = $this->get("userEmail");
            $userID = $this->get("userID");
            $donwload_link = "#";

        	$message = '<table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-family:Verdana;"><tr><td align="center"><table width="800" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;background:#315568;"><tr style="height:150px;text-align:center;"><td><img src="http://api.pairtodo.com/imgs/logo.png" /></td></tr><tr><td colspan="3" align="center" ><table width="600" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;background:#ffffff;border-radius:6px;min-height:480px;"><tr style="text-align:center;" valign="top"><td><h1 style="color:315568;font-weight:normal;font-size:23px;margin-top:40px;margin-bottom:40px;">'.$name.', ждет тебя с нетерпением в<br/>приложении ПараДел :)</h1><p style="color:315568;">ПараДел - это список дел для двоих на iPhone. Вы сможете давать друг другу поручения, назначать сроки их выполнения, отсылать задания в sms, хвалить партнера &#171;лайками&#187;.</p><p><span style="color:315568;font-weight:bold;font-size:18px;">Пройди три простых шага и наслаждайся!</span></p><ul style="list-style:none;margin:0;padding:0;margin-top:45px;"><li style="float:left;margin-left:40px;"><div style="background:#e62b4b;color:#fff;width:100px;height:100px;border-radius:100px;line-height:100px;font-size:65px;font-weight:normal;margin:0 auto;margin-bottom:15px;">1</div><span style="font-size:13px;color:#313e46;">Скачай ПараДел в<br/><a style="color:#313e46;" href="'.$donwload_link.'">Appstore</a></span></li><li style="float:left;margin-left:70px;"><div style="background:#315568;color:#fff;width:100px;height:100px;border-radius:100px;line-height:100px;font-size:65px;font-weight:normal;margin:0 auto;margin-bottom:15px;">2</div><span style="font-size:13px;color:#313e46;">Зарегистрируйся<br/>в ПараДел</span></li><li style="float:left;margin-left:70px;"><div style="background:#31b752;color:#fff;width:100px;height:100px;border-radius:100px;line-height:100px;font-size:65px;font-weight:normal;margin:0 auto;margin-bottom:15px;">3</div><span style="font-size:13px;color:#313e46;">Прими приглашение<br/>партнера</span></li></ul><p style="clear:both;padding-top:55px;padding-bottom:55px;"><a href="'.$donwload_link.'" target="_blank" style="display:inline-block;width:189px;height:54px;background:url(http://api.pairtodo.com/imgs/download.png)"></a></p><p style="color:#888888;font-size:12px;">Это приглашение было отправлено от имени '.$name.' (<a href="mailto: '.$email.'" style="color:#888888;">'.$email.'</a>)</p><p style="color:#888888;font-size:12px;">Если у вас есть какие-либо проблемы, предложения или пожелания, пожалуйста, напишите <a href="mailto: support@pairtodo.com" style="color:#888888;">support@pairtodo.com</a></p><p style="color:#888888;font-size:12px;padding-bottom:40px;">Вы можете <a href="http://api.pairtodo.com/user/unsubscribe/user/'.$userID.'/email/'.$email.'" style="color:#888888;">отказаться</a> от этих писем в любое время.</p></td></tr></table></td></tr><tr style="height:80px;"><td colspan="3" align="center"><table width="540" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;"><tr><td style="text-align:center;color:#9dadb5;font-size:13px;">&copy; 2015 <a href="http://pairtodo.com/" style="color:#9dadb5;" target="_blank">ПараДел</a></td></tr></table></td></tr></table></td></tr></table>';

            $this->send_mail('support@pairtodo.com', 'ПараДел', $this->get("email"), $message, 'Ваша половинка пригласила вас в ПараДел');

            $this->response(array("status" => "false", "msg" => "6", "userData" => array()), 200);
        }
    }

    /**
     * Send message in support
     */
    function contact_post()
    {		
        if($this->post("message")) {
            if($this->send_mail($this->post('email'), $this->post('name'), 'support@pairtodo.com', $this->post('message'), 'Письмо из приложения')) {
                $this->response(array("status" => "true", "msg" => "200"), 200); 
            }   else {
                $this->response(array("status" => "false", "msg" => "8"), 200);
            }
        }
    }

    /**
     * Add request in database
     */
    function pair_post()
    {        
        $data = array(
            'userID' => $this->post('userID'),           
            'pairID' => $this->post('pairID'),
            'pairName' => $this->post('pairName'), 
            'status' => 0,           
        );
            
        if($this->user_model->save('request', $data)) {
            $this->response(array("status" => "true", "msg" => "200"), 200); 
        } else {
            $this->response(array("status" => "false", "msg" => "5"), 200);
        }
    }

    /**
     * Check request to pair
     */
    function check_request_post()
    {        
        if($sender = $this->check_sender($this->post("userID"))) {
        	$answer = $this->user_model->get('request', array('userID' => $this->post("userID")));

        	$user = $this->user_model->get('user', array('userID' => $answer->pairID));
        	$userData = array(
        		'name' => $user->name, 
        		'email' => $user->email,
        		'sex' => $user->sex
        	);

        	if($answer->status == "Y" || $answer->status == "N") {
        		$params = array(
        			"userID" => $this->post("userID"),
        		);

        		$this->user_model->delete("request", $params);
        	}

        	$this->response(array("status" => "true", "action" => "answer", "answer" => $answer->status, "data" => $userData, "msg" => "200"), 200); 
        	exit();
        }

        if($recipient = $this->check_recipient($this->post("userID"))) {
        	$request = $this->user_model->get('request', array('pairID' => $this->post("userID")));
        	$user = $this->user_model->get('user', array('userID' => $request->userID));

        	$senderData = array(
        		"name" => $user->name,
        		"email" => $user->email,
        		"sex" => $user->sex,
        	);

        	$this->response(array("status" => "true", "action" => "request", "answer" => "0", "data" => $senderData,  "msg" => "200"), 200); 
        	exit();
        }

        $this->response(array("status" => "false", "msg" => "200"), 200);
    }

    /**
     * Check sender
     */
    private function check_sender($userID, $answer = null) {
    	if($this->user_model->check('request', 'userID', $userID)) {    		
    		return true;
    	} else {
    		return false;
    	}
    }

    /**
     * Check recipient
     */
    private function check_recipient($userID, $request = null) {
    	if($this->user_model->check('request', 'pairID', $userID)) {    		    		
    		return true;
    	} else {
    		return false;
    	}
    }

    /**
     * Accept request
     */
    function accept_request_post() {
        if($this->post('status') == 'Y') {
            $requestData = $this->user_model->get('request', array('pairID' => $this->post("userID")));

            $params = array(
                "userID" => $this->post("userID"),
            );

            $data = array(
                "pairID" => $requestData->pairID,
                "pairName" => $requestData->pairName,
            );
            if($this->user_model->update('user', $params, $data)) {


                $params = array(
                    "userID" => $requestData->pairID,
                );

                if($this->input->post("sex") == "male") {
                    $sex = "Мой парень";
                } else {
                    $sex = "Моя девушка";
                }

                $data = array(
                    "pairID" => $this->post("userID"),
                    "pairName" => $sex,
                );

                if($userParams->setPush == 1) {
                    $token = $this->user_model->get('devices', array('userID' => $requestData->pairID));

                    $this->send_push($token->tokenID, 'Вас добавила ваша вторая половинка');
                }

                $this->user_model->update('user', $params, $data);

            }
           
        }  

        $data = array(
            'status' => $this->post('status'),
        );

        $params = array(
            "pairID" => $this->post("userID"),
        );

        if($this->user_model->update('request', $params, $data)) {
            $this->response(array("status" => "true", "msg" => "200"), 200);
        } else {
            $this->response(array("status" => "false", "msg" => "5"), 200);
        }

    }

    /**
     * Delete pair
     */
    function delete_pair_post()
    {
        $result = $this->user_model->get('user', array('userID' => $this->post("userID")));
        
        $params = array(
            "userID" => $this->post("userID"),
            );

        $data = array(
            "pairID" => "0",
            "pairName" => "0",
            );

        if($this->user_model->update('user', $params, $data)) {
            $params = array(
                "userID" => $result->pairID,
                );

            $data = array(
                "pairID" => "0",
                "pairName" => "0",
                );

            $this->user_model->update('user', $params, $data);

            $this->response(array("status" => "true", "msg" => "200"), 200);   
        } else {
            $this->response(array("status" => "false", "msg" => "7"), 200);
        }
    }
    
    /**
     * Update user email
     */
    function update_email_post()
    {
        $params = array(
            "userID" => $this->post("userID"),
            );

        $data = array(
            "email" => $this->post("email"),
            );

        if($this->user_model->update('user', $params, $data)) {
            
            $this->response(array("status" => "true", "msg" => "200"), 200);   
        } else {
            $this->response(array("status" => "false", "msg" => "9"), 200);
        }
    }

    /**
     * Update user password
     */
    function update_password_post()
    {
        $params = array(
            "userID" => $this->post("userID"),
            );

        $data = array(
            "recovery" => $this->post("password"),
            "password" => crypt($this->post("password"), "PairToDo"),
            );

        if($this->user_model->update('user', $params, $data)) {
            
            $this->response(array("status" => "true", "msg" => "200"), 200);   
        } else {
            $this->response(array("status" => "false", "msg" => "10"), 200);
        }
    }

    /**
     * Update user photo
     */
    function update_photo_post()
    {
        $params = array(
            "userID" => $this->post("userID"),
            );

        $data = array(
            "photo" => $this->post("photo"),
            );

        if($this->user_model->update('user', $params, $data)) {
            
            $this->response(array("status" => "true", "msg" => "200"), 200);   
        } else {
            $this->response(array("status" => "false", "msg" => "11"), 200);
        }
    }

    /**
     * Update pair name
     */
    function update_pair_post()
    {
        $params = array(
            "userID" => $this->post("userID"),
            );

        $data = array(
            "pairName" => $this->post("pairName"),
            );

        if($this->user_model->update('user', $params, $data)) {
            
            $this->response(array("status" => "true", "msg" => "200"), 200);   
        } else {
            $this->response(array("status" => "false", "msg" => "12"), 200);
        }
    }
	
    /**
     * Update user name
     */
    function update_name_post()
    {
        $params = array(
            "userID" => $this->post("userID"),
            );

        $data = array(
            "name" => $this->post("name"),
            );

        if($this->user_model->update('user', $params, $data)) {
            
            $this->response(array("status" => "true", "msg" => "200"), 200);   
        } else {
            $this->response(array("status" => "false", "msg" => "16"), 200);
        }
    }
	
    /**
     * Update user sex
     */
    function update_sex_post()
    {
        $params = array(
            "userID" => $this->post("userID"),
            );

        $data = array(
            "sex" => $this->post("sex"),
            );

        if($this->user_model->update('user', $params, $data)) {            
            $this->response(array("status" => "true", "msg" => "200"), 200);   
        } else {
            $this->response(array("status" => "false", "msg" => "17"), 200);
        }
    }

    /**
     * Update date made in task
     */
    function update_date_post()
    {
        $params = array(
            "taskID" => $this->post("taskID"),
            );

        $data = array(
            "dateMade" => $this->post("dateMade"),
            "status" => $this->post("status"),
            );

        if($this->user_model->update('task', $params, $data)) {     

            $user = $this->user_model->get('task', array('taskID' => $this->post("taskID")));            
            $params = $this->user_model->get('user', array('userID' => $user->userID));            
            if($params->setPush == 1) {
                $token = $this->user_model->get('devices', array('userID' => $user->userID));
                $this->send_push($token->tokenID, 'Дело сделано!');
            }

            $this->response(array("status" => "true", "msg" => "200"), 200);   
        } else {
            $this->response(array("status" => "false", "msg" => "18"), 200);
        }
    }

    /**
     * Update email notification
     */
    function update_send_email_post()
    {
        $params = array(
            "userID" => $this->post("userID"),
            );

        $data = array(
            "setEmail" => $this->post("email")
            );

        if($this->user_model->update('user', $params, $data)) {            
            $this->response(array("status" => "true", "msg" => "200"), 200);   
        } else {
            $this->response(array("status" => "false", "msg" => "19"), 200);
        }
    }

    /**
     * Update push notification
     */
    function update_send_push_post()
    {
        $params = array(
            "userID" => $this->post("userID"),
            );

        $data = array(
            "setPush" => $this->post("push")
            );

        if($this->user_model->update('user', $params, $data)) {            
            $this->response(array("status" => "true", "msg" => "200"), 200);   
        } else {
            $this->response(array("status" => "false", "msg" => "20"), 200);
        }
    }

    /**
     * Update list made in task
     */
    public function update_listmade_post()
    {
        $params = array(
            "taskID" => $this->post("taskID"),
            );

        $data = array(
            "listMade" => $this->post("listMade")
            );

        if($this->user_model->update('task', $params, $data)) {            
            $this->response(array("status" => "true", "msg" => "200"), 200);   
        } else {
            $this->response(array("status" => "false", "msg" => "20"), 200);
        }
    }

    /**
     * Add task
     */
    function add_task_post($task_content = null)
    {
        if($this->post('list') != '0') {
            $symbols = array("[", "]", "\"");
            $str = str_replace($symbols, "", $this->post('list'));
            $array = explode(",", $str);                
            $list = count($array);

            $listMade = '[';
            for($i = 0; $i < $list; $i++) {            
                if($i != 0) {
                    $listMade .= ',';                
                }
                $listMade.= '"0"';
            }
            $listMade .= ']';

            $task_content = $this->explode_task_list($this->post('list'));

        } else {
            $listMade = "0";

            $task_content = $this->post('description');
        }
  
        $replacements = array("listMade" => $listMade);
        $task = array_replace($this->post(), $replacements);

        $params = $this->user_model->get('user', array('userID' => $this->post("userID")));
        if($params->setPush == 1) {
            $token = $this->user_model->get('devices', array('userID' => $this->post("userID")));
            $this->send_push($token->tokenID, 'У вас новая задача!');
        }
        if($params->setEmail == 1) {     
            $name = $params->name;
            $email = $params->email;       
            $date = $this->date_to_mail($this->post("date"));
            $userID = $params->userID;

            $message = '<table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-family:Verdana;"><tr><td align="center"><table width="800" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;background:#315568;"><tr style="height:150px;text-align:center;"><td><img src="http://api.pairtodo.com/imgs/logo.png" /></td></tr><tr><td colspan="3" align="center" ><table width="600" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;background:#ffffff;border-radius:6px;min-height:480px;"><tr style="text-align:center;" valign="top"><td><h1 style="color:315568;font-weight:normal;font-size:23px;margin-top:40px;margin-bottom:40px;">'.$name.', у тебя новое поручение от твоей<br/>второй половинки, проверь в приложении:</h1><div style="height:140px;"><p style="color:315568;">'.$task_content.'</p><p><span style="color:315568;font-weight:bold;">Срок:</span> '.$date.'</p></div><p><a href="https://www.facebook.com/pairtodo" target="_blank" style="display:inline-block;width:57px;height:58px;background:url(http://api.pairtodo.com/imgs/fb.png)"></a><a href="https://twitter.com/PairTodo" target="_blank" style="display:inline-block;width:57px;height:58px;background:url(http://api.pairtodo.com/imgs/tw.png)"></a><a href="https://vk.com/pairtodo" target="_blank" style="display:inline-block;width:57px;height:58px;background:url(http://api.pairtodo.com/imgs/vk.png)"></a></p><p style="color:#888888;font-size:12px;">Это приглашение было отправлено от имени '.$name.' (<a href="mailto: '.$email.'" style="color:#888888;">'.$email.'</a>)</p><p style="color:#888888;font-size:12px;">Если у вас есть какие-либо проблемы, предложения или пожелания, пожалуйста, напишите <a href="mailto: support@pairtodo.com" style="color:#888888;">support@pairtodo.com</a></p><p style="color:#888888;font-size:12px;">Вы можете <a href="http://api.pairtodo.com/user/unsubscribe/user/'.$userID.'/email/'.$email.'" style="color:#888888;">отказаться</a> от этих писем в любое время.</p></td></tr></table></td></tr><tr style="height:80px;"><td colspan="3" align="center"><table width="540" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;"><tr><td style="text-align:center;color:#9dadb5;font-size:13px;">&copy; 2015 <a href="http://pairtodo.com/" style="color:#9dadb5;" target="_blank">ПараДел</a></td></tr></table></td></tr></table></td></tr></table>';
            $this->send_mail('support@pairtodo.com', 'ПараДел', $params->email, $message, 'У вас новая задача');
        }

        if($this->user_model->save('task', $task)) {
            $this->response(array("status" => "true", "msg" => "200"), 200);
        } else {
            $this->response(array("status" => "false", "msg" => "13"), 200);
        }
    }


    /**
     * Edit task
     */
    function edit_task_post()
    {
        $params = array(
            "taskID" => $this->post("taskID"),
            );

        if($this->post('list') != '0') {
            $symbols = array("[", "]", "\"");
            $str = str_replace($symbols, "", $this->post('list'));
            $array = explode(",", $str);                
            $list = count($array);

            $listMade = '[';
            for($i = 0; $i < $list; $i++) {            
                if($i != 0) {
                    $listMade .= ',';                
                }
                $listMade.= '"0"';
            }
            $listMade .= ']';
        } else {
            $listMade = "0";
        }

        $replacements = array("listMade" => $listMade);
        $data = array_replace($this->post(), $replacements);

        if($this->user_model->update('task', $params, $data)) {            
            $this->response(array("status" => "true", "msg" => "200"), 200);   
        } else {
            $this->response(array("status" => "false", "msg" => "19"), 200);
        }
    }

    /**
     * Delete task
     */
    function delete_task_post()
    {
        if($this->user_model->delete('task', array('taskID' => $this->post("taskID")))) {
            $this->response(array("status" => "true", "msg" => "200"), 200);
        } else {
            $this->response(array("status" => "false", "msg" => "13"), 200);
        }
    }

    /**
     * Get user tasks
     */
    function get_task_get()
    {
        $result = $this->user_model->get_all('task', array('userID' => $this->get("userID")));

        $user = $this->user_model->get('user', array('userID' => $this->get("userID")));

        if($user != false) {
            $photo = $user->photo;
        } else {
            $photo = "0";
        }
        
        if($result != false) {

            $response = null;
            
            foreach ($result as $item) {

                if($item->list != '0') {
                    $symbols = array("[", "]", "\"");
                    $str = str_replace($symbols, "", $item->list);
                    $array = explode(",", $str);                
                    $list = $array;
                } else {
                    $list = array();
                }

                if($item->listMade != '0') {
                    $symbols = array("[", "]", "\"");
                    $str = str_replace($symbols, "", $item->listMade);
                    $array = explode(",", $str);                
                    $listMade = $array;
                } else {
                    $listMade = array();
                }

                $response[] = array(
                    'taskID' => $item->taskID,
                    'userID' => $item->userID,
                    'description' => $item->description,
                    'list' => $list,
                    'listMade' => $listMade,
                    'date' => $item->date,
                    'dateMade' => $item->dateMade,
                    'status' => $item->status,
                    'title' => $item->title,
                    'isImportant' => $item->isImportant,   
                    'authorID' => $item->authorID,
                    'isLike' => $item->isLike,             
                );
            }    

            $this->response(array("status" => "true", "msg" => "200", "photo" => $photo, "userTask" => $response), 200);
        } else {
            $this->response(array("status" => "true", "msg" => "200", "photo" => $photo, "userTask" => array()), 200);
        }
    }
	
    /**
     * Get personal data for user
     */
    function get_user_get()
    {
        $result = $this->user_model->get('user', array('userID' => $this->get("userID")));

        $userTask = $this->user_model->get_all('task', array('userID' => $this->get("userID")));

        if($userTask == false) {
            $userTask = 0;
        } else {
            $userTask = count($userTask);
        }
        
        if($result->pairID == 0) {
            $replacements = array("userTask" => $userTask, "pairTask" => 0);
        } else {
            $pairTask = $this->user_model->get_all('task', array('userID' => $result->pairID));

            if($pairTask == false) {
                $pairTask = 0;
            } else {
                $pairTask = count($pairTask);
            }

            $replacements = array("userTask" => $userTask, "pairTask" => $pairTask);
        }
        
        $result = array_replace((array)$result, $replacements);

        if(!empty($result)) {
            $this->response(array("status" => "true", "msg" => "200", "userData" => $result), 200);
        } else {
            $this->response(array("status" => "false", "msg" => "17", "userData" => array()), 200);
        }
    }

    /**
     * Get statistics
     */
    function get_statistics_get()
    {
        $pair = $this->user_model->get('user', array('userID' => $this->get("userID")));

        // Not fulfilled
        $NotData = $this->user_model->get_all('task', array('status' => 0, 'userID' => $this->get('userID')));
        if($NotData == false) {
            $NotFulfilledUser = 0;
        } else {
            $NotFulfilledUser = count($NotData);
        }
        $notDataPair = $this->user_model->get_all('task', array('status' => 0, 'userID' => $pair->pairID));
        if($notDataPair == false) {
            $NotFulfilledPair = 0;
        } else {
            $NotFulfilledPair = count($notDataPair);
        }
        // end

        // made
        $madeDataUser = $this->user_model->get_all('task', array('status' => 1, 'userID' => $this->get('userID')));
        if($madeDataUser == false) {
            $madeUser = 0;
        } else {
            $madeUser = count($madeDataUser);
        }
        $madeDataPair = $this->user_model->get_all('task', array('status' => 1, 'userID' => $pair->pairID));
        if($madeDataPair == false) {
            $madePair = 0;
        } else {
            $madePair = count($madeDataPair);
        }
        // end

        // like
        $likeDataUser = $this->user_model->get_all('likes', array('userID' => $this->get('userID')));
        if($likeDataUser == false) {
            $likeUser = 0;
        } else {
            $likeUser = count($likeDataUser);
        }
        $likeDataPair = $this->user_model->get_all('likes', array('userID' => $pair->pairID));
        if($likeDataPair == false) {
            $likePair = 0;
        } else {
            $likePair = count($likeDataPair);
        }
        // end

        // delay
        $delayData = $this->user_model->get_all('task', array('status' => 1, 'userID' => $this->get('userID')));                
        
        if($delayData == false) {
           $delayUser = 0; 
        } else {
            $delay = 0;
            foreach ($delayData as $item) {
                if($item->date <= $item->dateMade) {
                    $delay++;
                }
            }            
            $delayUser = $delay;
        }

        $delayDataPair = $this->user_model->get_all('task', array('status' => 1, 'userID' => $this->get('pairID')));
        if($delayDataPair == false) {
           $delayPair = 0; 
        } else {
            $delay = 0;
            foreach ($delayData as $item) {
                if($item->date <= $item->dateMade) {
                    $delay++;
                }
            } 
            $delayPair = $delay;
        }
        // end

        if($pair->pairID != 0) {
            $pairPhoto = $this->user_model->get('user', array('userID' => $pair->pairID));
            
            $this->response(array("status" => "true", "msg" => "200", "photo" => $pairPhoto->photo, "name" => $pair->pairName, "arrayMade" => array($madeUser, $madePair), "arrayDelay" => array($delayUser, $delayPair), "arratNotFulfilled" => array($NotFulfilledUser, $NotFulfilledPair), "arrayLike" => array($likePair, $likeUser)), 200);    
        } else {
            $this->response(array("status" => "false", "msg" => "200", "photo" => "", "name" => "", "arrayMade" => array(), "arrayDelay" => array(), "arratNotFulfilled" => array(), "arrayLike" => array()), 200);    
        }        
    }

    /**
     * Send like in task
     */
    function like_post() {
        $like = 0;
        if($this->user_model->check_like('likes', $this->post())) {         
            $this->user_model->delete('likes', $this->post());

            $params = array(
            'taskID' => $this->post('taskID'));

            $data = array(
                'isLike' => $like);

            $this->user_model->update('task', $params, $data);

            $this->response(array("status" => "true", "msg" => "200"), 200);
            $isLike = 0;
            exit();
        } else {
            $like = 1;

            $params = array(
            'taskID' => $this->post('taskID'));

            $data = array(
                'isLike' => $like);

            $this->user_model->update('task', $params, $data);

            if($like = $this->user_model->save('likes', $this->post())) {
                $this->response(array("status" => "true", "msg" => "200"), 200);
            } else {
                $this->response(array("status" => "false", "msg" => "21"), 200);   
            } 
        }         
    }

    /**
     * Send letter for user on email
     *
	 * @param $from ID sender
	 * @param $name Name sender
	 * @param $to ID whom
	 * @param $message Message letter
	 * @param $subject Subject letter
     * @return bool
     */
    private function send_mail($from, $name, $to, $message, $subject) {
        $config['protocol'] = 'sendmail';
        $config['mailpath'] = '/usr/sbin/sendmail';
        $config['charset'] = 'utf-8';
        $config['wordwrap'] = TRUE;
        $config['mailtype'] = 'html';
        $config['useragent'] = 'PairToDo';
        $config['priority'] = '4';

        $this->email->initialize($config);
        
        $this->email->from($from, $name);
        $this->email->to($to);          

        $this->email->subject($subject);
        $this->email->message($message);          

        if($this->email->send()) {
           return true;
        } else {
           return false;
        }
    }

    /**
     * Send push notification to users device
     * 
     * @param $device_token User devive token
     * @param $message Text of message
     */
    private function send_push($device_token, $message) {
        $this->load->library('apn');
        $this->apn->payloadMethod = 'enhance';
        $this->apn->connectToPush();
        
        $this->apn->setData(array( 'someKey' => true ));

        $send_result = $this->apn->sendMessage($device_token, $message, /*badge*/ 1, /*sound*/ 'default'  );

        if($send_result)
            log_message('debug','Отправлено успешно');
        else
            log_message('error',$this->apn->error);

        $this->apn->disconnectPush();
    }

    /**
     * User unsubscribe
     */
    function unsubscribe_get() {       
        $params = array(
            "userID" => $this->get('user'),
            "email" => $this->get('email'),
            );

        $data = array(
            "setEmail" => 0
            );

        if($this->user_model->update('user', $params, $data)) {            
            echo "OK";
        } else {
            echo "ERROR";
        }
    }



    /**
     * Convert task date in unix time in string
     *
     * @param $date Date in unix time
     * @return string $result Convert string
     */
    private function date_to_mail($date, $result = null) {

        if($date == 0) {
            $result = "Без срока";
        } else {
            $result = $date;
        }

        return $result;
    }

    /**
     * Convert task in string format to list
     *
     * @param $inputList Task in string format
     * @return string $result Task list
     */
    private function explode_task_list($inputList) {

    	$list = str_replace("[", "", $inputList);
    	$list = str_replace("]", "", $list);
    	$list = str_replace("\"", "", $list);

    	$newList = explode(",", $list);
    	$result = null;    	
    	for($i = 0; $i < count($newList); $i++) {
    		$result .= $i + 1 . ". " . $newList[$i];
    		if($i < (count($newList) - 1)) {
    			$result .= ", ";
    		}
    	}  

    	return $result;
    }

    /**
     * Send notifications
     */
    function push_time_get($task_content = null) {
        $to_end = time() - 300;

        if($users = $this->user_model->get_to_end('task')) {            
            foreach ($users as $user) {            
                print_r($user);    
                $params = $this->user_model->get('user', array('userID' => $user->userID));
                    if($params->setPush == 1) {
                        $token = $this->user_model->get('devices', array('userID' => $user->userID));
                        $this->send_push($token->tokenID, 'Срок выполнения дела подходит к концу!');
                    }
            }
        }

        if($users = $this->user_model->get_finish('task')) {            
            foreach ($users as $user) {                
                $params = $this->user_model->get('user', array('userID' => $user->userID));
                    if($params->setPush == 1) {
                        $token = $this->user_model->get('devices', array('userID' => $user->userID));
                        $this->send_push($token->tokenID, 'Срок выполнения дела прошел :(');
                    }
                    if($params->setEmail == 1) {
                        $name = $params->name;
                        $userID = $params->userID;
                        $email = $params->email;

                        if($user->description == 0) {
                        	$task_content = $this->explode_task_list($user->list);
                        	  		
                        } else {
                        	$task_content = $user->description;
                        }

                        $message = '<table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-family:Verdana;"><tr><td align="center"><table width="800" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;background:#315568;"><tr style="height:150px;text-align:center;"><td><img src="http://api.pairtodo.com/imgs/logo.png" /></td></tr><tr><td colspan="3" align="center" ><table width="600" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;background:#ffffff;border-radius:6px;min-height:480px;"><tr style="text-align:center;" valign="top"><td><h1 style="color:315568;font-weight:normal;font-size:23px;margin-top:40px;margin-bottom:40px;">'.$name.', у тебя есть <span style="background:#e62b4b;color:#fff;padding-left:2px;padding-right:2px;">просроченная задача</span>,<br/>проверь в приложении:</h1><div style="height:140px;"><p style="color:315568;">'.$task_content.'</p><p><span style="color:315568;font-weight:bold;">Срок:</span> '.$date.'</p></div><p><a href="https://www.facebook.com/pairtodo" target="_blank" style="display:inline-block;width:57px;height:58px;background:url(http://api.pairtodo.com/imgs/fb.png)"></a><a href="https://twitter.com/PairTodo" target="_blank" style="display:inline-block;width:57px;height:58px;background:url(http://api.pairtodo.com/imgs/tw.png)"></a><a href="https://vk.com/pairtodo" target="_blank" style="display:inline-block;width:57px;height:58px;background:url(http://api.pairtodo.com/imgs/vk.png)"></a></p><p style="color:#888888;font-size:12px;">Это приглашение было отправлено от имени '.$name.' (<a href="mailto: '.$email.'" style="color:#888888;">'.$email.'</a>)</p><p style="color:#888888;font-size:12px;">Если у вас есть какие-либо проблемы, предложения или пожелания, пожалуйста, напишите <a href="mailto: support@pairtodo.com" style="color:#888888;">support@pairtodo.com</a></p><p style="color:#888888;font-size:12px;padding-bottom:40px;">Вы можете <a href="http://api.pairtodo.com/user/unsubscribe/user/'.$userID.'/email/'.$email.'" style="color:#888888;">отказаться</a> от этих писем в любое время.</p></td></tr></table></td></tr><tr style="height:80px;"><td colspan="3" align="center"><table width="540" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;"><tr><td style="text-align:center;color:#9dadb5;font-size:13px;">&copy; 2015 <a href="http://pairtodo.com/" style="color:#9dadb5;" target="_blank">ПараДел</a></td></tr></table></td></tr></table></td></tr></table> ';
                        $this->send_mail('support@pairtodo.com', 'ПараДел', $params->email, $message, 'Срок выполнения дела прошел :(');
                    }
            }
        }
        
    }

    
}