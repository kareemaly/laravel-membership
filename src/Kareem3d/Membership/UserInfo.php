<?php namespace Kareem3d\Membership;

use Kareem3d\Eloquent\Model;

class UserInfo extends Model {

    /**
     * @var string
     */
    protected $table = 'ka_user_info';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $with = array('account', 'contacts');

    /**
     * @var array
     */
    protected $appends = array('name');

    /**
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * @param $name
     */
    public function setNameAttribute($name)
    {
        $pieces = explode(' ', $name);

        $this->first_name = isset($pieces[0]) ? $pieces[0] : '';
        $this->last_name  = isset($pieces[1]) ? $pieces[1] : '';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function account()
    {
        return $this->hasOne(Account::getClass());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {
        return $this->hasMany(UserContact::getClass(), 'user_info_id');
    }

}