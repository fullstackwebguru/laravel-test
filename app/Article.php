<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'articles';
    protected $fillable = ['category_id', 'title'];

    /**
     * Get the category that has this article
     */
    public function category()
    {
        return $this->belongsTo('App\Category');
    }
}
