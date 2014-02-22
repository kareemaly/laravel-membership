<?php namespace Kareem3d\Membership;

use Kareem3d\Eloquent\Model;

class Role extends Model {

    /**
     * @var string
     */
    protected $table = 'ka_roles';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $rules = array(
        'type' => 'required|unique:ka_roles'
    );

    /**
     * @param $type
     * @return bool|\Illuminate\Database\Eloquent\Model|static
     */
    public static function makeSureRoleExists($type)
    {
        return static::where('type', $type)->count() > 0 ? true : static::create(compact('type'));
    }

    /**
     * @param $type
     * @return mixed
     */
    public static function getByType($type)
    {
        return static::where('type', $type)->first();
    }

    /**
     * @param $type
     * @return bool
     */
    public static function noOneExistsIn($type)
    {
        $role = static::getByType($type);

        return $role->accounts()->count() == 0;
    }

    /**
     * @param Account $account
     */
    public function addAccountForever(Account $account)
    {
        $account->addRoleForever($this);
    }

    /**
     * @param Account $account
     * @param $from_date
     * @param $to_date
     */
    public function addAccount(Account $account, $from_date, $to_date)
    {
        $account->addRole($this, $from_date, $to_date);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function accounts()
    {
        return $this->belongsToMany(Account::getClass(), 'ka_account_role', 'role_id', 'account_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requests()
    {
        return $this->hasMany(RoleRequest::getClass(), 'role_id');
    }
}