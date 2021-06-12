<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Api extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Api_model');
    }

    public function signup_post() {
        $data = $this->Api_model->signup();

        if ( $data['result'] ) {
            $response['data'] = $data['iduser'];
            $response['status'] = 'success';
            $response['message'] = 'Registration successful';
            $this->set_response($response, REST_Controller::HTTP_OK);
        } else {
            $response['status'] = 'failed';
            $response['message'] = 'Registration failed';
            $this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function check_email_post() {
        $length = $this->Api_model->check_email()->num_rows();
        $data = $this->Api_model->check_email()->row_array();

        if ( $length == 0 ) {
            $response['status'] = 'yes';
            $response['message'] = 'Email available';
            $this->set_response($response, REST_Controller::HTTP_OK);
        } else {
            $response['status'] = 'no';
            $response['message'] = 'Email already taken';
            $this->set_response($response, REST_Controller::HTTP_OK);
        }
    }

    public function check_username_post() {
        $length = $this->Api_model->check_username()->num_rows();

        if ( $length == 0 ) {
            $response['status'] = 'yes';
            $response['message'] = 'Username available';
            $this->set_response($response, REST_Controller::HTTP_OK);
        } else {
            $response['status'] = 'no';
            $response['message'] = 'Username already taken';
            $this->set_response($response, REST_Controller::HTTP_OK);
        }
    }

    public function signin_post() {
        $form_data = json_decode(file_get_contents("php://input"));
        $username = $form_data->username;
        $password = sha1($form_data->password);

        $length = $this->Api_model->signin($username,$password)->num_rows();
        $data = $this->Api_model->signin($username,$password)->row_array();
        
        if ( $length > 0 ) {
            $tokenData = array();
            $tokenData['iduser'] = $data['id_user'].'@'.now();
            $token = AUTHORIZATION::generateToken($tokenData);
            $status = 'success';
            $message = 'Login successful';

            $response['username'] = $data['username'];
            $response['jwt'] = $token;
            $response['status'] = $status;
            $response['message'] = $message;
            $this->set_response($response, REST_Controller::HTTP_OK);
        } else {
            $status = 'failed';
            $message = 'Invalid username or password';

            $response['status'] = $status;
            $response['message'] = $message;
            $this->set_response($response, REST_Controller::HTTP_OK);
        }
    }

    public function load_profile_post() {
        $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {

            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);

            if ( $decodedToken != false ) {
                $data = $this->Api_model->load_profile()->result();
                $response['data'] = $data;
                $response['status'] = 'success';
                $response['message'] = 'Load profile successful';
                $this->set_response($response, REST_Controller::HTTP_OK);
            } else {
                $response['status'] = 'invalid';
                $response['message'] = 'Token is invalid or has expired';
                $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
            }
        } else {
            $response['status'] = 'unauthorised';
            $response['message'] = 'Missing token';
            $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    public function load_post_profile_post() {
        $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {

            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);

            if ( $decodedToken != false ) {
                $data = $this->Api_model->load_post_profile()->result();
                $response['data'] = $data;
                $response['status'] = 'success';
                $response['message'] = 'Load post profile successful';
                $this->set_response($response, REST_Controller::HTTP_OK);
            } else {
                $response['status'] = 'invalid';
                $response['message'] = 'Token is invalid or has expired';
                $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
            }
        } else {
            $response['status'] = 'unauthorised';
            $response['message'] = 'Missing token';
            $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    public function save_post_post() {
        $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {

            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);

            if ( $decodedToken != false ) {
                $tokenData = $decodedToken->iduser;
                $split = explode('@',$tokenData);
                $id = $split[0];
                
                $data = $this->Api_model->save_post($id);

                if ( $data ) {
                    $response['status'] = 'success';
                    $response['message'] = 'Save post successful';
                    $this->set_response($response, REST_Controller::HTTP_OK);
                } else {
                    $response['status'] = 'failed';
                    $response['message'] = 'Save post failed';
                    $this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                $response['status'] = 'invalid';
                $response['message'] = 'Token is invalid or has expired';
                $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
            }
        } else {
            $response['status'] = 'unauthorised';
            $response['message'] = 'Missing token';
            $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    public function load_posts_post() {
        $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {

            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);

            if ( $decodedToken != false ) {
                $tokenData = $decodedToken->iduser;
                $split = explode('@',$tokenData);
                $id = $split[0];

                $data = $this->Api_model->load_post($id)->result();
                $response['data'] = $data;
                $response['status'] = 'success';
                $response['message'] = 'Load post successful';
                $this->set_response($response, REST_Controller::HTTP_OK);
            } else {
                $response['status'] = 'invalid';
                $response['message'] = 'Token is invalid or has expired';
                $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
            }
        } else {
            $response['status'] = 'unauthorised';
            $response['message'] = 'Missing token';
            $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    public function love_post_post() {
        $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {

            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);

            if ( $decodedToken != false ) {
                $tokenData = $decodedToken->iduser;
                $split = explode('@',$tokenData);
                $id = $split[0];
                
                $data = $this->Api_model->love_post($id);

                if ( $data ) {
                    $response['status'] = 'success';
                    $response['message'] = 'Love post successful';
                    $this->set_response($response, REST_Controller::HTTP_OK);
                } else {
                    $response['status'] = 'failed';
                    $response['message'] = 'Love post failed';
                    $this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                $response['status'] = 'invalid';
                $response['message'] = 'Token is invalid or has expired';
                $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
            }
        } else {
            $response['status'] = 'unauthorised';
            $response['message'] = 'Missing token';
            $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    public function unlove_post_post() {
        $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {

            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);

            if ( $decodedToken != false ) {
                $tokenData = $decodedToken->iduser;
                $split = explode('@',$tokenData);
                $id = $split[0];
                
                $data = $this->Api_model->unlove_post($id);

                if ( $data ) {
                    $response['status'] = 'success';
                    $response['message'] = 'Unlove post successful';
                    $this->set_response($response, REST_Controller::HTTP_OK);
                } else {
                    $response['status'] = 'failed';
                    $response['message'] = 'Unlove post failed';
                    $this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                $response['status'] = 'invalid';
                $response['message'] = 'Token is invalid or has expired';
                $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
            }
        } else {
            $response['status'] = 'unauthorised';
            $response['message'] = 'Missing token';
            $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    public function load_comment_post() {
        $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {

            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);

            if ( $decodedToken != false ) {
                $data = $this->Api_model->load_comment()->result();
                $response['data'] = $data;
                $response['status'] = 'success';
                $response['message'] = 'Load comment successful';
                $this->set_response($response, REST_Controller::HTTP_OK);
            } else {
                $response['status'] = 'invalid';
                $response['message'] = 'Token is invalid or has expired';
                $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
            }
        } else {
            $response['status'] = 'unauthorised';
            $response['message'] = 'Missing token';
            $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    public function save_comment_post() {
        $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {

            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);

            if ( $decodedToken != false ) {
                $tokenData = $decodedToken->iduser;
                $split = explode('@',$tokenData);
                $id = $split[0];
                
                $data = $this->Api_model->save_comment($id);

                if ( $data ) {
                    $response['status'] = 'success';
                    $response['message'] = 'Save comment successful';
                    $this->set_response($response, REST_Controller::HTTP_OK);
                } else {
                    $response['status'] = 'failed';
                    $response['message'] = 'Save comment failed';
                    $this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                $response['status'] = 'invalid';
                $response['message'] = 'Token is invalid or has expired';
                $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
            }
        } else {
            $response['status'] = 'unauthorised';
            $response['message'] = 'Missing token';
            $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    public function load_lovers_post() {
        $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {

            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);

            if ( $decodedToken != false ) {
                $data = $this->Api_model->load_lovers()->result();
                $response['data'] = $data;
                $response['status'] = 'success';
                $response['message'] = 'Load lovers successful';
                $this->set_response($response, REST_Controller::HTTP_OK);
            } else {
                $response['status'] = 'invalid';
                $response['message'] = 'Token is invalid or has expired';
                $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
            }
        } else {
            $response['status'] = 'unauthorised';
            $response['message'] = 'Missing token';
            $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    public function save_avatar_post() {
        $data = json_decode(file_get_contents("php://input"));
        $iduser = $data->iduser;
        $username = $data->username;

        $data = $this->Api_model->save_avatar();

        if ( $data ) {
            $tokenData = array();
            $tokenData['iduser'] = $iduser.'@'.now();
            $token = AUTHORIZATION::generateToken($tokenData);

            $response['username'] = $username;
            $response['jwt'] = $token;
            $response['status'] = 'success';
            $response['message'] = 'Save avatar successful';
            $this->set_response($response, REST_Controller::HTTP_OK);
        } else {
            $response['status'] = 'failed';
            $response['message'] = 'Save avatar failed';
            $this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function load_suggest_post() {
        $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {

            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);

            if ( $decodedToken != false ) {
                $tokenData = $decodedToken->iduser;
                $split = explode('@',$tokenData);
                $id = $split[0];

                $data = $this->Api_model->load_suggest($id)->result();
                $response['data'] = $data;
                $response['status'] = 'success';
                $response['message'] = 'Load suggest successful';
                $this->set_response($response, REST_Controller::HTTP_OK);
            } else {
                $response['status'] = 'invalid';
                $response['message'] = 'Token is invalid or has expired';
                $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
            }
        } else {
            $response['status'] = 'unauthorised';
            $response['message'] = 'Missing token';
            $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    public function follow_post() {
        $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {

            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);

            if ( $decodedToken != false ) {
                $tokenData = $decodedToken->iduser;
                $split = explode('@',$tokenData);
                $id = $split[0];
                
                $data = $this->Api_model->follow($id);

                if ( $data ) {
                    $response['status'] = 'success';
                    $response['message'] = 'Follow successful';
                    $this->set_response($response, REST_Controller::HTTP_OK);
                } else {
                    $response['status'] = 'failed';
                    $response['message'] = 'Follow failed';
                    $this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                $response['status'] = 'invalid';
                $response['message'] = 'Token is invalid or has expired';
                $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
            }
        } else {
            $response['status'] = 'unauthorised';
            $response['message'] = 'Missing token';
            $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    public function unfollow_post() {
        $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {

            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);

            if ( $decodedToken != false ) {
                $tokenData = $decodedToken->iduser;
                $split = explode('@',$tokenData);
                $id = $split[0];
                
                $data = $this->Api_model->unfollow($id);

                if ( $data ) {
                    $response['status'] = 'success';
                    $response['message'] = 'Unfollow successful';
                    $this->set_response($response, REST_Controller::HTTP_OK);
                } else {
                    $response['status'] = 'failed';
                    $response['message'] = 'Unfollow failed';
                    $this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                $response['status'] = 'invalid';
                $response['message'] = 'Token is invalid or has expired';
                $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
            }
        } else {
            $response['status'] = 'unauthorised';
            $response['message'] = 'Missing token';
            $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

}