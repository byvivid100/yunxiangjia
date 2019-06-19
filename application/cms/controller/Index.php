<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/18
 * Time: 17:32
 */

namespace app\cms\controller;


use think\Controller;

class Index extends Controller
{
    public function index()
    {
        return $this->fetch();
    }
}