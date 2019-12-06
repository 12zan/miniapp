<?php

namespace App\Repositories;

class BaseRepository
{
    /**
     * 缓存时间，单位分
     */
    protected $cacheMinutes;

    /**
     * @param $id
     */
    public function find($id)
    {
        return $this->query()->find($id);
    }

     /**
     * @param $id
     */
    public function findOrFail($id)
    {
        return $this->query()->findOrFail($id);
    }
    /**
     * @return mixed
     */
    public function query()
    {
        return call_user_func(static::MODEL.'::query');
    }

    /**
     * 获取对应的 Model 表名.
     *
     * @return void
     */
    public function getTable()
    {
        return $this->query()->getModel()->getTable();
    }

}
