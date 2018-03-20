<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    const CREATED_AT = 'createtime';
    const UPDATED_AT = 'updatetime';
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $table = 'product';
    protected $primaryKey = 'id_product';
    
    protected $fillable = [
        'code', 'name', 'brand', 'channel',  'shop', 'year', 'colorcode', 'color', 'price', 'spec_size', 'remain_num'
    ];

    const CHANNEL_DEFAULT = 'default';
    const CHANNEL_PROXY = 'proxy';
    
    public function ProductInouts()
    {
        return $this->hasMany('App\ProductInout');
    }
    
    public function getChanneltxt()
    {
        if($this->channel == self::CHANNEL_DEFAULT){
            return '默认';
        }else if($this->channel == self::CHANNEL_PROXY){
            return '代销';
        }
    }
}
