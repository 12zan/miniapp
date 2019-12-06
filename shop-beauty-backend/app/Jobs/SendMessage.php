<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\TemplateSend;

class SendMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $rid;
    public $openid;
    public $type;
    public $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($rid, $openid, $type, $data)
    {
        $this->rid    = $rid;
        $this->openid = $openid;
        $this->type   = $type;
        $this->data   = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new TemplateSend)->handle($this->rid, $this->openid, $this->type, $this->data);
    }
}
