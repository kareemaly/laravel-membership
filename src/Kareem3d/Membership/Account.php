<?php namespace Kareem3d\Membership;

use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Auth\UserInterface;
use Illuminate\Hashing\BcryptHasher;
use Kareem3d\Eloquent\Model;

class Account extends Model implements UserInterface, RemindableInterface  {

    /**
     * One request online seconds
     */
    const REQUEST_ONLINE_SECONDS = 20;

    /**
     * @var string
     */
    protected $table = 'ka_accounts';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array('password');

    /**
     * @var array
     */
    protected $extensions = array('Images');

    /**
     * Validation rules
     *
     * @var array
     */
    protected $rules = array(
        'username' => 'min:6|unique:ka_accounts',
        'email'    => 'required|email|unique:ka_accounts',
        // Medium password strength is required
        'password' => 'required|regex:((?=.*\d)(?=.*[a-z]).{8,20})',
    );

    /**
     * @var array
     */
    protected $customMessages = array(
        'username.min' => 'Username is too short',
        'username.unique' => 'This username already exists',
        'email.required' => 'Email is required',
        'email.email' => 'Email format is invalid',
        'email.unique' => 'This email already exists',
        'password.required' => 'Password is required',
        'password.regex' => 'Password strength is very week'
    );

    /**
     * @param $email
     * @return Account
     */
    public static function getByEmail( $email )
    {
        return static::where('email', $email)->first();
    }

    /**
     * @param $role
     * @return mixed
     */
    protected function makeSureItsRule($role)
    {
        return $role instanceof Role ? $role : Role::getByType($role);
    }

    /**
     * @param $role
     * @return void
     */
    public function addRoleForever($role)
    {
        if(! $role = $this->makeSureItsRule($role)) return;

        $this->addRole($role, date('Y-m-d H:i:s'), 'forever');
    }

    /**
     * @param Role $role
     * @param $from_date
     * @param $to_date
     * @return void
     */
    public function addRole($role, $from_date, $to_date)
    {
        if(! $role = $this->makeSureItsRule($role)) return;

        if($this->roleExists($role))
        {
            $this->updateRole($role, $from_date, $to_date);
        }

        else
        {
            $this->roles()->attach($role->getKey(), $this->convertDates(compact('from_date', 'to_date')));
        }
    }

    /**
     * @param $role
     * @param $from_date
     * @param $to_date
     */
    public function updateRole($role, $from_date, $to_date)
    {
        if(! $role = $this->makeSureItsRule($role)) return;

        return $this->roles()->join('ka_roles', 'ka_roles.id', '=', 'ka_account_role.role_id')
                             ->where('ka_roles.id', $role->getKey())
                             ->update($this->convertDates(compact('from_date', 'to_date')));
    }

    /**
     * @param $role
     */
    public function removeRole($role)
    {
        if(! $role = $this->makeSureItsRule($role)) return;

        $this->roles()->detach($role->getKey());
    }

    /**
     * @param $role
     * @return bool
     */
    public function roleExists(Role $role)
    {
        return $role->accounts()->where('role_id', $role->id)->count() > 0;
    }

    /**
     * @param $type
     * @return bool
     */
    public function hasRole($type)
    {
        $now = date('Y-m-d H:i:s', strtotime('now'));

        // We check if the required role does exists and hasn't expired for this user
        return $this->roles()
            ->join('ka_roles', 'ka_roles.id', '=', 'ka_account_role.role_id')
            ->wherePivot('from_date', '<=', $now)
            ->wherePivot('to_date', '>=', $now)
            ->where('ka_roles.type', $type)->count() > 0;
    }

    /**
     * @param $role
     * @throws RoleNotFoundException
     */
    public function failIfNoRole( $role )
    {
        if(! $this->hasRole($role))
        {
            throw new RoleNotFoundException($role);
        }
    }

    /**
     * @param $type
     * @return RoleRequest
     * @todo Use a one select query
     */
    public function getRequested($type)
    {
        foreach($this->roleRequests()->with('role')->get() as $roleRequest)
        {
            if($roleRequest->role->type === $type)
            {
                return $roleRequest;
            }
        }
    }

    /**
     * @param $role
     * @param $description
     * @param $status
     */
    public function addRoleRequest($role, $description = '', $status = RoleRequest::PENDING)
    {
        if(! $role = $this->makeSureItsRule($role)) return;

        $this->roleRequests()->create(array(
            'role_id'    => $role->id,
            'account_id' => $this->id,

            'description' => $description,
            'status'      => $status
        ));
    }

    /**
     * @return \Illuminate\Hashing\HasherInterface
     */
    public static function getHasher()
    {
        return new BcryptHasher();
    }

    /**
     * @param UserInfo $userInfo
     * @return \Kareem3d\Membership\UserInfo
     */
    public function setInfo( $userInfo )
    {
        // If user info given is array then create new userinfo with the given array
        if(is_array($userInfo)) $userInfo = UserInfo::create($userInfo);

        $userInfo->save();

        // Then associate this user with the user info
        $this->user_info_id = $userInfo->id;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return (bool) $this->active;
    }

    /**
     * @param string $checkPassword
     * @return bool
     */
    public function checkPassword( $checkPassword )
    {
        return $this->getHasher()->check($checkPassword, $this->password);
    }

    /**
     * @return void
     */
    public function makePassword()
    {
        $this->password = $this->getHasher()->make($this->password);
    }

    /**
     * @return void
     */
    public function beforeSave()
    {
        // If password is dirty which means it did change
        if($this->isDirty('password')) {

            $this->makePassword();
        }
    }

    /**
     * Make this user online
     */
    public function makeOnline()
    {
        $this->online_at = new \DateTime();

        $this->save();
    }

    /**
     * @return string
     */
    public function getOnlineAt()
    {
        return $this->online_at;
    }

    /**
     * Check if last time he has been online is less than constant seconds or not
     *
     * @return bool
     */
    public function isOnline()
    {
        $now = strtotime('now');

        $lastOnline = strtotime($this->getOnlineAt());

        $seconds = $now - $lastOnline;

        return $seconds <= self::REQUEST_ONLINE_SECONDS;
    }

    /**
     * @return void
     */
    public function beforeValidate()
    {
        // Clean all attributes from XSS attack
        $this->cleanXSS();
    }

    /**
     * Validate Strong passwords
     */
    public function validateStrongPassword()
    {
        $this->rules['password'] = 'required|regex:(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$';
    }

    /**
     * @param string $key
     * @return mixed|void
     */
    public function getAttribute($key)
    {
        if (! $value = parent::getAttribute($key))
        {
            if($this->exists)
            {
                return $this->getInfo($key);
            }

            return null;
        }

        return $value;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getInfo($key)
    {
        return $this->info->getAttribute($key);
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the e-mail address where password reminders are sent.
     *
     * @return string
     */
    public function getReminderEmail()
    {
        return $this->email;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function info()
    {
        return $this->belongsTo(UserInfo::getClass(), 'user_info_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Account::getClass(),'ka_account_role', 'account_id', 'role_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function roleRequests()
    {
        return $this->hasMany(RoleRequest::getClass(), 'account_id');
    }
}