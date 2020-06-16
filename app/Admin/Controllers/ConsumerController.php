<?php

namespace App\Admin\Controllers;

use App\Model\Consumer;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Storage;

class ConsumerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Consumer';

    /**
     * @var string[]
     */
    protected $status = [
        0 => '禁用',
        1 => '正常',
        2 => 'VIP',
    ];

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Consumer());
        $grid->filter(function($filter){

            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('phone', '手机号');
            $filter->like('username', '用户名');
            $filter->between('created_at', '创建时间')->datetime();

        });

        $grid->column('id', __('ID'));
        $grid->column('phone', __('手机号'))->filter('like');
        $grid->column('username', __('用户名'))->filter('like');
        $grid->column('avatar', __('头像'))->image('', 50, 50);
        $grid->column('gold', __('金币'));
        $grid->column('qr_code', __('二维码'))->display(function ($qrCode){
            return env('APP_URL') . '/' . $qrCode;
        })->link()->qrcode(function($qrCode){
            return env('APP_URL') . '/' . $qrCode;
        });
        $grid->column('status', __('状态'))->using($this->status,1)->filter($this->status);
        $grid->column('created_at', __('创建时间'))->filter('range', 'datetime');
        $grid->column('updated_at', __('更新时间'))->filter('range', 'datetime');

        return $grid;
    }

    static function getQrUrl($qrCode, $row){
        return env('APP_URL') . '/' . $qrCode;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Consumer::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('phone', __('手机号'));
        $show->field('username', __('用户名'));
        $show->field('avatar', __('头像'))->image();
        $show->field('gold', __('金币'));
        $show->field('status', __('状态'))->using($this->status);
        $show->field('created_at', __('创建时间'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Consumer());

        $form->mobile('phone', __('手机号'))->rules('required|regex:/^1[345789][0-9]{9}$/')->rules(function ($form) {
            // 如果不是编辑状态，则添加字段唯一验证
            if (!$id = $form->model()->id) {
                return 'unique:consumer';
            }
        });
        $form->text('username', __('用户名'))->rules('required|min:1');
        $form->password('passwd', __('密码'))->rules('required');
        $form->image('avatar', __('头像'))->rules('required');
        $form->number('gold', __('金币'))->rules('required');
        $form->radio('status', __('状态'))->options($this->status)->default(1)->rules('required');

        $form->saving(function (Form $form) {
            if ($form->passwd && $form->model()->passwd != $form->passwd) {
                $form->passwd = md5($form->passwd);
            }
        });

        return $form;
    }
}
