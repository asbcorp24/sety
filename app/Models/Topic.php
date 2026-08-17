<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Topic extends Model {
 use HasFactory;
 protected $fillable = ['title','slug','module','excerpt','content','html_path','sort_order','is_published'];
 protected $casts = ['is_published'=>'boolean'];
 public function getRouteKeyName() { return 'slug'; }
 public function hasPracticeHtml(): bool { return !empty($this->html_path); }
}
