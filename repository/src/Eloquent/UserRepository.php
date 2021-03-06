<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 16-10-24
 * Time: 下午8:42
 */

namespace NEUQOJ\Repository\Eloquent;


use Illuminate\Support\Facades\DB;
use NEUQOJ\Repository\Traits\InsertWithIdTrait;

class UserRepository extends AbstractRepository
{
    function model()
    {
        return "NEUQOJ\Repository\Models\User";
    }

    use InsertWithIdTrait;

    public function deleteWhereIn(string $param,array $data)
    {
        return $this->model->whereIn($param,$data)->delete();
    }

    public function getRankList(string $startDate="total",int $page,int $size)
    {
        //直接返回总榜单
        if($startDate == "total")
            return $this->model
                ->select('id','name','solved','submit')
                ->orderBy('solved','desc')
                ->skip($size * --$page)
                ->take($size)
                ->get();
        else
        {
            $st = $size * ($page -1);

            $sql = "SELECT users.`id`,`name`,s.`solved`,t.`submit` FROM `users`
                                        right join
                                        (select count(distinct problem_id) solved ,user_id from solutions where created_at>str_to_date('$startDate','%Y-%m-%d') and result=4 group by user_id order by solved desc limit " . $st . ",$size) s on users.id=s.user_id
                                        left join
                                        (select count( problem_id) submit ,user_id from solutions where created_at>str_to_date('$startDate','%Y-%m-%d') group by user_id order by submit desc limit " .$st. ",".($size*2).") t on users.id=t.user_id
                                ORDER BY s.`solved` DESC,t.submit,created_at  LIMIT  0,50
                         ";

            return DB::select(DB::raw($sql));
        }
    }

}