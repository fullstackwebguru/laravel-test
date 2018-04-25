<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $fillable = ['name', 'url'];

    /**
     * Get the articles for the blog post.
     */
    public function articles()
    {
        return $this->hasMany('App\Article');
    }
}
