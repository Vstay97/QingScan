<?php


namespace app\controller;


use think\facade\Db;
use think\facade\View;
use think\Request;

class OneForAll extends Common
{
    public function index(Request $request){
        $pageSize = 20;
        $where = [];
        $search = $request->param('search');
        if (!empty($search)) {
            $where[] = ['url','like',"%{$search}%"];
        }
        $app_id = $request->param('app_id');
        if (!empty($app_id)) {
            $where[] = ['app_id','=',$app_id];
        }
        if ($this->auth_group_id != 5 && !in_array($this->userId, config('app.ADMINISTRATOR'))) {
            $where[] = ['user_id', '=', $this->userId];
        }
        $list = Db::table('one_for_all')->where($where)->order("id", 'desc')->paginate([
            'list_rows'=>$pageSize,
            'query'=>$request->param()
        ]);
        $data['list'] = $list->items();
        foreach ($data['list'] as &$v) {
            $v['app_name'] = Db::name('app')->where('id',$v['app_id'])->value('name');
        }
        $data['page'] = $list->render();
        //查询项目数据
        $projectArr = Db::table('app')->where('is_delete',0)->select()->toArray();
        $data['projectList'] = array_column($projectArr, 'name', 'id');
        return View::fetch('index', $data);
    }

    public function del(Request $request)
    {
        $id = $request->param('id');
        $where[] = ['id','=',$id];
        if ($this->auth_group_id != 5 && !in_array($this->userId, config('app.ADMINISTRATOR'))) {
            $where[] = ['user_id', '=', $this->userId];
        }
        if (Db::name('one_for_all')->where('id',$id)->delete()) {
            return redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->error('删除失败');
        }
    }

    // 批量删除
    public function batch_del(Request $request){
        return $this->batch_del_that($request,'one_for_all');
    }
}