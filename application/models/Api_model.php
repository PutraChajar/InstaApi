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
                    SELECT CONCAT ( 
                                'US' ,
                                DATE_FORMAT(CURRENT_TIMESTAMP,'%y') ,
                                LPAD((IFNULL(MAX(SUBSTRING(ID_USER, 5, 6)),0)+1), 6, '0')
                            ) AS ID_USER 
                    FROM USER
                ";
        $exe = $this->db->query($sql);
        $result = $exe->row_array();
        $id_user = $result["ID_USER"];

        $sql = 	"
                    INSERT INTO USER (ID_USER , USERNAME , PASSWORD , EMAIL , NAME, DATE_REGISTER)
                    VALUES ('".$id_user."' , '".$username."' , '".$password."' , '".$email."' , '".$fullname."' , SYSDATE());
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
                    SELECT ID_USER, EMAIL
                    FROM USER
                    WHERE EMAIL = '".$email."'
                ";
        $exe = $this->db->query($sql);
        return $exe;
    }

    public function check_username() {
        $data = json_decode(file_get_contents("php://input"));
        $username = $data->username;
        $sql =  "
                    SELECT USERNAME
                    FROM USER
                    WHERE UPPER(USERNAME) = UPPER('".$username."')
                ";
        $exe = $this->db->query($sql);
        return $exe;
    }

    public function signin($username,$password) {
        $sql =  "
                    SELECT ID_USER, USERNAME
                    FROM USER
                    WHERE (UPPER(USERNAME) = UPPER('".$username."') OR LOWER(EMAIL) = LOWER('".$username."'))
                    AND PASSWORD = '".$password."'
                ";
        $exe = $this->db->query($sql);
        return $exe;
    }

    public function load_profile() {
        $data = json_decode(file_get_contents("php://input"));
        $username = $data->username;
        $sql =  "
                    select id_user, username, email, name, photo,
                    ( select nvl(sum(1),0)
                      from followers
                      where id_user = a.id_user
                    ) follower,
                    ( select nvl(sum(1),0)
                      from followers
                      where follower = a.id_user
                    ) following,
                    ( select nvl(sum(1),0)
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
                ( select nvl(sum(1),0)
                  from love
                  where id_post = a.id_post
                ) love,
                ( select nvl(sum(1),0)
                  from `comment`
                  where id_post = a.id_post
                ) comment
                from post a
                where id_user = (select id_user from user where username = '".$username."')
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
                    SELECT CONCAT ( 
                                'PS' ,
                                DATE_FORMAT(CURRENT_TIMESTAMP,'%y') ,
                                LPAD((IFNULL(MAX(SUBSTRING(ID_POST, 5, 6)),0)+1), 6, '0')
                            ) AS ID_POST 
                    FROM POST
                ";
        $exe = $this->db->query($sql);
        $result = $exe->row_array();
        $id_post = $result["ID_POST"];

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
                    ( select nvl(sum(1),0)
                      from love
                      where id_post = a.id_post
                    ) love,
                    ( select nvl(sum(1),0)
                      from `comment`
                      where id_post = a.id_post
                    ) comment,
                    ( select nvl(count(*),0)
                      from love
                      where id_post = a.id_post
                      and id_user = a.id_user
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
                    SELECT CONCAT ( 
                                'CM' ,
                                DATE_FORMAT(CURRENT_TIMESTAMP,'%y') ,
                                LPAD((IFNULL(MAX(SUBSTRING(ID_COMMENT, 5, 6)),0)+1), 6, '0')
                            ) AS ID_COMMENT 
                    FROM COMMENT
                ";
        $exe = $this->db->query($sql);
        $result = $exe->row_array();
        $id_comment = $result["ID_COMMENT"];

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
                    UPDATE USER SET PHOTO = '".$name_photo."'
                    WHERE ID_USER = '".$iduser."'
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