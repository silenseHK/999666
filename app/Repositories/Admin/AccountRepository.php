<?php


namespace App\Repositories\Admin;


use App\Models\Cx_User;
use App\Repositories\BaseRepository;

class AccountRepository extends BaseRepository
{
    private $Cx_User;

    public function __construct(Cx_User $cx_User)
    {
        $this->Cx_User = $cx_User;
    }

    public function findAll($offset, $limit)
    {
        return $this->Cx_User->where("is_customer_service", 1)->select(["id", "phone", "nickname", "reg_time", "code", "whats_app_account", "whats_app_link"])->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countAll()
    {
        return $this->Cx_User->where("is_customer_service", 1)->count("id");
    }

    public function findById($id)
    {
        return $this->Cx_User->where("id", $id)->where("is_customer_service", 1)->select(["id", "phone", "nickname", "reg_time", "code", "whats_app_account", "whats_app_link"])->first();
    }

    public function getCode()
    {
        $code = $this->CreateCode();
        //把接收的邀请码再次返回给模型
        if ($this->recode($code)) {
            //不重复 返回验证码
            return $code;
        } else {
            //重复 再次生成
            while (true) {
                $this->getcode();
            }
        }
    }

    public function findByPhone($phone)
    {
        return $this->Cx_User->where("phone", $phone)->first();
    }

    public function CreateCode()
    {
        $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $rand = $code[rand(0, 25)]
            . strtoupper(dechex(date('m')))
            . date('d') . substr(time(), -5)
            . substr(microtime(), 2, 5)
            . sprintf('%02d', rand(0, 99));
        for (
            $a = md5($rand, true),
            $s = '0123456789ABCDEFGHIJKLMNOPQRSTUV',
            $d = '',
            $f = 0;
            $f < 6;
            $g = ord($a[$f]),
            $d .= $s[($g ^ ord($a[$f + 8])) - $g & 0x1F],
            $f++
        ) ;
        return $d;
    }

    public function recode($code)
    {
        $count = $this->Cx_User->where("code", $code)->count();
        if ($count > 0) {
            return false;
        }
        return true;
    }

    public function addAccount($data)
    {
        return $this->Cx_User->insertGetId($data);
    }

    public function editAccount($data)
    {
        return $this->Cx_User->where("id", $data["id"])->update($data);
    }

    public function delAccount($id)
    {
        return $this->Cx_User->where("id", $id)->delete();
    }

    public function searchAccount($where, $offset, $limit)
    {
        return $this->Cx_User->where(function ($query) use ($where) {
            if (array_key_exists("nickname", $where) && $where["nickname"]) {
                $query->where("nickname", "like", "%" . $where["nickname"] . "%");
            }
            if (array_key_exists("phone", $where) && $where["phone"]) {
                $query->where("phone", "like", "%" . $where["phone"] . "%");
            }
        })->where("is_customer_service", 1)->select(["id", "phone", "nickname", "reg_time", "code", "whats_app_account", "whats_app_link"])->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countSearchAccount($where)
    {
        return $this->Cx_User->where(function ($query) use ($where) {
            if (array_key_exists("nickname", $where) && $where["nickname"]) {
                $query->where("nickname", "like", "%" . $where["nickname"] . "%");
            }
            if (array_key_exists("phone", $where) && $where["phone"]) {
                $query->where("phone", "like", "%" . $where["phone"] . "%");
            }
        })->where("is_customer_service", 1)->count("id");
    }
}
