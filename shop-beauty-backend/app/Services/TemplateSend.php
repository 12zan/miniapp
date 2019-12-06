<?php

namespace App\Services;

use EasyWeChat\Factory as EwFactory;
use App\Repositories\AppRepository;

class TemplateSend
{

    public function handle($rid, $openid, $type, $data)
    {
        $appConfig = app(AppRepository::class)->findOrFailByRid($rid);

        $config = [
            'app_id' => $appConfig->openId,
            'secret' => $appConfig->appSecret
        ];

        $app = EwFactory::miniProgram($config);

        $formId  = \DB::table('form_ids')->where('openid', $openid)
            ->whereNull('used_at')
            ->orderBy('id', 'desc')
            ->first();

        if (empty($formId)) {
            \Log::info('发送模板消息失败,无可用formId', ['openid' => $openid]);
            return true;
        }

        $template = $this->getTemplate($rid, $type);

        if (empty($template)) {
            \Log::info('发送模板消息失败,无可用模板ID', ['rid' => $rid, 'type' => $type]);
            return true;
        }

        $sendData = [
            'touser'      => $openid,
            'template_id' => $template->template_id,
            'page'        => 'pages/orderDetails/orderDetails?orderId='.$data['orderId'],
            'form_id'     => $formId->form_id,
            'data' => [
                'keyword1' => $data['number'], //取餐号
                'keyword2' => '商品', //菜品
                'keyword3' => $data['time'], //下单时间
                'keyword4' => '温馨提示'
            ],
        ];

        if (env('APP_ENV') != 'local') {
            $app->template_message->send($sendData);
        } else {
            \Log::info('模板消息发送成功', $sendData);
        }

        $formId->used_at = app('carbon')->now();
        $formId->save();

        return true;
    }

    public function getTemplate($rid, $type)
    {
        $template = \DB::table('template_message_list')
            ->where(['rid' => $rid, 'type' => $type])
            ->first();

        return $template;
    }

}