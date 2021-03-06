<?php


namespace App\Repositories\Admin;


use App\Models\Cx_User;
use App\Models\Cx_User_Balance_Logs;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class UserRepository extends BaseRepository
{
    private $Cx_User, $Cx_User_Balance_Logs;

    public function __construct(Cx_User $cx_User, Cx_User_Balance_Logs $balance_Logs)
    {
        $this->Cx_User = $cx_User;
        $this->Cx_User_Balance_Logs = $balance_Logs;
    }

    public function findAll($offset, $limit)
    {
        return $this->Cx_User->where("is_customer_service", 0)->orderByDesc("last_time")->offset($offset)->limit($limit)->get()->toArray();
    }

    public function getBalanceLogs($userId, $offset, $limit)
    {
        return $this->Cx_User_Balance_Logs->with(["admin" => function ($query) {
            $query->select(["id", "nickname"]);
        }])->where("user_id", $userId)->offset($offset)->limit($limit)->orderByDesc("time")->get()->toArray();
    }

    public function countBalanceLogs($userId)
    {
        return $this->Cx_User_Balance_Logs->where("user_id", $userId)->count("id");
    }

    public function getRecommenders()
    {
        return $this->Cx_User->orderByDesc("last_time")->select(["id", "nickname"])->get()->toArray();
    }

    public function countAll()
    {
        return $this->Cx_User->where("is_customer_service", 0)->count("id");
    }

    public function findById($id)
    {
        return $this->Cx_User->where("id", $id)->first();
    }

    public function getUserByConditions($data, $offset, $limit)
    {
        return $this->whereCondition($data, $this->Cx_User)->offset($offset)->limit($limit)->orderByDesc("last_time")->get()->toArray();
    }

    public function countUserByConditions($data)
    {
        return $this->whereCondition($data, $this->Cx_User)->count("id");
    }

    public function addUser($data)
    {
        return $this->Cx_User->insertGetId($data);
    }

    public function editUser($data)
    {
        return $this->Cx_User->where("id", $data["id"])->update($data);
    }

    public function delUser($id)
    {
        return $this->Cx_User->where("id", $id)->delete();
    }

    public function batchModifyRemarks(array $ids, string $message)
    {
        return $this->Cx_User->whereIn("id", $ids)->update(["remarks" => $message]);
    }

    public function modifyUserStatus($id, $status)
    {
        return $this->Cx_User->where("id", $id)->update(["status" => $status]);
    }

    public function getCustomerService()
    {
        return $this->Cx_User->where("is_customer_service", 1)->select(["id", "phone"])->get()->toArray();
    }

    public function modifyCustomerService($ids, $customer_id)
    {
        return $this->Cx_User->whereIn("id", $ids)->update(["customer_service_id" => $customer_id]);
    }

    public function modifyEmptyAgent($ids, $data)
    {
        return $this->Cx_User->whereIn("id", $ids)->update($data);
    }

    public function addLogs($data)
    {
        return $this->Cx_User_Balance_Logs->insertGetId($data);
    }
}
