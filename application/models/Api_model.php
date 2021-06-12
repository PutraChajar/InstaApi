<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Api_model extends CI_Model {

    public function signup() {
        $data = json_decode(file_get_contents("php://input"));
        $email = $data->email;
        $fullname = $data->fullname;
        $username = $data->username;
        $password = sha1($data->password);

        $sql =  "
                    select concat ( 
                                'US' ,
                                date_format(current_timestamp,'%y') ,
                                lpad((ifnull(max(substring(id_user, 5, 6)),0)+1), 6, '0')
                            ) as id_user 
                    from user
                ";
        $exe = $this->db->query($sql);
        $result = $exe->row_array();
        $id_user = $result["id_user"];

        $sql = 	"
                    insert into user (id_user , username , password , email , name, date_register)
                    values ('".$id_user."' , '".$username."' , '".$password."' , '".$email."' , '".$fullname."' , sysdate());
                ";        
        $exe = $this->db->query($sql);

        $result = ( $exe ? true : false );
        $response['result'] = $result;
        $response['iduser'] = $id_user;
		return $response;
    }

    public function check_email() {
        $data = json_decode(file_get_contents("php://input"));
        $email = $data->email;

        $sql =  "
                    select id_user, email
                    from user
                    where email = '".$email."'
                ";
        $exe = $this->db->query($sql);
        return $exe;
    }

    public function check_username() {
        $data = json_decode(file_get_contents("php://input"));
        $username = $data->username;
        $sql =  "
                    select username
                    from user
                    where upper(username) = upper('".$username."')
                ";
        $exe = $this->db->query($sql);
        return $exe;
    }

    public function signin($username,$password) {
        $sql =  "
                    select id_user, username
                    from user
                    where (upper(username) = upper('".$username."') or lower(email) = lower('".$username."'))
                    and password = '".$password."'
                ";
        $exe = $this->db->query($sql);
        return $exe;
    }

    public function load_profile() {
        $data = json_decode(file_get_contents("php://input"));
        $username = $data->username;
        $sql =  "
                    select id_user, username, email, name, photo,
                    ( select ifnull(sum(1),0)
                      from followers
                      where id_user = a.id_user
                    ) follower,
                    ( select ifnull(sum(1),0)
                      from followers
                      where follower = a.id_user
                    ) following,
                    ( select ifnull(sum(1),0)
                      from post
                      where id_user = a.id_user
                    ) posts
                    from user a
                    where username = '".$username."'
                    limit 1
                ";
        $exe = $this->db->query($sql);
        return $exe;
    }

    public function load_post_profile() {
        $data = json_decode(file_get_contents("php://input"));
        $username = $data->username;
        $sql =  "
                select id_post, photo, caption,
                ( select ifnull(sum(1),0)
                  from love
                  where id_post = a.id_post
                ) love,
                ( select ifnull(sum(1),0)
                  from `comment`
                  where id_post = a.id_post
                ) comment
                from post a
                where id_user = (select id_user from user where username = '".$username."')
                order by date_post desc
                ";
        $exe = $this->db->query($sql);
        return $exe;
    }

    public function save_post($id) {
        $data = json_decode(file_get_contents("php://input"));
        $photo = $data->photo;
        $ext = $data->ext;
        $caption = $data->caption;

        $sql =  "
                    select concat ( 
                                'PS' ,
                                date_format(current_timestamp,'%y') ,
                                lpad((ifnull(max(substring(id_post, 5, 6)),0)+1), 6, '0')
                            ) as id_post 
                    from post
                ";
        $exe = $this->db->query($sql);
        $result = $exe->row_array();
        $id_post = $result["id_post"];

        $name_photo = $id_post . '.' . $ext;

        $sql = 	"   
                    insert into post (id_post, id_user, photo, caption, date_post)
                    values ('".$id_post."' , '".$id."' , '".$name_photo."' , '".$caption."' , sysdate());
                ";
        $exe = $this->db->query($sql);
        
        if ($exe) {
            $location = FCPATH . 'assets/images/post/';
            $image_base64 = base64_decode($photo);
            $file = $location . $name_photo;
            $save = file_put_contents($file, $image_base64);
        }

        $result = ( $save ? true : false );
		return $result;
    }

    public function load_post($id) {
        $sql =  "
                    select id_post, id_user, photo, caption, date_format(date_post, '%M %d') date_post,
                    ( select photo
                      from `user`
                      where id_user = a.id_user
                    ) photo_profile,
                    ( select username
                      from `user`
                      where id_user = a.id_user
                    ) username,
                    ( select ifnull(sum(1),0)
                      from love
                      where id_post = a.id_post
                    ) love,
                    ( select ifnull(sum(1),0)
                      from `comment`
                      where id_post = a.id_post
                    ) comment,
                    ( select ifnull(sum(1),0)
                      from love
                      where id_post = a.id_post
                      and id_user = '".$id."'
                    ) liked
                    from post a
                    where id_user = '".$id."'
                    or id_user in (select id_user from followers where follower = '".$id."')
                    order by date_post desc
                ";
        $exe = $this->db->query($sql);
        return $exe;
    }

    public function love_post($id) {
        $data = json_decode(file_get_contents("php://input"));
        $idpost = $data->idpost;

        $sql = 	"   
                    insert into love (id_post, id_user, date_like)
                    values ('".$idpost."' , '".$id."' , sysdate());
                ";
        $exe = $this->db->query($sql);

        $result = ( $exe ? true : false );
		return $result;
    }

    public function unlove_post($id) {
        $data = json_decode(file_get_contents("php://input"));
        $idpost = $data->idpost;

        $sql = 	"   
                    delete from love 
                    where id_post = '".$idpost."'
                    and id_user = '".$id."';
                ";
        $exe = $this->db->query($sql);

        $result = ( $exe ? true : false );
		return $result;
    }

    public function load_comment() {
        $data = json_decode(file_get_contents("php://input"));
        $idpost = $data->idpost;
        
        $sql =  "
                    select id_comment, id_post, id_user, comment, date_comment,
                    ( select username 
                      from `user`
                      where id_user = a.id_user
                    ) username,
                    ( select photo 
                      from `user`
                      where id_user = a.id_user
                    ) photo
                    from `comment` a
                    where id_post = '".$idpost."'
                    order by date_comment desc
                ";
        $exe = $this->db->query($sql);
        return $exe;
    }

    public function save_comment($id) {
        $data = json_decode(file_get_contents("php://input"));
        $idpost = $data->idpost;
        $comment = $data->comment;

        $sql =  "
                    select concat ( 
                                'CM' ,
                                date_format(current_timestamp,'%y') ,
                                lpad((ifnull(max(substring(id_comment, 5, 6)),0)+1), 6, '0')
                            ) as id_comment 
                    from comment
                ";
        $exe = $this->db->query($sql);
        $result = $exe->row_array();
        $id_comment = $result["id_comment"];

        $sql = 	"   
                    insert into comment (id_comment, id_post, id_user, comment, date_comment)
                    values ('".$id_comment."' , '".$idpost."' , '".$id."' , '".$comment."' , sysdate());
                ";
        $exe = $this->db->query($sql);

        $result = ( $exe ? true : false );
		return $result;
    }

    public function load_lovers() {
        $data = json_decode(file_get_contents("php://input"));
        $idpost = $data->idpost;
        
        $sql =  "
                    select id_post, id_user,
                    ( select username 
                      from `user`
                      where id_user = a.id_user
                    ) username,
                    ( select name 
                      from `user`
                      where id_user = a.id_user
                    ) name,
                    ( select photo 
                      from `user`
                      where id_user = a.id_user
                    ) photo
                    from love a
                    where id_post = '".$idpost."'
                    order by date_like desc
                ";
        $exe = $this->db->query($sql);
        return $exe;
    }

    public function save_avatar() {
        $data = json_decode(file_get_contents("php://input"));
        $iduser = $data->iduser;
        $photo = $data->photo;
        $ext = $data->ext;
        $name_photo = $iduser . '.' . $ext;

        $sql = 	"
                    update user set photo = '".$name_photo."'
                    where id_user = '".$iduser."'
                ";        
        $exe = $this->db->query($sql);

        if ($exe) {
            $location = FCPATH . 'assets/images/profile/';
            $image_base64 = base64_decode($photo);
            $file = $location . $name_photo;
            $save = file_put_contents($file, $image_base64);
        }

        $result = ( $save ? true : false );
		return $result;
    }

    public function load_suggest($id) {
        $sql =  "
                    select id_user, username, `name`, photo,
                    ( select count(*)
                      from followers
                      where id_user = a.id_user
                      and follower = '".$id."'
                    ) followed
                    from `user` a
                    where id_user not in (select id_user from followers where follower = '".$id."')
                    and id_user <> '".$id."'
                ";
        $exe = $this->db->query($sql);
        return $exe;
    }

    public function follow($id) {
        $data = json_decode(file_get_contents("php://input"));
        $iduser = $data->iduser;

        $sql = 	"   
                    insert into followers (id_user, follower)
                    values ('".$iduser."' , '".$id."');
                ";
        $exe = $this->db->query($sql);

        $result = ( $exe ? true : false );
		return $result;
    }

    public function unfollow($id) {
        $data = json_decode(file_get_contents("php://input"));
        $iduser = $data->iduser;

        $sql = 	"   
                    delete from followers 
                    where id_user = '".$iduser."'
                    and follower = '".$id."';
                ";
        $exe = $this->db->query($sql);

        $result = ( $exe ? true : false );
		return $result;
    }

}