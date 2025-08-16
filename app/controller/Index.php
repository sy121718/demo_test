<?php
declare (strict_types=1);

namespace app\controller;
use think\Exception;
use think\facade\Db;
use think\helper\Arr;
use app\BaseController;
use think\exception\ValidateException;
use app\validate\UserValidate;
use app\model\system\SysUserModel;
use think\Request;

class Index extends BaseController
{
    public function index(Request $request)
    {

        // 在中间件或某个方法中存储

// 在另一个方法中获取





    }

    public function demo(Request $request)
    {
        $a = app('user.demo');
        dump($a); // 输出: 23213123
    }
   
}


