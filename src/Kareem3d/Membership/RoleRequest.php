<?php namespace Kareem3d\Membership;

use Kareem3d\Eloquent\Model;

class RoleRequest extends Model {

    const PENDING = 'pending';
    const ACCEPTED = 'accepted';
    const REFUSED = 'refused';

    /**
     * @var string
     */
    protected $table = 'ka_role_requests';

    /**
     * Accept Request
     */
    public function accept()
    {
        $this->status = static::ACCEPTED;

        $this->save();
    }

    /**
     * Accept Request
     */
    public function refuse()
    {
        $this->status = static::REFUSED;

        $this->save();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::getClass(), 'role_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::getClass(), 'account_id');
    }

}